<?php
/**
 * CHECKBOX appointment form field
 *
 * Theme override (WooCommerce Appointments ships no checkbox.php template).
 * Rendered for the custom "Repeat Appointment" toggle added via
 * appointment_form_fields -> wellness_inject_recurring_fields().
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$class   = $field['class'];
$label   = $field['label'];
$name    = $field['name'];
$checked = $field['checked'] ?? '';
?>
<p class="form-field form-field-wide <?php esc_attr_e( implode( ' ', $class ) ); ?>">
	<label for="<?php esc_html_e( $name ); ?>"><?php esc_html_e( $label ); ?>:</label>
	<label class="wellness-recurring-toggle__control" for="<?php esc_html_e( $name ); ?>">
		<input type="checkbox" name="<?php esc_html_e( $name ); ?>" id="<?php esc_html_e( $name ); ?>" value="yes" <?php checked( $checked, 'yes' ); ?> />
		<span class="wellness-recurring-toggle__label"><?php esc_html_e( 'Yes', 'woodmart-child' ); ?></span>
	</label>
</p>
