# Multi-Timezone Support — Implementation Summary

**Date:** May 20, 2026
**Scope:** WooCommerce Appointments — Wellness site
**Files changed:** `wp-content/themes/woodmart-child/functions.php` + 6 email templates in `wp-content/themes/woodmart-child/woocommerce/emails/`
**Plugin version:** WooCommerce Appointments v4.26.4

---

## Non-Technical Summary

The site serves clients and therapists across multiple countries and timezones. Before this work, all times everywhere — booking confirmations, cancellations, admin notifications, therapist schedules — were shown in the server's clock time, which meant a therapist in Auckland had to manually calculate what "9:00 AM" meant for them. This update makes the system timezone-aware throughout:

- Clients receive emails showing their appointment in **their own local time**
- Therapists receive emails showing appointments in **their own local time**
- Therapists set their availability by typing their own local hours — no conversion needed
- The admin appointments list shows each therapist their sessions in their own clock time

---

## Technical Summary

All changes in `woodmart-child/functions.php` + 6 WooCommerce email template overrides. No plugin files modified.

| Phase | What it does | How |
|---|---|---|
| **1** | Timezone-aware formatting helpers | `wellness_tz_format()`, `wellness_get_customer_tz()`, `wellness_get_staff_tz()` with fallback label chain for raw UTC offsets |
| **2** | Detect & store client timezone | JS cookie on every page load; saved to `_customer_timezone` order meta at checkout |
| **3** | Staff timezone profile field | IANA timezone dropdown on user profile page; saved to `timezone_string` user meta |
| **4** | Admin appointment list in staff tz | Filters `woocommerce_appointments_get_start/end_date_with_time` to reformat for the logged-in staff member |
| **5** | Staff availability entry in their tz | JS on profile page converts stored site-tz values to staff tz on load, shows site-tz hints, converts back on submit |

### 6 Email Templates Updated

| Template | Timezone used |
|---|---|
| `customer-appointment-confirmed.php` | Customer's detected timezone |
| `customer-appointment-cancelled.php` | Customer's detected timezone |
| `customer-appointment-rescheduled.php` | Customer's detected timezone |
| `admin-new-appointment.php` | Staff member's timezone |
| `admin-appointment-cancelled.php` | Staff member's timezone |
| `admin-appointment-rescheduled.php` | Staff member's timezone |

### Key Technical Notes

- `wc_timezone_string()` returns `'+03:00'` (raw offset) when WP admin timezone is set to "UTC+3" rather than a city. `wc_appointment_get_timezone_name()` returns empty for this format — handled with a multi-pattern fallback label chain in `wellness_tz_format()`.
- Phase 5 JS uses pure arithmetic (no `Intl.DateTimeFormat`) for input value conversion, keeping it compatible with all browsers and avoiding IANA-string requirements for the site timezone.
- Day-rollover detection in Phase 5 hints: if converting staff time to site time crosses midnight, the hint shows `· prev day` or `· next day`.
- The form submit intercept in Phase 5 targets `$avail.closest('form')` — more robust than guessing the WordPress profile form ID.

---

## Code Changes

### `functions.php` — Phase 1: Helpers

```php
function wellness_tz_format( $timestamp, $tz_string, $format = 'g:i A' ) {
    if ( ! $timestamp ) { return ''; }
    $site_tz = wc_timezone_string();
    $tz      = ( $tz_string !== '' && $tz_string !== null ) ? $tz_string : $site_tz;
    $label   = wc_appointment_get_timezone_name( $tz );
    // Fallback label chain for Etc/GMT±N, ±HH:MM offsets, generic IANA, etc.
    if ( ! $label ) {
        if ( preg_match( '/^Etc\/GMT([+-])(\d+)$/', $tz, $m ) ) {
            $label = 'UTC' . ( $m[1] === '+' ? '-' : '+' ) . $m[2];
        } elseif ( preg_match( '/^([+-])(\d{1,2}):(\d{2})$/', $tz, $m ) ) {
            $h = (int) $m[2]; $min = (int) $m[3];
            $label = 'UTC' . $m[1] . $h . ( $min > 0 ? ':' . str_pad($min,2,'0',STR_PAD_LEFT) : '' );
        } elseif ( strpos( $tz, '/' ) !== false ) {
            $parts = explode( '/', $tz ); $label = str_replace('_',' ',end($parts));
        } else { $label = $tz ?: 'UTC'; }
    }
    if ( $tz === $site_tz ) {
        return date_i18n( $format, $timestamp ) . ' (' . $label . ')';
    }
    $formatted = wc_appointment_timezone_locale( 'site', 'user', $timestamp, $format, $tz );
    return $formatted . ' (' . $label . ')';
}

function wellness_get_customer_tz( $appointment ) {
    $tz = method_exists($appointment,'get_local_timezone') ? $appointment->get_local_timezone() : '';
    if ( ! $tz ) {
        $order = $appointment->get_order();
        if ( $order ) { $tz = $order->get_meta('_customer_timezone', true); }
    }
    return $tz ?: wc_timezone_string();
}

function wellness_get_staff_tz( $appointment ) {
    foreach ( (array) $appointment->get_staff_ids() as $staff_id ) {
        $tz = get_user_meta( (int) $staff_id, 'timezone_string', true );
        if ( $tz ) { return $tz; }
    }
    return wc_timezone_string();
}
```

---

### `functions.php` — Phase 2: Client timezone detection

```php
// Step 2a — Write browser timezone into the plugin's cookie (front-end, wp_footer)
add_action( 'wp_footer', function () { ?>
    <script id="wellness-tz-detect">
    (function () {
        var name = 'appointments_time_zone';
        var has  = document.cookie.split(';').some(function(c) {
            return c.trim().substring(0, name.length + 1) === name + '=';
        });
        if ( has ) return;
        try {
            var tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
            if ( tz ) {
                document.cookie = name + '=' + tz + '; path=/; max-age=2592000; SameSite=Lax';
            }
        } catch(e) {}
    })();
    </script>
<?php }, 20 );

// Step 2b — Persist timezone to order meta at checkout
add_action( 'woocommerce_checkout_order_created', function ( $order ) {
    if ( empty( $_COOKIE['appointments_time_zone'] ) ) { return; }
    $tz = sanitize_text_field( wp_unslash( $_COOKIE['appointments_time_zone'] ) );
    if ( ! in_array( $tz, timezone_identifiers_list(), true ) ) { return; }
    $order->update_meta_data( '_customer_timezone', $tz );
    $order->save();
} );
```

---

### `functions.php` — Phase 3: Staff timezone profile field

```php
add_action( 'show_user_profile', 'wellness_staff_timezone_field' );
add_action( 'edit_user_profile', 'wellness_staff_timezone_field' );

function wellness_staff_timezone_field( $user ) {
    if ( ! in_array( 'shop_staff', (array) $user->roles, true ) ) { return; }
    $current_tz = get_user_meta( $user->ID, 'timezone_string', true ) ?: wc_timezone_string();
    ?>
    <h3>Appointment Timezone</h3>
    <table class="form-table"><tr>
        <th><label for="timezone_string">Your timezone</label></th>
        <td>
            <select name="timezone_string" id="timezone_string">
                <?php echo wp_timezone_choice( $current_tz, get_user_locale( $user ) ); ?>
            </select>
        </td>
    </tr></table>
    <?php
    wp_nonce_field( 'wellness_staff_tz_' . $user->ID, '_wellness_tz_nonce' );
}

add_action( 'personal_options_update',  'wellness_save_staff_timezone' );
add_action( 'edit_user_profile_update', 'wellness_save_staff_timezone' );

function wellness_save_staff_timezone( $user_id ) {
    if ( ! isset( $_POST['_wellness_tz_nonce'] )
        || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wellness_tz_nonce'] ) ), 'wellness_staff_tz_' . $user_id )
    ) { return; }
    if ( ! current_user_can( 'edit_user', $user_id ) || empty( $_POST['timezone_string'] ) ) { return; }
    $tz = sanitize_text_field( wp_unslash( $_POST['timezone_string'] ) );
    if ( in_array( $tz, timezone_identifiers_list(), true ) || preg_match( '/^UTC[+-]?[\d.]*$/', $tz ) ) {
        update_user_meta( $user_id, 'timezone_string', $tz );
    }
}
```

---

### `functions.php` — Phase 4: Admin appointment list in staff timezone

```php
add_filter( 'woocommerce_appointments_get_start_date_with_time', 'wellness_admin_appointment_tz', 15, 3 );
add_filter( 'woocommerce_appointments_get_end_date_with_time',   'wellness_admin_appointment_tz', 15, 3 );

function wellness_admin_appointment_tz( $timestring, $appointment, $timestamp ) {
    if ( ! is_user_logged_in() || ! is_admin() ) { return $timestring; }
    $user = wp_get_current_user();
    if ( ! in_array( 'shop_staff', (array) $user->roles, true ) ) { return $timestring; }
    $tzstring = get_user_meta( $user->ID, 'timezone_string', true );
    if ( ! $tzstring ) { return $timestring; }
    return wellness_tz_format(
        $timestamp,
        $tzstring,
        wc_appointments_date_format() . ', ' . wc_appointments_time_format()
    );
}
```

---

### `functions.php` — Phase 5: Staff availability entry in their timezone

```php
add_action( 'admin_footer', 'wellness_staff_avail_tz_hints', 20 );

function wellness_staff_avail_tz_hints() {
    // Guards: admin only, user-edit/profile screen, shop_staff role,
    //         valid IANA timezone set, different from site tz.
    // ... (PHP: compute $diff_min, $site_label, $staff_city) ...
    ?>
    <script id="wellness-avail-tz-hint">
    (function($) {
        'use strict';
        var diffMin   = <?php echo (int) $diff_min; ?>;   // staff - site (minutes)
        var siteLabel = <?php echo wp_json_encode( $site_label ); ?>;
        var staffCity = <?php echo wp_json_encode( $staff_city ); ?>;

        function pad2(n) { return n < 10 ? '0' + n : '' + n; }
        function wrap(m) { return ((m % 1440) + 1440) % 1440; }
        function parseHHMM(s) {
            if (!s || !/^\d{1,2}:\d{2}$/.test(s)) { return null; }
            var p = s.split(':');
            return parseInt(p[0], 10) * 60 + parseInt(p[1], 10);
        }
        function toHHMM(m) { m = wrap(m); return pad2(Math.floor(m/60)) + ':' + pad2(m%60); }
        function toDisplay(m) {
            m = wrap(m); var h = Math.floor(m/60), min = m%60;
            var ampm = h >= 12 ? 'PM' : 'AM'; h = h % 12 || 12;
            return h + ':' + pad2(min) + ' ' + ampm;
        }

        function updateHint($inp) {
            var staffMin = parseHHMM($inp.val());
            var $hint = $inp.next('.wellness-tz-hint');
            if (!$hint.length) {
                $hint = $('<span>', {'class':'wellness-tz-hint'})
                    .css({display:'block',fontSize:'11px',color:'#777',marginTop:'3px',fontStyle:'italic'});
                $inp.after($hint);
            }
            if (staffMin === null) { $hint.text(''); return; }
            var rawSite  = staffMin - diffMin;
            var dayLabel = rawSite < 0 ? ' · prev day' : (rawSite >= 1440 ? ' · next day' : '');
            $hint.text('= ' + toDisplay(rawSite) + ' (' + siteLabel + dayLabel + ')');
        }

        function attachHints() {
            $('.from_time .time-picker, .to_time .time-picker').each(function() {
                var $inp = $(this);
                if (!$inp.data('wellness-tz-bound')) {
                    var siteMin = parseHHMM($inp.val());
                    if (siteMin !== null) { $inp.val(toHHMM(siteMin + diffMin)); }  // site → staff
                    $inp.data('wellness-tz-bound', true)
                        .on('input.wellness-tz change.wellness-tz', function() { updateHint($(this)); });
                }
                updateHint($inp);
            });
        }

        $(function() {
            var $avail = $('#appointments_availability');
            // Banner
            if ($avail.length && !$avail.find('.wellness-tz-notice').length) {
                $('<p>', {'class':'wellness-tz-notice'})
                    .css({background:'#fff8c5', borderLeft:'4px solid #e6a700',
                          padding:'8px 12px', margin:'0 0 12px', fontSize:'12px'})
                    .html('<strong>&#9888; Use your local time (' + $('<span>').text(staffCity).html() +
                          ' time) when entering these hours.</strong> ' +
                          'Everything will be adjusted automatically when you save. ' +
                          'The small note below each field is just for your reference.')
                    .prependTo($avail);
            }
            attachHints();
            $(document).on('click', '.add_grid_row', function() { setTimeout(attachHints, 150); });
            // Submit: convert staff tz → site tz before POST
            $avail.closest('form').on('submit.wellness-tz', function() {
                $('.from_time .time-picker, .to_time .time-picker').each(function() {
                    var staffMin = parseHHMM($(this).val());
                    if (staffMin !== null) { $(this).val(toHHMM(staffMin - diffMin)); }
                });
            });
        });
    })(jQuery);
    </script>
    <?php
}
```

---

### Email Templates — Customer-facing (use `wellness_get_customer_tz`)

**`customer-appointment-confirmed.php`**
```php
$start_timestamp = $appointment->get_start('timestamp');
$customer_tz     = wellness_get_customer_tz($appointment);
$start_formatted = $start_timestamp
    ? wellness_tz_format($start_timestamp, $customer_tz, 'F j, Y \a\t g:i A')
    : $appointment->get_start_date();
```

**`customer-appointment-cancelled.php`**
```php
$customer_tz      = wellness_get_customer_tz($appointment);
$datetime_display = $start_timestamp
    ? date_i18n('F j, Y', $start_timestamp) . ' at ' . wellness_tz_format($start_timestamp, $customer_tz)
    : $appointment->get_start_date();
```

**`customer-appointment-rescheduled.php`**
```php
$customer_tz     = wellness_get_customer_tz($appointment);
$new_time_display = $start_timestamp
    ? wellness_tz_format($start_timestamp, $customer_tz)
    : $appointment->get_start_date();
```

---

### Email Templates — Admin/staff-facing (use `wellness_get_staff_tz`)

**`admin-new-appointment.php`**, **`admin-appointment-cancelled.php`**, **`admin-appointment-rescheduled.php`**
```php
$start_timestamp = $appointment->get_start('timestamp');
$staff_tz        = wellness_get_staff_tz($appointment);
$date_display    = $start_timestamp ? date_i18n('F j, Y', $start_timestamp) : $appointment->get_start_date();
$time_display    = $start_timestamp ? wellness_tz_format($start_timestamp, $staff_tz) : '';
```

---

## Testing Checklist

### Phase 2 — Client timezone detection
- [ ] Book an appointment from a browser with a known timezone
- [ ] Confirm `_customer_timezone` is saved on the order (check via WooCommerce order meta)
- [ ] Test with JS disabled — confirm fallback to site timezone with no fatal error

### Phase 3 — Staff timezone profile
- [ ] Open a staff user profile in WP admin
- [ ] Confirm the timezone dropdown appears (only for `shop_staff` role)
- [ ] Select a timezone, save — confirm it persists on reload
- [ ] Confirm it does NOT appear on admin/customer profiles

### Phase 1 + Email templates — Correct times in emails
- [ ] Book a test appointment; confirm the customer confirmation email shows time in the customer's timezone with a readable label (e.g. `9:00 AM (Auckland)`)
- [ ] Cancel the appointment; confirm the cancellation email shows the correct timezone
- [ ] Reschedule; confirm the rescheduled email shows correct timezone
- [ ] Confirm all 3 admin notification emails show the time in the assigned staff's timezone
- [ ] Test with a staff member who has no timezone set — confirm fallback to site timezone, no blank/broken label
- [ ] Test with a client whose timezone was not detected — confirm fallback to site timezone, no blank label

### Phase 4 — Appointments list in staff timezone
- [ ] Log in as a `shop_staff` member with a set timezone
- [ ] Open the Appointments list — confirm times show in that staff's local time
- [ ] Log in as admin — confirm times show in site timezone (no interference)

### Phase 5 — Availability entry
- [ ] Open the staff profile page — confirm yellow banner appears with their city name
- [ ] Confirm existing availability rows show times in the staff's local time (not site time)
- [ ] Confirm the per-field hint shows the site-tz equivalent
- [ ] Enter a time that crosses midnight when converted — confirm `prev day` or `next day` label in hint
- [ ] Add a new availability rule, enter a time — confirm hint appears on the new row
- [ ] Save and reload — confirm times are still correct (no double-conversion)
- [ ] Verify stored values in the database are in site timezone

### Edge Cases (all phases)
- [ ] Staff with no timezone set — no banner, no JS errors, emails fall back to site tz
- [ ] Staff timezone = site timezone — no banner, no conversion attempted
- [ ] Site timezone configured as a raw UTC offset (`+03:00`) — labels display correctly (not blank)
- [ ] Site timezone configured as a city name (`Africa/Cairo`) — also works correctly
