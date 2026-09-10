<?php
/**
 * Therapist notification — the client submitted the intake form (thank-you page).
 *
 * Contains the full intake form so the therapist has every answer before the
 * session. Sent from wellness_send_intake_submitted_notification().
 *
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

$text_align = is_rtl() ? 'right' : 'left';

$wc_order  = isset( $order ) && $order instanceof WC_Order ? $order : null;
$intake_id = isset( $intake_id ) ? (int) $intake_id : 0;

// ── Client ───────────────────────────────────────────────────────────────────
$client_name  = '';
$client_email = '';
if ( $wc_order ) {
	$client_name  = trim( $wc_order->get_billing_first_name() . ' ' . $wc_order->get_billing_last_name() );
	$client_email = $wc_order->get_billing_email();
}
if ( empty( $client_name ) && $intake_id ) {
	$client_name = (string) get_post_meta( $intake_id, '_intake_client_name', true );
}
$order_number = $wc_order ? $wc_order->get_order_number() : '';

// ── Appointment context (best effort) ───────────────────────────────────────
$appointment   = isset( $appointment ) && is_object( $appointment ) ? $appointment : null;
$start_display = '';
if ( $appointment ) {
	$start_timestamp = $appointment->get_start( 'timestamp' );
	if ( $start_timestamp ) {
		$staff_tz      = wellness_get_staff_tz( $appointment );
		$start_display = wellness_tz_format( $start_timestamp, $staff_tz, 'F j, Y \a\t g:i A' );
	}
}

// ── Intake form data (grouped, every answer) ─────────────────────────────────
$defs   = function_exists( 'wellness_get_intake_field_defs' ) ? wellness_get_intake_field_defs() : [];
$groups = function_exists( 'wellness_thankyou_intake_field_groups' ) ? wellness_thankyou_intake_field_groups() : [];

$decode = static function ( $key, $field ) use ( $intake_id ) {
	$raw  = $intake_id ? get_post_meta( $intake_id, '_' . $key, true ) : '';
	$type = isset( $field['type'] ) ? $field['type'] : 'text';

	if ( 'checkbox' === $type ) {
		return ( 'yes' === $raw ) ? __( 'Yes', 'woodmart-child' ) : __( 'No', 'woodmart-child' );
	}
	if ( 'select' === $type && isset( $field['options'][ $raw ] ) ) {
		return $field['options'][ $raw ];
	}
	if ( '' === (string) $raw ) {
		return '—';
	}
	return (string) $raw;
};

$th_style = 'text-align: ' . $text_align . '; background: #f7f7f7; width: 38%; padding: 10px 14px; font-weight: 600;';
$td_style = 'text-align: ' . $text_align . '; padding: 10px 14px;';
?>

<?php
$heading = ! empty( $email_heading ) ? $email_heading : __( 'Client intake form submitted', 'woodmart-child' );
do_action( 'woocommerce_email_header', $heading, $email );
?>

<p style="margin: 0 0 20px; font-size: 15px; color: #333;">
	<?php
	if ( $client_name ) {
		echo esc_html( sprintf(
			/* translators: %s: client name */
			__( '%s has completed the intake form. Their answers are below so you can prepare for the session.', 'woodmart-child' ),
			$client_name
		) );
	} else {
		esc_html_e( 'A client has completed their intake form. Their answers are below so you can prepare for the session.', 'woodmart-child' );
	}
	?>
</p>

<!-- ── Session details ───────────────────────────────────────────────────── -->
<table cellspacing="0" cellpadding="8" border="1"
	style="width: 100%; border-collapse: collapse; margin: 0 0 24px; border-color: #e5e5e5;">
	<tbody>
		<?php if ( $client_name ) : ?>
		<tr>
			<th style="<?php echo esc_attr( $th_style ); ?>"><?php esc_html_e( 'Client', 'woodmart-child' ); ?></th>
			<td style="<?php echo esc_attr( $td_style ); ?>"><?php echo esc_html( $client_name ); ?></td>
		</tr>
		<?php endif; ?>
		<?php if ( $client_email ) : ?>
		<tr>
			<th style="<?php echo esc_attr( $th_style ); ?>"><?php esc_html_e( 'Email', 'woodmart-child' ); ?></th>
			<td style="<?php echo esc_attr( $td_style ); ?>"><?php echo esc_html( $client_email ); ?></td>
		</tr>
		<?php endif; ?>
		<?php if ( $start_display ) : ?>
		<tr>
			<th style="<?php echo esc_attr( $th_style ); ?>"><?php esc_html_e( 'Session', 'woodmart-child' ); ?></th>
			<td style="<?php echo esc_attr( $td_style ); ?>"><?php echo esc_html( $start_display ); ?></td>
		</tr>
		<?php endif; ?>
		<?php if ( $order_number ) : ?>
		<tr>
			<th style="<?php echo esc_attr( $th_style ); ?>"><?php esc_html_e( 'Order', 'woodmart-child' ); ?></th>
			<td style="<?php echo esc_attr( $td_style ); ?>">#<?php echo esc_html( $order_number ); ?></td>
		</tr>
		<?php endif; ?>
	</tbody>
</table>

<!-- ── Intake form (grouped) ─────────────────────────────────────────────── -->
<?php
foreach ( $groups as $group_title => $keys ) :
	if ( empty( $keys ) || ! is_array( $keys ) ) {
		continue;
	}

	$rows_html = '';
	foreach ( $keys as $key ) {
		if ( ! isset( $defs[ $key ] ) ) {
			continue;
		}
		$label = isset( $defs[ $key ]['label'] ) ? $defs[ $key ]['label'] : $key;
		$value = $decode( $key, $defs[ $key ] );

		$rows_html .= '<tr>';
		$rows_html .= '<th style="' . esc_attr( $th_style ) . '">' . esc_html( $label ) . '</th>';
		$rows_html .= '<td style="' . esc_attr( $td_style ) . '">' . esc_html( $value ) . '</td>';
		$rows_html .= '</tr>';
	}

	if ( '' === $rows_html ) {
		continue;
	}
	?>
	<h2 style="color: #333; font-size: 18px; font-weight: 600; margin: 0 0 12px;"><?php echo esc_html( $group_title ); ?></h2>
	<table cellspacing="0" cellpadding="8" border="1"
		style="width: 100%; border-collapse: collapse; margin: 0 0 24px; border-color: #e5e5e5;">
		<tbody><?php echo $rows_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- each cell escaped above. ?></tbody>
	</table>
	<?php
endforeach;
?>

<p style="margin: 0 0 24px; font-size: 13px; color: #666;">
	<?php esc_html_e( 'This form was completed by the client after booking. It is also available under Appointments → Intake Forms.', 'woodmart-child' ); ?>
</p>

<?php do_action( 'woocommerce_email_footer', $email ); ?>
