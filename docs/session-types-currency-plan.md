# Plan: Session types, currency (record USD / pay EGP), and test-fixtures admin page

> **Date:** 2026-08-31
> **Scope:** `woodmart-child` (`functions.php`) + `test-fixtures.php`. No plugin/parent-theme edits.
> **Status:** ✅ Implementation complete (2026-08-31) — phases 1–5 done. Optional follow-ups under Further Considerations.

## TL;DR

Fix the custom session-type slot length so slots use the chosen duration; rework USD→EGP
so the order is **recorded in USD** but **Paymob is charged the converted EGP** (option A),
reading frozen currency/country from the appointment (not the cart); and turn
`test-fixtures.php` into an admin-only page (no URL/token, no printed passwords).
Keep the recurring debug logs.

## Context (verified during review)

- Session-type products' slot calc only overrides `woocommerce_appointments_base_interval`
  (the step); `woocommerce_appointments_interval` (slot length) is never hooked, so a 60-min
  session renders a 50-min slot.
- The currency fix (`4fc80f0e`) currently re-denominates USD+Egypt orders to EGP at order
  creation, but the intended model is "record USD, convert for Paymob only."
- `test-fixtures.php` is a web-reachable root script with a hardcoded token and prints
  generated passwords.

## Steps

1. ✅ **Session-type slot length** (`functions.php`): add shared helper
   `wellness_selected_duration_option_minutes($product)`; hook both
   `woocommerce_appointments_interval` (slot **length**) and `woocommerce_appointments_base_interval`
   (step) to return the chosen duration, falling back to product default when blank. Refactor the
   existing base_interval callback to use the helper (near `:3910`).
2. ✅ **Currency option A** (`functions.php`, rewrite `wellness_convert_order_usd_to_egp()` at `:860`):
   do **not** change order currency/totals. Read frozen `_currency`/`_country` from the line item's
   `_appointment_id` → appointment meta (never `WC()->cart`). If USD + EG, set
   `_usd_to_egp_converted=yes`, `_usd_to_egp_rate`, `_original_currency=USD`, `_original_total`, and
   leave the order in USD. Keep the same two hook registrations + the `_save` wrapper (`:958`).
3. ✅ **Paymob conversion** (`functions.php`): add `paymob_intention_data` filter (priority 20)
   `wellness_paymob_intention_usd_to_egp()` — when `_usd_to_egp_converted` is set, multiply
   `$data['amount']` (and each item's `amount`) by the rate, set `currency='EGP'`, then update
   `PaymobCentsAmount` meta (= converted amount) and `save()` so the Paymob webhook validation passes.
4. ✅ **Test fixtures → admin page**: strip the direct-run entry/token from `test-fixtures.php`
   (`if ( ! defined('ABSPATH') ) exit;`, keep all `seed_*` functions, delete the bottom execution
   block). Add a WooCommerce submenu **"Test Therapist Fixtures"** in `functions.php`
   (capability `manage_options`, nonce-guarded POST) with Seed / Clean buttons;
   `require_once ABSPATH.'test-fixtures.php'` in the callback; passwords stay in
   `wellness_seed_password_<id>` transients (never echoed).
5. ✅ **Recurring** (kept as-is): `wellness_recurring_log()` unchanged. Optionally later fix
   the `set_total($line_total + $line_tax)` after `calculate_totals()`.

## Relevant files

- `wp-content/themes/woodmart-child/functions.php`
  - Session-type: `:3914` base_interval override; add `woocommerce_appointments_interval` + helper.
  - Currency: `:860` `wellness_convert_order_usd_to_egp()`; `:958` `_save` wrapper; add
    `paymob_intention_data` filter near the rate helpers `:755`.
  - Admin page: add near the test-banner section `:86` (or end of file).
- `test-fixtures.php` (site root) — strip entry/token, keep the `seed_*` functions.

## Verification

1. `php -l functions.php` and `php -l test-fixtures.php`.
2. Session types: select a 60-min session → slots are 60-min and step 60 min; a 50-min session stays 50.
3. Currency: book as an EG client on a USD therapist → the order stays USD; the Paymob intention
   amount is USD×rate in EGP, currency EGP; `PaymobCentsAmount` matches; webhook passes.
   USD + non-EG → Stripe, no conversion.
4. Resumed/Store-API/pay-for-order path: conversion still works (reads order/appointment meta, not cart).
5. Admin: `WooCommerce → Test Therapist Fixtures` shows Seed/Clean; seed creates the 3 hidden
   therapists + products; clean removes them; no public URL works; no password echoed.

## Decisions

- Keep the staff-based + geolocation currency model; freeze currency/country at add-to-cart and on
  the appointment.
- Option A: order recorded USD; only the Paymob charge is converted to EGP.
- Conversion reads frozen values from the appointment (not the cart) so it survives cart-less flows.
- `test-fixtures.php` becomes admin-only (no token/URL); passwords stored in transients.
- Keep the recurring debug logs.

## Further Considerations

1. The recurring follow-up path still converts line amounts to EGP for pre-fix USD parents
   (`wellness_create_recurring_appointments()`) — decide whether to also switch it to the option-A
   pattern.
2. The `?diagnose=1` geolocation matrix can be folded into the admin page as a "Run currency
   diagnosis" button.
3. Verify Paymob `getIntegrationIds()` still selects an EGP integration for a USD order (it falls back
   to all IDs when no currency match) in a live test.
