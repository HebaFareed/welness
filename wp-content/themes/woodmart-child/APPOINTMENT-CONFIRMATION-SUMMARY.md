# Appointment Confirmation Flow — Fix Summary

**Date:** May 20, 2026
**Scope:** WooCommerce Appointments — Wellness site
**Files changed:** `wp-content/themes/woodmart-child/functions.php`, `wp-content/themes/woodmart-child/woocommerce/emails/customer-appointment-confirmed.php`
**Plugin version:** WooCommerce Appointments v4.25.1

---

## Non-Technical Summary

When a client books and pays for a therapy session on The Wellness Hub, their appointment was not being marked as confirmed — it stayed stuck in "Paid" status, and no confirmation emails were sent. This was investigated and fixed across several connected issues:

1. The booking system was wired to a hook that doesn't exist, so the confirmation step never ran.
2. Once fixed, a second bug caused an infinite loop for free/fully-discounted sessions (the system kept toggling the appointment between "Paid" and "Confirmed" repeatedly).
3. A typo/leftover code in the confirmation email template was crashing the email entirely.

All three issues are now resolved. A booking made and paid for should immediately confirm and send emails to the client, assigned therapist, and admin.

---

## Testing Checklist

- [ ] Book a session as a customer, complete payment (full-price order)
  - [ ] Appointment status ends as **Confirmed** (not Paid, not Unpaid)
  - [ ] Customer receives **"Appointment Confirmed"** email
  - [ ] Admin receives **"Admin New Appointment"** email
  - [ ] Assigned therapist/staff receives the admin notification email
- [ ] Repeat the above with a **free / 100%-discounted** order (zero total)
  - [ ] No infinite status loop in order notes
  - [ ] Appointment ends as **Confirmed**
  - [ ] Emails still sent
- [ ] Check order notes — should show: `In Cart → Unpaid → Paid → Confirmed` (once, no repeats)
- [ ] Verify no duplicate emails are received
- [ ] Check Action Scheduler (`Tools → Scheduled Actions`) — no failed `wc-appointment-confirmed` or `woocommerce_admin_new_appointment_notification` jobs

---

## Technical Summary

Three distinct bugs in the appointment-on-payment flow were identified and fixed:

1. **Wrong hook name** — the original code listened on `woocommerce_appointment_to_paid` which doesn't exist. Changed to `woocommerce_appointment_unpaid_to_paid` and also added `woocommerce_appointment_confirmed_to_paid` as a fallback.

2. **Infinite loop on zero-total orders** — `WC_Appointment::save()` always calls `mark_confirmed_order_complete_when_total_zero()`. For $0 orders this triggered `$order->update_status('completed')` → `publish_appointments()` → `$appointment->paid()` → reverted to Paid → our hook re-confirmed → loop. Fixed with three guards: a static per-request flag, a filter suppressing the zero-total order auto-complete during our save, and permanently removing `publish_appointments` from `woocommerce_order_status_processing/completed` once confirmation has been issued.

3. **PHP syntax error in email template** — `customer-appointment-confirmed.php` contained orphaned `</td></tr><?php endif; ?>` lines from a session-fee table row that had been deleted without removing its closing tags. This caused "unexpected token `endif`" and crashed every `wc-appointment-confirmed` Action Scheduler job.

---

## Code Changes

### `functions.php` — Appointment confirmation hook

```php
add_action('woocommerce_appointment_unpaid_to_paid',    'wellness_confirm_appointment_on_payment', 9999);
add_action('woocommerce_appointment_confirmed_to_paid', 'wellness_confirm_appointment_on_payment', 9999);
function wellness_confirm_appointment_on_payment($appointment_id)
{
    static $done = array();
    if (isset($done[$appointment_id])) {
        return;
    }
    $done[$appointment_id] = true;

    $appointment = get_wc_appointment($appointment_id);
    if (! $appointment) {
        return;
    }

    // Neutralize zero-total order auto-complete during our save.
    $zero_filter = function ($status, $order) {
        return $order->get_status();
    };
    add_filter('woocommerce_appointments_zero_order_status', $zero_filter, 9999, 2);

    // Permanently detach publish_appointments — it has already done its job.
    // Any subsequent order status transition would only revert our 'confirmed'.
    $order_manager = isset($GLOBALS['wc_appointment_order_manager']) ? $GLOBALS['wc_appointment_order_manager'] : null;
    if ($order_manager) {
        remove_action('woocommerce_order_status_processing', array($order_manager, 'publish_appointments'), 20);
        remove_action('woocommerce_order_status_completed',  array($order_manager, 'publish_appointments'), 20);
    }

    $appointment->update_status('confirmed');

    remove_filter('woocommerce_appointments_zero_order_status', $zero_filter, 9999);
}
```

### `woocommerce/emails/customer-appointment-confirmed.php`

Removed orphaned closing tags that caused a fatal PHP syntax error:

```diff
-   </tr>
-   <?php endif; ?>
-           </td>
-       </tr>
-       <?php endif; ?>
+   </tr>
+   <?php endif; ?>

    <?php if ( $payment_method ) : ?>
```
