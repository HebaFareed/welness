<?php
/**
 * Admin appointment rescheduled email — Wellness Hub custom override (sent to therapist/staff)
 *
 * Overrides: woocommerce-appointments/templates/emails/admin-appointment-rescheduled.php
 *
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

$text_align  = is_rtl() ? 'right' : 'left';
$appointment = wc_appointments_maybe_appointment_object( $appointment );
$appointment = $appointment ? $appointment : get_wc_appointment( 0 );

// ── Client details ───────────────────────────────────────────────────────────
$wc_order    = $appointment->get_order();
$client_name = '';
$client_email = '';
$client_phone = '';
if ( $wc_order ) {
	$client_name  = trim( $wc_order->get_billing_first_name() . ' ' . $wc_order->get_billing_last_name() );
	$client_email = $wc_order->get_billing_email();
	$client_phone = $wc_order->get_billing_phone();
}
if ( empty( $client_name ) && $wc_order ) {
	$client_name = $wc_order->get_meta( 'billing_full_name', true ) ?: '';
}
if ( empty( $client_name ) ) {
	$customer     = $appointment->get_customer();
	$client_name  = $customer ? $customer->full_name : '';
	$client_email = $customer ? $customer->email : '';
}

// ── New appointment date & time ───────────────────────────────────────────────
$start_timestamp = $appointment->get_start( 'timestamp' );
$new_date        = $start_timestamp ? date_i18n( 'F j, Y', $start_timestamp )                    : $appointment->get_start_date();
$new_time        = $start_timestamp ? date_i18n( 'g:i A', $start_timestamp ) . ' (Cairo time)'   : '';

// ── Product / therapist ──────────────────────────────────────────────────────
$product_name = $appointment->get_product_name();
$staff_ids    = $appointment->get_staff_ids();
$staff_names  = [];
foreach ( $staff_ids as $staff_id ) {
	$staff_user = get_user_by( 'ID', $staff_id );
	if ( $staff_user ) {
		$staff_names[] = $staff_user->display_name;
	}
}
$therapist_display = implode( ', ', $staff_names );

// ── Dashboard link ────────────────────────────────────────────────────────────
$dashboard_url = admin_url( 'edit.php?post_type=wc_appointment' );
?>

<?php do_action( 'woocommerce_email_header', 'Session Rescheduled', $email ); ?>

<!-- ── Intro ─────────────────────────────────────────────────────────────── -->
<p style="margin: 0 0 8px;">Hello,</p>
<p style="margin: 0 0 24px;">
	The client has rescheduled the following therapy session.
</p>

<!-- ── Updated session details box ──────────────────────────────────────── -->
<h2 style="color: #333; font-size: 18px; font-weight: 600; margin: 0 0 12px;">
	Updated session details:
</h2>

<table cellspacing="0" cellpadding="8" border="1"
	style="width: 100%; border-collapse: collapse; margin: 0 0 24px; border-color: #e5e5e5;">
	<tbody>

		<?php if ( $client_name ) : ?>
		<tr>
			<th style="text-align: <?php esc_attr_e( $text_align ); ?>; background: #f7f7f7; width: 38%; padding: 10px 14px; font-weight: 600;">
				Client
			</th>
			<td style="text-align: <?php esc_attr_e( $text_align ); ?>; padding: 10px 14px;">
				<?php echo esc_html( $client_name ); ?>
				<?php if ( $client_email ) : ?>
					<br><a href="mailto:<?php echo esc_attr( $client_email ); ?>" style="color: inherit;"><?php echo esc_html( $client_email ); ?></a>
				<?php endif; ?>
				<?php if ( $client_phone ) : ?>
					<br><?php echo esc_html( $client_phone ); ?>
				<?php endif; ?>
			</td>
		</tr>
		<?php endif; ?>

		<?php if ( $therapist_display || $product_name ) : ?>
		<tr>
			<th style="text-align: <?php esc_attr_e( $text_align ); ?>; background: #f7f7f7; width: 38%; padding: 10px 14px; font-weight: 600;">
				Therapist
			</th>
			<td style="text-align: <?php esc_attr_e( $text_align ); ?>; padding: 10px 14px;">
				<?php echo esc_html( $therapist_display ?: $product_name ); ?>
			</td>
		</tr>
		<?php endif; ?>

		<tr>
			<th style="text-align: <?php esc_attr_e( $text_align ); ?>; background: #f7f7f7; width: 38%; padding: 10px 14px; font-weight: 600;">
				Appointment #
			</th>
			<td style="text-align: <?php esc_attr_e( $text_align ); ?>; padding: 10px 14px;">
				<?php echo esc_html( $appointment->get_id() ); ?>
			</td>
		</tr>

		<tr>
			<th style="text-align: <?php esc_attr_e( $text_align ); ?>; background: #f7f7f7; width: 38%; padding: 10px 14px; font-weight: 600;">
				New Date
			</th>
			<td style="text-align: <?php esc_attr_e( $text_align ); ?>; padding: 10px 14px;">
				<?php echo esc_html( $new_date ); ?>
			</td>
		</tr>

		<?php if ( $new_time ) : ?>
		<tr>
			<th style="text-align: <?php esc_attr_e( $text_align ); ?>; background: #f7f7f7; width: 38%; padding: 10px 14px; font-weight: 600;">
				New Time
			</th>
			<td style="text-align: <?php esc_attr_e( $text_align ); ?>; padding: 10px 14px;">
				<?php echo esc_html( $new_time ); ?>
			</td>
		</tr>
		<?php endif; ?>

	</tbody>
</table>

<!-- ── Footer note ───────────────────────────────────────────────────────── -->
<p style="margin: 0 0 8px;">No action is required from you at this time.</p>
<p style="margin: 0 0 24px;">
	You can view or manage your schedule from your
	<a href="<?php echo esc_url( $dashboard_url ); ?>" style="color: inherit;">therapist dashboard</a>.
</p>

<?php do_action( 'woocommerce_email_footer', $email ); ?>
