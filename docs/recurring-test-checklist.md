# Recurring Appointments — Manual Test Checklist

> **Scope:** Custom recurring appointment flow (replaces WooCommerce Product Add-Ons).
> **Files:** `woodmart-child/functions.php`, `woocommerce/appointment-form/{checkbox,number}.php`, `woocommerce/emails/customer-appointment-payment.php`, `customer-appointment-follow-up.php` (renamed), `admin-new-appointment.php`, `customer-appointment-confirmed.php`, `admin-appointment-cancelled.php`.
> **Note:** Preview on the **live** site (thewellnesshub-eg.com) will not show these changes until they are deployed. Test on the local/dev copy.

---

## 0. Setup / pre-flight

- [ ] Confirm the product has `_wc_appointment_enable_recurring` = **yes** (default ON). A product where it's off should show **no** repeat controls.
- [ ] Confirm `_wc_appointment_max_repeat_count` is set (default 2).
- [ ] **Remove** the per-product Product Add-Ons "Repeat Appointment" / "Interval" / "Number" addons from each bookable product (they are no longer read). Leaving them adds no recurring behaviour but is confusing.
- [ ] **Delete the old reveal JS** saved in theme options (the snippet targeting `.wc-pao-addon-id-*` for the checkbox/interval/qty). It is now obsolete/inert (those DOM nodes are gone); the reveal + schedule preview is handled by `wellness_recurring_form_js()` in `functions.php`.
- [ ] Delete/ignore the dead backup `wp-content/themes/woodmart-child/old_functions.php` (not loaded).
- [ ] In **Appearance → Customize → Recurring Appointments**, set "Recurring payment reminder (days before)" (default 1).

---

## 1. Booking form — client view

- [ ] On a bookable product page, the "Do you want to repeat this appointment?" checkbox + "Yes" appears.
- [ ] Checking the box reveals **Repeat every** (Weekly / Bi-Weekly / Monthly) and **Number of repeats** (default 2, max = Max Repeat Count).
- [ ] Unchecking hides them again.
- [ ] Select a date + time, pick an interval, set a count → a **"Your upcoming sessions"** list appears with each date/time.
- [ ] If a follow-up slot is taken, the preview shows a **shift notice** ("the exact slot was unavailable; we reserved the next available time").
- [ ] The repeat fields do **not** block the base booking (no `required_for_calculation` on them).

## 2. Cart & checkout review

- [ ] After adding a recurring item to the cart, the cart line shows **"Scheduled sessions: <dates>"** and **"Payment & confirmation: You will receive a payment reminder and a confirmation for each recurring session."**
- [ ] The checkout order-review table shows the same two lines.
- [ ] For a **single** (non-recurring) booking, no recurring meta/notes appear.

## 3. First order payment → follow-ups created

- [ ] Pay the first order. The parent appointment becomes **confirmed**.
- [ ] For a weekly × 2 booking starting 12 Aug 1pm: two follow-up **appointments** (19 Aug, 26 Aug at 1pm) are created with status **unpaid**.
- [ ] Two follow-up **orders** are created with status **pending**, each priced to match the parent order line item (including session-type price + EGP/USD).
- [ ] The follow-up orders carry the customer's `_customer_timezone`, currency, addresses, and payment method.
- [ ] If a slot was taken, an order note records the shift.
- [ ] **Action Scheduler:** `wellness-appointment-payment-reminder` scheduled at (start − lead days) and `wellness-appointment-payment-check` scheduled at (start) for each follow-up.

## 4. Payment reminder email

- [ ] The `customer-appointment-payment` email is sent N days before each follow-up (default 1 day).
- [ ] It shows the client's name, session details (date/time in the customer's timezone, therapist, duration, session type), the **amount due**, and a **"Pay for your session"** button with a working `order-pay` link.

## 5. Paying a follow-up

- [ ] Paying a follow-up order transitions its appointment unpaid → paid → **confirmed**.
- [ ] The **client** receives the `customer-appointment-confirmed` email listing **"Your recurring sessions."**
- [ ] The **therapist** receives the `admin-new-appointment` email, which now shows **"Recurring"** (e.g. "Weekly × 2") and a **"Scheduled sessions"** date list.

## 6. Unpaid follow-up auto-cancel

- [ ] If a follow-up is still unpaid at its start, the `wellness-appointment-payment-check` job cancels it.
- [ ] The therapist receives the `admin-appointment-cancelled` email with the **"Unpaid recurring appointment"** notice.
- [ ] The pending follow-up order is cancelled (generic order-cancelled customer email suppressed), and an order note explains the reason.

## 7. Interval / currency / product variants

- [ ] Test **weekly**, **bi-weekly**, and **monthly** intervals.
- [ ] Test counts > 1 and the max cap.
- [ ] Test a **session-type (duration options)** product — the follow-up price must match the selected session-type price (EGP & USD).
- [ ] Test **EGP** and **USD** therapist flows (set `_staff_currency` on the therapist).
- [ ] Test a **single** (non-recurring) booking — no recurring meta on order item, no extra orders/appointments.

---

## 8. Admin recurring display & cancel-series (added 2026-08-30)

- [ ] **Appointments list** and **Orders list** show a **"Recurring"** column with a badge (`Recurring · Paid` / `Recurring · Unpaid`).
- [ ] Clicking the badge toggles a **popup** listing the full series (dates, statuses, quick links to each appointment/order); it does **not** navigate to the order.
- [ ] The series popup shows **all** sessions (root + follow-ups), not just the first.
- [ ] On the **appointment** and **order** admin pages, the recurring chain appears in the **main area** with quick links.
- [ ] **Cancel recurring series** button appears only for admins/therapists when there is an upcoming session.
- [ ] The **waive-fees checkbox** explains the two outcomes (full refund vs standard cancellation fee).
- [ ] Cancelling the series cancels **upcoming** sessions only (past ones stay), cancels pending follow-up orders, sends **one** consolidated notice to the client and therapist/admin, and shows an admin notice.

---

## Notes / known items

- The old Product-Addons engine (`get_product_addon_value`) is fully removed from the active `functions.php`; only a commented `add_filter` marker remains.
- The custom follow-up template was renamed to `customer-appointment-follow-up.php` (the plugin's canonical name) so the styled follow-up email now renders.
- Before committing, review the diff (e.g. `git diff` on `functions.php` + templates). Do **not** commit without the user's go-ahead.
