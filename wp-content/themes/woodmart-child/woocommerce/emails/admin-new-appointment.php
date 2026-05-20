<?php
/**
 * Admin new appointment email — Wellness Hub custom override (sent to therapist/staff)
 *
 * Overrides: woocommerce-appointments/templates/emails/admin-new-appointment.php
 *
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

$text_align  = is_rtl() ? 'right' : 'left';
$appointment = wc_appointments_maybe_appointment_object( $appointment );
$appointment = $appointment ? $appointment : get_wc_appointment( 0 );
$wc_order    = $appointment->get_order();

// ── Client details ────────────────────────────────────────────────────────────
$client_name  = '';
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
	$client_email = $client_email ?: ( $customer ? $customer->email : '' );
}

// ── Appointment date & time ───────────────────────────────────────────────────
$start_timestamp = $appointment->get_start( 'timestamp' );
$staff_tz        = wellness_get_staff_tz( $appointment );
$date_display    = $start_timestamp ? date_i18n( 'F j, Y', $start_timestamp )         : $appointment->get_start_date();
$time_display    = $start_timestamp ? wellness_tz_format( $start_timestamp, $staff_tz ) : '';

// ── Duration ──────────────────────────────────────────────────────────────────
$duration_raw     = $appointment->get_duration();
$duration_display = is_numeric( $duration_raw ) ? intval( $duration_raw ) . ' minutes' : $duration_raw;

// ── Repeat / interval from order item addons ──────────────────────────────────
$repeat_appt = '';
$interval    = '';

if ( $wc_order ) {
	foreach ( $wc_order->get_items() as $item ) {
		$addons_html = $item->get_meta( '_tmcartepo_data' ) ?: $item->get_meta( 'Items' ) ?: '';

		// Try standard WC Product Addons meta (stored as line-item meta)
		foreach ( $item->get_meta_data() as $meta ) {
			$key = strtolower( $meta->key );
			$val = $meta->value;
			if ( strpos( $key, 'repeat' ) !== false ) {
				$repeat_appt = is_array( $val ) ? implode( ', ', $val ) : $val;
			}
			if ( $key === 'interval' ) {
				$interval = is_array( $val ) ? implode( ', ', $val ) : $val;
			}
		}

		// Fallback: parse from the formatted addons HTML string stored on the appointment
		if ( empty( $repeat_appt ) ) {
			$addons_raw = $appointment->get_addons();
			if ( $addons_raw ) {
				if ( preg_match( '!Repeat Appointment:</strong>\s*<p>(.*?)</p>!', $addons_raw, $m ) ) {
					$repeat_appt = $m[1];
				}
				if ( preg_match( '!Interval:</strong>\s*<p>(.*?)</p>!', $addons_raw, $m ) ) {
					$interval = $m[1];
				}
			}
		}
	}
}

// ── Dashboard link ────────────────────────────────────────────────────────────
$dashboard_url  = admin_url( 'edit.php?post_type=wc_appointment' );
$appointment_url = admin_url( 'post.php?post=' . $appointment->get_id() . '&action=edit' );
?>

<?php do_action( 'woocommerce_email_header', 'New Session Booked', $email ); ?>

<!-- ── Intro ─────────────────────────────────────────────────────────────── -->
<p style="margin: 0 0 8px;">Hello,</p>
<p style="margin: 0 0 24px;">
	A new therapy session has been booked and added to your schedule.
</p>

<!-- ── Appointment details box ───────────────────────────────────────────── -->
<h2 style="color: #333; font-size: 18px; font-weight: 600; margin: 0 0 12px;">
	Appointment details:
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

		<?php
		// Therapist display — reuse staff data already fetched via get_staff_ids()
		$_staff_ids   = $appointment->get_staff_ids();
		$_staff_names = [];
		foreach ( $_staff_ids as $_staff_id ) {
			$_staff_user = get_user_by( 'ID', $_staff_id );
			if ( $_staff_user ) {
				$_staff_names[] = $_staff_user->display_name;
			}
		}
		$_therapist_display = implode( ', ', $_staff_names );
		$_product_name      = $appointment->get_product_name();
		?>
		<?php if ( $_therapist_display || $_product_name ) : ?>
		<tr>
			<th style="text-align: <?php esc_attr_e( $text_align ); ?>; background: #f7f7f7; width: 38%; padding: 10px 14px; font-weight: 600;">
				Therapist
			</th>
			<td style="text-align: <?php esc_attr_e( $text_align ); ?>; padding: 10px 14px;">
				<?php echo esc_html( $_therapist_display ?: $_product_name ); ?>
			</td>
		</tr>
		<?php endif; ?>

		<tr>
			<th style="text-align: <?php esc_attr_e( $text_align ); ?>; background: #f7f7f7; width: 38%; padding: 10px 14px; font-weight: 600;">
				Appointment ID
			</th>
			<td style="text-align: <?php esc_attr_e( $text_align ); ?>; padding: 10px 14px;">
				<?php echo esc_html( $appointment->get_id() ); ?>
			</td>
		</tr>

		<tr>
			<th style="text-align: <?php esc_attr_e( $text_align ); ?>; background: #f7f7f7; width: 38%; padding: 10px 14px; font-weight: 600;">
				Date
			</th>
			<td style="text-align: <?php esc_attr_e( $text_align ); ?>; padding: 10px 14px;">
				<?php echo esc_html( $date_display ); ?>
			</td>
		</tr>

		<?php if ( $time_display ) : ?>
		<tr>
			<th style="text-align: <?php esc_attr_e( $text_align ); ?>; background: #f7f7f7; width: 38%; padding: 10px 14px; font-weight: 600;">
				Time
			</th>
			<td style="text-align: <?php esc_attr_e( $text_align ); ?>; padding: 10px 14px;">
				<?php echo esc_html( $time_display ); ?>
			</td>
		</tr>
		<?php endif; ?>

		<tr>
			<th style="text-align: <?php esc_attr_e( $text_align ); ?>; background: #f7f7f7; width: 38%; padding: 10px 14px; font-weight: 600;">
				Duration
			</th>
			<td style="text-align: <?php esc_attr_e( $text_align ); ?>; padding: 10px 14px;">
				<?php echo esc_html( $duration_display ); ?>
			</td>
		</tr>

		<?php if ( ! empty( $repeat_appt ) ) : ?>
		<tr>
			<th style="text-align: <?php esc_attr_e( $text_align ); ?>; background: #f7f7f7; width: 38%; padding: 10px 14px; font-weight: 600;">
				Repeat Appt
			</th>
			<td style="text-align: <?php esc_attr_e( $text_align ); ?>; padding: 10px 14px;">
				<?php echo esc_html( $repeat_appt ); ?>
			</td>
		</tr>
		<?php endif; ?>

		<?php if ( ! empty( $interval ) ) : ?>
		<tr>
			<th style="text-align: <?php esc_attr_e( $text_align ); ?>; background: #f7f7f7; width: 38%; padding: 10px 14px; font-weight: 600;">
				Interval
			</th>
			<td style="text-align: <?php esc_attr_e( $text_align ); ?>; padding: 10px 14px;">
				<?php echo esc_html( $interval ); ?>
			</td>
		</tr>
		<?php endif; ?>

	</tbody>
</table>

<!-- ── Footer note ───────────────────────────────────────────────────────── -->
<p style="margin: 0 0 8px;">
	You can view or manage this session from your
	<a href="<?php echo esc_url( $dashboard_url ); ?>" style="color: inherit;">therapist dashboard</a>.
</p>
<p style="margin: 0 0 24px;">
	No action is required unless you need to make a change.
</p>

<p style="margin: 0 0 6px;">Warm Regards,</p>
<p style="margin: 0 0 24px;"><strong>The Wellness Hub Team</strong></p>

<?php do_action( 'woocommerce_email_footer', $email ); ?>
