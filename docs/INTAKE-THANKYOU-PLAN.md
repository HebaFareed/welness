# Move Client Intake Form: Checkout → Thank-You Page + Reminder Email

> **Status:** In progress — implementation began 2026-09-08
> **Owner:** Wellness Hub (woodmart-child)
> **Files:** `wp-content/themes/woodmart-child/functions.php`, `woocommerce/checkout/form-checkout.php`, `woocommerce/emails/admin-new-appointment.php` (+ new `emails/customer-appointment-intake-reminder.php`)

---

## 1. Goal

1. Stop collecting the client intake form at checkout; simplify checkout to a 2-step wizard (Contact & Billing → Payment).
2. Collect the intake form on the **thank-you (order-received)** page instead, prefilled from billing info, still mandatory for client name + mobile (editable).
3. Send a **one-shot reminder email ~24h after booking** if the client has not completed intake.
4. Update the admin "new appointment" email to show **"Intake: pending"** for new clients (intake no longer exists at booking time).

## 2. Confirmed decisions (2026-09-08)

| # | Decision | Choice |
|---|----------|--------|
| 1 | Checkout flow after the move | Keep the existing wizard as **2 steps**: Step 1 Contact & Billing → Step 2 Payment |
| 2 | Required fields on the thank-you intake | `intake_client_name` + `intake_mobile`, **prefilled from billing** (name from billing first+last, mobile from `billing_phone`), still mandatory, client may edit |
| 3 | Reminder cadence | **One-shot ~24h after booking**, only when no intake is on file for the order billing email |
| 4 | Admin "new appointment" email | When intake exists → summary (unchanged). When missing → **"Intake: pending"** note |
| 5 | "Filled in" definition | A `customer_intake_form` CPT entry exists for the order's billing email (`_intake_email`) — existing dedupe rule |
| 6 | Existing clients | CPT on file ⇒ no thank-you form, no reminder. No overwrite path (create-only) |

## 3. Architecture / key facts

- All intake + multi-step checkout logic currently lives in the "Task 3" block of
  `functions.php` (`:5591`–`:7066`, end of file). CPT registration, admin submenu/columns,
  metabox, admin order card, and the `wellness_get_intake_summary_for_email()` helper are
  **kept** and reused as-is.
- The thank-you page is rendered by the **Woodmart parent** `woocommerce/checkout/thankyou.php`,
  which fires `woocommerce_thankyou` (order_id) when the Woodmart option
  `thank_you_page_default_content` is enabled (default `'1'` — already required by the existing
  order-overview override). `woocommerce_before_thankyou` fires whenever an order exists.
- Order-received URL for the reminder link: `WC_Order::get_checkout_order_received_url()`
  (`class-wc-order.php:1946`) — includes the order `key`, guest-revisitable.
- Scheduling/email pattern to mirror: `as_schedule_single_action(...)` +
  `add_action(...)` handler + `WC()->mailer()` wrap/send (`functions.php:1876`, `:1895`,
  `:1919`–`:1953`).

## 4. Implementation phases

### Phase A — Detach intake from checkout (2-step wizard)
- Remove from `functions.php` the checkout-facing registrations:
  - `add_filter('woocommerce_checkout_fields', 'wellness_add_intake_checkout_fields', 99999)`
  - `add_action('woocommerce_before_checkout_form', 'wellness_intake_section_headers_css', 5)`
  - `add_filter('woocommerce_checkout_fields', 'wellness_mark_section_header_rows', 100000)`
  - the 5 phase `add_action`s on `woocommerce_checkout_after_customer_details`
  - the step open/close `add_action`s (steps 2–6)
  - `add_action('woocommerce_checkout_order_processed', 'wellness_save_intake_form', 20)`
  - the `wellness_check_intake` AJAX `add_action`s
- Renumber the payment step 7 → 2 (`id="wellness-step-2"`, aria "Step 2: Payment",
  Back button `data-back="1"`); keep the card icon (no visible step number).
- Rewrite `wellness_multistep_checkout_js()` to 2 steps only: remove `checkIntake`,
  `intakeSkip`, the `wellness_check_intake` nonce, `#wellness-intake-skipped`;
  Continue → next step, Back → previous. Keep `revealStep`'s `resize` dispatch.
- `form-checkout.php`: Step 1 Continue already uses `data-next="2"` — no change needed.
- Result: checkout shows only Step 1 → Step 2 (Payment). No intake fields at checkout.

### Phase B — Intake form on the thank-you page
- New `wellness_render_thankyou_intake($order_id)` hooked on `woocommerce_thankyou`
  (with a `wp`/order-received fallback + rendered-once guard so it does not depend on the
  Woodmart option). Renders only when: order exists, billing email present, order not failed,
  and no `customer_intake_form` CPT exists for that email.
- Canonical field definitions extracted into `wellness_get_intake_field_defs()` (labels,
  types, options) so checkout code and the thank-you form can't drift.
- Single scrollable form, same 5 section groupings as today:
  Personal / Phone & Preferences / Emergency / Health / How You Heard.
- Prefill `intake_client_name` from billing first+last and `intake_mobile` from
  `billing_phone`; both server-side required; client may edit.
- Hidden `order_id` + `order_key` + nonce `wellness_thankyou_intake`.
- Own CSS/JS (the `.wellness-*` styles live inside `form-checkout.php` only and are not
  present on the order-received view).
- New AJAX `wellness_save_thankyou_intake` (`wp_ajax` + nopriv): verify nonce, order,
  `$_POST['order_key'] === $order->get_order_key()` (anti-spoof), no existing CPT,
  required fields present, then create the CPT record via a shared
  `wellness_create_intake_record()` (which re-checks existence internally to avoid
  duplicates). Return JSON; JS swaps the form for a confirmation.

### Phase C — One-shot ~24h intake reminder email
- Define `WELLNESS_INTAKE_REMINDER_DELAY_HOURS` (default `24`).
- Schedule on `woocommerce_checkout_order_processed` **and** on
  `woocommerce_order_status_processing`/`woocommerce_order_status_completed`
  (covers pay-later orders), guarded by order meta `_intake_reminder_scheduled`
  so the action is scheduled once per order. Skip when the email already has an intake.
- Handler `wellness_send_intake_reminder($order_id)` on action `wellness-intake-reminder`:
  skip if no order / status cancelled|failed|refunded|trash / not paid (`! $order->is_paid()`)
  / `_intake_reminder_sent` / intake exists for email; otherwise send via the mailer
  wrap/send pattern with a new child template `emails/customer-appointment-intake-reminder.php`
  (first name, order no., appointment time in the customer timezone, prominent
  "Complete your intake form" link to `$order->get_checkout_order_received_url()`).
  Mark `_intake_reminder_sent`.

### Phase D — Admin "new appointment" email pending note
- `woocommerce/emails/admin-new-appointment.php` (`~:314`): when
  `wellness_get_intake_summary_for_email()` returns empty, render an
  "Intake: pending — client completes it on the thank-you page" note instead of nothing.

### Phase E — Dead-code cleanup + verification
- Remove now-unhooked checkout-only intake functions (e.g. `wellness_should_show_intake_form`,
  old `wellness_add_intake_checkout_fields`, phase renderers, `wellness_save_intake_form`,
  `wellness_ajax_check_intake`, intake step helpers) after Phases A–D are verified working.
- `php -l` on edited files; grep that no orphan references remain.

## 5. Regression risks (assessment 2026-09-08)

1. **Payment-step reveal after renumber/JS rewrite** (high) — keep `revealStep` + `resize`
   dispatch so Stripe/Paymob iframes re-measure; test a full paid order on each gateway.
2. **Thank-you render depends on Woodmart `thank_you_page_default_content`** (high) — fallback
   render + rendered-once guard added; verify live option value.
3. **AJAX-only submit** (medium) — no no-JS path; add a `<noscript>` note.
4. **Duplicate-intake race** (medium) — `wellness_create_intake_record()` re-checks existence.
5. **Clinical-data gap** (medium, product) — admin email shows "pending"; therapists should get
   a "new intake submitted" signal (future task) and sessions should allow lead time.
6. **Reminder for unpaid orders** (medium) — handler skips when `! $order->is_paid()`.
7. **Reminder duplication for rapid repeat bookings** (low) — per-order
   `_intake_reminder_scheduled` + `_intake_reminder_sent` guards bound it.
8. **Mobile prefill empty if `billing_phone` disabled** (low) — field stays required.
9. **Reminder link if query string stripped** (low) — provide My Account link for logged-in users.
10. **Repeat-buyer behavior change** (low, intended) — returning clients with no intake CPT will
    now be asked on the thank-you page and reminded.

## 6. Verification checklist

- [ ] `php -l` passes on `functions.php` (and email template).
- [ ] New client EGP booking: checkout = Step 1 → Step 2 (Payment), no intake; after payment the
      thank-you page shows intake prefilled with name + mobile.
- [ ] Submit with empty name/mobile → blocked; filled → saved; page refresh → form gone.
- [ ] Admin → Intake Forms shows the new entry with linked order; admin order card shows intake.
- [ ] Admin new-appointment email: "Intake: pending" for new emails; summary for returning clients.
- [ ] Returning client (CPT on file): no thank-you form; no reminder.
- [ ] Reminder fires ~24h for paid orders with no intake; suppressed when intake completed;
      no duplicate sends (meta guards); suppressed for cancelled/failed/unpaid orders.
- [ ] Regression: recurring booking + USD therapist product check out through the 2-step wizard;
      Stripe/Paymob iframes still reveal correctly.
