# Test Therapist — Plan

> **Status:** Implementation in progress — seeding script generated (2026-08-30)
> **Date:** 2026-08-30
> **Project:** The Wellness Hub (`woodmart-child`)
> **Related:** `docs/recurring-test-checklist.md` (E2E test script) · `docs/RECURRING-APPOINTMENTS-PLAN.md`

## Goal

Provide a dedicated **test therapist** that can only be reached via direct URL (by guests or signed-in users) so the **recurring appointment** flow and the **session type / duration options** flow can be exercised end-to-end without touching real therapist pages or appearing in the public therapist directory.

## Decisions (confirmed)

| Topic | Decision |
|-------|----------|
| Creation | **One-time seeding script** `test-fixtures.php` (run where PHP has mysqli — WP container CLI, or web URL with `?seed=` token). Manual admin steps kept below for reference/verification. |
| Test flag | **Auto-detect by product slug/name containing `test`** — no admin metabox change. |
| Currency | **Two therapists** — one EGP, one USD (to test both currency flows simultaneously). |
| Visibility | Products set to **Catalog visibility = Hidden** → excluded from shop/archive/search, still reachable by direct URL. |

## URLs

| Product | URL (local) |
|---------|-------------|
| Test Therapist (EGP) | `http://welness.heba/appointment/test-therapist-egp/` |
| Test Therapist (USD) | `http://welness.heba/appointment/test-therapist-usd/` |

## Phase A — Code: test banner (`woodmart-child/functions.php`)

- Add `wellness_is_test_therapist_product( $product = null )` — returns true when the product slug or name contains `test` (case-insensitive). Resolves the product via `get_queried_object_id()` when called on a single product page.
- Hook `woocommerce_before_single_product` (priority 10) → `wellness_render_test_therapist_banner()`:
  - If the helper returns true, print a prominent amber/red banner: **TEST THERAPIST** badge + "QA only" text with the product name.
  - Inline `<style>` for the banner (flex layout, orange border, responsive stack on mobile) — matches the theme's existing inline-CSS pattern.
- **Status:** ✅ Done — 2026-08-30 (see `functions.php`, section "TEST THERAPIST BANNER").

## Seeding script — `test-fixtures.php` (one-time)

A standalone script at the site root automates **Phase B + Phase C** (staff users, availability, and both hidden products, including all child-theme meta):

- **Run (CLI, where PHP has mysqli — e.g. the WP container):** `php test-fixtures.php`
- **Run (web, with token):** `http://welness.heba/test-fixtures.php?seed=welness-seed-2026-08-30`
- **Clean up:** `php test-fixtures.php --clean` or `?seed=...&clean=1`
- **Idempotent:** re-running reuses existing users/products and syncs meta; `--clean` removes them.
- **Security:** web access is token-guarded; keep the script in the repo as a dev tool, but never leave it reachable on a production web root.

The values in Phases B/C below are the exact ones the script uses.

## Phase B — Data: create two test staff users (wp-admin → Users → Add New)

*Automated by `test-fixtures.php`; kept here for reference/verification.*

Create **two** users with role **`shop_staff`**:

| Field | User 1 (EGP) | User 2 (USD) |
|-------|--------------|--------------|
| Username | `test_therapist_egp` | `test_therapist_usd` |
| Email | `test.egp@wellnesshub-eg.com` | `test.usd@wellnesshub-eg.com` |
| Display name | `Test Therapist (EGP)` | `Test Therapist (USD)` |
| Role | `shop_staff` | `shop_staff` |
| `_staff_currency` | `EGP` | `USD` |
| `timezone_string` | `Africa/Cairo` | `America/New_York` |

- `_staff_currency` and `timezone_string` are set via the staff profile page fields rendered by `wellness_staff_timezone_field()` (`functions.php:2737`) and saved by `wellness_save_staff_timezone()` (`:2784`).
- `America/New_York` on user 2 also exercises the timezone-aware email path.

## Phase C — Data: create two appointment products (wp-admin → Products → Add New)

*Automated by `test-fixtures.php`; kept here for reference/verification.*

Repeat the following for each therapist (product A = EGP user 1, product B = USD user 2):

1. **Product type** = `Appointment` (WooCommerce Appointments).
2. **Name** = `Test Therapist (EGP)` / `Test Therapist (USD)`; **slug** = `test-therapist-egp` / `test-therapist-usd` (both contain `test` → banner auto-fires).
3. **Resources tab**: assign the matching test staff user as the resource/staff.
4. **General / availability**: set a price, a block duration (e.g. 50-minute blocks), and a weekly availability window broad enough that `get_appointable_minute_slots_for_date()` yields ≥ 1 slot. Keep the product **Interval ≤ availability-window length** (see the interval workaround note in `AGENTS.md`) so slots generate.
5. **Wellness box** (`functions.php:642` `wellness_product_meta_box_callback()`):
   - Tick **Enable Recurring** → `_wc_appointment_enable_recurring = yes`.
   - **Max Repeat Count** = `3` → `_wc_appointment_max_repeat_count`.
   - Tick **Enable Duration Options** → `_wc_appointment_enable_duration_options = yes`.
   - Add **Duration Options** (stored as JSON `_wc_appointment_duration_options`):

     | Label | Duration (min) | EGP Price | USD Price |
     |-------|:--------------:|:---------:|:---------:|
     | Individual – 30 min | 30 | 500 | 15 |
     | Couples – 60 min | 60 | 900 | 27 |
     | Family – 90 min | 90 | 1200 | 36 |
6. **Catalog visibility = Hidden** — excludes from shop/archive/search; product page remains reachable by direct URL.
7. Optional: assign an Appointment category (e.g. `Psychotherapy-online`) — affects only the breadcrumb; not required since the product is hidden.

## Phase D — Verification

1. **Run the seeding script** (`php test-fixtures.php` in the WP container, or the web URL with the token). Confirm it prints both staff IDs, both product IDs, and both `/appointment/...` URLs with no errors.
2. Guest (logged out) visit `/appointment/test-therapist-egp/` and `/appointment/test-therapist-usd/`:
   - **TEST THERAPIST** banner visible at the top.
   - Calendar renders; **Session Type** dropdown appears (given `_wc_appointment_enable_duration_options = yes`).
   - **"Do you want to repeat this appointment?"** checkbox appears; ticking reveals interval + count.
3. Confirm neither product appears in `/shop/`, the appointment-category archive, `/team-online/`, or site search.
4. Run the E2E recurring + session-type flow from `docs/recurring-test-checklist.md` on **each** therapist:
   - Pick a session type → calendar / Add-to-Cart activate; price reflects the option (EGP for user 1, USD for user 2).
   - Select a slot, tick repeat, set interval + count → "Your upcoming sessions" preview.
   - Add to cart → session-type + scheduled-sessions lines. Checkout (intake form shows for a first-time email) → pay the first order → follow-up appointments created **unpaid** + pending orders priced to the parent line item.
   - Pay a follow-up → client + therapist confirmation.
   - Verify the session-type price is preserved in follow-up orders (EGP vs USD).
5. Confirm the test products do **not** appear in the staff product-restriction dropdowns and do not break admin lists.

## Relevant files

- **NEW** `test-fixtures.php` (site root) — seeding script (kept in the repo as a dev tool; token-guarded web access).
- `wp-content/themes/woodmart-child/functions.php`
  - `:642` `wellness_product_meta_box_callback()` — recurring/duration toggles (reference for field values).
  - `:793` `save_wellness_product_meta_box()` — saves `_wc_appointment_enable_recurring`, `_wc_appointment_duration_options`, etc.
  - `:2737` `wellness_staff_timezone_field()` / `:2784` `wellness_save_staff_timezone()` — where `_staff_currency` + `timezone_string` are saved.
  - `:3284` / `:3740` — appointment form fields (session type + recurring) injection.
  - **NEW** — "TEST THERAPIST BANNER" section (helper + `woocommerce_before_single_product` hook).
- `docs/recurring-test-checklist.md` — E2E test script.
- `docs/RECURRING-APPOINTMENTS-PLAN.md` — recurring engine + meta keys reference.
- No plugin/parent-theme files are modified.

## Scope

- **Included:** two hidden test products + two staff users + staff availability + auto test-banner hook + one-time seeding script.
- **Excluded:** changes to real therapist pages, currency logic, recurring engine, session-type engine; no seeding script; no catalog/archive query changes (catalog visibility handles hiding).
