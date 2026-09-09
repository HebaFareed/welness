<?php
/**
 * Customer appointment rescheduled email — Wellness Hub custom template
 *
 * Used by wellness_send_customer_rescheduled_email() in functions.php.
 * Variables available: $appointment, $prev_start_date, $customer_first_name
 *
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

$text_align      = is_rtl() ? 'right' : 'left';
$appointment     = wc_appointments_maybe_appointment_object( $appointment );
$appointment     = $appointment ? $appointment : get_wc_appointment( 0 );

// ── Customer details ──────────────────────────────────────────────────────────
$wc_order       = $appointment->get_order();
$customer_email = '';
$customer_phone = '';

if ( $wc_order ) {
	$customer_email = $wc_order->get_billing_email();
	$customer_phone = $wc_order->get_billing_phone();
} else {
	$customer = $appointment->get_customer();
	if ( $customer ) {
		$customer_email = $customer->email;
	}
}
$customer_full_name = $wc_order
	? trim( $wc_order->get_billing_first_name() . ' ' . $wc_order->get_billing_last_name() )
	: ( isset( $customer ) ? $customer->full_name : '' );
if ( empty( $customer_full_name ) && $wc_order ) {
	$customer_full_name = $wc_order->get_meta( 'billing_full_name', true ) ?: '';
}

// ── Dates ─────────────────────────────────────────────────────────────────────
$start_timestamp  = $appointment->get_start( 'timestamp' );
$customer_tz      = wellness_get_customer_tz( $appointment );
$new_date_display = $start_timestamp
	? date_i18n( 'F j, Y', $start_timestamp )
	: $appointment->get_start_date();
$new_time_display = $start_timestamp
	? wellness_tz_format( $start_timestamp, $customer_tz )
	: '';

// Previous date — passed in as a formatted string from the trigger
$prev_date_display = ! empty( $prev_start_date ) ? $prev_start_date : '';

// ── Duration ──────────────────────────────────────────────────────────────────
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

// ── Therapist ─────────────────────────────────────────────────────────────────
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
?>

<?php do_action( 'woocommerce_email_header', 'Session Rescheduled', $email ); ?>

<!-- ── Intro ─────────────────────────────────────────────────────────────── -->
<p style="margin: 0 0 8px;">Hi <?php echo esc_html( $customer_first_name ); ?>,</p>
<p style="margin: 0 0 24px;">
	This email confirms an update to your therapy appointment.
</p>

<?php if ( $prev_date_display && $new_date_display ) : ?>
<p style="margin: 0 0 24px;">
	Your session has been successfully moved from
	<strong><?php echo esc_html( $prev_date_display ); ?></strong>
	to
	<strong><?php echo esc_html( $new_date_display );
		echo $new_time_display ? ' at ' . esc_html( $new_time_display ) : ''; ?></strong>.
</p>
<?php endif; ?>

<p style="margin: 0 0 24px;">
	Everything is set — no further action is needed from you.
</p>

<!-- ── Updated appointment details box ───────────────────────────────────── -->
<h2 style="color: #333; font-size: 18px; font-weight: 600; margin: 0 0 12px;">
	Updated appointment details
</h2>

<table cellspacing="0" cellpadding="8" border="1"
	style="width: 100%; border-collapse: collapse; margin: 0 0 24px; border-color: #e5e5e5;">
	<tbody>

		<?php if ( $therapist_display ) : ?>
		<tr>
			<th style="text-align: <?php esc_attr_e( $text_align ); ?>; background: #f7f7f7; width: 38%; padding: 10px 14px; font-weight: 600;">
				Therapist
			</th>
			<td style="text-align: <?php esc_attr_e( $text_align ); ?>; padding: 10px 14px;">
				<?php echo wp_kses_post( $therapist_display ); ?>
			</td>
		</tr>
		<?php endif; ?>

		<tr>
			<th style="text-align: <?php esc_attr_e( $text_align ); ?>; background: #f7f7f7; width: 38%; padding: 10px 14px; font-weight: 600;">
				Date
			</th>
			<td style="text-align: <?php esc_attr_e( $text_align ); ?>; padding: 10px 14px;">
				<?php echo esc_html( $new_date_display ); ?>
			</td>
		</tr>

		<?php if ( $new_time_display ) : ?>
		<tr>
			<th style="text-align: <?php esc_attr_e( $text_align ); ?>; background: #f7f7f7; width: 38%; padding: 10px 14px; font-weight: 600;">
				Time
			</th>
			<td style="text-align: <?php esc_attr_e( $text_align ); ?>; padding: 10px 14px;">
				<?php echo esc_html( $new_time_display ); ?>
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

		<?php if ( $price_display ) : ?>
		<tr>
			<th style="text-align: <?php esc_attr_e( $text_align ); ?>; background: #f7f7f7; width: 38%; padding: 10px 14px; font-weight: 600;">
				Price
			</th>
			<td style="text-align: <?php esc_attr_e( $text_align ); ?>; padding: 10px 14px;">
				<?php echo $price_display; // wc_price returns pre-escaped HTML ?>
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

<!-- ── Sign-off ───────────────────────────────────────────────────────────── -->
<p style="margin: 0 0 8px;">
	If you have questions or need assistance, our support team is here to help.
</p>
<p style="margin: 0 0 6px;">Warm regards,</p>
<p style="margin: 0 0 24px;"><strong>The Wellness Hub Team</strong></p>

<?php do_action( 'woocommerce_email_footer', $email ); ?>
