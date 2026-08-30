<?php
/**
 * Customer appointment confirmed email — Wellness Hub custom override
 *
 * Overrides: woocommerce-appointments/templates/emails/customer-appointment-confirmed.php
 *
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

$text_align  = is_rtl() ? 'right' : 'left';
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
$start_timestamp  = $appointment->get_start( 'timestamp' );
$customer_tz      = wellness_get_customer_tz( $appointment );
$start_formatted  = $start_timestamp
	? wellness_tz_format( $start_timestamp, $customer_tz, 'F j, Y \a\t g:i A' )
	: $appointment->get_start_date();

// ── Duration ─────────────────────────────────────────────────────────────────
$duration_raw     = $appointment->get_duration(); // returns numeric minutes
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

// ── Therapist / staff ────────────────────────────────────────────────────────
$staff_ids      = $appointment->get_staff_ids();
$staff_rows     = [];
if ( ! empty( $staff_ids ) ) {
	foreach ( $staff_ids as $staff_id ) {
		$staff_user = get_user_by( 'ID', $staff_id );
		if ( $staff_user ) {
			$display_name = $staff_user->display_name;
			// Optional: store a professional title in user meta field "staff_title"
			// e.g.  update_user_meta( $staff_id, 'staff_title', 'Counseling Psychologist (M.A)' );
			$staff_title = get_user_meta( $staff_id, 'staff_title', true );
			$staff_rows[] = $staff_title
				? $display_name . ', ' . $staff_title
				: $display_name;
		}
	}
}
$staff_display = implode( '<br>', $staff_rows );

// ── Session fee, discount & payment ──────────────────────────────────────────
$session_fee      = '';
$discount_display = '';
$coupon_codes     = [];
$payment_method   = '';
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
	$payment_method = $wc_order->get_payment_method_title();
}

// ── Cancellation allowed period ───────────────────────────────────────────────
$cancellation_hours = function_exists( 'get_wc_appointment_cancellation_policy_allowed_period' )
	? (int) get_wc_appointment_cancellation_policy_allowed_period()
	: 24;

// ── Recurring schedule (parent + follow-up dates) ────────────────────────────
$recurring          = get_post_meta( $appointment->get_id(), '_recurring', true );
$recurring_interval = get_post_meta( $appointment->get_id(), '_recurring_interval', true );
$recurring_count    = absint( get_post_meta( $appointment->get_id(), '_recurring_count', true ) );
$recurring_rows     = [];

if ( $recurring === 'yes' && $start_timestamp ) {
	list( $unit, $mult ) = wellness_recurring_interval_map( $recurring_interval );
	for ( $i = 0; $i <= $recurring_count; $i++ ) {
		$ts = $i === 0 ? $start_timestamp : strtotime( '+' . ( $i * $mult ) . ' ' . $unit, $start_timestamp );
		if ( $ts ) {
			$recurring_rows[] = wellness_tz_format( $ts, $customer_tz, 'F j, Y \a\t g:i A' );
		}
	}
}

?>

<?php do_action( 'woocommerce_email_header', 'New Session Booked', $email ); ?>

<!-- ── Intro ─────────────────────────────────────────────────────────────── -->
<p style="margin: 0 0 12px;">Hi <?php echo esc_html( $customer_first_name ); ?>,</p>
<p style="margin: 0 0 6px;">Your therapy session has been successfully scheduled.</p>
<p style="margin: 0 0 24px;">We're here to support you.</p>

<!-- ── Appointment details box ───────────────────────────────────────────────── -->
<h2 style="color: #333; font-size: 18px; font-weight: 600; margin: 0 0 12px;">
	Your Appointment details
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
				Date &amp; time
			</th>
			<td style="text-align: <?php esc_attr_e( $text_align ); ?>; padding: 10px 14px;">
				<?php echo esc_html( $start_formatted ); ?>
			</td>
		</tr>

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
				Appointment #
			</th>
			<td style="text-align: <?php esc_attr_e( $text_align ); ?>; padding: 10px 14px;">
				<?php echo esc_html( $appointment->get_id() ); ?>
			</td>
		</tr>

	<?php if ( $customer_email || $customer_phone ) : ?>
	<tr>
		<th style="text-align: <?php esc_attr_e( $text_align ); ?>; background: #f7f7f7; width: 38%; padding: 10px 14px; font-weight: 600;">
			Contact Details
		</th>
		<td style="text-align: <?php esc_attr_e( $text_align ); ?>; padding: 10px 14px;">
			<?php if ( $customer_full_name ) : ?>
				<?php echo esc_html( $customer_full_name ); ?><br>
			<?php endif; ?>
			<?php if ( $customer_email ) : ?>
				<a href="mailto:<?php echo esc_attr( $customer_email ); ?>" style="color: inherit;"><?php echo esc_html( $customer_email ); ?></a>
				<?php if ( $customer_phone ) : ?><br><?php endif; ?>
			<?php endif; ?>
			<?php if ( $customer_phone ) : ?>
				<?php echo esc_html( $customer_phone ); ?>
			<?php endif; ?>
		</td>
	</tr>
	<?php endif; ?>

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

		<?php if ( $payment_method ) : ?>
		<tr>
			<th style="text-align: <?php esc_attr_e( $text_align ); ?>; background: #f7f7f7; width: 38%; padding: 10px 14px; font-weight: 600;">
				Payment details
			</th>
			<td style="text-align: <?php esc_attr_e( $text_align ); ?>; padding: 10px 14px;">
				<?php echo esc_html( $payment_method ); ?>
			</td>
		</tr>
		<?php endif; ?>

	</tbody>
</table>

<?php if ( ! empty( $recurring_rows ) ) : ?>
<!-- ── Recurring sessions ─────────────────────────────────────────────────── -->
<h3 style="color: #333; font-size: 16px; font-weight: 600; margin: 0 0 8px;">
	Your recurring sessions
</h3>
<ul style="margin:0 0 12px; padding:0 0 0 18px;">
	<?php foreach ( $recurring_rows as $row ) : ?>
		<li><?php echo esc_html( $row ); ?></li>
	<?php endforeach; ?>
</ul>
<p style="margin:0 0 24px; font-size:13px; color:#555;">
	Each follow-up session is booked separately and paid separately — you'll receive a payment reminder before each one.
</p>
<?php endif; ?>

<!-- ── How to join ───────────────────────────────────────────────────────────── -->
<h3 style="color: #333; font-size: 16px; font-weight: 600; margin: 0 0 8px;">
	How to join your session?
</h3>
<p style="margin: 0 0 6px;">
	You will receive your secure session link and intake forms by email from your therapist before your appointment.
</p>
<p style="margin: 0 0 24px;">
	Please make sure you're in a quiet, private space and join a few minutes early if possible.
</p>

<!-- ── Cancellation / reschedule ─────────────────────────────────────────────── -->
<h3 style="color: #333; font-size: 16px; font-weight: 600; margin: 0 0 8px;">
	Need to Cancel, Reschedule, or Have a Question?
</h3>
<p style="margin: 0 0 6px;">
	You can cancel or reschedule your session up to <?php echo esc_html( $cancellation_hours ); ?> hours before your appointment without charge.
</p>
<p style="margin: 0 0 6px;">
	Late cancellations (within <?php echo esc_html( $cancellation_hours ); ?> hours) or missed sessions are charged in full, as this time has been reserved especially for you.
</p>
<p style="margin: 0 0 24px;">
	If an emergency occurs, please contact us and we'll do our best to support you.
</p>

<!-- ── Contact ───────────────────────────────────────────────────────────────── -->
<p style="margin: 0 0 6px;">
	You can manage your appointment or contact us from 10 AM – 6 PM at
	<a href="tel:+201019666330" style="color: inherit;">+201019666330</a>. Or
	<a href="mailto:info@thewellnesshub-eg.com" style="color: inherit;">info@thewellnesshub-eg.com</a>
</p>

<p style="margin: 24px 0 6px;">We're looking forward to supporting you.</p>
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
