<?php
/**
 * Customer appointment follow-up email — Wellness Hub custom template
 *
 * Sent to the client after their therapy session to check in and encourage
 * continued care.
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

// ── Session date / time ──────────────────────────────────────────────────────
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

// ── Book again URL ────────────────────────────────────────────────────────────
$book_again_url = wc_get_page_permalink( 'shop' );
?>

<?php do_action( 'woocommerce_email_header', 'Following Up on Your Session', $email ); ?>

<!-- ── Recommendation box (shown first) ──────────────────────────────────── -->
<table cellpadding="0" cellspacing="0" border="0"
	style="width:100%; background:#e8f4fb; border-left:4px solid #1e88e5; margin:0 0 24px; border-collapse:collapse;">
	<tr>
		<td style="padding:16px 20px;">
			<p style="margin:0 0 8px; font-weight:700; font-size:14px; color:#1565c0;">
				&#128161; What we recommend
			</p>
			<ul style="margin:0; padding:0 0 0 18px; font-size:13px; color:#555; line-height:1.9;">
				<li>Give yourself time to reflect on what came up in today's session.</li>
				<li>Try to practice any exercises or insights discussed with your therapist.</li>
				<li>Journaling your thoughts in the next 24–48 hours can be very helpful.</li>
				<li>Be gentle with yourself — progress takes time and every session counts.</li>
				<li>Consider scheduling your next session to maintain momentum in your journey.</li>
			</ul>
		</td>
	</tr>
</table>

<!-- ── Intro ─────────────────────────────────────────────────────────────── -->
<p style="margin: 0 0 8px;">Hi <?php echo esc_html( $customer_first_name ); ?>,</p>
<p style="margin: 0 0 6px;">
	Thank you for attending your therapy session. We hope it was a meaningful and supportive experience.
</p>
<p style="margin: 0 0 24px;">
	Taking this step for your well-being is something to be proud of.
</p>

<!-- ── Session summary ───────────────────────────────────────────────────── -->
<h2 style="color: #333; font-size: 18px; font-weight: 600; margin: 0 0 12px;">
	Session summary
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

<!-- ── Book next session ─────────────────────────────────────────────────── -->
<h3 style="color: #333; font-size: 16px; font-weight: 600; margin: 0 0 8px;">
	Ready to continue your journey?
</h3>
<p style="margin: 0 0 6px;">
	Consistent sessions help build trust and progress over time. Whenever you feel ready,
	you're welcome to book your next session.
</p>
<p style="margin: 0 0 24px;">
	<a href="<?php echo esc_url( $book_again_url ); ?>"
		style="display:inline-block; padding:10px 22px; background:#333; color:#fff; text-decoration:none; border-radius:4px; font-size:14px; font-weight:600;">
		Book your next session
	</a>
</p>

<!-- ── Feedback request ──────────────────────────────────────────────────── -->
<h3 style="color: #333; font-size: 16px; font-weight: 600; margin: 0 0 8px;">
	Share your experience
</h3>
<p style="margin: 0 0 24px;">
	Your feedback means a great deal to us and helps us continue improving our services.
	If you'd like to share how your session went, please reach out to us at
	<a href="mailto:info@thewellnesshub-eg.com" style="color: inherit;">info@thewellnesshub-eg.com</a>
	or call us at <a href="tel:+201019666330" style="color: inherit;">+201019666330</a>
	(10 AM – 6 PM).
</p>

<!-- ── Sign-off ───────────────────────────────────────────────────────────── -->
<p style="margin: 0 0 6px;">Warm regards,</p>
<p style="margin: 0 0 24px;">
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
