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

// ── Session Type (from order item meta) ──
$session_type = '';
if ( $wc_order ) {
	$item_id = get_post_meta( $appointment->get_id(), '_appointment_order_item_id', true );
	if ( $item_id ) {
		$_item = $wc_order->get_item( $item_id );
		$session_type = $_item ? $_item->get_meta( '_session_type' ) : '';
	}
}

// ── Recurring (replaces Product Add-Ons "Repeat Appointment" parsing) ────────
$recurring          = get_post_meta( $appointment->get_id(), '_recurring', true );
$recurring_interval = get_post_meta( $appointment->get_id(), '_recurring_interval', true );
$recurring_count    = absint( get_post_meta( $appointment->get_id(), '_recurring_count', true ) );

// Fallback to order item meta (set by wellness_add_recurring_to_order_item).
if ( $recurring !== 'yes' && $wc_order ) {
	foreach ( $wc_order->get_items() as $item ) {
		if ( $item->get_meta( '_recurring' ) === 'yes' ) {
			$recurring          = 'yes';
			$recurring_interval = $item->get_meta( '_recurring_interval' );
			$recurring_count    = absint( $item->get_meta( '_recurring_count' ) );
			break;
		}
	}
}

$recurring_label = '';
$recurring_rows  = [];
if ( $recurring === 'yes' ) {
	$labels = [
		'weekly'   => __( 'Weekly', 'woocommerce-appointments' ),
		'biweekly' => __( 'Bi-Weekly', 'woocommerce-appointments' ),
		'monthly'  => __( 'Monthly', 'woocommerce-appointments' ),
	];
	$interval_label  = isset( $labels[ $recurring_interval ] ) ? $labels[ $recurring_interval ] : $recurring_interval;
	$recurring_label = $interval_label . ' &times; ' . $recurring_count;

	if ( $start_timestamp ) {
		list( $unit, $mult ) = wellness_recurring_interval_map( $recurring_interval );
		for ( $i = 0; $i <= $recurring_count; $i++ ) {
			$ts = $i === 0 ? $start_timestamp : strtotime( '+' . ( $i * $mult ) . ' ' . $unit, $start_timestamp );
			if ( $ts ) {
				$recurring_rows[] = date_i18n( 'F j, Y', $ts ) . ' ' . ( $staff_tz ? wellness_tz_format( $ts, $staff_tz, 'g:i A' ) : '' );
			}
		}
	}
}

// Full recurring chain with statuses (root + follow-ups), so the therapist sees
// which sessions are confirmed, pending payment, or already past.
$recurring_chain = [];
if ( $recurring === 'yes' || ( function_exists( 'wellness_appointment_is_recurring' ) && wellness_appointment_is_recurring( $appointment ) ) ) {
	$recurring_chain = wellness_get_recurring_chain( $appointment );
}

// ── Price & discount ─────────────────────────────────────────────────────────
$price_display    = '';
$discount_display = '';
$coupon_codes     = [];

if ( $wc_order ) {
	// Pull the line item total for the appointment product
	foreach ( $wc_order->get_items() as $item ) {
		$line_total    = floatval( $item->get_total() );
		$line_subtotal = floatval( $item->get_subtotal() );
		if ( $line_total > 0 || $line_subtotal > 0 ) {
			$price_display = wc_price( $line_subtotal, [ 'currency' => $wc_order->get_currency() ] );
			// Per-item discount
			$item_discount = $line_subtotal - $line_total;
			if ( $item_discount > 0.001 ) {
				$discount_display = wc_price( $item_discount, [ 'currency' => $wc_order->get_currency() ] );
			}
			break; // first appointment item is enough
		}
	}
	// Order-level coupon codes
	$coupon_codes = $wc_order->get_coupon_codes();
	// Fallback: use order discount total if per-item discount wasn't found
	if ( empty( $discount_display ) ) {
		$order_discount = floatval( $wc_order->get_discount_total() );
		if ( $order_discount > 0.001 ) {
			$discount_display = wc_price( $order_discount, [ 'currency' => $wc_order->get_currency() ] );
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

		<?php if ( $recurring === 'yes' ) : ?>
		<tr>
			<th style="text-align: <?php esc_attr_e( $text_align ); ?>; background: #f7f7f7; width: 38%; padding: 10px 14px; font-weight: 600;">
				Recurring
			</th>
			<td style="text-align: <?php esc_attr_e( $text_align ); ?>; padding: 10px 14px;">
				<?php echo esc_html( $recurring_label ); ?>
			</td>
		</tr>
			<?php if ( ! empty( $recurring_chain ) ) : ?>
			<tr>
				<th style="text-align: <?php esc_attr_e( $text_align ); ?>; background: #f7f7f7; width: 38%; padding: 10px 14px; font-weight: 600;">
					Recurring sessions
				</th>
				<td style="text-align: <?php esc_attr_e( $text_align ); ?>; padding: 10px 14px;">
					<ul style="margin:0; padding:0 0 0 18px;">
						<?php foreach ( $recurring_chain as $chain_appt ) : ?>
							<?php
							$chain_start = $chain_appt->get_start();
							$is_past     = $chain_start && $chain_start < current_time( 'timestamp' );
							$is_current  = $chain_appt->get_id() === $appointment->get_id();
							$chain_label = $chain_start
								? ( $staff_tz ? wellness_tz_format( $chain_start, $staff_tz, 'F j, Y \a\t g:i A' ) : date_i18n( 'F j, Y \a\t g:i A', $chain_start ) )
								: '';
							?>
							<li>
								<?php echo esc_html( $chain_label ); ?>
								— <?php echo esc_html( wellness_appointment_status_label( $chain_appt->get_status() ) ); ?>
								<?php if ( $is_past ) : ?><em> (past)</em><?php endif; ?>
								<?php if ( $is_current ) : ?><strong> (this booking)</strong><?php endif; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</td>
			</tr>
			<?php endif; ?>
		<?php endif; ?>

	</tbody>
</table>

<?php
// ── Client Intake Form summary ──────────────────────────────────────────
// Intake is now collected on the thank-you page after booking. Returning
// clients show their stored summary; new clients show a pending notice.
$intake_rows = wellness_get_intake_summary_for_email($client_email);
if (! empty($intake_rows)) : ?>
<h2 style="color: #333; font-size: 18px; font-weight: 600; margin: 0 0 12px;">
	Client Intake Summary:
</h2>
<table cellspacing="0" cellpadding="8" border="1"
	style="width: 100%; border-collapse: collapse; margin: 0 0 24px; border-color: #e5e5e5;">
	<tbody>
		<?php echo $intake_rows; ?>
	</tbody>
</table>
<?php elseif (! empty($client_email)) : ?>
<h2 style="color: #333; font-size: 18px; font-weight: 600; margin: 0 0 12px;">
	Client Intake Form: Pending
</h2>
<table cellspacing="0" cellpadding="8" border="1"
	style="width: 100%; border-collapse: collapse; margin: 0 0 24px; border-color: #e5e5e5;">
	<tbody>
		<tr>
			<td style="text-align: left; padding: 10px 14px; font-style: italic;">
				This client has not completed the intake form yet. It is collected on the
				thank-you page after booking; the details will appear here and on the order
				once it has been submitted.
			</td>
		</tr>
	</tbody>
</table>
<?php endif; ?>

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
