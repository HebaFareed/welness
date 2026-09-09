<?php
/**
 * Customer appointment reminder email — Wellness Hub custom template
 *
 * Sent to the client as a reminder before their upcoming therapy session.
 *
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

$text_align  = is_rtl() ? 'right' : 'left';
$appointment = wc_appointments_maybe_appointment_object( $appointment );
$appointment = $appointment ? $appointment : get_wc_appointment( 0 );

// ── Customer details ─────────────────────────────────────────────────────────
$wc_order            = $appointment->get_order();
$customer_first_name = '';
$customer_email      = '';
$customer_phone      = '';

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
$customer_full_name = $wc_order
	? trim( $wc_order->get_billing_first_name() . ' ' . $wc_order->get_billing_last_name() )
	: ( isset( $customer ) ? $customer->full_name : '' );
if ( empty( $customer_full_name ) && $wc_order ) {
	$customer_full_name = $wc_order->get_meta( 'billing_full_name', true ) ?: '';
}
if ( empty( $customer_first_name ) && $customer_full_name ) {
	$_parts              = explode( ' ', trim( $customer_full_name ) );
	$customer_first_name = $_parts[0];
}

// ── Appointment date / time ──────────────────────────────────────────────────
$start_timestamp = $appointment->get_start( 'timestamp' );
$customer_tz     = wellness_get_customer_tz( $appointment );
$date_display    = $start_timestamp
	? date_i18n( 'F j, Y', $start_timestamp )
	: $appointment->get_start_date();
$time_display    = $start_timestamp
	? wellness_tz_format( $start_timestamp, $customer_tz )
	: '';

// ── Duration ─────────────────────────────────────────────────────────────────
$duration_raw     = $appointment->get_duration();
$duration_display = is_numeric( $duration_raw ) ? intval( $duration_raw ) . ' minutes' : $duration_raw;

// ── Session Type (from order item meta) ──
$session_type = '';
if ( $wc_order ) {
	$item_id = get_post_meta( $appointment->get_id(), '_appointment_order_item_id', true );
	if ( $item_id ) {
		$_item = $wc_order->get_item( $item_id );
		$session_type = $_item ? $_item->get_meta( '_session_type' ) : '';
	}
}

// ── Therapist ────────────────────────────────────────────────────────────────
$staff_ids  = $appointment->get_staff_ids();
$staff_rows = [];
foreach ( $staff_ids as $staff_id ) {
	$staff_user = get_user_by( 'ID', $staff_id );
	if ( $staff_user ) {
		$staff_title  = get_user_meta( $staff_id, 'staff_title', true );
		$staff_rows[] = $staff_title
			? $staff_user->display_name . ', ' . $staff_title
			: $staff_user->display_name;
	}
}
$staff_display = implode( '<br>', $staff_rows );

// ── Session fee & discount ────────────────────────────────────────────────────
$session_fee      = '';
$discount_display = '';
$coupon_codes     = [];

if ( $wc_order ) {
	$currency = $wc_order->get_currency();
	foreach ( $wc_order->get_items() as $_item ) {
		$_subtotal = floatval( $_item->get_subtotal() );
		if ( $_subtotal > 0 ) {
			$session_fee = wc_price( $_subtotal, [ 'currency' => $currency ] );
			$_item_disc  = $_subtotal - floatval( $_item->get_total() );
			if ( $_item_disc > 0.001 ) {
				$discount_display = wc_price( $_item_disc, [ 'currency' => $currency ] );
			}
			break;
		}
	}
	if ( empty( $session_fee ) ) {
		$session_fee = wc_price( $wc_order->get_total(), [ 'currency' => $currency ] );
	}
	$coupon_codes = $wc_order->get_coupon_codes();
	if ( empty( $discount_display ) ) {
		$_disc = floatval( $wc_order->get_discount_total() );
		if ( $_disc > 0.001 ) {
			$discount_display = wc_price( $_disc, [ 'currency' => $currency ] );
		}
	}
}

// ── Cancellation policy ───────────────────────────────────────────────────────
$cancellation_hours = function_exists( 'get_wc_appointment_cancellation_policy_allowed_period' )
	? (int) get_wc_appointment_cancellation_policy_allowed_period()
	: 24;

// ── Recurring chain (so the client sees the full series + confirmation status) ──
$recurring_chain = [];
if ( function_exists( 'wellness_appointment_is_recurring' ) && wellness_appointment_is_recurring( $appointment ) ) {
	$recurring_chain = wellness_get_recurring_chain( $appointment );
}
?>

<?php do_action( 'woocommerce_email_header', 'Session Reminder', $email ); ?>

<!-- ── Recommendation box (shown first) ──────────────────────────────────── -->
<table cellpadding="0" cellspacing="0" border="0"
	style="width:100%; background:#e8f5e9; border-left:4px solid #43a047; margin:0 0 24px; border-collapse:collapse;">
	<tr>
		<td style="padding:16px 20px;">
			<p style="margin:0 0 8px; font-weight:700; font-size:14px; color:#2e7d32;">
				&#10003; To get the most from your session
			</p>
			<ul style="margin:0; padding:0 0 0 18px; font-size:13px; color:#555; line-height:1.9;">
				<li>Find a quiet, private space with a stable internet connection.</li>
				<li>Have a glass of water nearby and sit comfortably.</li>
				<li>Take a few deep breaths before joining — it helps you settle in.</li>
				<li>If you have any notes or thoughts you'd like to share, jot them down beforehand.</li>
				<li>Join a few minutes early to test your audio and camera.</li>
			</ul>
		</td>
	</tr>
</table>

<!-- ── Intro ─────────────────────────────────────────────────────────────── -->
<p style="margin: 0 0 8px;">Hi <?php echo esc_html( $customer_first_name ); ?>,</p>
<p style="margin: 0 0 24px;">
	This is a friendly reminder that your therapy session is coming up soon.
	We look forward to supporting you.
</p>

<!-- ── Appointment details box ───────────────────────────────────────────── -->
<h2 style="color: #333; font-size: 18px; font-weight: 600; margin: 0 0 12px;">
	Your upcoming session
</h2>

<table cellspacing="0" cellpadding="8" border="1"
	style="width: 100%; border-collapse: collapse; margin: 0 0 24px; border-color: #e5e5e5;">
	<tbody>

		<?php if ( $staff_display ) : ?>
		<tr>
			<th style="text-align: <?php esc_attr_e( $text_align ); ?>; background: #f7f7f7; width: 38%; padding: 10px 14px; font-weight: 600;">
				Therapist
			</th>
			<td style="text-align: <?php esc_attr_e( $text_align ); ?>; padding: 10px 14px;">
				<?php echo wp_kses_post( $staff_display ); ?>
			</td>
		</tr>
		<?php endif; ?>

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

		<?php if ( ! empty( $session_type ) ) : ?>
		<tr>
			<th style="text-align: <?php esc_attr_e( $text_align ); ?>; background: #f7f7f7; width: 38%; padding: 10px 14px; font-weight: 600;">
				Session Type
			</th>
			<td style="text-align: <?php esc_attr_e( $text_align ); ?>; padding: 10px 14px;">
				<?php echo esc_html( $session_type ); ?>
			</td>
		</tr>
		<?php endif; ?>

		<tr>
			<th style="text-align: <?php esc_attr_e( $text_align ); ?>; background: #f7f7f7; width: 38%; padding: 10px 14px; font-weight: 600;">
				Appointment type
			</th>
			<td style="text-align: <?php esc_attr_e( $text_align ); ?>; padding: 10px 14px;">
				<?php echo esc_html__( 'Online', 'woodmart-child' ); ?>
			</td>
		</tr>

		<tr>
			<th style="text-align: <?php esc_attr_e( $text_align ); ?>; background: #f7f7f7; width: 38%; padding: 10px 14px; font-weight: 600;">
				Appointment #
			</th>
			<td style="text-align: <?php esc_attr_e( $text_align ); ?>; padding: 10px 14px;">
				<?php echo esc_html( $appointment->get_id() ); ?>
			</td>
		</tr>

		<?php if ( $session_fee ) : ?>
		<tr>
			<th style="text-align: <?php esc_attr_e( $text_align ); ?>; background: #f7f7f7; width: 38%; padding: 10px 14px; font-weight: 600;">
				Session fee
			</th>
			<td style="text-align: <?php esc_attr_e( $text_align ); ?>; padding: 10px 14px;">
				<?php echo $session_fee; // wc_price returns pre-escaped HTML ?>
			</td>
		</tr>
		<?php endif; ?>

		<?php if ( $discount_display ) : ?>
		<tr>
			<th style="text-align: <?php esc_attr_e( $text_align ); ?>; background: #f7f7f7; width: 38%; padding: 10px 14px; font-weight: 600;">
				Discount
			</th>
			<td style="text-align: <?php esc_attr_e( $text_align ); ?>; padding: 10px 14px;">
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

<?php if ( ! empty( $recurring_chain ) ) : ?>
<!-- ── Recurring appointment ────────────────────────────────────────────────── -->
<h3 style="color: #333; font-size: 16px; font-weight: 600; margin: 0 0 8px;">
	Your recurring sessions
</h3>
<ul style="margin:0 0 12px; padding:0 0 0 18px;">
	<?php foreach ( $recurring_chain as $chain_appt ) : ?>
		<?php
		$chain_start = $chain_appt->get_start();
		$is_past     = $chain_start && $chain_start < current_time( 'timestamp' );
		$is_current  = $chain_appt->get_id() === $appointment->get_id();
		$chain_label = $chain_start ? wellness_tz_format( $chain_start, $customer_tz, 'F j, Y \a\t g:i A' ) : '';
		?>
		<li>
			<?php echo esc_html( $chain_label ); ?>
			— <?php echo esc_html( wellness_appointment_status_label( $chain_appt->get_status() ) ); ?>
			<?php if ( $is_past ) : ?><em> (past)</em><?php endif; ?>
			<?php if ( $is_current ) : ?><strong> (this session)</strong><?php endif; ?>
		</li>
	<?php endforeach; ?>
</ul>
<p style="margin:0 0 24px; font-size:13px; color:#555;">
	Each recurring session is confirmed only after payment.
</p>
<?php endif; ?>

<!-- ── How to join ───────────────────────────────────────────────────────────── -->
<h3 style="color: #333; font-size: 16px; font-weight: 600; margin: 0 0 8px;">
	How to join your session?
</h3>
<p style="margin: 0 0 24px;">
	You will receive your secure session link from your therapist shortly before your appointment.
	Please make sure you're ready and in a private space when it's time.
</p>

<!-- ── Cancellation reminder ─────────────────────────────────────────────────── -->
<h3 style="color: #333; font-size: 16px; font-weight: 600; margin: 0 0 8px;">
	Need to cancel or reschedule?
</h3>
<p style="margin: 0 0 24px;">
	If you need to make any changes, please do so at least <?php echo esc_html( $cancellation_hours ); ?> hours
	before your session to avoid a cancellation charge. You can manage your appointment from your
	<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" style="color: inherit;">account</a>,
	or contact us at
	<a href="tel:+201019666330" style="color: inherit;">+201019666330</a> /
	<a href="mailto:info@thewellnesshub-eg.com" style="color: inherit;">info@thewellnesshub-eg.com</a>.
</p>

<!-- ── Sign-off ───────────────────────────────────────────────────────────── -->
<p style="margin: 24px 0 6px;">We're looking forward to your session.</p>
<p style="margin: 0 0 24px;">
	Warm Regards,<br>
	<strong>The Wellness Hub Team</strong>
</p>

<!-- ── Important Notice footer ───────────────────────────────────────────── -->
<table cellpadding="0" cellspacing="0" border="0"
	style="width:100%; background:#fff8e1; border-left:4px solid #f9a825; margin:24px 0 0; border-collapse:collapse;">
	<tr>
		<td style="padding:16px 20px;">
			<p style="margin:0 0 8px; font-weight:700; font-size:14px; color:#333;">
				&#9888; Important Notice
			</p>
			<p style="margin:0; font-size:13px; color:#555; line-height:1.7;">
				This service is not intended for emergencies.<br>
				If you are experiencing a crisis or feel at risk of harming yourself or others,
				please contact local emergency services or a trusted support person immediately.
			</p>
		</td>
	</tr>
</table>

<?php do_action( 'woocommerce_email_footer', $email ); ?>
