# Recurring Appointments — Implementation Plan

> **Status:** ✅ Implementation complete (Phases 0–7) + client schedule display + admin recurring UX + cancel-series action; live testing pending after deploy
> **Date:** 2026-08-30
> **Project:** The Wellness Hub (`woodmart-child`)
> **Related:** `AGENTS.md` pending task #5 — Recurring appointment reminders/notifications fix
>
> **Docs:** `recurring-test-checklist.md` (manual test steps) · this plan

## Goal

Convert the recurring appointment/booking flow from the WooCommerce **Product Add-Ons** plugin to a **custom child-theme** implementation (mirroring the existing Session Type / Duration Options feature), and fix the broken payment + notification flow.

The flow to deliver:

1. Client picks a booking slot on a bookable product.
2. A **Repeat Appointment** checkbox appears; on check it reveals an **Interval** dropdown (weekly / bi-weekly / monthly) and a **Number** input (default 2).
3. A **pre-booking schedule preview** shows the full list of upcoming sessions; if a slot is taken it shows a **shift notice** ("the 19 Aug 1pm slot was taken; we reserved 19 Aug 2pm").
4. On payment of the first order, follow-up **appointments + orders** are created.
5. Follow-up appointments are created **unpaid**, linked to **pending** orders, so the plugin's standard pay → `paid` → confirm flow fires automatically.
6. Each follow-up gets a configurable **"pay for your appointment" email** (with the order-pay link) sent ahead of it (default 1 day).
7. When the client pays a follow-up order, **client + therapist** get the confirmation email.
8. If a follow-up isn't paid by its date, it is **auto-cancelled** and the therapist is notified it was an unpaid recurring appointment.
9. The "Number" addon no longer appears in order meta when the client doesn't choose recurring.

## Recent additions (2026-08-30)

After the phases above, the following were added while testing:

- **Recurring series built from the order tree.** `wellness_get_recurring_chain()` now derives the series from the order hierarchy (root order + child follow-up orders created by the engine) instead of the appointment `_appointment_parent_id` meta, which wasn't reliably persisted on follow-ups. This fixed admin lists/popups showing only the first session.
- **Follow-up appointments recognised as recurring.** `wellness_appointment_is_recurring()` walks up to the root order and checks its line-item `_recurring`, so follow-ups are treated as part of the series even when their own meta is empty. Used by the order-edit page and appointment-metabox guards.
- **Recurring admin column + badge.** A "Recurring" column is added to the appointments and orders lists; the badge shows `Recurring · Paid/Unpaid`, is payment- & date-aware (past sessions marked, paid-session count), and clicking it toggles a popup listing the full series with quick links to each appointment/order.
- **Recurring chain in the main admin area** (not the sidebar), with quick links to related appointments/orders.
- **Cancel recurring series** (admin/therapist only): a button in the recurring chain cancels all upcoming sessions — paid sessions get either the standard cancellation fee + refund or a full refund (waive-fees checkbox); unpaid follow-up orders are cancelled; per-session cancel emails are suppressed in favour of one consolidated notice to the client and therapist/admin; an admin notice summarises the result.
- **Emails** (`admin-new-appointment`, `customer-appointment-confirmed`, `customer-appointment-reminder`) gate the recurring block on `wellness_appointment_is_recurring()` and show the actual chain dates, so follow-up emails also show the full series.

## Decisions

| Topic | Decision |
|-------|----------|
| Enablement | Per-product toggle `_wc_appointment_enable_recurring`, **default ON**; keep `_wc_appointment_max_repeat_count`. |
| Prompt UI | True **checkbox** that reveals Interval + Number. Needs a small theme `appointment-form/checkbox.php` template override. |
| Slot handling | Shift-to-next-available slot, **with server-side AJAX schedule preview + shift notice** (build now). |
| Payment reminder | **Configurable** lead time, default **1 day** before each follow-up. |
| Unpaid handling | **Auto-cancel** + notify therapist it was an unpaid recurring appointment. |
| Follow-up price | Copy the **parent order line-item price** (captures session-type/duration option price + EGP/USD). |
| Legacy meta | `_wc_appointment_booking_type` / `_wc_appointment_recurring_type` / `add_recurring_product_description()` → **comment out** for now. |
| Product addons cleanup | User removes the per-product "Repeat Appointment" & "Interval" addons manually. |

## Gaps / bugs found (review)

1. Follow-up orders created `pending` with **no pay-link email**; follow-up appointments inherit `paid` while their orders stay `pending` → status mismatch, no client/therapist confirmation for repeats.
2. `wellness_inject_repeat_number_addon()` (`functions.php:2095`) injects the `Number` addon on **all** appointable products → it always lands in order item meta even when not recurring.
3. `customer-appointment-followup.php` filename doesn't match the plugin's `customer-appointment-follow-up.php` → custom follow-up template is dead code.
4. Follow-up order price ignores the session-type/duration-option price (uses base product price).
5. `_customer_timezone` isn't copied to follow-up orders → customer timezone may fall back to site timezone.
6. Slot shift is silent — no client preview/notice.
7. Legacy `_wc_appointment_booking_type` / `_wc_appointment_recurring_type` / `add_recurring_product_description()` are unused/obsolete.
8. `admin-new-appointment.php` parses repeat/interval from order-addon meta — must be updated to the new meta and list all recurring dates.

## Meta keys

- `_wc_appointment_enable_recurring` — `yes` / `no`, default `yes` (product meta).
- `_wc_appointment_max_repeat_count` — existing, max number of follow-ups.
- `_recurring_interval` — `weekly` / `biweekly` / `monthly` (order item + appointment meta).
- `_recurring_count` — number of follow-ups (order item + appointment meta).

## Phases

### Phase 0 — Cleanup & meta definitions ✅ (in progress)
- Comment out `wellness_inject_repeat_number_addon()` + its hook (`functions.php:2094`–`:2131`).
- Rename `customer-appointment-followup.php` → `customer-appointment-follow-up.php`.
- Comment out legacy recurring code: `add_recurring_product_description()` (`:848`–`:878`) and the `_wc_appointment_recurring_type` / `_wc_appointment_booking_type` / `_wc_appointment_recurring_end_*` save blocks in `save_wellness_product_meta_box()` (`:782`).

### Phase 1 — Admin product config
- `wellness_product_meta_box_callback()` (`:642`): add an "Enable Recurring" checkbox (default checked); keep Max Repeat Count (`:660`).
- `save_wellness_product_meta_box()` (`:782`): persist `_wc_appointment_enable_recurring`.

### Phase 2 — Frontend booking form + AJAX preview
- Add theme template `woocommerce/appointment-form/checkbox.php`; reuse `select`/`number` templates for interval/count.
- Inject via `appointment_form_fields` (`:3056` pattern): `wc_appointments_field_recurring` (checkbox), `wc_appointments_field_recurring_interval`, `wc_appointments_field_recurring_count` (default 2, max = Max Repeat Count).
- JS/CSS via `woocommerce_after_appointment_form_output` (`:3300` pattern) to reveal interval + count on check.
- New AJAX endpoint `wellness_recurring_preview`: computes follow-up slots, checks availability via `wc_appointments_get_total_available_appointments_for_range()`, returns `[{date, time, shifted, notice}]`.

### Phase 3 — Capture & persist recurrence
- `woocommerce_appointments_get_posted_data` (`:3196`): copy `recurring` / `recurring_interval` / `recurring_count`.
- `woocommerce_checkout_create_order_line_item`: write `_recurring_interval` / `_recurring_count`.
- `woocommerce_checkout_order_processed` (`:3230`): persist to appointment post meta.

### Phase 4 — Rewrite the recurring engine
- Replace `get_product_addon_value()` with `wellness_create_recurring_appointments()` (hooked `status_changed` to=paid, priority 10).
- Create each follow-up **unpaid** linked to a new **pending** order; copy parent line-item price + `_customer_timezone`.
- Detect shift (requested vs created start) → note.
- Schedule `wellness-appointment-payment-reminder` (start − lead days) and `wellness-appointment-payment-check` (start).

### Phase 5 — Payment reminder email
- Template `customer-appointment-payment.php`; sender following `wellness_send_customer_rescheduled_email()` (`:1750`) using `wellness_get_customer_tz()` and `$order->get_checkout_payment_url()`.
- Lead-time setting (Customizer/option), default 1 day.

### Phase 6 — Auto-cancel unpaid repeats
- `wellness-appointment-payment-check` handler: cancel an unpaid appointment at its start + notify therapist/admin.

### Phase 7 — Email template updates
- `admin-new-appointment.php`: replace addon parsing with new meta; list all recurring dates.
- `customer-appointment-confirmed.php`: show recurring schedule + pay link for pending follow-ups.

### Phase 8 — Verification
- Single (non-recurring) → no recurring meta/orders. Recurring weekly/bi-weekly/monthly × N → correct follow-ups, unpaid + pending orders priced to parent line item. Session-type recurring → correct price. EGP vs USD. Payment reminder sent N days before; on pay, client + therapist confirm. Unpaid → auto-cancel + therapist notice. AJAX preview reflects shifted slots.

## Relevant files

- `wp-content/themes/woodmart-child/functions.php` — all logic.
- `woocommerce/appointment-form/checkbox.php` — new.
- `woocommerce/emails/customer-appointment-payment.php` — new.
- `woocommerce/emails/admin-new-appointment.php`, `customer-appointment-confirmed.php` — updates.
- Rename `woocommerce/emails/customer-appointment-followup.php` → `customer-appointment-follow-up.php`.
