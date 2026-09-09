<?php
/**
 * Customer appointment payment reminder — Wellness Hub custom template
 *
 * Sent to a client before a recurring follow-up appointment to prompt them to
 * complete payment, including a direct order-pay link.
 *
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

$text_align  = is_rtl() ? 'right' : 'left';
$appointment = isset( $appointment ) ? wc_appointments_maybe_appointment_object( $appointment ) : null;
$appointment = $appointment ? $appointment : get_wc_appointment( 0 );

$wc_order = isset( $order ) && $order instanceof WC_Order ? $order : ( $appointment ? $appointment->get_order() : null );

// ── Customer details ─────────────────────────────────────────────────────────
$customer_first_name = $customer_first_name ?? '';
if ( empty( $customer_first_name ) && $wc_order ) {
	$customer_first_name = $wc_order->get_billing_first_name();
}
$customer_full_name = $wc_order
	? trim( $wc_order->get_billing_first_name() . ' ' . $wc_order->get_billing_last_name() )
	: '';
if ( empty( $customer_first_name ) && $customer_full_name ) {
	$_parts              = explode( ' ', trim( $customer_full_name ) );
	$customer_first_name = $_parts[0];
}

// ── Appointment date / time (customer timezone) ──────────────────────────────
$start_timestamp = $appointment ? $appointment->get_start( 'timestamp' ) : 0;
$customer_tz     = $appointment ? wellness_get_customer_tz( $appointment ) : wc_timezone_string();
$date_display    = $start_timestamp ? date_i18n( 'F j, Y', $start_timestamp ) : ( $appointment ? $appointment->get_start_date() : '' );
$time_display    = $start_timestamp ? wellness_tz_format( $start_timestamp, $customer_tz ) : '';

$duration_raw     = $appointment ? $appointment->get_duration() : 0;
$duration_display = is_numeric( $duration_raw ) ? intval( $duration_raw ) . ' minutes' : (string) $duration_raw;

// ── Session type ─────────────────────────────────────────────────────────────
$session_type = '';
if ( $appointment && $wc_order ) {
	$item_id = get_post_meta( $appointment->get_id(), '_appointment_order_item_id', true );
	if ( $item_id ) {
		$_item = $wc_order->get_item( $item_id );
		$session_type = $_item ? $_item->get_meta( '_session_type' ) : '';
	}
}

// ── Therapist ────────────────────────────────────────────────────────────────
$staff_display = '';
if ( $appointment ) {
	$staff_rows = [];
	foreach ( $appointment->get_staff_ids() as $staff_id ) {
		$staff_user = get_user_by( 'ID', $staff_id );
		if ( $staff_user ) {
			$staff_title  = get_user_meta( $staff_id, 'staff_title', true );
			$staff_rows[] = $staff_title ? $staff_user->display_name . ', ' . $staff_title : $staff_user->display_name;
		}
	}
	$staff_display = implode( '<br>', $staff_rows );
}

// ── Amount due & pay link ────────────────────────────────────────────────────
$amount_due = '';
$pay_url    = isset( $pay_url ) ? $pay_url : '';
if ( $wc_order ) {
	$currency   = $wc_order->get_currency();
	$amount_due = wc_price( $wc_order->get_total(), array( 'currency' => $currency ) );
	if ( ! $pay_url ) {
		$pay_url = $wc_order->get_checkout_payment_url();
	}
}
?>

<?php do_action( 'woocommerce_email_header', $email_heading ? $email_heading : 'Complete your appointment payment', $email ); ?>

<!-- ── Action needed box ────────────────────────────────────────────────────── -->
<table cellpadding="0" cellspacing="0" border="0"
	style="width:100%; background:#fff8e1; border-left:4px solid #f9a825; margin:0 0 24px; border-collapse:collapse;">
	<tr>
		<td style="padding:16px 20px; text-align:center;">
			<p style="margin:0 0 12px; font-weight:700; font-size:15px; color:#a15c00;">
				Action needed
			</p>
			<p style="margin:0 0 16px; font-size:14px; color:#555;">
				Your upcoming session is reserved, but payment hasn't been received yet.
				Complete payment to confirm your appointment.
			</p>
			<?php if ( $amount_due ) : ?>
				<p style="margin:0 0 16px; font-size:16px; font-weight:700; color:#333;">
					<?php echo $amount_due; // wc_price returns pre-escaped HTML ?>
				</p>
			<?php endif; ?>
			<?php if ( $pay_url ) : ?>
				<a href="<?php echo esc_url( $pay_url ); ?>"
					style="display:inline-block; padding:12px 28px; background:#4f7cff; color:#ffffff; text-decoration:none; border-radius:6px; font-size:15px; font-weight:600;">
					Pay for your session
				</a>
			<?php endif; ?>
			<p style="margin:12px 0 0; font-size:12px; color:#999;">
				Your session will be confirmed as soon as payment is received.
			</p>
		</td>
	</tr>
</table>

<!-- ── Intro ─────────────────────────────────────────────────────────────── -->
<p style="margin: 0 0 8px;">Hi <?php echo esc_html( $customer_first_name ); ?>,</p>
<p style="margin: 0 0 24px;">Here are the details for your upcoming session.</p>

<!-- ── Appointment details box ───────────────────────────────────────────── -->
<h2 style="color: #333; font-size: 18px; font-weight: 600; margin: 0 0 12px;">
	Your upcoming session
</h2>

<table cellspacing="0" cellpadding="8" border="1"
	style="width: 100%; border-collapse: collapse; margin: 0 0 24px; border-color: #e5e5e5;">
	<tbody>

		<?php if ( $staff_display ) : ?>
		<tr>
			<th style="text-align: <?php esc_attr_e( $text_align ); ?>; background: #f7f7f7; width: 38%; padding: 10px 14px; font-weight: 600;">Therapist</th>
			<td style="text-align: <?php esc_attr_e( $text_align ); ?>; padding: 10px 14px;"><?php echo wp_kses_post( $staff_display ); ?></td>
		</tr>
		<?php endif; ?>

		<tr>
			<th style="text-align: <?php esc_attr_e( $text_align ); ?>; background: #f7f7f7; width: 38%; padding: 10px 14px; font-weight: 600;">Date</th>
			<td style="text-align: <?php esc_attr_e( $text_align ); ?>; padding: 10px 14px;"><?php echo esc_html( $date_display ); ?></td>
		</tr>

		<?php if ( $time_display ) : ?>
		<tr>
			<th style="text-align: <?php esc_attr_e( $text_align ); ?>; background: #f7f7f7; width: 38%; padding: 10px 14px; font-weight: 600;">Time</th>
			<td style="text-align: <?php esc_attr_e( $text_align ); ?>; padding: 10px 14px;"><?php echo esc_html( $time_display ); ?></td>
		</tr>
		<?php endif; ?>

		<tr>
			<th style="text-align: <?php esc_attr_e( $text_align ); ?>; background: #f7f7f7; width: 38%; padding: 10px 14px; font-weight: 600;">Duration</th>
			<td style="text-align: <?php esc_attr_e( $text_align ); ?>; padding: 10px 14px;"><?php echo esc_html( $duration_display ); ?></td>
		</tr>

		<?php if ( ! empty( $session_type ) ) : ?>
		<tr>
			<th style="text-align: <?php esc_attr_e( $text_align ); ?>; background: #f7f7f7; width: 38%; padding: 10px 14px; font-weight: 600;">Session Type</th>
			<td style="text-align: <?php esc_attr_e( $text_align ); ?>; padding: 10px 14px;"><?php echo esc_html( $session_type ); ?></td>
		</tr>
		<?php endif; ?>

		<tr>
			<th style="text-align: <?php esc_attr_e( $text_align ); ?>; background: #f7f7f7; width: 38%; padding: 10px 14px; font-weight: 600;">Appointment type</th>
			<td style="text-align: <?php esc_attr_e( $text_align ); ?>; padding: 10px 14px;"><?php echo esc_html__( 'Online', 'woodmart-child' ); ?></td>
		</tr>

	</tbody>
</table>

<!-- ── Footer note ───────────────────────────────────────────────────────────── -->
<p style="margin: 0 0 24px;">
	If you have any questions or need to reschedule, please contact us.
</p>

<?php do_action( 'woocommerce_email_footer', $email ); ?>
