## Summary

| # | Task | Priority | Est. | Actual | Status |
|---|------|:--------:|:----:|:------:|:------:|
| 1 | Currency per therapist | Medium | 0.5 h | 1 h | ✅ Done — 2026-06-19 |
| 2 | Session type & length dropdowns | Medium | 2 h | 2 h | ✅ Done — 2026-06-19 |
| 3 | First-time customer intake form | High | 1 h | 2.25 h | ✅ Done — 2026-06-19 |
| 4 | Scheduling table visual improvements | Medium | 3–4 h | — | ⬜ Not started |
| 5 | Recurring appointment reminders fix | High | 1 h | — | ✅ Done — 2026-08-30 |
| 6 | Admin recurring display + cancel-series action | Medium | — | — | ✅ Done — 2026-08-30 |

---

### 1. Currency per therapist ✅ Done — 2026-06-19
`⏱ 0.5 h est. / 1 h actual` `⚑ Medium`

~~Add a currency selector (EGP/USD) to the therapist's user profile page.~~

- ~~Pattern to follow: Existing `timezone_string` user meta field on the staff profile.~~
- ~~Meta key: `_staff_currency`~~
- ~~Hooks to modify: `woocommerce_product_get_price`, `woocommerce_currency`, `woocommerce_product_get_block_cost`~~
- ~~Logic: When a product has a specific staff assigned → use that staff's currency; otherwise fall back to the current geolocation logic.~~

**What was implemented (see `functions.php`):**

- Replaced geolocation-based currency (`WC_Geolocation::geolocate_ip()`) with staff-based logic.
- Added `wellness_get_active_currency($product)` helper — checks first assigned staff's `_staff_currency` user meta, defaults to **EGP**.
- Currency selector (EGP/USD) added to staff user profile page alongside the timezone field.
- Save logic validated to `'EGP'` / `'USD'` only.
- **Currency** column in Users admin list (staff users only; `—` for others).
- All 5 currency hooks (`woocommerce_product_get_price`, `_get_sale_price`, `_get_block_cost`, `_display_cost`, `woocommerce_currency`) switched to staff-based resolution.
- AJAX pricing (`wc_appointments_calculate_costs`) handled by parsing the `form` POST parameter for `wc_appointments_field_staff` and reading `_staff_currency` directly.
- `woocommerce_currency_symbol` function updated (filter remains commented out).
- Early-call guard via `did_action('wp_loaded')` prevents `get_cart` warnings in debug.log.
- `AGENTS.md` updated to reflect staff-based currency system.

---

### 2. Session type & length dropdowns on therapist pages ✅ Done — 2026-06-19
`⏱ 2 h est. / 2 h actual` `⚑ Medium`

~~Add repeatable fields (add / remove / reorder) for session types and lengths.~~

- ~~**Session types:** Individual, Couples, Group, Family~~
- ~~**Session lengths:** 30 min, 45 min, 60 min, 90 min~~
- ~~**Storage:** Serialized arrays in post meta — `_wc_appointment_session_types`, `_wc_appointment_session_lengths`~~
- ~~**Admin:** Custom meta box on the product edit page (extend `wellness_product_meta_box_callback` pattern)~~
- ~~**Frontend:** Populate dropdowns on the booking form via WooCommerce Appointments filters~~

**What was implemented (see `functions.php` + 6 email templates):**

- **Admin — Enable toggle** (`_wc_appointment_enable_duration_options`): single checkbox in the wellness product meta box.
- **Admin — Repeater table**: JS-driven table with columns for Label, Duration (min), EGP Price, USD Price. Add/remove buttons. Stored as JSON in `_wc_appointment_duration_options`.
- **Frontend — Single "Session Type" dropdown**: injected via `appointment_form_fields` filter. Calendar, QTY, and Add-to-cart hidden until a type is selected (MutationObserver + jQuery delegated events).
- **Price replacement**: `appointments_calculated_product_price` filter returns the chosen option's price, replacing the base price entirely. Dual-currency (EGP/USD) via `wellness_get_ajax_currency()`.
- **USD price guard**: `get_usd_booking_cost()` (priority 999999 on `woocommerce_product_get_price`) skips base-price override for duration-enabled products in AJAX/cart/checkout contexts.
- **Duration override**: `appointment_form_posted_total_duration` filter sets booking slot duration from selected option.
- **Cart display**: stored as `session_type` key (non-underscore) → auto-displayed via the plugin's `get_item_data()`. Label registered via `woocommerce_appointments_data_labels`.
- **Order persistence**: `woocommerce_checkout_create_order_line_item` adds `_session_type` to order item meta. `woocommerce_checkout_order_processed` + `woocommerce_store_api_checkout_order_processed` copies to appointment post meta.
- **Order detail display**: `woocommerce_display_item_meta` filter adds Session Type to thank-you page, admin order view.
- **Email display**: All 6 overridden email templates read `_session_type` from the linked order item (via `_appointment_order_item_id` → `$wc_order->get_item()`) and display it as a dedicated row in the appointment details table.
- **Hide calendar JS**: `woocommerce_after_appointment_form_output` injects MutationObserver + change handler that hides/shows form fields and triggers `addon-duration-changed` for cost recalculation.

---

### 3. First-time customer intake form on checkout ✅ Done — 2026-06-19
`⏱ 1 h est. / 2.25 h actual` `⚑ High`

~~Use fields from `CLIENT INTAKE FORM.md` (name, birth date, address, phone, emergency contact, checkboxes, referral source).~~

~~Add via: Checkout Field Editor plugin **or** `woocommerce_checkout_fields` filter in `functions.php`~~

~~Condition: Show **only** for first-time customers (no previous completed orders)~~

~~Output: Save responses as order meta, visible to therapist~~

**What was implemented (see `functions.php` + `woocommerce/checkout/form-checkout.php`):**

- **CPT `customer_intake_form`** registered on `init` — stores intake responses with email as unique reference key. Hidden from public; visible in admin under the Appointments menu.
- **Condition helper** `wellness_should_show_intake_form()` — returns false if billing email already has an intake form CPT entry. Static cache per request. Handles AJAX checkout updates, logged-in users, and guest checkout.
- **Checkout fields** — ~28 fields injected via `woocommerce_checkout_fields` filter (priority 99999) into the `'intake'` section group. Covers: client name, birth date, address, mobile, home phone, work phone, emergency contact (name, relation, phones), preferred communication, three mental-health yes/no questions, expectations textarea, and referral source (friend name, doctor/therapist name, family, location, online search, Facebook). Section headers rendered as `<h3>`/`<h4>` via custom field types `intake_section`/`intake_subsection`.
- **Custom field rendering** — `wellness_render_intake_field()` outputs standard labels above inputs, standard selects with chevron arrows, textareas, and pill-toggle checkboxes. Custom CSS hides WooCommerce default table-row output for section header rows.
- **Multi-step checkout (7 steps)** — Step 1 (Contact & Billing) is built into the custom `form-checkout.php` template in `woodmart-child/woocommerce/checkout/`. Steps 2–6 (Personal Details, Phone & Preferences, Emergency Contact, Health Background, How You Heard About Us) render via hooks on `woocommerce_checkout_after_customer_details` with priority offsets. Step 7 (Payment) renders via custom `wellness_checkout_payment_step` action. Each step has SVG icons, back/continue buttons with chevron arrows, and step-entering animation.
- **Custom checkout template** (`woodmart-child/woocommerce/checkout/form-checkout.php`) — overrides Woodmart's template to place step 1 wrapper divs directly in the HTML, ensuring billing fields + account creation + additional information all render inside step 1. Eliminates hook-priority guesswork that previously leaked these sections outside the step wrapper.
- **Payment step** — payment methods unhooked from `woocommerce_checkout_order_review` (priority 20) and rendered inside Step 7 via `wellness_checkout_payment_step` action. Returning customers skip from step 1 → step 7.
- **AJAX intake check** — when customer clicks "Continue" from step 1, JS posts the billing email to `wp_ajax_wellness_check_intake` to determine whether intake steps should be shown or skipped.
- **Validation** — `woocommerce_checkout_process`: `intake_client_name` and `intake_mobile` required.
- **Save** — `woocommerce_checkout_order_processed` (priority 20): creates a `customer_intake_form` CPT post with all field values stored as `_intake_*` post meta. Defensive duplicate check against email.
- **Admin — Intake Forms submenu** — added under `edit.php?post_type=wc_appointment` (visible to admin + shop_staff). Custom list columns: Email, Phone, Linked Order. Read-only metabox displays all 27 intake fields in a structured table.
- **Order detail display** — `woocommerce_admin_order_data_after_billing_address`: shows intake summary card (name, birth date, mobile, home, preferred comm, emergency contact, medications, expectations excerpt) with link to full form.
- **Admin email intake summary** — `wellness_get_intake_summary_for_email()` helper fetches intake CPT by client email and returns formatted HTML table rows. Included in `admin-new-appointment.php` email template after the appointment details table, visible to therapists/staff on new session bookings.

---

### 4. Scheduling table visual improvements
`⏱ 3–4 h` `⚑ Medium`

Improve the staff appointment dashboard with a calendar/grid view and drag-and-drop rescheduling.

- **Library:** FullCalendar.io integration
- **AJAX:** Custom endpoints for rescheduling
- **Safeguards:** Conflict detection (booked slots), permission checks (staff can only move their own), cost recalculation on duration change
- **Notifications:** Trigger reschedule emails on successful drag-and-drop

---

### 5. Recurring appointment reminders fix ✅ Done — 2026-08-30
`⏱ 1 h est. / — actual` `⚑ High`

Resolved by replacing the Product Add-Ons "Repeat Appointment" flow with a custom child-theme implementation (see `RECURRING-APPOINTMENTS-PLAN.md`):

- (a) ✅ Therapist gets a **consolidated series summary** in the first confirmation email (`admin-new-appointment.php` shows "Recurring" + full session list).
- (b) ✅ **Day-before reminder to client** prompting payment — `customer-appointment-payment.php` + `wellness_send_recurring_payment_reminder()`, scheduled via Action Scheduler at (start − lead days).
- (c) ✅ **Payment confirmations** for client + therapist fire on each follow-up payment.
- (d) ✅ Follow-ups stay **unpaid** until paid; unpaid ones are **auto-cancelled** at their start time (`wellness-appointment-payment-check`) with a therapist notice.

---

### 6. Admin recurring display + cancel-series action ✅ Done — 2026-08-30
`⏱ — est. / — actual` `⚑ Medium`

- Recurring series derived from the **order hierarchy** (`wellness_get_recurring_chain`) so admin lists/details show the full series.
- Follow-up appointments recognised as recurring (`wellness_appointment_is_recurring`) via the root order item.
- **"Recurring" column + badge** on the appointments and orders admin lists — payment- and date-aware, with a popup of the full series and quick links.
- Recurring chain shown in the **main admin area** (not the sidebar) with quick links.
- **Cancel recurring series** (admin/therapist only): waive-fees checkbox (full refund vs standard cancellation fee), `admin_post` handler cancelling upcoming sessions (paid/unpaid) + pending follow-up orders, one consolidated notification to client + therapist/admin, and an admin notice.
- Emails gate the recurring block on `wellness_appointment_is_recurring()` and show actual chain dates.