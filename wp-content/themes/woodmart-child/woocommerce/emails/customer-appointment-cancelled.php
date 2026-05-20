<?php
/**
 * Customer appointment cancelled email — Wellness Hub custom override
 *
 * Overrides: woocommerce-appointments/templates/emails/customer-appointment-cancelled.php
 *
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

$appointment = wc_appointments_maybe_appointment_object( $appointment );
$appointment = $appointment ? $appointment : get_wc_appointment( 0 );

// ── Customer details ────────────────────────────────────────────────────────
$wc_order           = $appointment->get_order();
$customer_first_name = '';
$customer_email     = '';
$customer_phone     = '';

if ( $wc_order ) {
	$customer_first_name = $wc_order->get_billing_first_name();
	$customer_email      = $wc_order->get_billing_email();
	$customer_phone      = $wc_order->get_billing_phone();
} else {
	$customer = $appointment->get_customer();
	if ( $customer ) {
		$name_parts          = explode( ' ', $customer->full_name );
		$customer_first_name = $name_parts[0];
		$customer_email      = $customer->email;
	}
}

// ── Customer full name ────────────────────────────────────────────────────────
$customer_full_name = '';
if ( $wc_order ) {
	$customer_full_name = trim( $wc_order->get_billing_first_name() . ' ' . $wc_order->get_billing_last_name() );
} elseif ( isset( $customer ) ) {
	$customer_full_name = $customer->full_name;
}
if ( empty( $customer_full_name ) && $wc_order ) {
	$customer_full_name = $wc_order->get_meta( 'billing_full_name', true ) ?: '';
}
if ( empty( $customer_first_name ) && $customer_full_name ) {
	$_parts              = explode( ' ', trim( $customer_full_name ) );
	$customer_first_name = $_parts[0];
}

// ── Therapist ────────────────────────────────────────────────────────────────
$staff_ids   = $appointment->get_staff_ids();
$staff_names = [];
foreach ( $staff_ids as $staff_id ) {
	$staff_user = get_user_by( 'ID', $staff_id );
	if ( $staff_user ) {
		$title         = get_user_meta( $staff_id, 'staff_title', true );
		$staff_names[] = $title
			? $staff_user->display_name . ', ' . $title
			: $staff_user->display_name;
	}
}
$therapist_display = implode( '<br>', $staff_names );

// ── Appointment date & time ───────────────────────────────────────────────────
$start_timestamp   = $appointment->get_start( 'timestamp' );
$customer_tz       = wellness_get_customer_tz( $appointment );
$datetime_display  = $start_timestamp
	? date_i18n( 'F j, Y', $start_timestamp ) . ' at ' . wellness_tz_format( $start_timestamp, $customer_tz )
	: $appointment->get_start_date();

// ── Cancellation allowed period ───────────────────────────────────────────────
$cancellation_hours = function_exists( 'get_wc_appointment_cancellation_policy_allowed_period' )
	? (int) get_wc_appointment_cancellation_policy_allowed_period()
	: 24;

// ── Book again URL ────────────────────────────────────────────────────────────
$book_again_url = wc_get_page_permalink( 'shop' );
?>

<?php do_action( 'woocommerce_email_header', 'Session Cancelled', $email ); ?>

<!-- ── Intro ─────────────────────────────────────────────────────────────── -->
<p style="margin: 0 0 8px;">Hi <?php echo esc_html( $customer_first_name ); ?>,</p>
<p style="margin: 0 0 24px;">
	This email confirms that your therapy session scheduled for
	<strong><?php echo esc_html( $datetime_display ); ?></strong>
	has been cancelled.
</p>

<!-- ── Appointment details ────────────────────────────────────────────────────── -->
<table cellspacing="0" cellpadding="8" border="1"
	style="width: 100%; border-collapse: collapse; margin: 0 0 24px; border-color: #e5e5e5;">
	<tbody>

		<?php if ( $customer_full_name ) : ?>
		<tr>
			<th style="text-align: left; background: #f7f7f7; width: 38%; padding: 10px 14px; font-weight: 600;">Name</th>
			<td style="padding: 10px 14px;"><?php echo esc_html( $customer_full_name ); ?></td>
		</tr>
		<?php endif; ?>

		<?php if ( $customer_email ) : ?>
		<tr>
			<th style="text-align: left; background: #f7f7f7; width: 38%; padding: 10px 14px; font-weight: 600;">Email</th>
			<td style="padding: 10px 14px;"><a href="mailto:<?php echo esc_attr( $customer_email ); ?>" style="color: inherit;"><?php echo esc_html( $customer_email ); ?></a></td>
		</tr>
		<?php endif; ?>

		<?php if ( $customer_phone ) : ?>
		<tr>
			<th style="text-align: left; background: #f7f7f7; width: 38%; padding: 10px 14px; font-weight: 600;">Phone</th>
			<td style="padding: 10px 14px;"><?php echo esc_html( $customer_phone ); ?></td>
		</tr>
		<?php endif; ?>

		<?php if ( $therapist_display ) : ?>
		<tr>
			<th style="text-align: left; background: #f7f7f7; width: 38%; padding: 10px 14px; font-weight: 600;">Therapist</th>
			<td style="padding: 10px 14px;"><?php echo wp_kses_post( $therapist_display ); ?></td>
		</tr>
		<?php endif; ?>

		<tr>
			<th style="text-align: left; background: #f7f7f7; width: 38%; padding: 10px 14px; font-weight: 600;">Session date</th>
			<td style="padding: 10px 14px;"><?php echo esc_html( $datetime_display ); ?></td>
		</tr>

	</tbody>
</table>



<p style="margin: 0 0 24px;">
	If this cancellation was made intentionally, no further action is needed.
</p>

<p style="margin: 0 0 24px;">
	Cancellations are sometimes necessary, and you can return whenever it feels right for you.
	If you'd like to reschedule or book a new session, you're welcome to do so at any time
	through your <a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" style="color: inherit;">account</a>.
</p>

<!-- ── Payment note ──────────────────────────────────────────────────────── -->
<p style="margin: 0 0 8px; font-weight: 600;">Regarding payment:</p>
<p style="margin: 0 0 24px;">
	If the session was cancelled more than <?php echo esc_html( $cancellation_hours ); ?> hours in advance,
	your payment will be refunded or credited according to our cancellation policy.
</p>

<p style="margin: 0 0 24px;">
	If you have questions about this change, our support team is here to help.
</p>

<!-- ── Sign-off ───────────────────────────────────────────────────────────── -->
<p style="margin: 0 0 6px;">Warm regards,</p>
<p style="margin: 0 0 24px;"><strong>The Wellness Hub Team</strong></p>

<?php do_action( 'woocommerce_email_footer', $email ); ?>
