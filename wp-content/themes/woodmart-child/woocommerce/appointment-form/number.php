<?php
/**
 * NUMBER appointment form field
 *
 * Theme override of woocommerce-appointments/templates/appointment-form/number.php.
 * Adds support for an explicit $field['value'] (used by the recurring "Number of
 * repeats" field to default to 2). Falls back to $min when no value is set,
 * preserving the plugin's default behaviour.
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

$after = $field['after'] ?? null;
$class = $field['class'];
$label = $field['label'];
$max   = $field['max'] ?? null;
$min   = $field['min'] ?? null;
$name  = $field['name'];
$step  = $field['step'] ?? null;
$value = $field['value'] ?? ( ! empty( $min ) ? $min : 0 );
?>
<p class="form-field form-field-wide <?php esc_attr_e( implode( ' ', $class ) ); ?>">
	<label for="<?php esc_html_e( $name ); ?>"><?php esc_html_e( $label ); ?>:</label>
	<input
		type="number"
		value="<?php echo esc_attr( $value ); ?>"
		step="<?php echo ( isset( $step ) ) ? esc_attr( $step ) : ''; ?>"
		min="<?php echo ( isset( $min ) ) ? esc_attr( $min ) : ''; ?>"
		max="<?php echo ( isset( $max ) ) ? esc_attr( $max ) : ''; ?>"
		name="<?php esc_html_e( $name ); ?>"
		id="<?php esc_html_e( $name ); ?>"
	/> <?php echo ( ! empty( $after ) ) ? esc_html( $after ) : ''; ?>
</p>
