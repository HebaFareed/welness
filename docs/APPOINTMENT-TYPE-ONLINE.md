# Feature note: Appointment type — Online

> **Date:** 2026-09-09
> **Scope:** `woodmart-child` — `functions.php` + `woocommerce/emails/*.php`. No plugin/parent-theme edits.
> **Status:** ✅ Shipped — commit `f1c5ef4c`.

## What it does

Adds a fixed **Appointment type: Online** line to the cart/checkout item details and to every
appointment email, so clients and therapists can see the session is online.

## Where it appears

- **Cart / checkout review** — appended to the cart item data via
  `woocommerce_get_item_data` → `wellness_appointment_type_cart_item_data()`
  (`functions.php`, priority 20).
- **Order item meta** — thank-you page, admin order line items, and standard WooCommerce order
  emails via `woocommerce_display_item_meta` → `wellness_display_appointment_type_in_orders()`.
- **Appointment emails** — an explicit `Appointment type` row in each appointment template:
  `admin-new-appointment`, `admin-appointment-cancelled`, `admin-appointment-rescheduled`,
  `customer-appointment-confirmed`, `customer-appointment-cancelled`, `customer-appointment-payment`,
  `customer-appointment-follow-up`, `customer-appointment-reminder`,
  `customer-appointment-rescheduled`, `customer-appointment-intake-reminder`.

Both filters are guarded to appointment products (`is_wc_appointment_product()`) and skip if the
label is already present, so non-appointment products are unaffected.

## Notes / limits

- The value is a **fixed label** (`Online`) — it is not derived from any product, staff, or
  appointment data. If in-person sessions are introduced, replace the literal with a helper that
  reads a real source (e.g. product meta `_wc_appointment_type`) and add an admin field.
- `customer-appointment-intake-reminder.php` belongs to the intake refactor (commit `5ddb117c`);
  the row in that template ships with the intake module.

## Verification

- `php -l` clean on `functions.php` and all edited email templates.
- Manual: book with a test therapist, check the cart/checkout row, then trigger each appointment
  email and confirm the `Appointment type: Online` row renders.
