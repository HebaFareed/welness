<?php
/**
 * Customer appointment intake reminder — Wellness Hub custom template
 *
 * Sent ~24h after a paid booking when the client has not yet completed their
 * intake form, with a direct link back to the order-received page where the
 * form lives.
 *
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

$text_align = is_rtl() ? 'right' : 'left';

$wc_order = isset( $order ) && $order instanceof WC_Order ? $order : null;

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

$order_number = $wc_order ? $wc_order->get_order_number() : '';
$intake_url   = isset( $intake_url ) ? $intake_url : ( $wc_order ? $wc_order->get_checkout_order_received_url() : '' );

// ── Appointment context (best effort; intake matters regardless) ─────────────
$appointment   = isset( $appointment ) && is_object( $appointment ) ? $appointment : null;
$start_display = '';
$staff_display = '';
if ( $appointment ) {
	$start_timestamp = $appointment->get_start( 'timestamp' );
	if ( $start_timestamp ) {
		$customer_tz   = wellness_get_customer_tz( $appointment );
		$start_display = wellness_tz_format( $start_timestamp, $customer_tz, 'F j, Y \a\t g:i A' );
	}
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
?>

<?php
$heading = ! empty( $email_heading ) ? $email_heading : 'One last step: your intake form';
do_action( 'woocommerce_email_header', $heading, $email );
?>

<!-- ── Action box ──────────────────────────────────────────────────────────── -->
<table cellpadding="0" cellspacing="0" border="0"
	style="width:100%; background:#eef4f6; border-left:4px solid #4b7f8c; margin:0 0 24px; border-collapse:collapse;">
	<tr>
		<td style="padding:16px 20px; text-align:center;">
			<p style="margin:0 0 12px; font-weight:700; font-size:15px; color:#2c3e4f;">
				Complete your intake form
			</p>
			<p style="margin:0 0 16px; font-size:14px; color:#555;">
				To help your therapist prepare for your session, please take a couple of
				minutes to complete the confidential intake form. Your details are shared
				only with your therapist.
			</p>
			<?php if ( $intake_url ) : ?>
				<a href="<?php echo esc_url( $intake_url ); ?>"
					style="display:inline-block; padding:12px 28px; background:#4b7f8c; color:#ffffff; text-decoration:none; border-radius:6px; font-size:15px; font-weight:600;">
					Complete your intake form
				</a>
			<?php endif; ?>
		</td>
	</tr>
</table>

<!-- ── Intro ─────────────────────────────────────────────────────────────── -->
<p style="margin: 0 0 8px;">Hi <?php echo esc_html( $customer_first_name ); ?>,</p>
<p style="margin: 0 0 24px;">
	Thank you for booking with The Wellness Hub. Your appointment is confirmed
	<?php if ( $order_number ) : ?>— order #<?php echo esc_html( $order_number ); ?><?php endif; ?>.
	Before your session, we ask every new client to complete a short intake form so
	your therapist can prepare for your visit.
</p>

<!-- ── Appointment details box (when available) ──────────────────────────── -->
<?php if ( $appointment ) : ?>
	<h2 style="color: #333; font-size: 18px; font-weight: 600; margin: 0 0 12px;">
		Your appointment
	</h2>

	<table cellspacing="0" cellpadding="8" border="1"
		style="width: 100%; border-collapse: collapse; margin: 0 0 24px; border-color: #e5e5e5;">
		<tbody>
			<?php if ( $start_display ) : ?>
				<tr>
					<th style="text-align: <?php echo esc_attr( $text_align ); ?>; background: #f7f7f7; width: 38%; padding: 10px 14px; font-weight: 600;">Date &amp; time</th>
					<td style="text-align: <?php echo esc_attr( $text_align ); ?>; padding: 10px 14px;"><?php echo esc_html( $start_display ); ?></td>
				</tr>
			<?php endif; ?>
			<?php if ( ! empty( $staff_display ) ) : ?>
				<tr>
					<th style="text-align: <?php echo esc_attr( $text_align ); ?>; background: #f7f7f7; width: 38%; padding: 10px 14px; font-weight: 600;">Therapist</th>
					<td style="text-align: <?php echo esc_attr( $text_align ); ?>; padding: 10px 14px;"><?php echo $staff_display; // WPCS: contains <br> ?></td>
				</tr>
			<?php endif; ?>
			<?php if ( $order_number ) : ?>
				<tr>
					<th style="text-align: <?php echo esc_attr( $text_align ); ?>; background: #f7f7f7; width: 38%; padding: 10px 14px; font-weight: 600;">Order</th>
					<td style="text-align: <?php echo esc_attr( $text_align ); ?>; padding: 10px 14px;">#<?php echo esc_html( $order_number ); ?></td>
				</tr>
			<?php endif; ?>
			<tr>
				<th style="text-align: <?php echo esc_attr( $text_align ); ?>; background: #f7f7f7; width: 38%; padding: 10px 14px; font-weight: 600;">Appointment type</th>
				<td style="text-align: <?php echo esc_attr( $text_align ); ?>; padding: 10px 14px;"><?php echo esc_html__( 'Online', 'woodmart-child' ); ?></td>
			</tr>
		</tbody>
	</table>
<?php endif; ?>

<!-- ── Footer note ─────────────────────────────────────────────────────────── -->
<p style="margin: 0 0 8px;">
	If you have already completed the form, please ignore this email.
</p>
<p style="margin: 0 0 24px;">
	If you have any questions or need to reschedule, please contact us.
</p>

<p style="margin: 0 0 6px;">Warm Regards,</p>
<p style="margin: 0 0 24px;"><strong>The Wellness Hub Team</strong></p>

<?php do_action( 'woocommerce_email_footer', $email ); ?>
