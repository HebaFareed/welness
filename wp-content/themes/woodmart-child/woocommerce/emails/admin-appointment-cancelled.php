<?php

/**
 * Admin appointment cancelled email — Wellness Hub custom override (sent to therapist/staff)
 *
 * Overrides: woocommerce-appointments/templates/emails/admin-appointment-cancelled.php
 *
 * @version 1.0.0
 */

defined('ABSPATH') || exit;

$text_align  = is_rtl() ? 'right' : 'left';
$appointment = wc_appointments_maybe_appointment_object($appointment);
$appointment = $appointment ? $appointment : get_wc_appointment(0);

// ── Client details ──────────────────────────────────────────────────────────
$wc_order    = $appointment->get_order();
$client_name = '';
$client_email = '';
$client_phone = '';
if ($wc_order) {
	$client_name  = trim($wc_order->get_billing_first_name() . ' ' . $wc_order->get_billing_last_name());
	$client_email = $wc_order->get_billing_email();
	$client_phone = $wc_order->get_billing_phone();
}
if (empty($client_name) && $wc_order) {
	$client_name = $wc_order->get_meta('billing_full_name', true) ?: '';
}
if (empty($client_name)) {
	$customer     = $appointment->get_customer();
	$client_name  = $customer ? $customer->full_name : '';
	$client_email = $client_email ?: ($customer ? $customer->email : '');
}

// ── Appointment date & time ───────────────────────────────────────────────────
$start_timestamp = $appointment->get_start('timestamp');
$staff_tz        = wellness_get_staff_tz( $appointment );
$date_display    = $start_timestamp ? date_i18n('F j, Y', $start_timestamp)            : $appointment->get_start_date();
$time_display    = $start_timestamp ? wellness_tz_format( $start_timestamp, $staff_tz ) : '';

// ── Duration ──────────────────────────────────────────────────────────────────
$duration_raw     = $appointment->get_duration();
$duration_display = is_numeric($duration_raw) ? intval($duration_raw) . ' minutes' : $duration_raw;

// ── Session Type (from order item meta) ──
$session_type = '';
if ( $wc_order ) {
	$item_id = get_post_meta( $appointment->get_id(), '_appointment_order_item_id', true );
	if ( $item_id ) {
		$_item = $wc_order->get_item( $item_id );
		$session_type = $_item ? $_item->get_meta( '_session_type' ) : '';
	}
}

// ── Product / therapist ──────────────────────────────────────────────────────
$product_name = $appointment->get_product_name();
$staff_ids    = $appointment->get_staff_ids();
$staff_names  = [];
foreach ($staff_ids as $staff_id) {
	$staff_user = get_user_by('ID', $staff_id);
	if ($staff_user) {
		$staff_names[] = $staff_user->display_name;
	}
}
$therapist_display = implode(', ', $staff_names);

// ── Price & discount ─────────────────────────────────────────────────────────
$price_display    = '';
$discount_display = '';
$coupon_codes     = [];

if ( $wc_order ) {
	foreach ( $wc_order->get_items() as $item ) {
		$line_total    = floatval( $item->get_total() );
		$line_subtotal = floatval( $item->get_subtotal() );
		if ( $line_total > 0 || $line_subtotal > 0 ) {
			$price_display = wc_price( $line_subtotal, [ 'currency' => $wc_order->get_currency() ] );
			$item_discount = $line_subtotal - $line_total;
			if ( $item_discount > 0.001 ) {
				$discount_display = wc_price( $item_discount, [ 'currency' => $wc_order->get_currency() ] );
			}
			break;
		}
	}
	$coupon_codes = $wc_order->get_coupon_codes();
	if ( empty( $discount_display ) ) {
		$order_discount = floatval( $wc_order->get_discount_total() );
		if ( $order_discount > 0.001 ) {
			$discount_display = wc_price( $order_discount, [ 'currency' => $wc_order->get_currency() ] );
		}
	}
}

// ── Dashboard link ────────────────────────────────────────────────────────────
// ── Unpaid recurring cancellation flag ────────────────────────────────────────
$unpaid_recurring = get_post_meta( $appointment->get_id(), '_recurring_unpaid_cancelled', true ) === 'yes';

$dashboard_url = admin_url('edit.php?post_type=wc_appointment');
?>

<?php do_action('woocommerce_email_header', 'Session Cancelled', $email); ?>

<!-- ── Intro ─────────────────────────────────────────────────────────────── -->
<p style="margin: 0 0 8px;">Hello,</p>
<p style="margin: 0 0 24px;">
	This is to inform you that the following therapy session has been cancelled.
</p>

<?php if ( $unpaid_recurring ) : ?>
<!-- ── Unpaid recurring notice ────────────────────────────────────────────── -->
<table cellpadding="0" cellspacing="0" border="0"
	style="width:100%; background:#fff8e1; border-left:4px solid #f9a825; margin:0 0 24px; border-collapse:collapse;">
	<tr>
		<td style="padding:16px 20px;">
			<p style="margin:0 0 8px; font-weight:700; font-size:14px; color:#a15c00;">
				&#9888; Unpaid recurring appointment
			</p>
			<p style="margin:0; font-size:13px; color:#555; line-height:1.7;">
				This was a recurring appointment that was not paid on time, so it was automatically cancelled.
			</p>
		</td>
	</tr>
</table>
<?php endif; ?>

<!-- ── Session details box ───────────────────────────────────────────────── -->
<h2 style="color: #333; font-size: 18px; font-weight: 600; margin: 0 0 12px;">
	Session details:
</h2>

<table cellspacing="0" cellpadding="8" border="1"
	style="width: 100%; border-collapse: collapse; margin: 0 0 24px; border-color: #e5e5e5;">
	<tbody>

		<?php if ($client_name) : ?>
			<tr>
				<th style="text-align: <?php esc_attr_e($text_align); ?>; background: #f7f7f7; width: 38%; padding: 10px 14px; font-weight: 600;">
					Client
				</th>
				<td style="text-align: <?php esc_attr_e($text_align); ?>; padding: 10px 14px;">
					<?php echo esc_html($client_name); ?>
					<?php if ($client_email) : ?>
						<br><a href="mailto:<?php echo esc_attr($client_email); ?>" style="color: inherit;"><?php echo esc_html($client_email); ?></a>
					<?php endif; ?>
					<?php if ($client_phone) : ?>
						<br><?php echo esc_html($client_phone); ?>
					<?php endif; ?>
				</td>
			</tr>
		<?php endif; ?>
		<?php if ($therapist_display || $product_name) : ?>
			<tr>
				<th style="text-align: <?php esc_attr_e($text_align); ?>; background: #f7f7f7; width: 38%; padding: 10px 14px; font-weight: 600;">
					Therapist
				</th>
				<td style="text-align: <?php esc_attr_e($text_align); ?>; padding: 10px 14px;">
					<?php echo esc_html($therapist_display ?: $product_name); ?>
				</td>
			</tr>
		<?php endif; ?>
		<tr>
			<th style="text-align: <?php esc_attr_e($text_align); ?>; background: #f7f7f7; width: 38%; padding: 10px 14px; font-weight: 600;">
				Appointment #
			</th>
			<td style="text-align: <?php esc_attr_e($text_align); ?>; padding: 10px 14px;">
				<?php echo esc_html($appointment->get_id()); ?>
			</td>
		</tr>

		<tr>
			<th style="text-align: <?php esc_attr_e($text_align); ?>; background: #f7f7f7; width: 38%; padding: 10px 14px; font-weight: 600;">
				Date
			</th>
			<td style="text-align: <?php esc_attr_e($text_align); ?>; padding: 10px 14px;">
				<?php echo esc_html($date_display); ?>
			</td>
		</tr>

		<?php if ($time_display) : ?>
			<tr>
				<th style="text-align: <?php esc_attr_e($text_align); ?>; background: #f7f7f7; width: 38%; padding: 10px 14px; font-weight: 600;">
					Time
				</th>
				<td style="text-align: <?php esc_attr_e($text_align); ?>; padding: 10px 14px;">
					<?php echo esc_html($time_display); ?>
				</td>
			</tr>
		<?php endif; ?>

		<tr>
			<th style="text-align: <?php esc_attr_e($text_align); ?>; background: #f7f7f7; width: 38%; padding: 10px 14px; font-weight: 600;">
				Duration
			</th>
			<td style="text-align: <?php esc_attr_e($text_align); ?>; padding: 10px 14px;">
				<?php echo esc_html($duration_display); ?>
			</td>
		</tr>

		<?php if ( ! empty( $session_type ) ) : ?>
		<tr>
			<th style="text-align: <?php esc_attr_e($text_align); ?>; background: #f7f7f7; width: 38%; padding: 10px 14px; font-weight: 600;">
				Session Type
			</th>
			<td style="text-align: <?php esc_attr_e($text_align); ?>; padding: 10px 14px;">
				<?php echo esc_html( $session_type ); ?>
			</td>
		</tr>
		<?php endif; ?>

		<?php if ( $price_display ) : ?>
		<tr>
			<th style="text-align: <?php esc_attr_e($text_align); ?>; background: #f7f7f7; width: 38%; padding: 10px 14px; font-weight: 600;">
				Price
			</th>
			<td style="text-align: <?php esc_attr_e($text_align); ?>; padding: 10px 14px;">
				<?php echo $price_display; // wc_price returns pre-escaped HTML ?>
			</td>
		</tr>
		<?php endif; ?>

		<?php if ( $discount_display ) : ?>
		<tr>
			<th style="text-align: <?php esc_attr_e($text_align); ?>; background: #f7f7f7; width: 38%; padding: 10px 14px; font-weight: 600;">
				Discount
			</th>
			<td style="text-align: <?php esc_attr_e($text_align); ?>; padding: 10px 14px;">
				&minus;<?php echo $discount_display; // wc_price returns pre-escaped HTML ?>
				<?php if ( ! empty( $coupon_codes ) ) : ?>
					<span style="color: #666; font-size: 12px;">
						(<?php echo esc_html( implode( ', ', $coupon_codes ) ); ?>)
					</span>
				<?php endif; ?>
			</td>
		</tr>
		<?php endif; ?>

	</tbody>
</table>

<!-- ── Footer note ───────────────────────────────────────────────────────── -->
<p style="margin: 0 0 8px;">No action is required from you at this time.</p>
<p style="margin: 0 0 24px;">
	You can view or manage your schedule from your
	<a href="<?php echo esc_url($dashboard_url); ?>" style="color: inherit;">therapist dashboard</a>.
</p>

<?php do_action('woocommerce_email_footer', $email); ?>