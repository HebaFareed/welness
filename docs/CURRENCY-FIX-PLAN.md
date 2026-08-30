# Plan: Fix EGP/USD price–currency mismatch on appointment orders

> **Status:** Implemented (2026-08-30) — phases A–F + review fixes in `woodmart-child/functions.php`
> **Date:** 2026-08-30
> **Scope:** `woodmart-child` (currency/payment logic). No plugin/parent theme edits.
> **Bug:** Some orders charged in a USD amount but labeled EGP (e.g. EGP 60 instead of EGP 1500 / USD 60).

---

## Root cause

The order **amount** is frozen at add-to-cart by the plugin's cost calculation
(`WC_Appointments_Cost_Calculation::calculate_appointment_cost()` →
`functions.php:3404`, `wellness_get_ajax_currency()` / `wellness_get_active_currency()`).
The order **currency label + gateway** are re-resolved at checkout by
`change_woocommerce_currency()` (`functions.php:418`) and
`wellness_filter_gateways_by_currency()` (`functions.php:512`) — a **second**
`WC_Geolocation::geolocate_ip()` call. Two independent geolocation calls can disagree
(geo-IP flake / empty result treated as EGP) → amount = USD value (60) labeled EGP.

Cloudflare amplifies this: `WC_Geolocation` switches between `CF-IPCountry` header, the
client IP from `X-Forwarded-For`, an external geo-IP API, and `REMOTE_ADDR`
(`class-wc-geolocation.php:110-135`, `:241-262`). Behind a proxy the two calls
(add-to-cart vs checkout) can hit different sources and return different countries.

Confirmed with site owner: affected therapist `_staff_currency` = `''` (Location Based),
one staff per product page, initial booking.

---

## Confirmed payment model (location-aware; Paymob does NOT convert)

| Order currency | Payer location | Gateway | Handling |
|---|---|---|---|
| EGP | any | **Paymob** (EGP) | charge EGP as-is |
| USD | Egypt (EG) | **Paymob** (EGP) | convert USD→EGP at a live cached rate, **at order creation only**, then charge EGP |
| USD | NOT Egypt | **Stripe** (USD) | charge USD as-is |

Paymob charges in EGP and does not convert currencies, so USD→EGP conversion is done
internally (open.er-api.com live rate, cached).

---

## Fix strategy

1. Make geolocation **deterministic behind Cloudflare** first (so the payer country is
   correct every time).
2. **Freeze** the resolved currency + payer country at add-to-cart (same moment as the
   price) and persist them on the cart item + appointment.
3. Make the **currency label, gateway, and recurring orders** consume those frozen values.
4. Add a live **USD→EGP rate provider** (cached) and apply the conversion **only at order
   creation** for the USD+Egypt path.
5. Cart/checkout display stays in USD (conversion is not shown to the customer).

---

## Steps

### Phase A — Deterministic geolocation + freeze currency & country at add-to-cart
1. Hook `woocommerce_geolocate_ip` (`class-wc-geolocation.php:155`) to return the country
   from `HTTP_CF_IPCOUNTRY` when present/valid (ISO 3166-1 alpha-2), only trusting it when
   the request is proxied by Cloudflare (check `HTTP_CF_RAY` / `CF-Connecting-IP`) to
   prevent spoofing. Otherwise it returns `false` so WC resolves the real client IP. Note:
   WC 10.5 `get_ip_address()` reads `HTTP_X_REAL_IP` / `HTTP_X_FORWARDED_FOR` / `REMOTE_ADDR`
   (not `CF-Connecting-IP`), so the fallback relies on Cloudflare sending `X-Forwarded-For`.
   Do this first — the freeze depends on its accuracy.
2. Add `wellness_get_location_country()` (wraps `WC_Geolocation::geolocate_ip()`, returns
   `$geo['country']` or `''`); refactor `wellness_get_location_currency()`
   (`functions.php:282`) to use it.
3. Add a child-theme filter on `woocommerce_add_cart_item_data` (priority 20, after the
   plugin's 10) to set `_currency = wellness_get_ajax_currency($product, $_POST)` and
   `_country = wellness_get_location_country()` on `$cart_item_meta['appointment']`.
4. Persist both to the appointment post meta via `_appointment_id` (plugin sets it at
   `includes/class-wc-appointment-cart-manager.php:260`).

### Phase B — Currency label + gateway use frozen values
5. `change_woocommerce_currency()` (`functions.php:418`): prefer
   `$cart_item['appointment']['_currency']`, fall back to live resolution for pre-fix carts.
6. `wellness_filter_gateways_by_currency()` (`functions.php:512`): read frozen
   `_currency` + `_country`; route per the model above (USD+EG → Paymob for conversion;
   USD+non-EG → Stripe; else Paymob). Fall back to live values for pre-fix carts. For a
   standalone pay-for-order page (empty cart, e.g. recurring follow-ups) it routes by the
   order's own currency and best-effort recovers the payer country from `_appointment_id` →
   `_country` appointment meta.

### Phase C — Live USD→EGP rate provider (cached)
7. Add `wellness_get_usd_egp_rate()` (options-based, non-blocking): the rate is stored in
   options `wellness_usd_egp_rate` / `wellness_usd_egp_rate_updated`. A fresh value (<12h) is
   returned; a stale value is returned immediately and a background refresh is scheduled
   (Action Scheduler `wellness_refresh_usd_egp_rate`); if no value exists the fallback
   (`wellness_usd_egp_fallback`) is returned and a refresh scheduled, so checkout never
   blocks on the API. `wellness_prewarm_usd_egp_rate()` on `init` fetches once on a page load
   when empty. Fetches `https://open.er-api.com/v6/latest/USD` → `rates.EGP`.

### Phase D — Convert USD→EGP only at order creation (no cart/display conversion)
8. Hook `woocommerce_checkout_create_order` (+ blocks wrapper on
   `woocommerce_store_api_checkout_order_processed`). When the frozen currency is USD and the
   frozen payer country is EG, re-denominate the order to EGP at this step only: multiply the
   line items (incl. per-rate tax breakdowns), order tax items, and order totals by
   `wellness_get_usd_egp_rate()`, set order currency EGP, so Paymob charges the converted
   EGP amount. On a resumed pending/failed order the guard is state-aware
   (`_usd_to_egp_converted` plus currency already EGP) so it re-converts after USD items are
   re-added. Stores audit meta `_usd_to_egp_converted`, `_usd_to_egp_rate`,
   `_original_currency`, `_original_total`. The cart/checkout display stays in USD.

### Phase E — Recurring follow-up orders
9. `wellness_create_recurring_appointments()` (`functions.php:1019`): inherit parent
   `_currency` / `_country` from appointment/order meta + parent line-item price; only fall
   back to `$product->get_price()` when no parent item. Copy `_currency` / `_country` to
   `$new_appointment_data`; apply the same USD+EG conversion at order creation.

### Phase F — Optional hardening (recommended)
10. Freeze the resolved **country** in the WC session (`wellness_freeze_country_session` on
    `template_redirect`, plus a lazy freeze inside `wellness_get_location_country()`) so the
    page display, add-to-cart, and checkout all agree within a session. Currency is not
    stored separately; it derives from the country + staff.

---

## Relevant files

- `wp-content/themes/woodmart-child/functions.php` — new: `wellness_get_location_country()`,
  `wellness_geolocate_ip`, `wellness_freeze_currency_on_cart_item`,
  `wellness_cart_item_currency`, `wellness_cart_item_country`, `wellness_freeze_country_session`,
  `wellness_fetch_usd_egp_rate`, `wellness_get_usd_egp_rate`, `wellness_prewarm_usd_egp_rate`,
  `wellness_refresh_usd_egp_rate_callback`, `wellness_convert_order_usd_to_egp`,
  `wellness_convert_order_usd_to_egp_save`. Changed: `wellness_get_location_currency()`,
  `change_woocommerce_currency()`, `wellness_filter_gateways_by_currency()`,
  `wellness_create_recurring_appointments()`.
- `wp-content/plugins/woocommerce-appointments/includes/class-wc-appointment-cart-manager.php:260`
  (`add_cart_item_data` sets `_cost` + `_appointment_id`)
- `wp-content/plugins/woocommerce-appointments/includes/class-wc-appointments-cost-calculation.php:30`
  (`calculate_appointment_cost`)
- `wp-content/plugins/woocommerce/includes/class-wc-geolocation.php` — `:110` `get_ip_address()`,
  `:155` `woocommerce_geolocate_ip` filter, `:241` `get_country_code_from_headers()`
- New: FX rate helper + transient cache + site options for rate/fallback

---

## Verification

1. Extend `test-fixtures.php`: therapist with `_staff_currency=''` and one with `'USD'`;
   set `_wc_usd_display_cost` so EGP price 1500 / USD 60; set a fallback EGP rate.
2. Book as an EG client and a foreign client; confirm the order currency matches the frozen
   `_currency`, and the gateway + charge follow the model.
3. Force `WC_Geolocation::geolocate_ip()` to return `[]` at checkout; confirm the order
   stays on the frozen currency/country instead of flipping to EGP.
4. Confirm a USD+EG order is converted to EGP (USD amount × rate) at order creation only —
   the cart still showed USD — and that Paymob charges the converted EGP (not raw 60 as EGP).
5. Verify the rate fetch from open.er-api.com works and is cached; verify the fallback
   fires on a simulated API failure.
6. Simulate Cloudflare (`HTTP_CF_IPCOUNTRY=EG`, `CF-Connecting-IP`) and confirm
   `wellness_get_location_currency()` returns EGP deterministically across add-to-cart and
   checkout; confirm the spoofing guard ignores a spoofed header on a non-Cloudflare request.
7. Verify recurring follow-up inherits parent currency/price/country.
8. `php -l functions.php`.

---

## Decisions

- Keep the staff-based + geolocation currency model.
- Make geolocation deterministic behind Cloudflare via the `woocommerce_geolocate_ip`
  filter (validated `CF-IPCountry`), so add-to-cart and checkout always agree.
- Paymob does NOT convert: conversion is internal at order creation via a live cached
  USD→EGP rate from open.er-api.com.
- Conversion applies ONLY at the order step; the cart/checkout display stays in USD.
- No backfill of historical orders.
- Backwards-compatible fallback for pre-fix carts (existing cart items without frozen data).
- Excluded: re-pricing existing orders; changing the geolocation service.

---

## Post-review fixes (2026-08-30)

1. State-aware conversion guard: skip only when `_usd_to_egp_converted` is set AND the order
   currency is already EGP, so a resumed pending/failed order is re-converted.
2. Tax items: conversion now also multiplies `WC_Order_Item_Tax` totals and the product line
   item per-rate tax breakdowns (`get_taxes()`/`set_taxes()`).
3. Non-blocking rate: `wellness_get_usd_egp_rate()` no longer fetches synchronously on the
   hot path; it returns cached/stale/fallback and schedules an Action Scheduler refresh.
   `wellness_prewarm_usd_egp_rate()` runs on `init` to fetch once when empty.
4. Removed the dead `wellness_currency` session value; the session freeze stores only
   `wellness_country` (function renamed to `wellness_freeze_country_session`).
5. Standalone pay-for-order routing: `wellness_filter_gateways_by_currency()` now routes by
   the order currency when the pay-for-order cart is empty (fixes recurring follow-up USD
   orders falling through to Paymob).
6. Added `_original_total` audit meta.

Remaining (requires the WP runtime): run the Verification checklist above on `welness.heba`.

---

## Further considerations

1. Rate cache TTL (~12h, matches the source's daily update) and a sensible
   `wellness_usd_egp_fallback` (e.g. current ~30–31 EGP/USD).
2. When converting at order creation, verify line-item vs. order-total conversion and tax
   handling so Paymob receives the correct EGP total.
3. Ensure Cloudflare is configured to send `CF-IPCountry` (the "Add Country Header" / IP
   geolocation toggle) so the deterministic path is available; otherwise the fallback
   client-IP path is used.
4. Historical mis-labeled orders: intentionally not backfilled (per decision).
