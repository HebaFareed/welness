<?php

// ═══════════════════════════════════════════════════════════════════════════════
// EMAIL TESTING — force all outgoing emails to a single address
// Comment out the add_filter lines below when done testing.
// ═══════════════════════════════════════════════════════════════════════════════
define('WELLNESS_TEST_EMAIL', 'heba.f.fawzy@gmail.com'); // ← change this

// ═══════════════════════════════════════════════════════════════════════════════
// INTAKE DEBUG — force intake form to show regardless of customer status.
// Comment out when done testing.
// ═══════════════════════════════════════════════════════════════════════════════
// define('WELLNESS_INTAKE_DEBUG', true);

// add_filter( 'woocommerce_email_recipient_admin_appointment_cancelled',    'wellness_force_test_recipient', 99 );
// add_filter( 'woocommerce_email_recipient_admin_appointment_rescheduled',  'wellness_force_test_recipient', 99 );
// add_filter( 'woocommerce_email_recipient_admin_new_appointment',          'wellness_force_test_recipient', 99 );
// add_filter( 'woocommerce_email_recipient_appointment_cancelled',          'wellness_force_test_recipient', 99 );
// add_filter( 'woocommerce_email_recipient_appointment_confirmed',          'wellness_force_test_recipient', 99 );
// add_filter( 'wp_mail',                                                    'wellness_force_wp_mail_recipient', 99 );

function wellness_force_test_recipient($recipient)
{
	return WELLNESS_TEST_EMAIL;
}

// Catches any email sent via wp_mail() directly (e.g. the custom rescheduled email).
function wellness_force_wp_mail_recipient($args)
{
	$args['to'] = WELLNESS_TEST_EMAIL;
	return $args;
}
// ═══════════════════════════════════════════════════════════════════════════════

/**
 * Add viewport meta tag for mobile responsiveness.
 *
 * Woodmart's header.php does not include a viewport meta tag. Without it,
 * iOS Safari renders the page at a 980 px desktop viewport and scales it
 * down, causing touch-event coordinates to be miscalculated — taps land on
 * the wrong elements. This affects all iPhone users regardless of gateway.
 */
add_action('wp_head', 'wellness_add_viewport_meta', 1);
function wellness_add_viewport_meta()
{
	echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">' . "\n";
}

/**
 * Enqueue script and styles for child theme
 */
function woodmart_child_enqueue_styles()
{
	wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css', array('woodmart-style'), woodmart_get_theme_info('Version'));
}
add_action('wp_enqueue_scripts', 'woodmart_child_enqueue_styles', 10010);

// Add product short description above add to cart button in loop
function add_short_description_above_add_to_cart_button($link, $product, $args)
{
	$short_description = $product->get_short_description(); // Get product short description
	if (! empty($short_description)) {
		// Wrap the short description in <p> tags if not already wrapped
		if (strpos($short_description, '<div>') === false) {
			$short_description = '<div class="loop-short-desc">' . $short_description . '</div>';
		}
		// Add a line break after the short description
		$short_description_with_break = $short_description . '<br>';
		return $short_description_with_break . $link;
	}
	return $link;
}
add_filter('woocommerce_loop_add_to_cart_link', 'add_short_description_above_add_to_cart_button', 10, 3);





// Redirect to checkout after adding product to cart
function redirect_to_checkout_after_add_to_cart()
{
	global $woocommerce;
	// Redirect to checkout
	return wc_get_checkout_url();
}
add_filter('woocommerce_add_to_cart_redirect', 'redirect_to_checkout_after_add_to_cart');




add_action('woocommerce_bookings_after_booking_base_cost', 'add_usd_booking_base_cost', 10, 1);
function add_usd_booking_base_cost($product_id)
{
	$usd_booking_base_cost = get_post_meta($product_id, '_wc_usd_booking_cost', true);
	woocommerce_wp_text_input(array(
		'id'                => '_wc_usd_booking_cost',
		'label'             => __('Base cost (USD)', 'woocommerce-bookings'),
		'description'       => __('One-off cost for the booking as a whole in USD.', 'woocommerce-bookings'),
		'value'             => $usd_booking_base_cost,
		'type'              => 'number',
		'desc_tip'          => true,
		'custom_attributes' => array(
			'min'  => '',
			'step' => '0.01',
		),
	));
}

add_action('woocommerce_bookings_after_booking_block_cost', 'add_usd_booking_block_cost', 10, 1);
function add_usd_booking_block_cost($product_id)
{
	$usd_booking_block_cost = get_post_meta($product_id, '_wc_usd_booking_block_cost', true);
	woocommerce_wp_text_input(array(
		'id'                => '_wc_usd_booking_block_cost',
		'label'             => __('Block cost (USD)', 'woocommerce-bookings'),
		'description'       => __('This is the cost per block booked in USD. All other costs (for resources and persons) are added to this.', 'woocommerce-bookings'),
		'value'             => $usd_booking_block_cost,
		'type'              => 'number',
		'desc_tip'          => true,
		'custom_attributes' => array(
			'min'  => '',
			'step' => '0.01',
		),
	));
}

add_action('woocommerce_product_options_pricing', 'add_usd_display_cost', 10, 1);
function add_usd_display_cost()
{
	global $product_object;
	$usd_display_cost = $product_object->get_meta('_wc_usd_display_cost');
	//   $usd_display_cost = get_post_meta($product_id, '_wc_usd_display_cost', true);
	woocommerce_wp_text_input(array(
		'id'                => '_wc_usd_display_cost',
		'label'             => __('Regular Price (USD)', 'woocommerce-bookings'),
		'description'       => __('The cost is displayed to the user on the frontend in USD. Leave blank to have it calculated for you. If a booking has varying costs, this will be prefixed with the word "from:".', 'woocommerce-bookings'),
		'value'             => $usd_display_cost,
		'data_type' => 'price',
		'desc_tip'          => true,
	));

	// sale price
	$usd_display_sale_price = $product_object->get_meta('_wc_usd_display_sale_price');
	woocommerce_wp_text_input(array(
		'id'                => '_wc_usd_display_sale_price',
		'label'             => __('Sale Price (USD)', 'woocommerce-bookings'),
		'description'       => __('The sale price is displayed to the user on the frontend in USD. Leave blank to have it calculated for you. If a booking has varying costs, this will be prefixed with the word "from:".', 'woocommerce-bookings'),
		'value'             => $usd_display_sale_price,
		'data_type' => 'price',
		'desc_tip'          => true,
	));
}

add_action('woocommerce_process_product_meta', 'save_usd_booking_fields', 10, 1);
function save_usd_booking_fields($product_id)
{
	$usd_booking_base_cost = $_POST['_wc_usd_booking_cost'];
	if (!empty($usd_booking_base_cost)) {
		update_post_meta($product_id, '_wc_usd_booking_cost', esc_attr($usd_booking_base_cost));
	}

	$usd_booking_block_cost = $_POST['_wc_usd_booking_block_cost'];
	if (!empty($usd_booking_block_cost)) {
		update_post_meta($product_id, '_wc_usd_booking_block_cost', esc_attr($usd_booking_block_cost));
	}

	$usd_display_cost = $_POST['_wc_usd_display_cost'];
	if (!empty($usd_display_cost)) {
		update_post_meta($product_id, '_wc_usd_display_cost', esc_attr($usd_display_cost));
	}

	$usd_display_sale_price = $_POST['_wc_usd_display_sale_price'];
	if (isset($usd_display_sale_price)) {
		update_post_meta($product_id, '_wc_usd_display_sale_price', esc_attr($usd_display_sale_price));
	}
}

// ═══════════════════════════════════════════════════════════════════════════════
// Currency — staff-based with geolocation fallback
// _staff_currency values: '' (Location Based), 'EGP', 'USD'.
// When empty / 'Location Based', currency is resolved via IP geolocation
// (Egypt → EGP, elsewhere → USD).  An explicit 'USD' or 'EGP' overrides
// location for that therapist's products.
// ═══════════════════════════════════════════════════════════════════════════════

/**
 * Determine currency based on visitor geolocation.
 *
 * @return string 'EGP' or 'USD'
 */
function wellness_get_location_currency()
{
	$geo = WC_Geolocation::geolocate_ip();
	if (empty($geo) || $geo['country'] === 'EG') {
		return 'EGP';
	}
	return 'USD';
}

/**
 * Determine the active currency for a product based on its assigned staff.
 *
 * @param WC_Product|null $product
 * @return string 'EGP' or 'USD'
 */
function wellness_get_active_currency($product = null)
{
	$currency = wellness_get_location_currency(); // location-based default

	if ($product) {
		$staff_ids = $product->get_staff_ids();
		if (! empty($staff_ids)) {
			$staff_id      = (int) $staff_ids[0];
			$staff_currency = get_user_meta($staff_id, '_staff_currency', true);
			if ($staff_currency === 'EGP' || $staff_currency === 'USD') {
				$currency = $staff_currency; // explicit override beats location
			}
		}
	}

	return $currency;
}

add_filter('woocommerce_product_get_price', 'get_usd_booking_cost', 999999, 2);
function get_usd_booking_cost($cost, $product)
{
	if (is_admin() && !defined('DOING_AJAX')) {
		return $cost;
	}

	// Default EGP — no conversion needed.
	if (wellness_get_active_currency($product) === 'EGP') {
		return $cost;
	}

	// Duration Options: when enabled, the price is already set correctly by
	// appointments_calculated_product_price (AJAX) or add_cart_item() (cart).
	// Only skip the base-price override in transactional contexts so the
	// product/shop page still shows the base USD price.
	$has_duration_options = get_post_meta($product->get_id(), '_wc_appointment_enable_duration_options', true) === 'yes';
	if ($has_duration_options) {
		// AJAX cost calculation — appointments_calculated_product_price handles pricing.
		if (wp_doing_ajax()) {
			return $cost;
		}
		// Cart / checkout — add_cart_item() already called set_price() with the correct value.
		if (did_action('woocommerce_cart_loaded_from_session') || is_checkout()) {
			return $cost;
		}
	}

	// USD: use USD meta fields.
	$sale_price = $product->get_meta('_wc_usd_display_sale_price');
	if (!empty($sale_price)) {
		return $sale_price;
	}
	$usd_cost = get_post_meta($product->get_id(), '_wc_usd_display_cost', true);
	if (empty($usd_cost)) {
		return $cost;
	}

	if ($product->get_meta('addons_instance') === 'yes') {
		return $cost;
	}

	return $usd_cost;
}

add_filter('woocommerce_product_get_sale_price', 'get_usd_booking_sale_price', 999999, 2);
function get_usd_booking_sale_price($cost, $product)
{
	if (is_admin() && !defined('DOING_AJAX')) {
		return $cost;
	}

	if (wellness_get_active_currency($product) === 'EGP') {
		return $cost;
	}

	$usd_cost = get_post_meta($product->get_id(), '_wc_usd_display_sale_price', true);
	if (empty($usd_cost)) {
		return $cost;
	}

	return $usd_cost;
}

add_filter('woocommerce_product_get_block_cost', 'get_usd_booking_block_cost', 999999, 2);
function get_usd_booking_block_cost($cost, $product)
{
	if (is_admin() && !defined('DOING_AJAX')) {
		return $cost;
	}

	if (wellness_get_active_currency($product) === 'EGP') {
		return $cost;
	}

	$usd_cost = get_post_meta($product->get_id(), '_wc_usd_booking_block_cost', true);
	if (empty($usd_cost)) {
		return $cost;
	}

	return $usd_cost;
}

add_filter('woocommerce_product_display_cost', 'get_usd_display_cost', 999999, 2);
function get_usd_display_cost($cost, $product)
{
	if (is_admin() && !defined('DOING_AJAX')) {
		return $cost;
	}

	if (wellness_get_active_currency($product) === 'EGP') {
		return $cost;
	}

	$usd_cost = get_post_meta($product->get_id(), '_wc_usd_display_cost', true);
	if (empty($usd_cost)) {
		return $cost;
	}

	return $usd_cost;
}

add_filter('woocommerce_currency', 'change_woocommerce_currency', 999999, 1);
function change_woocommerce_currency($currency)
{
	// Bail early — WooCommerce isn't ready before wp_loaded.
	if (! did_action('wp_loaded')) {
		return $currency;
	}

	// ── AJAX: wc_appointments_calculate_costs sends staff ID in 'form' ───
	if (wp_doing_ajax() && ! empty($_REQUEST['form'])) {
		$form_data = [];
		parse_str($_REQUEST['form'], $form_data);
		if (! empty($form_data['wc_appointments_field_staff'])) {
			$staff_id       = (int) $form_data['wc_appointments_field_staff'];
			$staff_currency = get_user_meta($staff_id, '_staff_currency', true);
			if ($staff_currency === 'USD') {
				return 'USD';
			}
			if ($staff_currency === 'EGP') {
				return 'EGP';
			}
			// empty / Location Based — fall through to product resolution
		}
		// No staff field in form (staff_assignment !== 'customer'),
		// or staff is Location Based — resolve from product.
		$product_id = $form_data['add-to-cart'] ?? ($form_data['appointable-product-id'] ?? 0);
		if ($product_id) {
			$product = wc_get_product($product_id);
			if ($product && wellness_get_active_currency($product) === 'USD') {
				return 'USD';
			}
		}
		return $currency;
	}

	// ── Cart / checkout ───────────────────────────────────────────────────
	if (WC()->cart && ! WC()->cart->is_empty()) {
		foreach (WC()->cart->get_cart() as $cart_item) {
			if (wellness_get_active_currency($cart_item['data']) !== 'EGP') {
				return 'USD';
			}
		}
	}

	// ── Single product page — queried object if ready ─────────────────────
	if (is_product()) {
		$product_id = get_queried_object_id();
		if ($product_id) {
			$product = wc_get_product($product_id);
			if ($product && wellness_get_active_currency($product) !== 'EGP') {
				return 'USD';
			}
			return $currency;
		}
		// queried object not ready — fall through to global $product
	}

	// ── Fall back to global $product ──────────────────────────────────────
	global $product;

	if (! $product || wellness_get_active_currency($product) === 'EGP') {
		return $currency;
	}

	return 'USD';
}

//add_filter('woocommerce_currency_symbol', 'change_woocommerce_currency_symbol', 999999, 2);
function change_woocommerce_currency_symbol($currency_symbol, $currency)
{
	if ($currency === 'USD') {
		return '$';
	}

	return $currency_symbol;
}


// add_filter('woocommerce_product_addons_option_price_raw', function ($option_price, $option) {

//     if (class_exists('WOOCS')) {
//         global $WOOCS;
//         $option_price = $WOOCS->woocs_exchange_value(floatval($option_price));
//     }

//     return $option_price;
// }, 10, 2);

// ═══════════════════════════════════════════════════════════════════════════════
// Payment Gateway Routing — Stripe for USD, Paymob for EGP.
// Default: Paymob.  Only switch to Stripe when the cart resolves to USD.
// ═══════════════════════════════════════════════════════════════════════════════

add_filter('woocommerce_available_payment_gateways', 'wellness_filter_gateways_by_currency', 999999);

function wellness_filter_gateways_by_currency($gateways)
{
	// Do not interfere with admin screens.
	if (is_admin() && ! wp_doing_ajax()) {
		return $gateways;
	}

	// ── Determine currency from cart ─────────────────────────────────────
	$is_usd = false;

	if (WC()->cart && ! WC()->cart->is_empty()) {
		foreach (WC()->cart->get_cart() as $cart_item) {
			if (wellness_get_active_currency($cart_item['data']) === 'USD') {
				$is_usd = true;
				break;
			}
		}
	}

	// ── Allowed gateway IDs per currency ──────────────────────────────────
	$stripe_ids = [
		'stripe_cc',
		'stripe_applepay',
		'stripe_googlepay',
	];

	$paymob_ids = [
		'paymob',
		'paymob-pixel',
		'paymob-4475761-card-vpc-egp',
		'paymob-4371991-staging-test-vpc-egp',
		'paymob-5618802-migs-online-next-apple-pay-vpc-egp',
		'paymob-subscription',
	];

	// ── Default: Paymob (EGP). Only USD cart switches to Stripe. ────────
	$allowed = $is_usd ? $stripe_ids : $paymob_ids;

	foreach ($gateways as $id => $gateway) {
		if (! in_array($id, $allowed, true)) {
			unset($gateways[$id]);
		}
	}

	return $gateways;
}

// add css code to admin panel when user role is shop_staff
add_action('admin_head', 'wellness_booking_admin_css');
function wellness_booking_admin_css()
{
	$user = wp_get_current_user();
	if (in_array('shop_staff', $user->roles) && !in_array('administrator', $user->roles)) {
?>
		<style>
			#adminmenu li.menu-top:not(li#menu-posts-wc_appointment) {
				display: none;
			}

			.notice.notice-warning.update-nag.inline {
				display: none;
			}
		</style>
	<?php
	}
}

// redirect user to wp-admin/edit.php?post_type=wc_appointment if user role is shop_staff upon login
add_action('wp_login', 'wellness_redirect_shop_staff_to_appointments', 10, 2);
function wellness_redirect_shop_staff_to_appointments($user_login, $user)
{
	if (in_array('shop_staff', $user->roles)) {
		wp_redirect(admin_url('edit.php?post_type=wc_appointment'));
		exit;
	}
}


/** 
 * Add with Code Snippets plugin.
 *
 * In this example we're creating a appointment 1 week after an appointment is paid for.
 * This does not create another order or payment, just an additional appointment.
 * $exact is false meaning if our slot is taken, the next available slot will be used.
 */
// add_action('woocommerce_appointment_in-cart_to_paid', 'auto_create_followup_appointment');
// add_action('woocommerce_appointment_unpaid_to_paid', 'auto_create_followup_appointment');
// add_action('woocommerce_appointment_confirmed_to_paid', 'auto_create_followup_appointment');
// add_action('woocommerce_appointment_pending_to_paid', 'auto_create_followup_appointment');
// add_action('woocommerce_appointment_to_confirmed', 'auto_create_followup_appointment');
function auto_create_followup_appointment($appointment_id)
{
	// Get the previous appointment from the ID
	$prev_appointment = get_wc_appointment($appointment_id);
	$recurring_end_period_length = get_post_meta($prev_appointment->get_product_id(), '_wc_appointment_recurring_end_period_length', true);
	$recurring_end_period_unit = get_post_meta($prev_appointment->get_product_id(), '_wc_appointment_recurring_end_period_unit', true);

	// Get product ID
	$product_id = $prev_appointment->get_product_id();

	// get product meta booking type
	$booking_type = get_post_meta($product_id, '_wc_appointment_booking_type', true);
	// Only execute if product meta value is recurring
	if ($booking_type == 'recurring') {

		// Don't want follow ups for follow ups
		if ($prev_appointment->get_parent_id() <= 0) {

			$recurring_type = get_post_meta($product_id, '_wc_appointment_recurring_type', true);

			if ($recurring_type == 'weekly') {
				$interval = '+1 week';
			} else if ($recurring_type == 'every_other_week') {
				$interval = '+2 weeks';
			} else if ($recurring_type == 'monthly') {
				$interval = '+1 month';
			}

			// book till end period
			if ($recurring_end_period_length > 0) {
				if ($recurring_type == 'weekly' && $recurring_end_period_unit == 'month') {
					$recurring_end_period_length = $recurring_end_period_length * 4;
				} else if ($recurring_type == 'weekly' && $recurring_end_period_unit == 'year') {
					$recurring_end_period_length = $recurring_end_period_length * 52;
				} else if ($recurring_type == 'every_other_week' && $recurring_end_period_unit == 'month') {
					$recurring_end_period_length = $recurring_end_period_length * 2;
				} else if ($recurring_type == 'every_other_week' && $recurring_end_period_unit == 'year') {
					$recurring_end_period_length = $recurring_end_period_length * 26;
				} else if ($recurring_type == 'monthly' && $recurring_end_period_unit == 'year') {
					$recurring_end_period_length = $recurring_end_period_length * 12;
				}

				for ($i = 1; $i < $recurring_end_period_length; $i++) {
					$interval = "+$i $recurring_end_period_unit";
					$new_appointment_data = array(
						'start_date' => strtotime($interval, $prev_appointment->get_start()), // same time, 1 week on
						'end_date'   => strtotime($interval, $prev_appointment->get_end()),   // same time, 1 week on
						'staff_ids'  => $prev_appointment->get_staff_ids(),                     // same staff
						'parent_id'  => $appointment_id,                                        // set the parent
					);
					// Was the previous appointment all day?
					if ($prev_appointment->is_all_day()) {
						$new_appointment_data['all_day'] = true;
					}
					create_wc_appointment(
						$prev_appointment->get_product_id(), // Creating a appointment for the previous appointments product
						$new_appointment_data,               // Use the data pulled above
						$prev_appointment->get_status(),     // Match previous appointments status
						false                                // Not exact, look for next available slot
					);
				}
			}
		} else {
			// book till end of the year
			for ($i = 1; $i < 12; $i++) {
				$interval = "+$i month";
				$new_appointment_data = array(
					'start_date' => strtotime($interval, $prev_appointment->get_start()), // same time, 1 week on
					'end_date'   => strtotime($interval, $prev_appointment->get_end()),   // same time, 1 week on
					'staff_ids'  => $prev_appointment->get_staff_ids(),                     // same staff
					'parent_id'  => $appointment_id,                                        // set the parent
				);
				// Was the previous appointment all day?
				if ($prev_appointment->is_all_day()) {
					$new_appointment_data['all_day'] = true;
				}
				create_wc_appointment(
					$prev_appointment->get_product_id(), // Creating a appointment for the previous appointments product
					$new_appointment_data,               // Use the data pulled above
					$prev_appointment->get_status(),     // Match previous appointments status
					false                                // Not exact, look for next available slot
				);
			}
		}
	}
}

// add meta box to product edit page
add_action('woocommerce_product_options_general_product_data', 'wellness_product_meta_box_callback');
function add_wellness_product_meta_box($settings)
{
	$settings[] = array(
		'title' => __('Wellness Product', 'woocommerce'),
		'type' => 'title',
		'desc' => '',
		'id' => 'wellness_product_meta_box'
	);

	$settings[] = array(
		'title' => __('Booking Type', 'woocommerce'),
		'desc' => __('Select booking type for this product', 'woocommerce'),
		'id' => '_wc_appointment_booking_type',
		'type' => 'select',
		'options' => array(
			'recurring' => 'Recurring',
			'single' => 'Single'
		),
		'default' => 'single',
		'css' => 'min-width:300px;',
		'desc_tip' => true
	);

	$settings[] = array(
		'title' => __('Recurring Type', 'woocommerce'),
		'desc' => __('Select recurring type for this product', 'woocommerce'),
		'id' => '_wc_appointment_recurring_type',
		'type' => 'select',
		'options' => array(
			'weekly' => 'Weekly',
			'every_other_week' => 'Every Other Week',
			'monthly' => 'Monthly',
		),
		'default' => 'weekly',
		'css' => 'min-width:300px;',
		'desc_tip' => true
	);

	$settings[] = array('type' => 'sectionend', 'id' => 'wellness_product_meta_box');

	return $settings;
}
function wellness_product_meta_box_callback()
{
	global $post;
	$product_id = $post->ID;
	// ── DEPRECATED (2026-08-30): legacy booking/recurring type vars commented out. ──
	// $_wc_appointment_booking_type = get_post_meta($product_id, '_wc_appointment_booking_type', true) ?? 'single';
	// $recurring_type = get_post_meta($product_id, '_wc_appointment_recurring_type', true);

	// $recurring_types = array(
	// 	'weekly' => 'Weekly',
	// 	'every_other_week' => 'Every Other Week',
	// 	'monthly' => 'Monthly',
	// );

	// $recurring_type_options = '<option value="">Select Recurring Type</option>';
	// foreach ($recurring_types as $key => $value) {
	// 	$selected = $recurring_type == $key ? 'selected' : '';
	// 	$recurring_type_options .= "<option value='$key' $selected>$value</option>";
	// }

	$recurring_type_html = '<div class="options_group">';

	// ── Enable Recurring toggle (default ON) ─────────────────────────────
	$enable_recurring = get_post_meta($product_id, '_wc_appointment_enable_recurring', true);
	$enable_recurring = ($enable_recurring === '') ? 'yes' : $enable_recurring; // default ON

	$recurring_type_html .= '
	<p class="form-field">
		<label for="_wc_appointment_enable_recurring">Enable Recurring</label>
		<input type="checkbox" name="_wc_appointment_enable_recurring" id="_wc_appointment_enable_recurring" value="yes" ' . checked($enable_recurring, 'yes', false) . ' />
		<span class="description">Let clients repeat this appointment (choose interval &amp; number of repeats). Disable for single-session products.</span>
	</p>';

	$max_repeat_count = get_post_meta($product_id, '_wc_appointment_max_repeat_count', true);
	$max_repeat_count = ($max_repeat_count !== '') ? intval($max_repeat_count) : 2;

	$recurring_type_html .= '
  <p class="form-field">
    <label for="_wc_appointment_max_repeat_count">Max Repeat Count</label>
    <input type="number" name="_wc_appointment_max_repeat_count" id="_wc_appointment_max_repeat_count" value="' . $max_repeat_count . '" min="1" />
    <span class="description">Maximum number of times a customer can repeat this appointment (default: 2).</span>
  </p>';

	$recurring_type_html .= '</div>';

	// ── Session Types & Lengths section ──────────────────────────────────
	$enable_duration      = get_post_meta($product_id, '_wc_appointment_enable_duration_options', true) === 'yes';
	$duration_options     = json_decode(get_post_meta($product_id, '_wc_appointment_duration_options', true), true);
	if (! is_array($duration_options)) $duration_options = [];

	$recurring_type_html .= '<div class="options_group wellness-session-fields">';

	// Enable Duration Options toggle
	$recurring_type_html .= '
	<p class="form-field">
		<label for="_wc_appointment_enable_duration_options">Enable Duration Options</label>
		<input type="checkbox" name="_wc_appointment_enable_duration_options" id="_wc_appointment_enable_duration_options" value="yes" ' . checked($enable_duration, true, false) . ' />
		<span class="description">Let customers pick from custom duration options (e.g. "Individual – 30 min", "Couples – 60 min"). Each option sets duration + replaces the base price.</span>
	</p>';

	// Duration Options repeater
	$dur_json = esc_attr(json_encode($duration_options));
	$recurring_type_html .= '
	<div id="wellness-duration-wrapper" style="' . ($enable_duration ? '' : 'display:none;') . '">
		<h4 style="margin:12px 0 4px;">Duration Options <span class="description">(label, duration in minutes, EGP/USD replacement prices)</span></h4>
		<table class="widefat wellness-repeater-table" id="wellness-duration-table" style="width:auto;min-width:80%;">
			<thead><tr>
				<th style="width:22%;">Label</th>
				<th style="width:13%;">Duration (min)</th>
				<th style="width:18%;">EGP Price</th>
				<th style="width:18%;">USD Price</th>
				<th style="width:9%;"></th>
			</tr></thead>
			<tbody id="wellness-duration-tbody"></tbody>
		</table>
		<button type="button" class="button wellness-add-row" data-target="duration">+ Add Duration Option</button>
		<input type="hidden" name="_wc_appointment_duration_options" id="_wc_appointment_duration_options" value="' . $dur_json . '" />
	</div>';

	$recurring_type_html .= '</div>'; // .wellness-session-fields

	// ── Inline JS for repeater ──────────────────────────────────────────
	$recurring_type_html .= '
<script>
(function($){
	var data = ' . json_encode($duration_options) . ';

	function renderTable() {
		var tbody = $("#wellness-duration-tbody");
		tbody.empty();
		if (!data.length) {
			tbody.append(\'<tr class="wellness-empty-row"><td colspan="5" style="color:#999;font-style:italic;">No entries yet — click “Add” to create one.</td></tr>\');
			return;
		}
		$.each(data, function(i, row){
			var html = \'<tr>\' +
				\'<td><input type="text" class="wellness-label" value="\' + escAttr(row.label||"") + \'" placeholder="e.g. Individual – 30 min" style="width:95%;" /></td>\' +
				\'<td><input type="number" class="wellness-duration" value="\' + escAttr(row.duration||"") + \'" placeholder="30" style="width:95%;" /></td>\' +
				\'<td><input type="number" step="0.01" class="wellness-price-egp" value="\' + escAttr(row.price_egp||"") + \'" placeholder="0" style="width:95%;" /></td>\' +
				\'<td><input type="number" step="0.01" class="wellness-price-usd" value="\' + escAttr(row.price_usd||"") + \'" placeholder="0" style="width:95%;" /></td>\' +
				\'<td><button type="button" class="button wellness-remove-row" data-index="\' + i + \'">×</button></td>\' +
				\'</tr>\';
			tbody.append(html);
		});
	}

	function syncHidden() {
		var entries = [];
		$("#wellness-duration-tbody tr:not(.wellness-empty-row)").each(function(){
			var $r = $(this);
			var entry = {
				label:     ($r.find(".wellness-label").val() || "").trim(),
				duration:  $r.find(".wellness-duration").val() || "",
				price_egp: $r.find(".wellness-price-egp").val() || "",
				price_usd: $r.find(".wellness-price-usd").val() || ""
			};
			if (entry.label) entries.push(entry);
		});
		data = entries;
		$("#_wc_appointment_duration_options").val(JSON.stringify(entries));
	}

	function escAttr(str) { return String(str).replace(/&/g,"&amp;").replace(/"/g,"&quot;").replace(/</g,"&lt;").replace(/>/g,"&gt;"); }

	renderTable();

	$("#_wc_appointment_enable_duration_options").on("change", function(){
		$("#wellness-duration-wrapper").toggle(this.checked);
	});

	$(".wellness-add-row").on("click", function(){
		data.push({label:"", duration:"", price_egp:"", price_usd:""});
		renderTable();
		syncHidden();
	});

	$(document).on("click", ".wellness-remove-row", function(){
		var index = $(this).data("index");
		data.splice(index, 1);
		renderTable();
		syncHidden();
	});

	$(document).on("input", "#wellness-duration-table input", function(){
		syncHidden();
	});
})(jQuery);
</script>';

	echo $recurring_type_html;
}
add_action('save_post', 'save_wellness_product_meta_box');
function save_wellness_product_meta_box($post_id)
{
	// ── DEPRECATED (2026-08-30): legacy recurring meta commented out. ──────
	// if (array_key_exists('_wc_appointment_recurring_type', $_POST)) {
	// 	update_post_meta(
	// 		$post_id,
	// 		'_wc_appointment_recurring_type',
	// 		$_POST['_wc_appointment_recurring_type']
	// 	);
	// }

	// if (array_key_exists('_wc_appointment_booking_type', $_POST)) {
	// 	update_post_meta(
	// 		$post_id,
	// 		'_wc_appointment_booking_type',
	// 		$_POST['_wc_appointment_booking_type']
	// 	);
	// }

	// if (array_key_exists('_wc_appointment_recurring_end_length', $_POST)) {
	// 	update_post_meta(
	// 		$post_id,
	// 		'_wc_appointment_recurring_end_length',
	// 		$_POST['_wc_appointment_recurring_end_length']
	// 	);
	// }

	// if (array_key_exists('_wc_appointment_recurring_end_unit', $_POST)) {
	// 	update_post_meta(
	// 		$post_id,
	// 		'_wc_appointment_recurring_end_unit',
	// 		$_POST['_wc_appointment_recurring_end_unit']
	// 	);
	// }

	// ── Enable Recurring toggle ──────────────────────────────────────────
	$enable_recurring = isset($_POST['_wc_appointment_enable_recurring']) && $_POST['_wc_appointment_enable_recurring'] === 'yes' ? 'yes' : 'no';
	update_post_meta($post_id, '_wc_appointment_enable_recurring', $enable_recurring);

	if (array_key_exists('_wc_appointment_max_repeat_count', $_POST)) {
		update_post_meta(
			$post_id,
			'_wc_appointment_max_repeat_count',
			absint($_POST['_wc_appointment_max_repeat_count'])
		);
	}

	// ── Duration Options enable toggle ──────────────────────────────────
	$enable_duration = isset($_POST['_wc_appointment_enable_duration_options']) && $_POST['_wc_appointment_enable_duration_options'] === 'yes' ? 'yes' : 'no';
	update_post_meta($post_id, '_wc_appointment_enable_duration_options', $enable_duration);

	// ── Duration Options JSON ───────────────────────────────────────────
	if (array_key_exists('_wc_appointment_duration_options', $_POST)) {
		$options = json_decode(stripslashes($_POST['_wc_appointment_duration_options']), true);
		if (is_array($options)) {
			$options = array_values(array_filter(array_map(function ($entry) {
				$label = sanitize_text_field($entry['label'] ?? '');
				if ($label === '') return null;
				return [
					'label'     => $label,
					'duration'  => absint($entry['duration'] ?? 0),
					'price_egp' => floatval($entry['price_egp'] ?? 0),
					'price_usd' => floatval($entry['price_usd'] ?? 0),
				];
			}, $options)));
			update_post_meta($post_id, '_wc_appointment_duration_options', wp_json_encode($options));
		}
	}
}

// add to product description that the product is recurring
// DEPRECATED (2026-08-30): legacy recurring description commented out. Recurring
// is now driven by the `_wc_appointment_enable_recurring` toggle + frontend fields.
// add_action('woocommerce_single_product_summary', 'add_recurring_product_description', 20);
// add_action('woocommerce_before_add_to_cart_button', 'add_recurring_product_description', 20);
// function add_recurring_product_description()
// {
// 	global $product;
// 	$product_id = $product->get_id();
// 	$booking_type = get_post_meta($product_id, '_wc_appointment_booking_type', true);
// 	$recurring_type = get_post_meta($product_id, '_wc_appointment_recurring_type', true);

// 	if ($booking_type == 'recurring') {
// 		$recurring_types = array(
// 			'weekly' => 'Weekly',
// 			'every_other_week' => 'Every Other Week',
// 			'monthly' => 'Monthly',
// 		);

// 		$end_period_length = get_post_meta($product_id, '_wc_appointment_recurring_end_length', true) ?? 1;
// 		$end_period_unit = get_post_meta($product_id, '_wc_appointment_recurring_end_unit', true) ?? 'month';

// 		if ($end_period_length && $end_period_unit) {
// 			$end_period_label = $end_period_length . ' ' . $end_period_unit;
// 			echo "<p>This is a recurring product, it will be repeated " . $recurring_types[$recurring_type] . " from the first appointment till " . $end_period_label . ".</p>";
// 		} else {
// 			echo "<p>This is a recurring product, it will be repeated " . $recurring_types[$recurring_type] . " from the first appointment.</p>";
// 		}
// 	}
// }

add_filter('woocommerce_loop_add_to_cart_link', 'custom_replace_add_to_cart_button', 10, 2);

function custom_replace_add_to_cart_button($button, $product)
{
	// Check if the product is purchasable and in stock
	if (!$product->is_purchasable() || !$product->is_in_stock()) {
		return '<a href="' . esc_url(get_permalink($product->get_id())) . '" class="button read-more">' . __('Read More', 'woocommerce') . '</a>';
	}

	// Optionally apply to all products
	return '<a href="' . esc_url(get_permalink($product->get_id())) . '" class="button read-more">' . __('Read More', 'woocommerce') . '</a>';
}

// get product addon value on order creation
/**
 * TEMP DEBUG — log via the WooCommerce logger (source: wellness-recurring).
 * Visible under WooCommerce → Status → Logs.
 */
function wellness_recurring_log($message)
{
	if (function_exists('wc_get_logger')) {
		wc_get_logger()->info('[WELLNESS_RECURRING] ' . $message, array('source' => 'wellness-recurring'));
	}
}

/**
 * Create recurring follow-up appointments + orders when the parent (first)
 * appointment is paid. Replaces the old WooCommerce Product Add-Ons approach.
 *
 * Each follow-up appointment is created as 'unpaid' and linked to a new
 * 'pending' order, so the standard payment flow (order paid → appointment
 * paid → confirmed) triggers client + therapist confirmation emails.
 */
function wellness_create_recurring_appointments($from_status, $to_status, $appointment_id)
{
	// TEMP DEBUG
	wellness_recurring_log('engine fired: appt=' . $appointment_id . ' from=' . $from_status . ' to=' . $to_status);

	// Only trigger on the paid transition to avoid duplicates on later status changes (paid -> confirmed).
	if ($to_status !== 'paid') {
		return;
	}

	$appointment = get_wc_appointment($appointment_id);
	if (! $appointment) {
		wellness_recurring_log('engine: appointment not found');
		return;
	}

	// Only the first appointment of a recurring series triggers creation.
	if ($appointment->get_parent_id() > 0) {
		wellness_recurring_log('engine: is follow-up (parent_id=' . $appointment->get_parent_id() . '), skip');
		return;
	}

	// Read recurrence settings: appointment post meta first, then fall back to the
	// order item (the appointment post meta may be empty if the appointment wasn't
	// linked to the order item by woocommerce_checkout_order_processed).
	$recurring = get_post_meta($appointment_id, '_recurring', true);
	$interval  = get_post_meta($appointment_id, '_recurring_interval', true);
	$count     = absint(get_post_meta($appointment_id, '_recurring_count', true));

	if ($recurring !== 'yes') {
		$order = $appointment->get_order();
		if ($order) {
			foreach ($order->get_items() as $item) {
				if ($item->get_meta('_recurring') === 'yes') {
					$recurring = 'yes';
					$interval  = $item->get_meta('_recurring_interval');
					$count     = absint($item->get_meta('_recurring_count'));
					break;
				}
			}
		}
		// Persist to the appointment so downstream features (admin chain, emails) can read it.
		if ($recurring === 'yes') {
			update_post_meta($appointment_id, '_recurring', 'yes');
			if ($interval) update_post_meta($appointment_id, '_recurring_interval', $interval);
			update_post_meta($appointment_id, '_recurring_count', $count);
			wellness_recurring_log('engine: recurrence resolved from order item, persisted to appointment');
		}
	}

	if ($recurring !== 'yes') {
		wellness_recurring_log('engine: _recurring not yes, skip');
		return;
	}

	if (! $interval || $count <= 0) {
		wellness_recurring_log('engine: interval/count missing (interval=' . $interval . ' count=' . $count . '), skip');
		return;
	}

	$product_id = $appointment->get_product_id();
	$product    = wc_get_product($product_id);
	if (! $product) {
		wellness_recurring_log('engine: product not found');
		return;
	}

	// The product must still allow recurring (default ON).
	$enable = get_post_meta($product_id, '_wc_appointment_enable_recurring', true);
	$enable = ($enable === '') ? 'yes' : $enable;
	if ($enable !== 'yes') {
		wellness_recurring_log('engine: product recurring disabled, skip');
		return;
	}

	// Cap against the product-level maximum.
	$max_count = max(1, intval(get_post_meta($product_id, '_wc_appointment_max_repeat_count', true) ?: 2));
	$count     = min($count, $max_count);

	// Confirm follow-ups aren't already created.
	$existing = get_posts(array(
		'post_type'   => 'wc_appointment',
		'post_status' => 'any',
		'meta_query'  => array(
			array('key' => '_appointment_parent_id', 'value' => $appointment_id),
		),
		'fields' => 'ids',
	));
	if (! empty($existing)) {
		wellness_recurring_log('engine: follow-ups already exist (' . count($existing) . '), skip');
		return;
	}

	list($unit, $mult) = wellness_recurring_interval_map($interval);

	$original_start = $appointment->get_start();
	$original_end   = $appointment->get_end();
	$all_day        = $appointment->is_all_day();

	$order = $appointment->get_order();
	if (! $order) {
		return;
	}
	$currency    = $order->get_currency();
	$customer_id = $order->get_customer_id();
	$tz          = $order->get_meta('_customer_timezone', true);

	// Copy the parent line-item price (captures session-type / EGP / USD price).
	$parent_item = null;
	foreach ($order->get_items() as $item) {
		if ((int) $item->get_product_id() === (int) $product_id || $item->get_meta('_appointment_id') == $appointment_id) {
			$parent_item = $item;
			break;
		}
	}

	$fallback_price  = (float) $product->get_price();
	$line_subtotal   = $parent_item ? (float) $parent_item->get_subtotal() : $fallback_price;
	$line_total      = $parent_item ? (float) $parent_item->get_total() : $fallback_price;
	$line_tax        = $parent_item ? (float) $parent_item->get_total_tax() : 0;
	$line_subtotal_tax = $parent_item ? (float) $parent_item->get_subtotal_tax() : 0;
	$session_type    = $parent_item ? (string) $parent_item->get_meta('_session_type') : '';

	for ($i = 1; $i <= $count; $i++) {
		$offset       = '+' . ($i * $mult) . ' ' . $unit;
		$target_start = strtotime($offset, $original_start);
		$target_end   = strtotime($offset, $original_end);
		if (! $target_start || ! $target_end) {
			continue;
		}

		// Create the follow-up order first so we can link the appointment to it.
		$new_order = wc_create_order(array(
			'customer_id' => $customer_id,
			'created_via' => 'recurring',
			'parent'      => $order->get_id(),
		));
		$new_order->set_address($order->get_address('billing'), 'billing');
		$new_order->set_address($order->get_address('shipping'), 'shipping');
		$new_order->set_created_via('recurring');
		$new_order->set_currency($currency);
		$new_order->set_payment_method($order->get_payment_method());
		$new_order->set_payment_method_title($order->get_payment_method_title());
		if ($tz) {
			$new_order->update_meta_data('_customer_timezone', $tz);
		}
		if (! wc_tax_enabled()) {
			$new_order->set_shipping_tax(0);
			$new_order->set_cart_tax(0);
		}
		$new_order->calculate_totals();

		$new_order_id = $new_order->get_id();

		$item_id = wc_add_order_item($new_order_id, array(
			'order_item_name' => $product->get_title(),
			'order_item_type' => 'line_item',
		));

		wc_update_order_item_meta($item_id, '_qty', 1);
		wc_update_order_item_meta($item_id, '_tax_class', $product->get_tax_class());
		wc_update_order_item_meta($item_id, '_product_id', $product->get_id());
		wc_update_order_item_meta($item_id, '_variation_id', '');
		wc_update_order_item_meta($item_id, '_line_subtotal', $line_subtotal);
		wc_update_order_item_meta($item_id, '_line_total', $line_total);
		wc_update_order_item_meta($item_id, '_line_tax', $line_tax);
		wc_update_order_item_meta($item_id, '_line_subtotal_tax', $line_subtotal_tax);
		if ($session_type) {
			wc_update_order_item_meta($item_id, '_session_type', $session_type);
		}

		$new_order->calculate_totals();
		$new_order->set_total($line_total + $line_tax);
		$new_order->save();

		// Create the follow-up appointment as 'unpaid' linked to the pending order.
		$new_appointment_data = array(
			'start_date'  => $target_start,
			'end_date'    => $target_end,
			'staff_ids'   => $appointment->get_staff_ids(),
			'parent_id'   => $appointment_id,
			'customer_id' => $appointment->get_customer_id(),
		);
		if ($all_day) {
			$new_appointment_data['all_day'] = true;
		}

		$new_appointment = create_wc_appointment($product_id, $new_appointment_data, 'unpaid', false);
		if (! $new_appointment) {
			$new_order->update_status('failed');
			continue;
		}

		$new_appointment->set_order_id($new_order_id);
		$new_appointment->set_order_item_id($item_id);
		$new_appointment->set_parent_id($appointment_id); // ensure the follow-up links to the root
		$new_appointment->save();

		wellness_recurring_log('created follow-up: appt=' . $new_appointment->get_id() . ' order=' . $new_order_id . ' start=' . $new_appointment->get_start());

		if ($session_type) {
			update_post_meta($new_appointment->get_id(), '_session_type', $session_type);
		}

		// Detect any silent slot shift (exact slot was taken).
		$created_start = $new_appointment->get_start();
		if ($created_start != $target_start) {
			$shift_note = sprintf(
				/* translators: 1: requested time, 2: booked time */
				'Recurring follow-up moved: requested %1$s, booked %2$s (the exact slot was unavailable).',
				date_i18n('M j, Y g:i A', $target_start),
				date_i18n('M j, Y g:i A', $created_start)
			);
			$new_order->add_order_note($shift_note);
		}

		$new_appointment->maybe_schedule_event('reminder');
		$new_appointment->maybe_schedule_event('complete');

		// Schedule the pay reminder + unpaid-cancel check.
		$reminder_days = wellness_get_recurring_payment_reminder_days();
		$reminder_ts   = $created_start - ($reminder_days * DAY_IN_SECONDS);
		if ($reminder_ts > current_time('timestamp')) {
			as_schedule_single_action($reminder_ts, 'wellness-appointment-payment-reminder', array($new_appointment->get_id(), $new_order_id), 'wca');
		}
		as_schedule_single_action($created_start, 'wellness-appointment-payment-check', array($new_appointment->get_id(), $new_order_id), 'wca');
	}
}

/**
 * Lead time (days before the appointment) for the recurring payment-reminder email.
 */
function wellness_get_recurring_payment_reminder_days()
{
	$days = intval(get_option('wellness_recurring_payment_reminder_days', 1));
	return $days > 0 ? $days : 1;
}

/**
 * Send the recurring "complete payment" reminder for a follow-up appointment.
 * Hooked from the Action Scheduler job scheduled in wellness_create_recurring_appointments().
 */
add_action('wellness-appointment-payment-reminder', 'wellness_send_recurring_payment_reminder', 10, 2);
function wellness_send_recurring_payment_reminder($appointment_id, $order_id)
{
	$appointment = get_wc_appointment($appointment_id);
	if (! $appointment) return;

	$order = wc_get_order($order_id);
	if (! $order) return;

	// Only while the follow-up order is still pending and the appointment unpaid.
	if (! $order->has_status('pending')) return;
	if (! in_array($appointment->get_status(), ['unpaid', 'in-cart', 'pending-confirmation'], true)) return;

	$recipient = $order->get_billing_email();
	if (empty($recipient)) return;

	$customer_first_name = $order->get_billing_first_name();
	if (empty($customer_first_name)) {
		$_full = $order->get_meta('billing_full_name', true);
		if ($_full) {
			$_parts              = explode(' ', trim($_full));
			$customer_first_name = $_parts[0];
		}
	}

	$pay_url = $order->get_checkout_payment_url();

	$mailer  = WC()->mailer();
	$subject = __('Complete your appointment payment', 'woodmart-child');

	ob_start();
	$email_obj       = new stdClass();
	$email_obj->id   = 'customer_appointment_payment';

	wc_get_template(
		'emails/customer-appointment-payment.php',
		[
			'appointment'         => $appointment,
			'order'               => $order,
			'pay_url'             => $pay_url,
			'customer_first_name' => $customer_first_name,
			'email_heading'       => __('Complete your appointment payment', 'woodmart-child'),
			'sent_to_admin'       => false,
			'plain_text'          => false,
			'email'               => $email_obj,
		],
		'',
		get_stylesheet_directory() . '/woocommerce/'
	);
	$message = ob_get_clean();

	$message = $mailer->wrap_message($subject, $message);
	$mailer->send($recipient, $subject, $message, $mailer->get_headers(), []);
}

/**
 * Auto-cancel an unpaid recurring follow-up appointment at its start time and
 * notify the therapist. Hooked from the Action Scheduler job scheduled in
 * wellness_create_recurring_appointments().
 */
add_action('wellness-appointment-payment-check', 'wellness_cancel_unpaid_recurring_appointment', 10, 2);
function wellness_cancel_unpaid_recurring_appointment($appointment_id, $order_id)
{
	$appointment = get_wc_appointment($appointment_id);
	if (! $appointment) return;

	// Only cancel if the appointment is still unpaid / not yet confirmed.
	if (! in_array($appointment->get_status(), ['unpaid', 'in-cart', 'pending-confirmation'], true)) return;

	// Flag it so the admin email template can explain the reason (Phase 7).
	update_post_meta($appointment_id, '_recurring_unpaid_cancelled', 'yes');

	// Cancel the appointment — fires the admin(staff) and customer
	// appointment-cancelled emails via the plugin's status transition hooks.
	$appointment->update_status('cancelled');

	// Cancel the still-pending follow-up order and suppress the generic
	// "order cancelled" email (the appointment-cancelled email already covers
	// the customer). Add an order note so the therapist sees the reason.
	$order = wc_get_order($order_id);
	if ($order && $order->has_status('pending')) {
		add_filter('woocommerce_email_enabled_customer_cancelled_order', function ($enabled, $o) use ($order) {
			return ($o && $o->get_id() === $order->get_id()) ? false : $enabled;
		}, 10, 2);

		$order->update_status('cancelled', __('Recurring appointment was not paid and has been cancelled.', 'woodmart-child'));
	}
}

add_action('woocommerce_appointment_status_changed', 'wellness_create_recurring_appointments', 10, 3);
add_filter('wc_products_array_filter_readable', 'restrict_products_to_staff_based_on_role', 10, 2);
function restrict_products_to_staff_based_on_role($products, $staff_id)
{
	$user = get_user_by('ID', $staff_id);
	$role = $user->roles;

	if (in_array('shop_staff', $role)) {
		$products = array_filter($products, function ($product) use ($staff_id) {
			return in_array($staff_id, $product->get_staff_ids());
		});
	}

	return $products;
}


function exclude_other_author_products($query)
{

	$current_user = wp_get_current_user();
	if (!in_array('shop_staff', $current_user->roles))
		return $query;
	if ($query->query['post_type'] == 'product' && $query->is_main_query()) {
		$query->set(
			'post__in',
			WC_Data_Store::load('product-appointment')->get_appointable_product_ids()
		);
	}
}

add_action('pre_get_posts', 'exclude_other_author_products');


// add_filter('woocommerce_product_addons_price_raw', 'set_addon_price', 10, 2);
// add_filter('woocommerce_product_addons_option_price_raw', 'set_addon_price', 10, 2);
function set_addon_price($price, $option)
{
	if ($option['price_type'] === 'quantity_based') {
		// get product price and set it as addon price
		$product_id = get_the_ID();
		$product = wc_get_product($product_id);
		$price = $product->get_price();
	}


	return $price;
}

// BEGIN ENQUEUE PARENT ACTION
// AUTO GENERATED - Do not modify or remove comment markers above or below:

if (!function_exists('chld_thm_cfg_locale_css')):
	function chld_thm_cfg_locale_css($uri)
	{
		if (empty($uri) && is_rtl() && file_exists(get_template_directory() . '/rtl.css'))
			$uri = get_template_directory_uri() . '/rtl.css';
		return $uri;
	}
endif;
add_filter('locale_stylesheet_uri', 'chld_thm_cfg_locale_css');

if (!function_exists('chld_thm_cfg_parent_css')):
	function chld_thm_cfg_parent_css()
	{
		wp_enqueue_style('chld_thm_cfg_parent', trailingslashit(get_template_directory_uri()) . 'style.css', array('bootstrap', 'woodmart-style', 'wd-wpcf7', 'wd-revolution-slider', 'wd-wpbakery-base', 'wd-wpbakery-base-deprecated', 'wd-woocommerce-base', 'wd-mod-star-rating', 'wd-woo-el-track-order', 'wd-woocommerce-block-notices', 'wd-header-base', 'wd-mod-tools', 'wd-header-search', 'wd-wd-search-results', 'wd-wd-search-form', 'wd-header-elements-base', 'wd-social-icons', 'wd-header-mobile-nav-dropdown', 'wd-section-title', 'wd-mod-highlighted-text', 'wd-widget-collapse', 'wd-footer-base', 'wd-swiper', 'wd-testimonial-old', 'wd-swiper-pagin', 'wd-bottom-toolbar'));
	}
endif;
add_action('wp_enqueue_scripts', 'chld_thm_cfg_parent_css', 10010);

// END ENQUEUE PARENT ACTION

function md_custom_woocommerce_checkout_fields($fields)
{
	$fields['order']['order_comments']['placeholder'] = 'Any notes or comments that your therapist needs to be aware of before the session?';

	return $fields;
}
add_filter('woocommerce_checkout_fields', 'md_custom_woocommerce_checkout_fields');

// edit my account tabs
add_filter('woocommerce_account_menu_items', 'wellness_edit_my_account_tabs', 20, 1);
function wellness_edit_my_account_tabs($items)
{
	// remove the orders tab
	unset($items['bookings']);
	// remove the downloads tab
	unset($items['downloads']);
	// remove the addresses tab
	unset($items['edit-address']);
	// remove the account details tab
	unset($items['edit-account']);

	return $items;
}

function wooc_validate_extra_register_fields($username, $email, $validation_errors)
{
	if (isset($_POST['billing_first_name']) && empty($_POST['billing_first_name'])) {
		$validation_errors->add('billing_first_name_error', __('First name is required!', 'woocommerce'));
	}
	if (isset($_POST['billing_last_name']) && empty($_POST['billing_last_name'])) {
		$validation_errors->add('billing_last_name_error', __('Last name is required!.', 'woocommerce'));
	}

	if (isset($_POST['password']) && empty($_POST['password'])) {
		$validation_errors->add('password_error', __('Password is required!', 'woocommerce'));
	}

	return $validation_errors;
}
add_action('woocommerce_register_post', 'wooc_validate_extra_register_fields', 10, 3);

function wooc_extra_register_fields()
{ ?>
	<p class="form-row form-row-first">
		<label for="reg_billing_first_name"><?php _e('First name', 'woocommerce'); ?><span class="required">*</span></label>
		<input type="text" class="input-text" name="billing_first_name" id="reg_billing_first_name" value="<?php if (! empty($_POST['billing_first_name'])) esc_attr_e($_POST['billing_first_name']); ?>" />
	</p>
	<p class="form-row form-row-last">
		<label for="reg_billing_last_name"><?php _e('Last name', 'woocommerce'); ?><span class="required">*</span></label>
		<input type="text" class="input-text" name="billing_last_name" id="reg_billing_last_name" value="<?php if (! empty($_POST['billing_last_name'])) esc_attr_e($_POST['billing_last_name']); ?>" />
	</p>
	<p class="form-row form-row-wide">
		<label for="reg_billing_phone"><?php _e('Phone', 'woocommerce'); ?></label>
		<input type="text" class="input-text" name="billing_phone" id="reg_billing_phone" value="<?php esc_attr_e($_POST['billing_phone']); ?>" />
	</p>
	<div class="clear"></div>
	<?php
}
add_action('woocommerce_register_form_start', 'wooc_extra_register_fields');

// redirect user to my account page after registration/login
add_action('woocommerce_registration_redirect', 'redirect_after_registration');
function redirect_after_registration($redirect)
{
	$redirect = wc_get_page_permalink('myaccount');
	return $redirect;
}

add_action('woocommerce_login_redirect', 'redirect_after_login');
function redirect_after_login($redirect)
{
	$redirect = wc_get_page_permalink('myaccount');
	return $redirect;
}

// show upcomming appointments on the top of the my account page
add_action('woocommerce_account_content', 'show_upcoming_appointments', 0);
function show_upcoming_appointments()
{

	// get current endpoint
	$current_endpoint = WC()->query->get_current_endpoint();
	// show only on main account page
	if (empty($current_endpoint) && $current_endpoint != 0) {
		$orderManager = new WC_Appointment_Order_Manager();
		$appointments = $orderManager->appointments_endpoint_content(0);
	}
}

// add filter to redirect cancel appointment to the cancellation policy page
// add_filter('woocommerce_appointments_cancel_appointment_redirect', 'redirect_cancel_appointment_to_cancellation_policy');
// function redirect_cancel_appointment_to_cancellation_policy($redirect_url)
// {
// 	// get the cancellation policy page
// 	$cancellation_policy_page = get_option('wc_appointment_cancellation_policy_page');
// 	if (!empty($cancellation_policy_page)) {
// 		$redirect_url = get_permalink($cancellation_policy_page);
// 	} else {
// 		$redirect_url = wc_get_page_permalink('myaccount');
// 	}
// 	return $redirect_url;
// }

// create a field for cancellation policy page in the customizer
add_action('customize_register', 'wellness_customize_register');
function wellness_customize_register($wp_customize)
{
	// Add a section for the cancellation policy page
	$wp_customize->add_section('wc_appointment_cancellation_policy_section', array(
		'title' => __('Cancellation Policy', 'woocommerce'),
		'priority' => 30,
	));

	// add a setting for cancellation policy allowed period
	$wp_customize->add_setting('wc_appointment_cancellation_policy_allowed_period', array(
		'default' => '',
	));

	// add a control for cancellation policy allowed period
	$wp_customize->add_control('wc_appointment_cancellation_policy_allowed_period', array(
		'label' => __('Cancellation Policy Allowed Period (in hours)', 'woocommerce'),
		'section' => 'wc_appointment_cancellation_policy_section',
		'type' => 'number',
		'settings' => 'wc_appointment_cancellation_policy_allowed_period',
	));

	// add a control for cancellation fee
	$wp_customize->add_setting('wc_appointment_cancellation_fee', array(
		'default' => '',
	));
	$wp_customize->add_control('wc_appointment_cancellation_fee', array(
		'label' => __('Cancellation Fee', 'woocommerce'),
		'section' => 'wc_appointment_cancellation_policy_section',
		'type' => 'number',
		'settings' => 'wc_appointment_cancellation_fee',
	));

	// add a control for cancellation fee in USD
	$wp_customize->add_setting('wc_appointment_cancellation_fee_usd', array(
		'default' => '',
	));

	$wp_customize->add_control('wc_appointment_cancellation_fee_usd', array(
		'label' => __('Cancellation Fee in USD', 'woocommerce'),
		'section' => 'wc_appointment_cancellation_policy_section',
		'type' => 'number',
		'settings' => 'wc_appointment_cancellation_fee_usd',
	));

	// ── Recurring Appointments section ─────────────────────────────────────
	$wp_customize->add_section('wellness_recurring_section', array(
		'title' => __('Recurring Appointments', 'woodmart-child'),
		'priority' => 35,
	));
	$wp_customize->add_setting('wellness_recurring_payment_reminder_days', array(
		'default' => 1,
	));
	$wp_customize->add_control('wellness_recurring_payment_reminder_days', array(
		'label' => __('Recurring payment reminder (days before)', 'woodmart-child'),
		'section' => 'wellness_recurring_section',
		'type' => 'number',
		'settings' => 'wellness_recurring_payment_reminder_days',
	));
}


// save the cancellation policy settings
add_action('customize_save_after', 'save_wc_appointment_cancellation_policy_settings');
function save_wc_appointment_cancellation_policy_settings()
{
	// save the cancellation policy allowed period
	if (isset($_POST['wc_appointment_cancellation_policy_allowed_period'])) {
		update_option('wc_appointment_cancellation_policy_allowed_period', sanitize_text_field($_POST['wc_appointment_cancellation_policy_allowed_period']));
	}

	// save the cancellation fee
	if (isset($_POST['wc_appointment_cancellation_fee'])) {
		update_option('wc_appointment_cancellation_fee', sanitize_text_field($_POST['wc_appointment_cancellation_fee']));
	}

	// save the cancellation fee in USD
	if (isset($_POST['wc_appointment_cancellation_fee_usd'])) {
		update_option('wc_appointment_cancellation_fee_usd', sanitize_text_field($_POST['wc_appointment_cancellation_fee_usd']));
	}
}

/**
 * Save the recurring appointments settings.
 */
add_action('customize_save_after', 'save_wellness_recurring_settings');
function save_wellness_recurring_settings()
{
	if (isset($_POST['wellness_recurring_payment_reminder_days'])) {
		update_option('wellness_recurring_payment_reminder_days', absint($_POST['wellness_recurring_payment_reminder_days']));
	}
}

// get cancellation fee depending on the user country
function get_wc_appointment_cancellation_fee($currency = null)
{
	$fee = get_theme_mod('wc_appointment_cancellation_fee', '');

	if (empty($fee)) {
		// if the fee is not set, return 0
		return 0;
	}

	// get the user country code
	if (!$currency) {
		// if the currency is not set, get the user country code
		$currency = get_woocommerce_currency();
	}

	if ($currency == 'EGP') {
		// if the currency is EGP, return the fee as is
		return $fee;
	}

	// get the cancellation fee in USD
	$cancellation_fee_usd = get_theme_mod('wc_appointment_cancellation_fee_usd', '');
	if (!empty($cancellation_fee_usd)) {
		return $cancellation_fee_usd;
	}

	return $fee;
}

function get_wc_appointment_cancellation_policy_allowed_period()
{
	// get the cancellation policy allowed period
	$allowed_period = get_theme_mod('wc_appointment_cancellation_policy_allowed_period', '');
	if (empty($allowed_period)) {
		// if the allowed period is not set, return 0
		return 0;
	}

	return $allowed_period;
}

// add action on appointment cancellation to check if the cancellation is within the allowed period
// if not, add a fee to the order
add_action('woocommerce_appointments_cancelled_appointment', 'check_appointment_cancellation_policy', 10, 1);
function check_appointment_cancellation_policy($appointment_id)
{
	// get the appointment
	$appointment = get_wc_appointment($appointment_id);
	if (!$appointment) {
		return;
	}

	// get the cancellation policy allowed period
	$allowed_period = get_wc_appointment_cancellation_policy_allowed_period();
	if ($allowed_period <= 0) {
		return;
	}

	// get the cancellation fee
	$cancellation_fee = get_wc_appointment_cancellation_fee();
	if ($cancellation_fee <= 0) {
		return; // no cancellation fee set, do nothing
	}

	// error_log('Checking appointment cancellation policy for appointment ID: ' . $appointment_id . ' with allowed period: ' . $allowed_period . ' hours and cancellation fee: ' . $cancellation_fee);

	// if the cancellation is not within the allowed period, add a fee to the order
	$order_id = $appointment->get_order_id();
	if (!$order_id) {
		return; // no order associated with this appointment
	}

	$order = wc_get_order($order_id);
	if (!$order) {
		return; // no order found
	}

	// check whether the cancellation is within the allowed period
	$cancellation_time = current_time('timestamp');
	$order_time = $order->get_date_created()->getTimestamp();

	// if the cancellation is within the allowed period, do nothing
	if (($cancellation_time - $order_time) < ($allowed_period * HOUR_IN_SECONDS)) {
		return; // cancellation is within the allowed period, do nothing
	}

	// // error_log('Appointment cancellation is outside the allowed period, adding cancellation fee to order ID: ' . $order_id);


	// if the cancellation is not within the allowed period, add a fee to the order, regardless of the order payment status

	// if the order is cancelled, we can add the cancellation fee
	// get the order fees if any
	$fees = $order->get_fees();
	if (!empty($fees)) {
		// if there are fees, check if the cancellation fee is already added
		foreach ($fees as $fee) {
			if ($fee->get_name() == __('Cancellation Fee', 'woocommerce')) {
				return; // cancellation fee is already added, do nothing
			}
		}
	}

	// create a new fee for the order
	// get the order object

	$currency = $order->get_currency();
	$fee_amount = get_wc_appointment_cancellation_fee($currency);

	$fee = new WC_Order_Item_Fee();
	$fee->set_name(__('Cancellation Fee', 'woocommerce'));
	$fee->set_amount($fee_amount);
	$fee->set_total($fee_amount);
	$fee->set_tax_class('');
	$fee->set_tax_status('none'); // no tax for cancellation fee
	$fee->set_order_id($order_id);

	// add the fee to the order
	$order->add_item($fee);

	// recalculate the order totals
	$order->calculate_totals();

	// error_log('appointment status' . $appointment->get_status() . ' for appointment ID: ' . $appointment_id . ' with order ID: ' . $order_id . ' - cancellation fee of ' . get_wc_appointment_cancellation_fee() . ' added to the order.');

	// refund the cancelled appointment amount if it was paid
	// error_log('Creating refund for appointment ID: ' . $appointment_id . ' for order ID: ' . $order_id);

	// get the appointment amount from the order items
	$order_items = $order->get_items();

	// error_log('Order ID: ' . $order_id . ' has ' . count($order_items) . ' items.');
	foreach ($order_items as $item) {
		// error_log('Checking item with ID: ' . $item->get_id() . ' and meta ' . var_export($item->get_meta('_appointment_id'), true));
		if ($item->get_meta('_appointment_id')[0] == $appointment_id) {
			$appointment_amount = $item->get_total();
			// error_log('Found appointment amount: ' . $appointment_amount . ' for appointment ID: ' . $appointment_id);
			break;
		}
	}

	if (isset($appointment_amount)) {
		// check if the order has already been refunded
		$refunds = $order->get_refunds();
		// error_log('Order ID: ' . $order_id . ' has ' . count($refunds) . ' refunds.');
		$already_refunded = false;
		foreach ($refunds as $refund) {
			if ($refund->get_amount() >= $appointment_amount) {
				$already_refunded = true;
				break;
			}
		}
		if ($already_refunded) {
			// error_log('Appointment ID: ' . $appointment_id . ' has already been refunded, skipping refund creation.');
			return; // already refunded, do nothing
		}

		// create a refund for the appointment amount
		// create a manual refund for the appointment amount
		// error_log('Creating refund for appointment ID: ' . $appointment_id . ' with amount: ' . $appointment_amount . ' for order ID: ' . $order_id);

		$refund = wc_create_refund(array(
			'amount' => $appointment_amount,
			'reason' => __('Appointment cancelled', 'woocommerce'),
			'order_id' => $order_id,
			'line_items' => array(),
			'refund_payment' => true, // refund the payment
		));

		if (is_wp_error($refund)) {
			// error_log('Error creating refund for appointment ID: ' . $appointment_id . ' - ' . $refund->get_error_message());
		} else {
			// error_log('Refund created for appointment ID: ' . $appointment_id . ' with amount: ' . $appointment_amount);
		}
	}

	// send email as an order customer note
	$note = sprintf(
		__('Your appointment with ID %d has been cancelled. A cancellation fee of %s has been added to your order. Please contact us if you have any questions.', 'woocommerce'),
		$appointment_id,
		wc_price(get_wc_appointment_cancellation_fee(), array('currency' => $order->get_currency()))
	);

	$order->add_order_note($note, 1); // 1 means this is a customer note
}

/**
 * Add column to the users table to show orders count and sort by it
 */
add_filter('manage_users_columns', function ($columns) {
	$columns['orders_count'] = __('Orders Count', 'your-text-domain');
	$columns['created_at'] = __('Created At', 'your-text-domain');
	return $columns;
});

add_action('manage_users_custom_column', function ($value, $column_name, $user_id) {
	if ($column_name === 'orders_count') {
		$value = count_orders_by_user($user_id);
	}
	if ($column_name === 'created_at') {
		$user = get_userdata($user_id);
		$value = date('Y-m-d', strtotime($user->user_registered));
	}
	return $value;
}, 10, 3);

add_filter('manage_users_sortable_columns', function ($sortable_columns) {
	$sortable_columns['orders_count'] = 'orders_count';
	$sortable_columns['created_at'] = 'created_at';
	return $sortable_columns;
});

add_action('pre_get_users', function ($query) {
	if (!is_admin() || !$query->is_main_query()) {
		return;
	}

	$orderby = $query->get('orderby');
	if ($orderby === 'orders_count') {
		$query->set('meta_key', '_order_count');
		$query->set('orderby', 'meta_value_num');
	}
	if ($orderby === 'created_at') {
		$query->set('orderby', 'user_registered');
	}
});

function count_orders_by_user($user_id)
{
	$args = array(
		'customer_id' => $user_id,
		'return' => 'ids',
		'limit' => -1, // No limit
	);

	$orders = wc_get_orders($args);
	return count($orders);
}

/**
 * Change coupon location
 * **/
// Hide original coupon with CSS and add custom placeholder
add_action('wp_head', 'hide_original_coupon_form');
function hide_original_coupon_form()
{
	if (is_checkout()) {
	?>
		<style>
			.woocommerce-form-coupon-toggle,
			.checkout_coupon.woocommerce-form-coupon {
				display: none !important;
			}
		</style>
	<?php
	}
}

// Add custom coupon placeholder above payment
add_action('woocommerce_review_order_before_payment', 'custom_coupon_placeholder');
function custom_coupon_placeholder()
{
	?>
	<div class="custom-coupon-wrapper" style="margin-bottom: 20px;">
		<p class="form-row form-row-wide">
			<label for="custom_coupon_code">Have a coupon code?</label>
			<input type="text" name="custom_coupon_code" id="custom_coupon_code" class="input-text" placeholder="Coupon code" />
		</p>
		<button type="button" class="button" id="apply_custom_coupon">Apply Coupon</button>
	</div>
	<script type="text/javascript">
		jQuery(function($) {
			$('#apply_custom_coupon').on('click', function(e) {
				e.preventDefault();
				var couponCode = $('#custom_coupon_code').val().trim();

				if (!couponCode) {
					alert('Please enter a coupon code');
					return false;
				}

				var $button = $(this);
				$button.prop('disabled', true).text('Applying...');

				$.ajax({
					type: 'POST',
					url: wc_checkout_params.wc_ajax_url.toString().replace('%%endpoint%%', 'apply_coupon'),
					data: {
						security: wc_checkout_params.apply_coupon_nonce,
						coupon_code: couponCode
					},
					success: function(response) {
						$('.woocommerce-error, .woocommerce-message, .woocommerce-info').remove();
						if (response) {
							$('form.checkout').before(response);
						}
						$(document.body).trigger('update_checkout');
						$button.prop('disabled', false).text('Apply Coupon');
						$('#custom_coupon_code').val('');
					},
					error: function() {
						$button.prop('disabled', false).text('Apply Coupon');
					}
				});

				return false;
			});

			// Allow Enter key to apply coupon
			$('#custom_coupon_code').on('keypress', function(e) {
				if (e.which === 13) {
					e.preventDefault();
					$('#apply_custom_coupon').click();
				}
			});
		});
	</script>
<?php
}
// Change coupon text
add_filter('woocommerce_checkout_coupon_message', 'custom_coupon_message');
function custom_coupon_message()
{
	return 'If you have a discount code, you can enter it here.';
}

// Add custom checkbox after privacy policy
add_action('woocommerce_checkout_after_terms_and_conditions', 'custom_cancellation_checkbox', 20);
function custom_cancellation_checkbox()
{
?>
	<p class="form-row custom-cancellation-policy">
		<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
			<input type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" name="cancellation_policy" id="cancellation_policy" />
			<span class="woocommerce-terms-and-conditions-checkbox-text">
				I understand and agree to the Cancellation &amp; Refund Policy, and confirm that this service is not intended for emergencies.
			</span>
		</label>
	</p>
	<?php
}
// Validate the checkbox
add_action('woocommerce_checkout_process', 'validate_cancellation_checkbox');
function validate_cancellation_checkbox()
{
	if (! isset($_POST['cancellation_policy']) || $_POST['cancellation_policy'] !== 'on') {
		wc_add_notice(__('You must agree to the Cancellation & Refund Policy.'), 'error');
	}
}

// Change Billing Details text
add_filter('woocommerce_checkout_fields', 'change_billing_details_label');
function change_billing_details_label($fields)
{
	// This changes the section title
	add_filter('gettext', 'custom_billing_details_text', 20, 3);
	return $fields;
}

function custom_billing_details_text($translated_text, $text, $domain)
{
	if ($domain === 'woocommerce' && $text === 'Billing details') {
		$translated_text = 'Payment and Contact Details';
	}
	return $translated_text;
}




// ═══════════════════════════════════════════════════════════════════════════════
// Admin Appointment Cancelled Email (sent to therapist/staff)
// Template override: woocommerce/emails/admin-appointment-cancelled.php
// ═══════════════════════════════════════════════════════════════════════════════

/**
 * Override the subject line of the admin "Appointment Cancelled" email.
 * Appends the appointment number via the {appointment_number} placeholder
 * which is already resolved by the email class before sending.
 */
add_filter('woocommerce_email_subject_admin_appointment_cancelled', 'wellness_admin_cancelled_subject', 10, 2);
function wellness_admin_cancelled_subject($subject, $email)
{
	$id = isset($email->object) ? $email->object->get_id() : '';
	return sprintf(
		/* translators: %s: appointment ID */
		__('A Session has been cancelled.', 'woocommerce-appointments'),
		$id
	);
}

/**
 * Override the subject line of the customer-facing "Appointment Cancelled" email.
 */
add_filter('woocommerce_email_subject_appointment_cancelled', 'wellness_customer_cancelled_subject', 10, 2);
function wellness_customer_cancelled_subject($subject, $email)
{
	return __('Your therapy session has been cancelled.', 'woocommerce-appointments');
}

/**
 * Override the subject line of the admin "Appointment Rescheduled" email (sent to therapist).
 */
add_filter('woocommerce_email_subject_admin_appointment_rescheduled', 'wellness_admin_rescheduled_subject', 10, 2);
function wellness_admin_rescheduled_subject($subject, $email)
{
	$id = isset($email->object) ? $email->object->get_id() : '';
	return sprintf(
		/* translators: %s: appointment ID */
		__('A session has been rescheduled.', 'woocommerce-appointments'),
		$id
	);
}

/**
 * Override the subject line of the admin "New Appointment" email (sent to therapist).
 */
add_filter('woocommerce_email_subject_admin_new_appointment', 'wellness_admin_new_appointment_subject', 10, 2);
function wellness_admin_new_appointment_subject($subject, $email)
{
	$id = isset($email->object) ? $email->object->get_id() : '';
	return sprintf(
		/* translators: %s: appointment ID */
		__('You have a new session booked.', 'woocommerce-appointments'),
		$id
	);
}

/**
 * Send a custom rescheduled confirmation email to the customer.
 * The plugin only has an admin-facing rescheduled email, so we build and send
 * the customer version ourselves using the same WC mailer infrastructure.
 *
 * @param int    $appointment_id
 * @param string $prev_start_date  Formatted previous start date string.
 * @param string $prev_end_date    Formatted previous end date string.
 */
add_action('woocommerce_appointments_rescheduled_appointment', 'wellness_send_customer_rescheduled_email', 20, 3);
function wellness_send_customer_rescheduled_email($appointment_id, $prev_start_date, $prev_end_date)
{
	$appointment = get_wc_appointment($appointment_id);
	if (! $appointment) {
		return;
	}

	// ── Recipient ─────────────────────────────────────────────────────────────
	$wc_order            = $appointment->get_order();
	$customer_first_name = '';
	$recipient           = '';

	if ($wc_order) {
		$recipient           = $wc_order->get_billing_email();
		$customer_first_name = $wc_order->get_billing_first_name();
	} else {
		$customer = $appointment->get_customer();
		if ($customer) {
			$recipient           = $customer->email;
			$parts               = explode(' ', $customer->full_name);
			$customer_first_name = $parts[0];
		}
	}

	if (empty($customer_first_name) && $wc_order) {
		$_full = $wc_order->get_meta('billing_full_name', true);
		if ($_full) {
			$_parts              = explode(' ', trim($_full));
			$customer_first_name = $_parts[0];
		}
	}

	if (empty($recipient)) {
		return;
	}

	// ── Build email via WC mailer ─────────────────────────────────────────────
	$mailer  = WC()->mailer();
	$subject = __('Your therapy session has been rescheduled.', 'woocommerce-appointments');

	// Capture the template output
	ob_start();
	// Pass a minimal $email object so header/footer hooks receive something sensible
	$email_obj       = new stdClass();
	$email_obj->id   = 'customer_appointment_rescheduled';

	wc_get_template(
		'emails/customer-appointment-rescheduled.php',
		[
			'appointment'         => $appointment,
			'prev_start_date'     => $prev_start_date,
			'prev_end_date'       => $prev_end_date,
			'customer_first_name' => $customer_first_name,
			'email_heading'       => __('Session Rescheduled', 'woocommerce-appointments'),
			'sent_to_admin'       => false,
			'plain_text'          => false,
			'email'               => $email_obj,
		],
		'',
		get_stylesheet_directory() . '/woocommerce/'
	);
	$message = ob_get_clean();

	// Wrap in WC email styles
	$message = $mailer->wrap_message($subject, $message);

	$mailer->send($recipient, $subject, $message, $mailer->get_headers(), []);
}



// ═══════════════════════════════════════════════════════════════════════════════
// Appointment booking flow — auto-confirm on payment
// ═══════════════════════════════════════════════════════════════════════════════

/**
 * Confirm appointments once the order is paid, and survive WC Appointments'
 * publish_appointments() reverting confirmed → paid.
 *
 * Loop source (zero-total / fully discounted orders):
 *   WC_Appointment::save() always calls mark_confirmed_order_complete_when_total_zero()
 *   which, for zero-total orders, calls $order->update_status('completed').
 *   That fires woocommerce_order_status_completed → publish_appointments() →
 *   $appointment->paid() (accepts 'confirmed' as source) → fires
 *   woocommerce_appointment_confirmed_to_paid → our hook re-confirms → save →
 *   mark_confirmed fires again → infinite loop.
 *
 * Fix:
 *  1. Static guard: only run the confirm once per appointment per request.
 *  2. While our update_status('confirmed') is in progress, neutralize
 *     the zero-order auto-complete via woocommerce_appointments_zero_order_status
 *     (return current order status so update_status is a no-op).
 *  3. Also temporarily detach publish_appointments from order_status_{processing,completed}
 *     so any stray order status transition during our save cannot re-revert us.
 */
add_action('woocommerce_appointment_unpaid_to_paid',    'wellness_confirm_appointment_on_payment', 9999);
add_action('woocommerce_appointment_confirmed_to_paid', 'wellness_confirm_appointment_on_payment', 9999);
function wellness_confirm_appointment_on_payment($appointment_id)
{
	static $done = array();
	if (isset($done[$appointment_id])) {
		return;
	}
	$done[$appointment_id] = true;

	$appointment = get_wc_appointment($appointment_id);
	if (! $appointment) {
		return;
	}

	// (2) Neutralize the zero-total auto-complete during our save.
	$zero_filter = function ($status, $order) {
		return $order->get_status();
	};
	add_filter('woocommerce_appointments_zero_order_status', $zero_filter, 9999, 2);

	// (3) Detach publish_appointments() permanently for the rest of this request.
	//     It has already done its job (bumped us unpaid → paid, which triggered
	//     this hook). Any subsequent order status transition in this request
	//     (e.g. pending → completed firing later) would only call paid() again
	//     and revert our 'confirmed' status. We don't need it anymore.
	$order_manager = isset($GLOBALS['wc_appointment_order_manager']) ? $GLOBALS['wc_appointment_order_manager'] : null;
	if ($order_manager) {
		remove_action('woocommerce_order_status_processing', array($order_manager, 'publish_appointments'), 20);
		remove_action('woocommerce_order_status_completed',  array($order_manager, 'publish_appointments'), 20);
	}

	$appointment->update_status('confirmed');

	remove_filter('woocommerce_appointments_zero_order_status', $zero_filter, 9999);
}

/**
 * Suppress the generic WooCommerce "New Order" admin email for orders that
 * contain an appointment product.
 *
 * The admin_new_appointment email already notifies the therapist/staff,
 * so the default new order email creates a confusing duplicate.
 */
// Admin "New Order" email
add_filter('woocommerce_email_enabled_new_order', 'wellness_disable_new_order_email_for_appointments', 10, 2);
// Customer "Your order has been received" (processing) email
add_filter('woocommerce_email_enabled_customer_processing_order', 'wellness_disable_new_order_email_for_appointments', 10, 2);
function wellness_disable_new_order_email_for_appointments($enabled, $order)
{
	if (! $order instanceof WC_Order) {
		return $enabled;
	}
	foreach ($order->get_items() as $item) {
		$product_id = $item->get_product_id();
		if ($product_id && 'wc_appointment' === get_post_type($product_id)) {
			return false;
		}
		// Also check if order has appointment meta (set by WC Appointments)
		if ($item->get_meta('_appointment_id')) {
			return false;
		}
	}
	// Fallback: check via WC Appointments helper if available
	if (function_exists('wc_appointments_get_appointment_from_order_item')) {
		foreach ($order->get_items() as $item) {
			if (wc_appointments_get_appointment_from_order_item($item->get_id())) {
				return false;
			}
		}
	}
	return $enabled;
}

// Hide billing and shipping details on the thank-you (order received) page
add_action('wp_head', 'wellness_hide_thankyou_address_details');
function wellness_hide_thankyou_address_details()
{
	if (is_order_received_page()) {
	?>
		<style>
			.woocommerce-order .woocommerce-customer-details,
			.woocommerce-order .woocommerce-columns--addresses {
				display: none !important;
			}
		</style>
	<?php
	}
}

// Replace default WooCommerce email address blocks with a simple customer info section
add_action('woocommerce_email_customer_details', 'wellness_remove_email_addresses', 1);
function wellness_remove_email_addresses()
{
	if (! function_exists('WC') || ! WC()->mailer()) {
		return;
	}
	$mailer = WC()->mailer();
	remove_action('woocommerce_email_customer_details', array($mailer, 'customer_details'), 10);
	remove_action('woocommerce_email_customer_details', array($mailer, 'email_addresses'), 20);
}


// Show customer full name, email, and phone in WooCommerce emails
add_action('woocommerce_email_customer_details', 'wellness_show_customer_info', 10, 4);
function wellness_show_customer_info($order, $sent_to_admin, $plain_text, $email)
{
	$full_name     = $order->get_meta('billing_full_name');
	if (empty($full_name)) {
		$full_name = trim($order->get_billing_first_name() . ' ' . $order->get_billing_last_name());
	}
	$email_address = $order->get_billing_email();
	$phone         = $order->get_billing_phone();

	if ($plain_text) {
		echo "\n" . esc_html__('Customer Details', 'woocommerce') . "\n\n";
		if ($full_name)     echo esc_html__('Name', 'woocommerce') . ': ' . esc_html($full_name) . "\n";
		if ($email_address) echo esc_html__('Email', 'woocommerce') . ': ' . esc_html($email_address) . "\n";
		if ($phone)         echo esc_html__('Phone', 'woocommerce') . ': ' . esc_html($phone) . "\n";
	} else {
	?>
		<h2><?php esc_html_e('Customer Details', 'woocommerce'); ?></h2>
		<table cellspacing="0" cellpadding="0" style="width:100%;margin-bottom:20px;">
			<?php if ($full_name) : ?>
				<tr>
					<th style="text-align:left;padding:4px 0;width:120px;"><?php esc_html_e('Name', 'woocommerce'); ?></th>
					<td><?php echo esc_html($full_name); ?></td>
				</tr>
			<?php endif; ?>
			<?php if ($email_address) : ?>
				<tr>
					<th style="text-align:left;padding:4px 0;"><?php esc_html_e('Email', 'woocommerce'); ?></th>
					<td><?php echo esc_html($email_address); ?></td>
				</tr>
			<?php endif; ?>
			<?php if ($phone) : ?>
				<tr>
					<th style="text-align:left;padding:4px 0;"><?php esc_html_e('Phone', 'woocommerce'); ?></th>
					<td><?php echo esc_html($phone); ?></td>
				</tr>
			<?php endif; ?>
		</table>
	<?php
	}
}


// Remove "Shipping" row from order totals on thank you page and in emails
add_filter('woocommerce_get_order_item_totals', 'wellness_remove_shipping_from_order_totals', 999, 3);
function wellness_remove_shipping_from_order_totals($total_rows, $order, $tax_display)
{
	unset($total_rows['shipping']);
	return $total_rows;
}

// Hide shipping address form and shipping methods on the checkout page
add_filter('woocommerce_cart_needs_shipping_address', '__return_false', 999);
add_filter('woocommerce_cart_needs_shipping', '__return_false', 999);

// Hide shipping-related UI on the checkout page via CSS
add_action('wp_head', 'wellness_hide_checkout_shipping_ui');
function wellness_hide_checkout_shipping_ui()
{
	if (is_checkout()) {
	?>
		<style>
			.woocommerce-shipping-fields,
			.woocommerce-shipping-totals,
			.shipping,
			#ship-to-different-address {
				display: none !important;
			}
		</style>
	<?php
	}
}

// Change "Thank you. Your order has been received." to appointment wording on the thank you page
add_filter('woocommerce_thankyou_order_received_text', 'wellness_thankyou_order_received_text', 10, 2);
function wellness_thankyou_order_received_text($message, $order)
{
	return esc_html__('Thank you. Your appointment has been received.', 'woocommerce');
}

// Override woodmart_order_overview to replace "Order number:" with "Appointment number:"
if (! function_exists('woodmart_order_overview')) {
	function woodmart_order_overview($order)
	{
		if (! $order || ! is_a($order, 'WC_Order')) {
			return;
		}
	?>
		<?php
		// Retrieve the real appointment ID(s) linked to this order.
		$appointment_ids = class_exists('WC_Appointment_Data_Store')
			? WC_Appointment_Data_Store::get_appointment_ids_from_order_id($order->get_id())
			: array();
		$appointment_display = ! empty($appointment_ids)
			? implode(', ', array_map('absint', $appointment_ids))
			: $order->get_order_number();
		?>
		<ul class="woocommerce-order-overview woocommerce-thankyou-order-details order_details">
			<li class="woocommerce-order-overview__order order">
				<span><?php esc_html_e('Appointment number:', 'woocommerce'); ?></span>
				<strong><?php echo esc_html($appointment_display); ?></strong>
			</li>

			<li class="woocommerce-order-overview__date date">
				<span><?php esc_html_e('Date:', 'woocommerce'); ?></span>
				<strong><?php echo wc_format_datetime($order->get_date_created()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
						?></strong>
			</li>

			<?php if (is_user_logged_in() && $order->get_user_id() === get_current_user_id() && $order->get_billing_email()) : ?>
				<li class="woocommerce-order-overview__email email">
					<span><?php esc_html_e('Email:', 'woocommerce'); ?></span>
					<strong><?php echo $order->get_billing_email(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
							?></strong>
				</li>
			<?php endif; ?>

			<li class="woocommerce-order-overview__total total">
				<span><?php esc_html_e('Total:', 'woocommerce'); ?></span>
				<strong><?php echo $order->get_formatted_order_total(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
						?></strong>
			</li>

			<?php if ($order->get_payment_method_title()) : ?>
				<li class="woocommerce-order-overview__payment-method method">
					<span><?php esc_html_e('Payment method:', 'woocommerce'); ?></span>
					<strong><?php echo wp_kses_post($order->get_payment_method_title()); ?></strong>
				</li>
			<?php endif; ?>
		</ul>
	<?php
	}
}

// Change "Returning customer?" to "Returning client?" on the checkout login toggle
add_filter('woocommerce_checkout_login_message', function () {
	return esc_html__('Returning client?', 'woocommerce');
});

/**
 * Inject a "Number" addon field on all appointable products so customers can
 * specify how many times they want to repeat the appointment at booking time.
 * Skipped if an addon named "Number" already exists (manually configured).
 *
 * DEPRECATED (2026-08-30): Recurring is now handled by the custom child-theme
 * "Repeat Appointment" fields. This addon injected 'Number' on every
 * appointable product, so it appeared in order item meta even when the customer
 * did not choose to repeat. Commented out pending removal.
 */
// add_filter('woocommerce_product_addons_get_items', 'wellness_inject_repeat_number_addon', 10, 2);
// function wellness_inject_repeat_number_addon($addons, $product_id)
// {
// 	// Only inject if this product has repeat configured (has _wc_appointment_max_repeat_count or is appointable).
// 	$post_type = get_post_type($product_id);
// 	if (!in_array($post_type, ['product', 'wc_appointment'], true)) {
// 		return $addons;
// 	}

// 	// Don't duplicate if a Number field already exists.
// 	foreach ($addons as $addon) {
// 		if (isset($addon['name']) && strtolower(trim($addon['name'])) === 'number') {
// 			return $addons;
// 		}
// 	}

// 	$saved_max   = get_post_meta($product_id, '_wc_appointment_max_repeat_count', true);
// 	$max_allowed = ($saved_max !== '') ? max(1, intval($saved_max)) : 2;

// 	$addons[] = array(
// 		'name'              => 'Number',
// 		'title_format'      => 'label',
// 		'description'       => 'How many times would you like to repeat this appointment? (max: ' . $max_allowed . ')',
// 		'type'              => 'custom_text',
// 		'display'           => '',
// 		'position'          => count($addons),
// 		'required'          => 0,
// 		'restrictions'      => 1,
// 		'restrictions_type' => 'any_integer',
// 		'adjust_price'      => 0,
// 		'price_type'        => 'flat_fee',
// 		'price'             => '',
// 		'min'               => 1,
// 		'max'               => $max_allowed,
// 		'options'           => array(),
// 	);

// 	return $addons;
// }


/**
 * Add an "Availability Rules" column to the admin product list table.
 */
function wca_add_admin_avail_rules_column($columns)
{
	$new_columns = [];
	foreach ($columns as $key => $label) {
		$new_columns[$key] = $label;
		if ('name' === $key) {
			$new_columns['wca_staff']      = __('Staff', 'woocommerce-appointments');
			$new_columns['wca_avail_rules'] = __('Availability', 'woocommerce-appointments');
		}
	}
	return $new_columns;
}
add_filter('manage_product_posts_columns', 'wca_add_admin_avail_rules_column', 20);

/**
 * Render the "Staff" column cell.
 */
function wca_render_admin_staff_column($column, $post_id)
{
	if ('wca_staff' !== $column) {
		return;
	}

	$product = wc_get_product($post_id);

	if (!is_wc_appointment_product($product) || !$product->has_staff()) {
		echo '<span aria-hidden="true">—</span>';
		return;
	}

	$names = [];
	foreach ($product->get_staff_ids() as $staff_id) {
		$staff   = new WC_Product_Appointment_Staff($staff_id);
		$names[] = esc_html($staff->get_display_name() ?: sprintf(__('Staff #%d', 'woocommerce-appointments'), $staff_id));
	}

	echo implode('<br>', $names);
}
add_action('manage_product_posts_custom_column', 'wca_render_admin_staff_column', 10, 2);

/**
 * Render the "Availability Rules" column cell.
 *
 * Shows each availability rule defined on the product and on each of its
 * assigned staff members. For every rule the output includes:
 *   - source badge: "Product" or the staff member's display name
 *   - whether the slot is open or blocked (appointable yes/no)
 *   - range type + human-readable from/to values
 */
function wca_render_admin_avail_rules_column($column, $post_id)
{
	if ('wca_avail_rules' !== $column) {
		return;
	}

	$product = wc_get_product($post_id);

	if (! is_wc_appointment_product($product)) {
		echo '<span aria-hidden="true">—</span>';
		return;
	}

	// Collect rules: [ ['source' => string, 'rule' => WC_Appointments_Availability], ... ]
	$entries = [];

	// Product-level rules.
	foreach ($product->get_availability() as $rule) {
		$entries[] = ['source' => __('Product', 'woocommerce-appointments'), 'rule' => $rule];
	}

	// Staff-level rules.
	if ($product->has_staff()) {
		foreach ($product->get_staff_ids() as $staff_id) {
			$staff = new WC_Product_Appointment_Staff($staff_id);
			$name  = $staff->get_display_name() ?: sprintf(__('Staff #%d', 'woocommerce-appointments'), $staff_id);
			foreach ($staff->get_availability() as $rule) {
				$entries[] = ['source' => $name, 'rule' => $rule];
			}
		}
	}

	if (empty($entries)) {
		echo '<span style="color:#999;font-size:11px;">' . esc_html__('No rules set', 'woocommerce-appointments') . '</span>';
		return;
	}

	echo '<div class="wca-rules-list">';
	foreach ($entries as $entry) {
		$rule        = $entry['rule'];  // plain array from get_all_as_array()
		$source      = $entry['source'];
		$appointable = 'yes' === ($rule['appointable'] ?? '');
		$range_type  = $rule['range_type'] ?? '';

		// Build human-readable range string.
		switch ($range_type) {
			case 'custom':
				$from  = $rule['from_date'] ?? '';
				$to    = $rule['to_date'] ?? '';
				$range = $from && $to ? esc_html($from) . ' &rarr; ' . esc_html($to) : esc_html($from ?: $to);
				break;
			case 'months':
				$range = wca_month_label($rule['from_range'] ?? '') . ' &rarr; ' . wca_month_label($rule['to_range'] ?? '');
				break;
			case 'weeks':
				$range = sprintf(__('Week %s &rarr; %s', 'woocommerce-appointments'), esc_html($rule['from_range'] ?? ''), esc_html($rule['to_range'] ?? ''));
				break;
			case 'days':
				$range = wca_day_label($rule['from_range'] ?? '') . ' &rarr; ' . wca_day_label($rule['to_range'] ?? '');
				break;
			case 'time':
			case 'time:range':
				$range = esc_html($rule['from_range'] ?? '') . ' &rarr; ' . esc_html($rule['to_range'] ?? '');
				break;
			default:
				$range = esc_html($range_type);
				break;
		}

		$status_class = $appointable ? 'wca-rule--open' : 'wca-rule--blocked';
		$status_label = $appointable ? '&#10003;' : '&#10007;';

		printf(
			'<div class="wca-rule %s"><span class="wca-rule-source">%s</span> <span class="wca-rule-status">%s</span> <span class="wca-rule-range">%s</span></div>',
			esc_attr($status_class),
			esc_html($source),
			$status_label,
			$range
		);
	}
	echo '</div>';
}
add_action('manage_product_posts_custom_column', 'wca_render_admin_avail_rules_column', 10, 2);

/** Return a short month name for a 1-based month number. */
function wca_month_label($n)
{
	$labels = [1 => 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
	return isset($labels[(int) $n]) ? esc_html($labels[(int) $n]) : esc_html($n);
}

/** Return a short day name for a 1-based ISO weekday number (1=Mon … 7=Sun). */
function wca_day_label($n)
{
	$labels = [1 => 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
	return isset($labels[(int) $n]) ? esc_html($labels[(int) $n]) : esc_html($n);
}

/**
 * Admin CSS for the Availability Rules column.
 */
add_action('admin_head', function () {
	$screen = get_current_screen();
	if (! $screen || 'edit-product' !== $screen->id) {
		return;
	}
	?>
	<style>
		.column-wca_staff {
			width: 140px;
			font-size: 12px;
			line-height: 1.6;
		}

		.column-wca_avail_rules {
			width: 200px;
		}

		.wca-rules-list {
			display: flex;
			flex-direction: column;
			gap: 3px;
		}

		.wca-rule {
			display: flex;
			gap: 4px;
			align-items: baseline;
			font-size: 11px;
			line-height: 1.4;
			padding: 1px 4px;
			border-radius: 3px;
		}

		.wca-rule--open {
			background: #f0faf0;
		}

		.wca-rule--blocked {
			background: #fff0f0;
		}

		.wca-rule-source {
			font-weight: 600;
			color: #555;
			white-space: nowrap;
		}

		.wca-rule-status {
			font-weight: 700;
		}

		.wca-rule--open .wca-rule-status {
			color: #2e7d32;
		}

		.wca-rule--blocked .wca-rule-status {
			color: #c62828;
		}

		.wca-rule-range {
			color: #333;
		}
	</style>
<?php
});


// ═══════════════════════════════════════════════════════════════════════════════
// Phase 1 — Timezone-aware appointment time formatting
// Used by all 6 child-theme email templates so the logic lives in one place.
// ═══════════════════════════════════════════════════════════════════════════════

/**
 * Format a raw appointment timestamp in a given timezone and append its label.
 *
 * For same-timezone output uses date_i18n() (WordPress i18n-aware).
 * For cross-timezone conversion delegates to wc_appointment_timezone_locale()
 * which is the same function the plugin's own get_start_date() uses.
 *
 * @param int    $timestamp   Raw value from $appointment->get_start('timestamp').
 * @param string $tz_string   IANA timezone string, e.g. 'America/New_York'.
 * @param string $format      PHP date() format string.
 * @return string             e.g. "4:00 PM (Cairo)" or "9:00 AM (New York)"
 */
function wellness_tz_format($timestamp, $tz_string, $format = 'g:i A')
{
	if (! $timestamp) {
		return '';
	}
	$site_tz = wc_timezone_string();
	$tz      = ($tz_string !== '' && $tz_string !== null) ? $tz_string : $site_tz;
	$label   = wc_appointment_get_timezone_name($tz);

	// wc_appointment_get_timezone_name() returns '' for Etc/GMT±N offsets,
	// raw ±HH:MM offsets (what wc_timezone_string() returns when WP timezone
	// is set to UTC+N rather than a city), and plain 'UTC'.
	// Build a readable fallback so we never render an empty label like "10 AM ()".
	if (! $label) {
		if (preg_match('/^Etc\/GMT([+-])(\d+)$/', $tz, $m)) {
			// POSIX Etc/GMT sign is inverted vs UTC convention: Etc/GMT-2 = UTC+2.
			$label = 'UTC' . ($m[1] === '+' ? '-' : '+') . $m[2];
		} elseif (preg_match('/^([+-])(\d{1,2}):(\d{2})$/', $tz, $m)) {
			// Raw offset from wc_timezone_string(): '+03:00', '-05:30', etc.
			$h     = (int) $m[2];
			$min   = (int) $m[3];
			$label = 'UTC' . $m[1] . $h . ($min > 0 ? ':' . str_pad($min, 2, '0', STR_PAD_LEFT) : '');
		} elseif (strpos($tz, '/') !== false) {
			// Generic IANA: use the last segment, e.g. 'Africa/Cairo' → 'Cairo'.
			$parts = explode('/', $tz);
			$label = str_replace('_', ' ', end($parts));
		} else {
			$label = $tz ?: 'UTC'; // 'UTC', 'UTC+2', etc.
		}
	}

	if ($tz === $site_tz) {
		return date_i18n($format, $timestamp) . ' (' . $label . ')';
	}

	// Plugin's own offset-arithmetic conversion — same path as get_start_date().
	$formatted = wc_appointment_timezone_locale('site', 'user', $timestamp, $format, $tz);
	return $formatted . ' (' . $label . ')';
}

/**
 * Resolve the timezone to display to a customer.
 * Priority: _local_timezone on appointment → _customer_timezone on order → site tz.
 *
 * @param WC_Appointment $appointment
 * @return string IANA timezone string
 */
function wellness_get_customer_tz($appointment)
{
	$tz = method_exists($appointment, 'get_local_timezone') ? $appointment->get_local_timezone() : '';
	if (! $tz) {
		$order = $appointment->get_order();
		if ($order) {
			$tz = $order->get_meta('_customer_timezone', true);
		}
	}
	return $tz ?: wc_timezone_string();
}

/**
 * Resolve the timezone to display to staff/admin.
 * Uses the first assigned staff member's timezone_string user meta; falls back to site tz.
 *
 * @param WC_Appointment $appointment
 * @return string IANA timezone string
 */
function wellness_get_staff_tz($appointment)
{
	foreach ((array) $appointment->get_staff_ids() as $staff_id) {
		$tz = get_user_meta((int) $staff_id, 'timezone_string', true);
		if ($tz) {
			return $tz;
		}
	}
	return wc_timezone_string();
}


// ═══════════════════════════════════════════════════════════════════════════════
// Phase 2 — Client timezone detection & order meta storage
// ═══════════════════════════════════════════════════════════════════════════════

/**
 * Step 2a — On every front-end page, write the browser's IANA timezone string
 * into the 'appointments_time_zone' cookie that the WooCommerce Appointments
 * plugin already reads.  Only fires when the cookie is absent — an explicit
 * timezone selection on the booking form always wins.
 */
add_action('wp_footer', function () {
?>
	<script id="wellness-tz-detect">
		(function() {
			var name = 'appointments_time_zone';
			var has = document.cookie.split(';').some(function(c) {
				return c.trim().substring(0, name.length + 1) === name + '=';
			});
			if (has) return;
			try {
				var tz = Intl.DateTimeFormat().resolvedOptions().timeZone;
				if (tz) {
					document.cookie = name + '=' + tz +
						'; path=/; max-age=2592000; SameSite=Lax';
				}
			} catch (e) {}
		})();
	</script>
<?php
}, 20);

/**
 * Step 2b — When a WooCommerce order is created at checkout, persist the
 * detected timezone into '_customer_timezone' order meta.  This is the
 * fallback that Phase 1 email templates read when the appointment itself
 * carries no _local_timezone (guest checkout, old orders, or products with
 * the customer_timezones product setting disabled).
 *
 * The value is validated against PHP's timezone_identifiers_list() before
 * storage, so a tampered cookie cannot inject arbitrary data.
 */
add_action('woocommerce_checkout_order_created', function ($order) {
	if (empty($_COOKIE['appointments_time_zone'])) {
		return;
	}
	$tz = sanitize_text_field(wp_unslash($_COOKIE['appointments_time_zone']));
	if (! in_array($tz, timezone_identifiers_list(), true)) {
		return;
	}
	$order->update_meta_data('_customer_timezone', $tz);
	$order->save();
});


// ═══════════════════════════════════════════════════════════════════════════════
// Phase 3 — Staff timezone UI
// ═══════════════════════════════════════════════════════════════════════════════

/**
 * Render a timezone <select> on the shop_staff user profile page.
 * The plugin already saves 'timezone_string' user meta when the field is
 * present — we only need to render the field from the child theme.
 */
add_action('show_user_profile', 'wellness_staff_timezone_field');
add_action('edit_user_profile', 'wellness_staff_timezone_field');

function wellness_staff_timezone_field($user)
{
	if (! in_array('shop_staff', (array) $user->roles, true)) {
		return;
	}
	$current_tz = get_user_meta($user->ID, 'timezone_string', true) ?: wc_timezone_string();
	$staff_currency = get_user_meta($user->ID, '_staff_currency', true) ?: '';
?>
	<h3><?php esc_html_e('Appointment Timezone', 'woodmart-child'); ?></h3>
	<table class="form-table" role="presentation">
		<tr>
			<th><label for="timezone_string"><?php esc_html_e('Your timezone', 'woodmart-child'); ?></label></th>
			<td>
				<select name="timezone_string" id="timezone_string">
					<?php echo wp_timezone_choice($current_tz, get_user_locale($user)); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
					?>
				</select>
				<p class="description">
					<?php esc_html_e('Appointment times in notification emails and the admin dashboard will display in this timezone.', 'woodmart-child'); ?>
				</p>
			</td>
		</tr>
		<tr>
			<th><label for="_staff_currency"><?php esc_html_e('Your currency', 'woodmart-child'); ?></label></th>
			<td>
				<select name="_staff_currency" id="_staff_currency">
					<option value="" <?php selected($staff_currency, ''); ?>>(Location Based)</option>
					<option value="EGP" <?php selected($staff_currency, 'EGP'); ?>>EGP (Egyptian Pound)</option>
					<option value="USD" <?php selected($staff_currency, 'USD'); ?>>USD (US Dollar)</option>
				</select>
				<p class="description">
					<?php esc_html_e('Prices for products assigned to you will display in this currency. When set to "Location Based", currency is determined by the visitor\'s country (Egypt → EGP, elsewhere → USD).', 'woodmart-child'); ?>
				</p>
			</td>
		</tr>
	</table>
<?php
	wp_nonce_field('wellness_staff_tz_' . $user->ID, '_wellness_tz_nonce');
}

/**
 * Save the timezone field submitted from the staff profile page.
 * Validates against PHP's timezone list and WordPress UTC offset strings.
 */
add_action('personal_options_update',  'wellness_save_staff_timezone');
add_action('edit_user_profile_update', 'wellness_save_staff_timezone');

function wellness_save_staff_timezone($user_id)
{
	if (
		! isset($_POST['_wellness_tz_nonce'])
		|| ! wp_verify_nonce(
			sanitize_text_field(wp_unslash($_POST['_wellness_tz_nonce'])),
			'wellness_staff_tz_' . $user_id
		)
	) {
		return;
	}
	if (! current_user_can('edit_user', $user_id)) {
		return;
	}
	if (empty($_POST['timezone_string'])) {
		return;
	}
	$tz = sanitize_text_field(wp_unslash($_POST['timezone_string']));
	// Accept IANA strings and WordPress-style UTC offset strings (UTC, UTC+2, UTC-5.5 …).
	if (
		in_array($tz, timezone_identifiers_list(), true)
		|| preg_match('/^UTC[+-]?[\d.]*$/', $tz)
	) {
		update_user_meta($user_id, 'timezone_string', $tz);
	}

	// Save staff currency (Location Based / EGP / USD).
	if (isset($_POST['_staff_currency'])) {
		$currency = sanitize_text_field(wp_unslash($_POST['_staff_currency']));
		if (in_array($currency, array('', 'EGP', 'USD'), true)) {
			if ($currency === '') {
				delete_user_meta($user_id, '_staff_currency');
			} else {
				update_user_meta($user_id, '_staff_currency', $currency);
			}
		}
	}
}


// ═══════════════════════════════════════════════════════════════════════════════
// Phase 4 — Appointment list shows times in staff's own timezone
// ═══════════════════════════════════════════════════════════════════════════════

/**
 * When a shop_staff user views appointments in wp-admin, convert the displayed
 * start/end times from site timezone to their configured timezone.
 *
 * Mirrors the plugin's adjust_appointment_to_timezone() (class-wc-appointments-
 * init.php:372) whose filter hooks are commented out. We re-attach them here so
 * no plugin files are modified.
 */
add_filter('woocommerce_appointments_get_start_date_with_time', 'wellness_admin_appointment_tz', 15, 3);
add_filter('woocommerce_appointments_get_end_date_with_time',   'wellness_admin_appointment_tz', 15, 3);

function wellness_admin_appointment_tz($timestring, $appointment, $timestamp)
{
	if (! is_user_logged_in() || ! is_admin()) {
		return $timestring;
	}
	$user = wp_get_current_user();
	if (! in_array('shop_staff', (array) $user->roles, true)) {
		return $timestring;
	}
	$tzstring = get_user_meta($user->ID, 'timezone_string', true);
	if (! $tzstring) {
		return $timestring; // No timezone set — show site timezone unchanged.
	}
	return wellness_tz_format(
		$timestamp,
		$tzstring,
		wc_appointments_date_format() . ', ' . wc_appointments_time_format()
	);
}


// ═══════════════════════════════════════════════════════════════════════════════
// Phase 5 — Availability time-entry hints for shop_staff users
// ═══════════════════════════════════════════════════════════════════════════════

/**
 * On the staff profile edit page, inject:
 *   1. A yellow notice above the availability table reminding the staff that
 *      times are stored in site timezone.
 *   2. A live hint below every "from" / "to" time input that converts the
 *      entered site time to the staff member's own timezone, so they can
 *      verify what the slot means in their local time.
 *
 * Uses admin_footer so we can read GET params and current user safely.
 * Only activates when:
 *   - The current screen is a user-edit or profile page.
 *   - The profile being edited belongs to a shop_staff member.
 *   - That staff member has an IANA timezone set that differs from the site tz.
 */
// ─────────────────────────────────────────────────────────────────────────────
// FIX: WooCommerce Appointments fatal-errors on `UTC+N` timezone strings.
//
// `wp_timezone_choice()` saves manual offsets as e.g. 'UTC+7' / 'UTC-5.5',
// but PHP's DateTimeZone constructor only accepts that for IANA names or
// strict offsets like '+07:00'.  The plugin calls `new DateTimeZone($meta)`
// directly in class-wc-appointments-admin-staff-profile.php, which throws an
// uncaught exception and breaks the staff profile page.
//
// We can't touch the plugin, so we normalise the value on read.
// `+07:00` is a valid DateTimeZone argument and round-trips through WP fine.
// ─────────────────────────────────────────────────────────────────────────────
function wellness_normalise_user_timezone_meta($value, $object_id, $meta_key, $single)
{
	static $busy = false;
	if ($busy || 'timezone_string' !== $meta_key) {
		return $value;
	}

	$busy = true;
	$raw  = get_user_meta($object_id, 'timezone_string', true);
	$busy = false;

	if (! is_string($raw) || '' === $raw) {
		return $value;
	}
	// Already an IANA name or strict offset — pass through.
	if (in_array($raw, timezone_identifiers_list(), true)) {
		return $value;
	}
	if (preg_match('/^[+-]\d{2}:\d{2}$/', $raw)) {
		return $value;
	}

	if ('UTC' === $raw) {
		$fixed = '+00:00';
	} elseif (preg_match('/^UTC([+-])([\d.]+)$/', $raw, $m)) {
		$hours = (float) $m[2];
		$h     = (int) floor($hours);
		$min   = (int) round(($hours - $h) * 60);
		$fixed = $m[1] . sprintf('%02d:%02d', $h, $min);
	} else {
		return $value;
	}

	return $single ? $fixed : array($fixed);
}
add_filter('get_user_metadata', 'wellness_normalise_user_timezone_meta', 10, 4);

add_action('admin_footer', 'wellness_staff_avail_tz_hints', 20);

function wellness_staff_avail_tz_hints()
{
	if (! is_admin()) {
		return;
	}

	$screen = get_current_screen();
	if (! $screen || ! in_array($screen->base, array('user-edit', 'profile'), true)) {
		return;
	}

	// Skip the read-only Google Calendar "Synced rules" view — those rows come
	// straight from Google and the plugin can store times outside 0–23:59 to
	// represent multi-day events.  Touching them would corrupt the display.
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if (isset($_GET['view']) && 'synced' === $_GET['view']) {
		return;
	}

	// Profile being edited (own profile or another user's).
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$user_id = isset($_GET['user_id']) ? (int) $_GET['user_id'] : get_current_user_id();
	$user    = get_user_by('ID', $user_id);
	if (! $user || ! in_array('shop_staff', (array) $user->roles, true)) {
		return;
	}

	$staff_tz_raw = get_user_meta($user_id, 'timezone_string', true);

	if (! $staff_tz_raw) {
		return;
	}

	$staff_tz = trim((string) $staff_tz_raw);

	// Accept any of:
	//   IANA              'Pacific/Auckland', 'Africa/Cairo'
	//   plain UTC         'UTC'
	//   WP UTC w/ decimal 'UTC+0', 'UTC+3', 'UTC-5.5', 'UTC+13'
	//   WP UTC w/ colon   'UTC+5:30', 'UTC-07:00'
	//   bare offset       '+05:00', '-07:00', '+03:00'
	$is_iana = in_array($staff_tz, timezone_identifiers_list(), true);
	$is_utc_offset = (
		'UTC' === $staff_tz ||
		preg_match('/^UTC[+-][\d.:]+$/', $staff_tz) ||
		preg_match('/^[+-]\d{1,2}(:\d{2})?$/', $staff_tz)
	);
	if (! $is_iana && ! $is_utc_offset) {
		return;
	}

	$site_tz_str = wc_timezone_string(); // e.g. '+03:00' or 'Africa/Cairo'

	// ── Site offset in minutes (DST-aware for today) ────────────────────────
	try {
		$site_tz_obj     = new DateTimeZone($site_tz_str);
		$site_offset_min = (int) round($site_tz_obj->getOffset(new DateTime('now', $site_tz_obj)) / 60);
	} catch (Exception $e) {
		$site_offset_min = (int) round((float) get_option('gmt_offset') * 60);
	}

	// ── Human-readable label for the site timezone ──────────────────────────
	$site_label = wc_appointment_get_timezone_name($site_tz_str);
	if (! $site_label) {
		if (preg_match('/^Etc\/GMT([+-])(\d+)$/', $site_tz_str, $m)) {
			$site_label = 'UTC' . ($m[1] === '+' ? '-' : '+') . $m[2];
		} elseif (preg_match('/^([+-])(\d{1,2}):(\d{2})$/', $site_tz_str, $m)) {
			$h          = (int) $m[2];
			$min        = (int) $m[3];
			$site_label = 'UTC' . $m[1] . $h . ($min > 0 ? ':' . str_pad($min, 2, '0', STR_PAD_LEFT) : '');
		} else {
			$site_label = $site_tz_str ?: 'site timezone';
		}
	}

	// ── Staff offset in minutes ───────────────────────────────────────────────
	if ($is_iana) {
		// DST-aware for IANA strings.
		try {
			$staff_tz_obj     = new DateTimeZone($staff_tz);
			$staff_offset_min = (int) round($staff_tz_obj->getOffset(new DateTime('now', $staff_tz_obj)) / 60);
		} catch (Exception $e) {
			$staff_offset_min = $site_offset_min;
		}
	} elseif ('UTC' === $staff_tz) {
		$staff_offset_min = 0;
	} elseif (preg_match('/^UTC([+-])(\d{1,2}):(\d{2})$/', $staff_tz, $m)) {
		// 'UTC+5:30' colon format.
		$sign             = '+' === $m[1] ? 1 : -1;
		$staff_offset_min = $sign * ((int) $m[2] * 60 + (int) $m[3]);
	} elseif (preg_match('/^UTC([+-])([\d.]+)$/', $staff_tz, $m)) {
		// 'UTC+3', 'UTC-5.5' decimal format.
		$sign             = '+' === $m[1] ? 1 : -1;
		$staff_offset_min = (int) round($sign * (float) $m[2] * 60);
	} elseif (preg_match('/^([+-])(\d{1,2})(?::(\d{2}))?$/', $staff_tz, $m)) {
		// Bare '+05:00' / '-7' format.
		$sign             = '+' === $m[1] ? 1 : -1;
		$min              = isset($m[3]) && '' !== $m[3] ? (int) $m[3] : 0;
		$staff_offset_min = $sign * ((int) $m[2] * 60 + $min);
	} else {
		$staff_offset_min = 0;
	}

	$diff_min = $staff_offset_min - $site_offset_min;

	if (0 === $diff_min) {
		return; // Same effective offset — no conversion or hint needed.
	}

	// ── Display label for the staff timezone ─────────────────────────────────
	if (strpos($staff_tz, '/') !== false) {
		// IANA: use the city segment (e.g. 'Pacific/Auckland' → 'Auckland').
		$staff_parts = explode('/', $staff_tz);
		$staff_city  = str_replace('_', ' ', end($staff_parts));
	} else {
		// UTC / UTC+3 / UTC-5.5 — use as-is.
		$staff_city = $staff_tz;
	}

?>
	<script id="wellness-avail-tz-hint">
		(function($) {
			'use strict';

			// diffMin = staffOffsetMin - siteOffsetMin  (positive → staff is ahead of site)
			var diffMin = <?php echo (int) $diff_min; ?>;
			var siteLabel = <?php echo wp_json_encode($site_label); ?>;
			var staffCity = <?php echo wp_json_encode($staff_city); ?>;

			// ── Helpers ────────────────────────────────────────────────────────

			function pad2(n) {
				return n < 10 ? '0' + n : '' + n;
			}

			/** Wrap raw minutes into 0–1439. */
			function wrap(m) {
				return ((m % 1440) + 1440) % 1440;
			}

			/** Parse "H:MM" or "HH:MM" → total minutes, or null if blank/invalid. */
			function parseHHMM(s) {
				if (!s || !/^\d{1,2}:\d{2}$/.test(s)) {
					return null;
				}
				var p = s.split(':');
				var h = parseInt(p[0], 10),
					m = parseInt(p[1], 10);
				// Reject obviously broken stored values (>23h, >59m) so we don't
				// double-convert garbage left in the DB by previous bugs.
				if (h > 23 || m > 59) {
					return null;
				}
				return h * 60 + m;
			}

			/** Total minutes (may be negative or > 1439) → "HH:MM" for <input type="time">. */
			function toHHMM(m) {
				m = wrap(m);
				return pad2(Math.floor(m / 60)) + ':' + pad2(m % 60);
			}

			/** Total minutes → "h:mm AM/PM" for display hints. */
			function toDisplay(m) {
				m = wrap(m);
				var h = Math.floor(m / 60),
					min = m % 60;
				var ampm = h >= 12 ? 'PM' : 'AM';
				h = h % 12 || 12;
				return h + ':' + pad2(min) + ' ' + ampm;
			}

			// ── Per-field hint (shows site-tz equivalent of what staff typed) ──

			function updateHint($inp) {
				var staffMin = parseHHMM($inp.val());
				var $hint = $inp.next('.wellness-tz-hint');
				if (!$hint.length) {
					$hint = $('<span>', {
						'class': 'wellness-tz-hint'
					}).css({
						display: 'block',
						fontSize: '11px',
						color: '#777',
						marginTop: '3px',
						fontStyle: 'italic'
					});
					$inp.after($hint);
				}
				if (staffMin === null) {
					$hint.text('');
					return;
				}

				// rawSite is not yet wrapped — allows detecting day rollover.
				var rawSite = staffMin - diffMin;
				var dayLabel = rawSite < 0 ? ' · prev day' : (rawSite >= 1440 ? ' · next day' : '');
				$hint.text('= ' + toDisplay(rawSite) + ' (' + siteLabel + dayLabel + ')');
			}

			// ── Attach hints & convert displayed values on first encounter ─────

			function attachHints() {
				$('.from_time .time-picker, .to_time .time-picker').each(function() {
					var $inp = $(this);
					// First encounter: the stored value is in site tz — show it in staff tz.
					if (!$inp.data('wellness-tz-bound')) {
						var siteMin = parseHHMM($inp.val());
						if (siteMin !== null) {
							$inp.val(toHHMM(siteMin + diffMin));
						}
						$inp.data('wellness-tz-bound', true)
							.on('input.wellness-tz change.wellness-tz', function() {
								updateHint($(this));
							});
					}
					updateHint($inp);
				});
			}

			// ── Boot ───────────────────────────────────────────────────────────

			$(function() {

				var $avail = $('#appointments_availability');
				if (!$avail.length) {
					return;
				}

				// Banner notice — placed BEFORE the panel-wrap so the plugin's
				// layout CSS inside the panel is not disturbed.
				if (!$avail.prev('.wellness-tz-notice').length) {
					$('<p>', {
							'class': 'wellness-tz-notice'
						})
						.css({
							background: '#fff8c5',
							borderLeft: '4px solid #e6a700',
							padding: '8px 12px',
							margin: '0 0 12px',
							fontSize: '12px'
						})
						.html(
							'<strong>&#9888; Use your local time (' +
							$('<span>').text(staffCity).html() +
							' time) when entering these hours.</strong> ' +
							'Everything will be adjusted automatically when you save. ' +
							'The small note below each field is just for your reference.'
						)
						.insertBefore($avail);
				}

				// Initial pass: convert + attach hints.
				attachHints();

				// Re-run after "Add Rule" inserts a new table row (new rows are empty — no conversion needed).
				$(document).on('click', '.add_grid_row', function() {
					setTimeout(attachHints, 150);
				});

				// Before save: convert all staff-tz input values back to site tz for storage.
				$avail.closest('form').on('submit.wellness-tz', function() {
					$('.from_time .time-picker, .to_time .time-picker').each(function() {
						var staffMin = parseHHMM($(this).val());
						if (staffMin !== null) {
							$(this).val(toHHMM(staffMin - diffMin));
						}
					});
				});
			});

		})(jQuery);
	</script>
<?php
}


// ═══════════════════════════════════════════════════════════════════════════════
// Users list — Timezone column
// ═══════════════════════════════════════════════════════════════════════════════

add_filter('manage_users_columns', function ($columns) {
	$columns['wellness_timezone'] = __('Timezone', 'woodmart-child');
	return $columns;
});

add_filter('manage_users_custom_column', function ($output, $column_name, $user_id) {
	if ($column_name !== 'wellness_timezone') {
		return $output;
	}
	$tz = get_user_meta($user_id, 'timezone_string', true);
	if (! $tz) {
		return '<span style="color:#aaa;">—</span>';
	}
	// City name for IANA strings, raw value for UTC offset strings.
	if (strpos($tz, '/') !== false) {
		$parts = explode('/', $tz);
		$label = str_replace('_', ' ', end($parts));
	} else {
		$label = $tz;
	}
	return '<span title="' . esc_attr($tz) . '">' . esc_html($label) . '</span>';
}, 10, 3);

// ═══════════════════════════════════════════════════════════════════════════════
// Users list — Currency column (staff users only)
// ═══════════════════════════════════════════════════════════════════════════════

add_filter('manage_users_columns', function ($columns) {
	$columns['wellness_currency'] = __('Currency', 'woodmart-child');
	return $columns;
});

add_filter('manage_users_custom_column', function ($output, $column_name, $user_id) {
	if ($column_name !== 'wellness_currency') {
		return $output;
	}
	$user = get_userdata($user_id);
	if (! $user || ! in_array('shop_staff', (array) $user->roles, true)) {
		return '<span style="color:#aaa;">—</span>';
	}
	$currency = get_user_meta($user_id, '_staff_currency', true);
	if ($currency === '') {
		return '(Location Based)';
	}
	return esc_html($currency);
}, 10, 3);

// ═══════════════════════════════════════════════════════════════════════════════
// Task 2 — Duration Options dropdown (frontend + cost + duration + persistence)
// ═══════════════════════════════════════════════════════════════════════════════

/**
 * Resolve currency for a product during AJAX cost calculation.
 */
function wellness_get_ajax_currency($product, $posted = [])
{
	if (! empty($posted['wc_appointments_field_staff'])) {
		$staff_currency = get_user_meta((int) $posted['wc_appointments_field_staff'], '_staff_currency', true);
		if ($staff_currency === 'USD') return 'USD';
		if ($staff_currency === 'EGP') return 'EGP';
		// empty / Location Based — fall through to product-based resolution
	}
	return wellness_get_active_currency($product);
}

// ── Phase 2: Inject Duration dropdown into the booking form ─────────────

add_filter('appointment_form_fields', 'wellness_inject_duration_dropdown', 20);
function wellness_inject_duration_dropdown($fields)
{
	if (! is_product()) return $fields;
	$product_id = get_queried_object_id();
	if (! $product_id) return $fields;
	$product = wc_get_product($product_id);
	if (! $product || ! is_wc_appointment_product($product)) return $fields;

	$enabled = get_post_meta($product_id, '_wc_appointment_enable_duration_options', true);
	if ($enabled !== 'yes') return $fields;

	$options = json_decode(get_post_meta($product_id, '_wc_appointment_duration_options', true), true);
	$options = is_array($options) ? $options : [];

	$select_options = ['' => __('— Select Session Type —', 'woodmart-child')];
	foreach ($options as $i => $opt) {
		if (! empty($opt['label'])) {
			$select_options[$i] = $opt['label'];
		}
	}

	if (count($select_options) > 1) {
		$fields['duration_option'] = [
			'type'    => 'select',
			'name'    => 'wc_appointments_field_duration_option',
			'label'   => __('Session Type', 'woodmart-child'),
			'class'   => ['required_for_calculation', 'wc_appointments_field_duration_option', 'wc_appointments_field_duration'],
			'options' => $select_options,
		];
	}

	return $fields;
}

add_filter('appointments_calculated_product_price', 'wellness_duration_option_price', 200, 3);
function wellness_duration_option_price($price, $product, $posted)
{
	$product_id = $product->get_id();

	if (! isset($posted['wc_appointments_field_duration_option']) || $posted['wc_appointments_field_duration_option'] === '') {
		return $price;
	}

	$options = json_decode(get_post_meta($product_id, '_wc_appointment_duration_options', true), true);
	$options = is_array($options) ? $options : [];
	$index   = $posted['wc_appointments_field_duration_option'];

	if (! isset($options[$index])) {
		return $price;
	}

	$opt      = $options[$index];
	$currency = wellness_get_ajax_currency($product, $posted);

	if ($currency === 'USD' && ! empty($opt['price_usd'])) {
		return floatval($opt['price_usd']);
	}
	if (! empty($opt['price_egp'])) {
		return floatval($opt['price_egp']);
	}

	return $price;
}

// ── Phase 4: Duration override based on selected option ─────────────────

add_filter('appointment_form_posted_total_duration', 'wellness_override_duration_option', 20, 3);
function wellness_override_duration_option($duration_in_total, $product, $posted)
{
	$product_id = $product->get_id();

	if (! isset($posted['wc_appointments_field_duration_option']) || $posted['wc_appointments_field_duration_option'] === '') {
		return $duration_in_total;
	}

	$options = json_decode(get_post_meta($product_id, '_wc_appointment_duration_options', true), true);
	$options = is_array($options) ? $options : [];
	$index   = $posted['wc_appointments_field_duration_option'];

	if (! isset($options[$index]) || empty($options[$index]['duration'])) {
		return $duration_in_total;
	}

	$chosen_minutes = absint($options[$index]['duration']);
	if ($chosen_minutes <= 0) {
		return $duration_in_total;
	}

	$duration_unit = $product->get_duration_unit();
	if (in_array($duration_unit, ['minute', 'hour'])) {
		return $chosen_minutes;
	}

	return $duration_in_total;
}

// ── Phase 4b: Override Interval to match selected Duration Option ───────

add_filter('woocommerce_appointments_base_interval', 'wellness_override_interval_for_duration_option', 20, 2);
function wellness_override_interval_for_duration_option($base_interval, $product)
{
	// Only during AJAX slot calculation.
	if (! wp_doing_ajax()) {
		return $base_interval;
	}

	// Parse the AJAX form data to find the selected session type.
	$form_data = $_POST['form'] ?? '';
	if (empty($form_data)) {
		return $base_interval;
	}

	parse_str($form_data, $posted);

	if (empty($posted['wc_appointments_field_duration_option'])) {
		return $base_interval;
	}

	$options = json_decode(
		get_post_meta($product->get_id(), '_wc_appointment_duration_options', true),
		true
	);
	$options = is_array($options) ? $options : [];
	$index   = $posted['wc_appointments_field_duration_option'];

	if (! isset($options[$index]) || empty($options[$index]['duration'])) {
		return $base_interval;
	}

	$chosen_minutes = absint($options[$index]['duration']);
	if ($chosen_minutes <= 0) {
		return $base_interval;
	}

	return $chosen_minutes;
}

// ── Phase 5: Persist selected duration label in appointment data ────────

add_filter('woocommerce_appointments_get_posted_data', 'wellness_capture_duration_label', 10, 3);
function wellness_capture_duration_label($data, $product, $posted)
{
	$product_id = $product->get_id();

	if (! isset($posted['wc_appointments_field_duration_option']) || $posted['wc_appointments_field_duration_option'] === '') {
		return $data;
	}

	$options = json_decode(get_post_meta($product_id, '_wc_appointment_duration_options', true), true);
	$options = is_array($options) ? $options : [];
	$idx     = $posted['wc_appointments_field_duration_option'];
	if (isset($options[$idx]['label'])) {
		// Non-underscore key so it displays in cart item meta.
		$data['session_type'] = $options[$idx]['label'];
	}


	return $data;
}

// ── Register Session Type label for cart / order item display ──────────

add_filter('woocommerce_appointments_data_labels', 'wellness_add_session_type_label');
function wellness_add_session_type_label($labels)
{
	$labels['session_type'] = __('Session Type', 'woodmart-child');
	return $labels;
}

// ── Persist Session Type to appointment post meta after order is processed ──
// We use checkout_order_processed (shortcode) + store_api_checkout_order_processed
// (block) because woocommerce_new_order_item fires before item meta is persisted.

add_action('woocommerce_checkout_order_processed', 'wellness_persist_session_types', 20);
add_action('woocommerce_store_api_checkout_order_processed', 'wellness_persist_session_types', 20);
function wellness_persist_session_types($order)
{
	if (! is_a($order, 'WC_Order')) {
		$order = wc_get_order($order);
		if (! $order) return;
	}

	foreach ($order->get_items() as $item) {
		if (! is_a($item, 'WC_Order_Item_Product')) continue;

		$appointment_id = $item->get_meta('_appointment_id');
		if (! $appointment_id) continue;

		$session_type = $item->get_meta('_session_type');
		if ($session_type) {
			update_post_meta($appointment_id, '_session_type', $session_type);
		}
	}
}

// Also persist for orders that skip "processing" (e.g., zero-total → completed).
add_action('woocommerce_order_status_completed', 'wellness_persist_session_types_on_completed', 20);
function wellness_persist_session_types_on_completed($order_id)
{
	$order = wc_get_order($order_id);
	if (! $order) return;
	wellness_persist_session_types($order);
}

// ── Capture session type during checkout and store as order item meta ──

add_action('woocommerce_checkout_create_order_line_item', 'wellness_add_session_type_to_order_item', 10, 4);
function wellness_add_session_type_to_order_item($item, $cart_item_key, $values, $order)
{
	if (! empty($values['appointment']['session_type'])) {
		$item->add_meta_data('_session_type', $values['appointment']['session_type']);
	}
}

// ── Display Session Type in order item meta (thank-you page, admin, emails) ──

add_filter('woocommerce_display_item_meta', 'wellness_display_session_type_in_orders', 10, 3);
function wellness_display_session_type_in_orders($html, $item, $args)
{
	if (! is_a($item, 'WC_Order_Item_Product')) {
		return $html;
	}

	$session_type = $item->get_meta('_session_type');
	if (! $session_type) {
		return $html;
	}

	// Only add if not already displayed by the appointments plugin.
	if (strpos($html, 'Session Type') !== false) {
		return $html;
	}

	$html .= '<li class="wellness-session-type">';
	$html .= '<strong class="wc-item-meta-label">' . esc_html__('Session Type', 'woodmart-child') . ':</strong> ';
	$html .= '<span>' . esc_html($session_type) . '</span>';
	$html .= '</li>';

	return $html;
}

// ═══════════════════════════════════════════════════════════════════════════════
// Recurring — capture & persist recurrence data (cart → order item → appointment)
// ═══════════════════════════════════════════════════════════════════════════════

// Capture recurring values into appointment data. Only set when the customer
// chose to repeat, so no recurring meta appears on single (non-repeat) bookings.
add_filter('woocommerce_appointments_get_posted_data', 'wellness_capture_recurring_data', 12, 3);
function wellness_capture_recurring_data($data, $product, $posted)
{
	if (empty($posted['wc_appointments_field_recurring']) || $posted['wc_appointments_field_recurring'] !== 'yes') {
		return $data;
	}

	$interval = $posted['wc_appointments_field_recurring_interval'] ?? '';
	$count    = absint($posted['wc_appointments_field_recurring_count'] ?? 0);
	if ($interval === '' || $count <= 0) {
		return $data;
	}

	// Non-underscore keys so they display in cart item meta.
	$data['recurring']          = 'yes';
	$data['recurring_interval'] = $interval;
	$data['recurring_count']    = $count;

	wellness_recurring_log('capture OK: interval=' . $interval . ' count=' . $count);

	return $data;
}

// Register labels for the recurring appointment-data keys (cart display).
add_filter('woocommerce_appointments_data_labels', 'wellness_add_recurring_labels');
function wellness_add_recurring_labels($labels)
{
	$labels['recurring']          = __('Repeat Appointment', 'woodmart-child');
	$labels['recurring_interval'] = __('Repeat Interval', 'woodmart-child');
	$labels['recurring_count']    = __('Number of Repeats', 'woodmart-child');
	return $labels;
}

// Write recurrence as order item meta during checkout.
add_action('woocommerce_checkout_create_order_line_item', 'wellness_add_recurring_to_order_item', 10, 4);
function wellness_add_recurring_to_order_item($item, $cart_item_key, $values, $order)
{
	if (empty($values['appointment']['recurring']) || $values['appointment']['recurring'] !== 'yes') {
		return;
	}
	$item->add_meta_data('_recurring', 'yes');
	wellness_recurring_log('order item meta written: recurring=yes');
	if (! empty($values['appointment']['recurring_interval'])) {
		$item->add_meta_data('_recurring_interval', $values['appointment']['recurring_interval']);
	}
	if (! empty($values['appointment']['recurring_count'])) {
		$item->add_meta_data('_recurring_count', absint($values['appointment']['recurring_count']));
	}
}

// Persist recurrence to the appointment post meta after the order is processed.
add_action('woocommerce_checkout_order_processed', 'wellness_persist_recurring', 20);
add_action('woocommerce_store_api_checkout_order_processed', 'wellness_persist_recurring', 20);
function wellness_persist_recurring($order)
{
	if (! is_a($order, 'WC_Order')) {
		$order = wc_get_order($order);
		if (! $order) return;
	}

	foreach ($order->get_items() as $item) {
		if (! is_a($item, 'WC_Order_Item_Product')) continue;

		$appointment_id = $item->get_meta('_appointment_id');
		if (! $appointment_id) continue;

		if ($item->get_meta('_recurring') === 'yes') {
			update_post_meta($appointment_id, '_recurring', 'yes');
			update_post_meta($appointment_id, '_recurring_interval', $item->get_meta('_recurring_interval'));
			update_post_meta($appointment_id, '_recurring_count', absint($item->get_meta('_recurring_count')));
			wellness_recurring_log('persisted to appointment ' . $appointment_id . ' recurring=yes');
		}
	}
}

// Also persist for orders that skip "processing" (zero-total → completed).
add_action('woocommerce_order_status_completed', 'wellness_persist_recurring_on_completed', 20);
function wellness_persist_recurring_on_completed($order_id)
{
	$order = wc_get_order($order_id);
	if (! $order) return;
	wellness_persist_recurring($order);
}

// Display a single "Repeat: Weekly × 2" line in order item meta
// (thank-you page, admin, emails) when the booking is recurring.
add_filter('woocommerce_display_item_meta', 'wellness_display_recurring_in_orders', 12, 3);
function wellness_display_recurring_in_orders($html, $item, $args)
{
	if (! is_a($item, 'WC_Order_Item_Product')) return $html;

	if ($item->get_meta('_recurring') !== 'yes') return $html;

	$interval = $item->get_meta('_recurring_interval');
	$count    = (int) $item->get_meta('_recurring_count');
	if (! $interval || ! $count) return $html;

	$interval_labels = [
		'weekly'   => __('Weekly', 'woodmart-child'),
		'biweekly' => __('Bi-Weekly', 'woodmart-child'),
		'monthly'  => __('Monthly', 'woodmart-child'),
	];
	$label = isset($interval_labels[$interval]) ? $interval_labels[$interval] : $interval;

	// Avoid duplicating the appointments-plugin rendered rows.
	if (strpos($html, 'Repeat Appointment') !== false || strpos($html, '>Repeat:') !== false) {
		return $html;
	}

	$html .= '<li class="wellness-recurring">';
	$html .= '<strong class="wc-item-meta-label">' . esc_html__('Repeat', 'woodmart-child') . ':</strong> ';
	$html .= '<span>' . esc_html($label) . '</span> &times; ' . esc_html($count);
	$html .= '</li>';

	// Full schedule of recurring dates.
	$start = 0;
	$appointment_id = $item->get_meta('_appointment_id');
	if ($appointment_id) {
		$appt = get_wc_appointment($appointment_id);
		if ($appt) $start = (int) $appt->get_start();
	}
	$dates = $start ? wellness_compute_recurring_dates($start, $interval, $count) : [];
	if ($dates) {
		$html .= '<li class="wellness-recurring-schedule">';
		$html .= '<strong class="wc-item-meta-label">' . esc_html__('Scheduled sessions', 'woodmart-child') . ':</strong> ';
		$html .= '<span>' . esc_html(implode(', ', $dates)) . '</span>';
		$html .= '</li>';
	}

	$html .= '<li class="wellness-recurring-note">';
	$html .= esc_html(sprintf(
		/* translators: %d: days before the appointment */
		__('Each recurring session is confirmed only after payment — please pay at least %d day(s) before your appointment. A reminder will be sent before each session.', 'woodmart-child'),
		wellness_get_recurring_payment_reminder_days()
	));
	$html .= '</li>';

	return $html;
}

// ── Hide calendar until Duration option is selected ─────────────────────

add_action('woocommerce_after_appointment_form_output', 'wellness_hide_calendar_until_duration', 20, 2);
function wellness_hide_calendar_until_duration($position, $product_id)
{
	// Only inject on 'after' position (once) and only when duration options are enabled.
	if ($position !== 'after') return;

	$enabled = get_post_meta($product_id, '_wc_appointment_enable_duration_options', true);
	if ($enabled !== 'yes') return;

?>
	<style>
		/* Hide form elements until duration option is selected */
		#wc-appointments-appointment-form.wellness-duration-pending > *:not(.wc_appointments_field_duration_option) {
			display: none !important;
		}
		#wc-appointments-appointment-form.wellness-duration-pending ~ .quantity,
		#wc-appointments-appointment-form.wellness-duration-pending ~ .single_add_to_cart_button {
			display: none !important;
		}
	</style>
	<script>
		(function($) {
			function wellnessToggleDurationFields() {
				var $select = $('#wc-appointments-appointment-form .wc_appointments_field_duration_option select');
				if (!$select.length) return;

				var $form = $('#wc-appointments-appointment-form');
				var chosen = $select.val();
				var isChosen = chosen !== '' && chosen !== null;

				if (isChosen) {
					$form.removeClass('wellness-duration-pending');
				} else {
					$form.addClass('wellness-duration-pending');
				}
			}

			$(document).on('change', '#wc-appointments-appointment-form .wc_appointments_field_duration_option select', function() {
				wellnessToggleDurationFields();
				// Trigger the plugin's cost calc via its custom event.
				$(this).closest('form').triggerHandler('addon-duration-changed');
			});

			// Initialize on page load
			if ($('#wc-appointments-appointment-form').length) {
				wellnessToggleDurationFields();
			}
		})(jQuery);
	</style>
<?php
}


// ═══════════════════════════════════════════════════════════════════════════════
// Recurring Appointments — custom fields, schedule preview & AJAX
// (replaces the WooCommerce Product Add-Ons "Repeat Appointment" flow)
// ═══════════════════════════════════════════════════════════════════════════════

/**
 * Inject the recurring fields into the appointment form.
 * Only shows when the product has _wc_appointment_enable_recurring = 'yes' (default).
 */
add_filter('appointment_form_fields', 'wellness_inject_recurring_fields', 25);
function wellness_inject_recurring_fields($fields)
{
	if (! is_product()) return $fields;

	$product_id = get_queried_object_id();
	if (! $product_id) return $fields;

	$product = wc_get_product($product_id);
	if (! $product || ! is_wc_appointment_product($product)) return $fields;

	$enable = get_post_meta($product_id, '_wc_appointment_enable_recurring', true);
	$enable = ($enable === '') ? 'yes' : $enable; // default ON
	if ($enable !== 'yes') return $fields;

	$max_count = max(1, intval(get_post_meta($product_id, '_wc_appointment_max_repeat_count', true) ?: 2));

	$fields['recurring'] = [
		'type'  => 'checkbox',
		'name'  => 'wc_appointments_field_recurring',
		'label' => __('Do you want to repeat this appointment?', 'woodmart-child'),
		'class' => ['wellness-recurring-toggle'],
	];

	$fields['recurring_interval'] = [
		'type'    => 'select',
		'name'    => 'wc_appointments_field_recurring_interval',
		'label'   => __('Repeat every', 'woodmart-child'),
		'class'   => ['wellness-recurring-lead', 'wellness-recurring-interval'],
		'options' => [
			'weekly'   => __('Weekly', 'woodmart-child'),
			'biweekly' => __('Bi-Weekly', 'woodmart-child'),
			'monthly'  => __('Monthly', 'woodmart-child'),
		],
	];

	$fields['recurring_count'] = [
		'type'  => 'number',
		'name'  => 'wc_appointments_field_recurring_count',
		'label' => __('Number of repeats', 'woodmart-child'),
		'class' => ['wellness-recurring-lead', 'wellness-recurring-count'],
		'min'   => 1,
		'max'   => $max_count,
		'step'  => 1,
		'value' => 2,
	];

	return $fields;
}

/**
 * Map a recurrence interval key to a strtotime unit + multiplier.
 */
function wellness_recurring_interval_map($interval)
{
	switch ($interval) {
		case 'biweekly':
			return ['week', 2];
		case 'monthly':
			return ['month', 1];
		case 'weekly':
		default:
			return ['week', 1];
	}
}

/**
 * Compute the list of formatted recurring session dates (first + follow-ups).
 *
 * @param int         $start_ts  Start timestamp of the first appointment.
 * @param string      $interval  weekly | biweekly | monthly.
 * @param int         $count     Number of follow-ups.
 * @param string|null $tz        IANA timezone for formatting (optional).
 * @return string[]
 */
function wellness_compute_recurring_dates($start_ts, $interval, $count, $tz = null)
{
	if (! $start_ts || ! $interval || (int) $count <= 0) return [];

	list($unit, $mult) = wellness_recurring_interval_map($interval);
	$rows = [];
	for ($i = 0; $i <= (int) $count; $i++) {
		$ts = $i === 0 ? (int) $start_ts : strtotime('+' . ($i * $mult) . ' ' . $unit, (int) $start_ts);
		if ($ts) {
			$rows[] = $tz
				? wellness_tz_format($ts, $tz, 'F j, Y \a\t g:i A')
				: date_i18n('F j, Y \a\t g:i A', $ts);
		}
	}
	return $rows;
}

/**
 * Get the full recurring chain (root + follow-ups) for an appointment, sorted by start time.
 *
 * @param WC_Appointment $appointment An appointment that is (or belongs to) a recurring series.
 * @return WC_Appointment[]
 */
function wellness_get_recurring_chain($appointment)
{
	if (! $appointment) return [];

	$root_id = $appointment->get_parent_id() > 0 ? $appointment->get_parent_id() : $appointment->get_id();
	$root    = get_wc_appointment($root_id);
	if (! $root) return [];

	$children = get_posts(array(
		'post_type'   => 'wc_appointment',
		'post_status' => 'any',
		'meta_query'  => array(
			array('key' => '_appointment_parent_id', 'value' => $root_id),
		),
		'fields' => 'ids',
	));

	$chain = [];
	foreach (array_merge(array($root_id), $children) as $id) {
		$a = get_wc_appointment($id);
		if ($a) $chain[] = $a;
	}

	usort($chain, function ($a, $b) {
		return $a->get_start() - $b->get_start();
	});

	return $chain;
}

/**
 * Human-friendly label for an appointment status.
 */
function wellness_appointment_status_label($status)
{
	$labels = [
		'unpaid'               => __('Unpaid (pending payment)', 'woodmart-child'),
		'paid'                 => __('Paid', 'woodmart-child'),
		'confirmed'            => __('Confirmed', 'woodmart-child'),
		'complete'             => __('Completed', 'woodmart-child'),
		'cancelled'            => __('Cancelled', 'woodmart-child'),
		'pending-confirmation' => __('Pending confirmation', 'woodmart-child'),
		'in-cart'              => __('In cart', 'woodmart-child'),
		'was-in-cart'          => __('Was in cart', 'woodmart-child'),
	];
	return isset($labels[$status]) ? $labels[$status] : ucfirst(str_replace('-', ' ', $status));
}

/**
 * Render a compact recurring chain list (dates + status + past) for admin views.
 *
 * @param WC_Appointment $appointment Any appointment in a recurring series.
 */
function wellness_render_recurring_chain($appointment)
{
	$chain = wellness_get_recurring_chain($appointment);
	if (empty($chain)) return;

	$now = current_time('timestamp');

	echo '<div class="wellness-recurring-chain" style="margin:10px 0; padding:10px 12px; background:#f7f7f7; border-left:4px solid #4f7cff;">';
	echo '<h4 style="margin:0 0 8px; font-size:13px;">' . esc_html__('Recurring Appointment', 'woodmart-child') . '</h4>';
	echo '<ul style="margin:0; padding:0 0 0 16px;">';
	foreach ($chain as $a) {
		$start      = $a->get_start();
		$is_past    = $start && $start < $now;
		$is_current = $a->get_id() === $appointment->get_id();
		$label      = $start ? date_i18n('F j, Y g:i A', $start) : '';

		echo '<li>';
		echo '<a href="' . esc_url(admin_url('post.php?post=' . $a->get_id() . '&action=edit')) . '" target="_blank">' . esc_html__('Appt #', 'woodmart-child') . esc_html($a->get_id()) . '</a>';
		if ($label) {
			echo ' &mdash; ' . esc_html($label);
		}
		echo ' &mdash; ' . esc_html(wellness_appointment_status_label($a->get_status()));
		if ($is_past) {
			echo ' <em>(past)</em>';
		}
		if ($is_current) {
			echo ' <strong>(this)</strong>';
		}

		$o = $a->get_order();
		if ($o) {
			echo ' &mdash; <a href="' . esc_url($o->get_edit_order_url()) . '" target="_blank">' . esc_html__('Order #', 'woodmart-child') . esc_html($o->get_order_number()) . '</a>';
		}
		echo '</li>';
	}
	echo '</ul></div>';
}

/**
 * Show the recurring chain on the admin order edit page.
 */
add_action('woocommerce_admin_order_data_after_order_details', 'wellness_show_recurring_on_order');
function wellness_show_recurring_on_order($order)
{
	if (! $order instanceof WC_Order) return;

	$appointment_ids = class_exists('WC_Appointment_Data_Store')
		? WC_Appointment_Data_Store::get_appointment_ids_from_order_id($order->get_id())
		: array();
	if (empty($appointment_ids)) return;

	$appointment = get_wc_appointment($appointment_ids[0]);
	if (! $appointment) return;

	$root_id = $appointment->get_parent_id() > 0 ? $appointment->get_parent_id() : $appointment->get_id();
	if (get_post_meta($root_id, '_recurring', true) !== 'yes') return;

	wellness_render_recurring_chain($appointment);
}

/**
 * Add a recurring-chain metabox on the wc_appointment admin edit screen.
 */
add_action('add_meta_boxes', 'wellness_recurring_admin_metabox', 10, 2);
function wellness_recurring_admin_metabox($post_type, $post)
{
	if ($post_type !== 'wc_appointment' || ! $post) return;

	$appointment = get_wc_appointment($post->ID);
	if (! $appointment) return;

	$root_id = $appointment->get_parent_id() > 0 ? $appointment->get_parent_id() : $appointment->get_id();
	if (get_post_meta($root_id, '_recurring', true) !== 'yes') return;

	add_meta_box(
		'wellness_recurring_chain',
		__('Recurring Appointment', 'woodmart-child'),
		'wellness_recurring_admin_metabox_cb',
		'wc_appointment',
		'normal',
		'high'
	);
}

function wellness_recurring_admin_metabox_cb($post)
{
	$appointment = get_wc_appointment($post->ID);
	if (! $appointment) {
		echo '<p>&mdash;</p>';
		return;
	}
	wellness_render_recurring_chain($appointment);
}

/**
 * Whether an appointment belongs to a recurring series (root has _recurring = yes).
 *
 * Falls back to the root order item's _recurring meta, because the appointment
 * post meta is not always set at checkout (the order item _appointment_id is
 * absent when woocommerce_checkout_order_processed runs, so the engine only
 * persists it when the order is paid). Without this fallback, unpaid recurrences
 * would show as "—" in the admin "Recurring" column.
 */
function wellness_appointment_is_recurring($appointment)
{
	static $cache = array();
	if (! $appointment) return false;

	$root_id = $appointment->get_parent_id() > 0 ? $appointment->get_parent_id() : $appointment->get_id();
	if (isset($cache[$root_id])) return $cache[$root_id];

	$is_recurring = get_post_meta($root_id, '_recurring', true) === 'yes';

	// Fallback: check the root order item meta (persisted reliably at checkout).
	if (! $is_recurring) {
		$root = get_wc_appointment($root_id);
		if ($root) {
			$order = $root->get_order();
			if ($order) {
				foreach ($order->get_items() as $item) {
					if ($item->get_meta('_recurring') === 'yes') {
						$is_recurring = true;
						break;
					}
				}
			}
		}
		// Persist so downstream direct meta reads (admin chain / metabox / order
		// card) agree with the list column.
		if ($is_recurring) {
			update_post_meta($root_id, '_recurring', 'yes');
		}
	}

	$cache[$root_id] = $is_recurring;
	return $cache[$root_id];
}

/**
 * Insert a column after a given key in an admin list-table columns array.
 */
function wellness_insert_after($items, $after_key, $key, $label)
{
	$new = array();
	foreach ($items as $k => $v) {
		$new[$k] = $v;
		if ($k === $after_key) {
			$new[$key] = $label;
		}
	}
	if (! isset($new[$key])) {
		$new[$key] = $label;
	}
	return $new;
}

/**
 * "Recurring" column on the appointments admin list (after the name column).
 */
add_filter('manage_wc_appointment_posts_columns', 'wellness_appointments_list_columns');
function wellness_appointments_list_columns($columns)
{
	return wellness_insert_after($columns, 'appointment_id', 'wellness_recurring', __('Recurring', 'woodmart-child'));
}

add_action('manage_wc_appointment_posts_custom_column', 'wellness_appointments_list_column_content', 10, 2);
function wellness_appointments_list_column_content($column, $post_id)
{
	if ($column !== 'wellness_recurring') return;

	$appointment = get_wc_appointment($post_id);
	if (! $appointment || ! wellness_appointment_is_recurring($appointment)) {
		echo '<span style="color:#bbb;">&mdash;</span>';
		return;
	}
	wellness_render_recurring_badge(wellness_get_recurring_chain($appointment), 'appointment', $appointment->get_order());
}

/**
 * "Recurring" column on the orders admin list (after the order-number column).
 */
add_filter('manage_shop_order_posts_columns', 'wellness_orders_list_columns');
add_filter('manage_edit-shop_order_columns', 'wellness_orders_list_columns');
add_filter('manage_woocommerce_page_wc-orders_columns', 'wellness_orders_list_columns');
function wellness_orders_list_columns($columns)
{
	return wellness_insert_after($columns, 'order_number', 'wellness_recurring', __('Recurring', 'woodmart-child'));
}

/**
 * Whether an order is part of a recurring series (first order or a follow-up order).
 */
function wellness_orders_is_recurring($order)
{
	if (! $order) return false;

	foreach ($order->get_items() as $item) {
		if ($item->get_meta('_recurring') === 'yes') {
			return true;
		}
	}

	if (class_exists('WC_Appointment_Data_Store')) {
		$appointment_ids = WC_Appointment_Data_Store::get_appointment_ids_from_order_id($order->get_id());
		if (! empty($appointment_ids)) {
			$appointment = get_wc_appointment($appointment_ids[0]);
			if ($appointment && wellness_appointment_is_recurring($appointment)) {
				return true;
			}
		}
	}

	return false;
}

/**
 * First recurring-series appointment linked to an order (root or follow-up).
 */
function wellness_order_recurring_appointment($order)
{
	if (! $order || ! class_exists('WC_Appointment_Data_Store')) return null;

	$appointment_ids = WC_Appointment_Data_Store::get_appointment_ids_from_order_id($order->get_id());
	if (empty($appointment_ids)) return null;

	$appointment = get_wc_appointment($appointment_ids[0]);
	if ($appointment && wellness_appointment_is_recurring($appointment)) {
		return $appointment;
	}
	return null;
}

// Legacy CPT orders list.
add_action('manage_shop_order_posts_custom_column', 'wellness_orders_list_column_content', 10, 2);
function wellness_orders_list_column_content($column, $post_id)
{
	if ($column !== 'wellness_recurring') return;
	wellness_orders_recurring_cell(wc_get_order($post_id));
}

// HPOS orders list (WC_Order object is passed).
add_action('manage_woocommerce_page_wc-orders_custom_column', 'wellness_orders_list_hpos_column', 10, 2);
function wellness_orders_list_hpos_column($column, $order)
{
	if ($column !== 'wellness_recurring') return;
	wellness_orders_recurring_cell($order instanceof WC_Order ? $order : null);
}

function wellness_orders_recurring_cell($order)
{
	if (! $order || ! wellness_orders_is_recurring($order)) {
		echo '<span style="color:#bbb;">&mdash;</span>';
		return;
	}
	$appointment = wellness_order_recurring_appointment($order);
	$chain       = $appointment ? wellness_get_recurring_chain($appointment) : array();
	wellness_render_recurring_badge($chain, 'order', $order);
}

/**
 * Shared clickable badge + popup for the recurring admin-list columns.
 *
 * Payment- and date-aware: the badge reflects whether the viewed order has been
 * paid, and the popup lists each session with its date, a "past" marker when it
 * has already occurred, and its per-session payment status.
 *
 * @param array       $chain WC_Appointment[] of the recurring series.
 * @param string      $type  'appointment' or 'order'.
 * @param WC_Order|null $order The order being viewed (used for payment status).
 */
function wellness_render_recurring_badge($chain = array(), $type = 'appointment', $order = null)
{
	$days    = wellness_get_recurring_payment_reminder_days();
	$now     = current_time('timestamp');
	$is_paid = $order instanceof WC_Order && $order->is_paid();

	// Count sessions in the series that are paid / confirmed / completed.
	$paid_sessions = 0;
	$total         = count($chain);
	foreach ($chain as $a) {
		if (in_array($a->get_status(), array('paid', 'confirmed', 'complete'), true)) {
			$paid_sessions++;
		}
	}

	$status_label = $is_paid
		? __('Paid', 'woodmart-child')
		: __('Unpaid', 'woodmart-child');
	$status_class = $is_paid ? 'wellness-rec-badge--paid' : 'wellness-rec-badge--unpaid';

	// Note — accurate, date-aware status (avoids claiming the whole series is paid).
	if ($total > 0 && $paid_sessions >= $total) {
		$note = __('All sessions paid.', 'woodmart-child');
	} elseif ($is_paid) {
		/* translators: 1: paid count, 2: total count */
		$note = sprintf(__('Order paid &middot; %1$d of %2$d sessions paid.', 'woodmart-child'), $paid_sessions, $total);
	} else {
		/* translators: %d: days before the appointment */
		$note = sprintf(__('pay/confirm %d day(s) before session', 'woodmart-child'), $days);
	}

	echo '<span class="wellness-rec-wrap">';
	echo '<button type="button" class="wellness-rec-badge ' . esc_attr($status_class) . '" title="' . esc_attr__('Show recurring details', 'woodmart-child') . '">';
	echo esc_html__('Recurring', 'woodmart-child') . ' &middot; ' . esc_html($status_label);
	echo '</button>';

	echo '<span class="wellness-rec-note">' . esc_html($note) . '</span>';

	echo '<div class="wellness-rec-pop">';
	echo '<div class="wellness-rec-pop-title">' . esc_html__('Recurring series', 'woodmart-child') . '</div>';
	if (empty($chain)) {
		echo '<div class="wellness-rec-pop-row">' . esc_html__('No linked sessions yet.', 'woodmart-child') . '</div>';
	} else {
		foreach ($chain as $a) {
			$start     = $a->get_start();
			$when      = $start ? date_i18n('M j, Y g:i A', $start) : '';
			$is_past   = $start && $start < $now;
			$appt_link = admin_url('post.php?post=' . $a->get_id() . '&action=edit');
			$o         = $a->get_order();

			echo '<div class="wellness-rec-pop-row">';
			echo '<a href="' . esc_url($appt_link) . '" target="_blank">' . esc_html__('Appt #', 'woodmart-child') . esc_html($a->get_id()) . '</a>';
			if ($when) {
				echo ' <span class="wellness-rec-pop-when">' . esc_html($when) . ($is_past ? ' <span class="wellness-rec-pop-past">(' . esc_html__('past', 'woodmart-child') . ')</span>' : '') . '</span>';
			}
			echo ' <span class="wellness-rec-pop-status">' . esc_html(wellness_appointment_status_label($a->get_status())) . '</span>';
			if ($o) {
				echo ' &mdash; ' . esc_html__('Order', 'woodmart-child') . ' <a href="' . esc_url($o->get_edit_order_url()) . '" target="_blank">#' . esc_html($o->get_order_number()) . '</a>';
			}
			echo '</div>';
		}
	}
	echo '</div>'; // .wellness-rec-pop
	echo '</span>'; // .wellness-rec-wrap
}

/**
 * CSS/JS for the recurring badge popup on the appointments and orders list screens.
 */
add_action('admin_footer', 'wellness_recurring_list_popup_assets');
function wellness_recurring_list_popup_assets()
{
	$screen = get_current_screen();
	if (! $screen) return;

	$is_appointments = ('edit' === $screen->base && 'wc_appointment' === $screen->post_type);
	$is_orders       = ('edit' === $screen->base && 'shop_order' === $screen->post_type)
		|| (false !== strpos((string) $screen->id, 'wc-orders'));

	if (! $is_appointments && ! $is_orders) return;
	?>
	<style>
		.wellness-rec-wrap { position: relative; display: inline-block; }
		.wellness-rec-badge { display: inline-block; padding: 2px 8px; border-radius: 3px; background: #e3edff; color: #1a56db; font-size: 11px; font-weight: 600; text-decoration: none; border: 1px solid #c7d8ff; cursor: pointer; font: inherit; line-height: inherit; white-space: nowrap; }
		.wellness-rec-badge:hover { background: #d4e2ff; }
		.wellness-rec-badge--paid { background: #e7f6e7; color: #1e7d32; border-color: #b7e3b7; }
		.wellness-rec-badge--unpaid { background: #fff3e0; color: #b26a00; border-color: #ffd9a8; }
		.wellness-rec-note { display: block; color: #666; font-size: 11px; line-height: 1.5; margin-top: 2px; }
		.wellness-rec-pop-past { color: #999; font-style: italic; }
		.wellness-rec-pop { display: none; position: absolute; top: 100%; left: 0; z-index: 9999; min-width: 320px; background: #fff; border: 1px solid #ccd0d4; box-shadow: 0 4px 12px rgba(0,0,0,.15); padding: 8px 10px; border-radius: 4px; }
		.wellness-rec-pop.open { display: block; }
		.wellness-rec-pop-title { font-weight: 600; margin-bottom: 6px; font-size: 12px; }
		.wellness-rec-pop-row { font-size: 12px; line-height: 1.7; padding-top: 4px; margin-top: 4px; }
		.wellness-rec-pop-row + .wellness-rec-pop-row { border-top: 1px solid #f0f0f1; }
		.wellness-rec-pop-when { color: #555; }
		.wellness-rec-pop-status { color: #1a56db; font-weight: 600; }
	</style>
	<script>
	(function($) {
		$(document).on('click', '.wellness-rec-badge', function(e) {
			e.preventDefault();
			e.stopPropagation();
			var $pop = $(this).siblings('.wellness-rec-pop');
			$('.wellness-rec-pop.open').not($pop).removeClass('open');
			$pop.toggleClass('open');
		});
		$(document).on('click', function(e) {
			if (!$(e.target).closest('.wellness-rec-wrap').length) {
				$('.wellness-rec-pop.open').removeClass('open');
			}
		});
	})(jQuery);
	</script>
	<?php
}

/**
 * Append the recurring schedule + pay/confirm note to cart and checkout review item data.
 */
add_filter('woocommerce_get_item_data', 'wellness_recurring_cart_item_data', 10, 2);
function wellness_recurring_cart_item_data($item_data, $cart_item)
{
	if (empty($cart_item['appointment']['recurring']) || $cart_item['appointment']['recurring'] !== 'yes') {
		return $item_data;
	}

	$interval = $cart_item['appointment']['recurring_interval'] ?? '';
	$count    = absint($cart_item['appointment']['recurring_count'] ?? 0);
	$start    = $cart_item['appointment']['_start_date'] ?? 0;
	if (! $interval || ! $count || ! $start) return $item_data;

	$dates = wellness_compute_recurring_dates($start, $interval, $count);
	if ($dates) {
		$item_data[] = [
			'name'  => __('Scheduled sessions', 'woodmart-child'),
			'value' => implode(', ', $dates),
		];
	}

	$item_data[] = [
		'name'  => __('Payment & confirmation', 'woodmart-child'),
		'value' => sprintf(
			__('Each recurring session is confirmed only after payment — please pay at least %d day(s) before your appointment. A reminder will be sent before each session.', 'woodmart-child'),
			wellness_get_recurring_payment_reminder_days()
		),
	];

	return $item_data;
}

/**
 * Appointment length in seconds for the given product, honouring a selected
 * duration option (session type) when provided.
 */
function wellness_recurring_duration_seconds($product, $duration_option = '')
{
	$duration = $product->get_duration();
	$unit     = $product->get_duration_unit();

	if ($duration_option !== '' && $duration_option !== null) {
		$options = json_decode(get_post_meta($product->get_id(), '_wc_appointment_duration_options', true), true);
		if (is_array($options) && isset($options[$duration_option]['duration']) && absint($options[$duration_option]['duration']) > 0) {
			return absint($options[$duration_option]['duration']) * 60;
		}
	}

	switch ($unit) {
		case 'hour':
			return $duration * HOUR_IN_SECONDS;
		case 'day':
			return $duration * DAY_IN_SECONDS;
		case 'minute':
		default:
			return $duration * MINUTE_IN_SECONDS;
	}
}

/**
 * AJAX: compute the upcoming recurring schedule for the pre-booking preview.
 * Checks each follow-up slot via wc_appointments_get_total_available_appointments_for_range()
 * and shifts to the next available slot (mirroring create_wc_appointment) when taken.
 */
add_action('wp_ajax_wellness_recurring_preview', 'wellness_recurring_preview');
add_action('wp_ajax_nopriv_wellness_recurring_preview', 'wellness_recurring_preview');
function wellness_recurring_preview()
{
	check_ajax_referer('wellness_recurring_preview', 'nonce');

	$product_id = absint($_POST['product_id'] ?? 0);
	$product    = wc_get_product($product_id);
	if (! $product || ! is_wc_appointment_product($product)) {
		wp_send_json_error(['message' => __('Invalid product.', 'woodmart-child')]);
	}

	$year  = absint($_POST['year'] ?? date('Y'));
	$month = absint($_POST['month'] ?? 0);
	$day   = absint($_POST['day'] ?? 0);
	$time  = sanitize_text_field($_POST['time'] ?? '');
	if (! $month || ! $day || ! $time) {
		wp_send_json_error(['message' => __('Please pick a date and time first.', 'woodmart-child')]);
	}

	$base_start = strtotime("$year-$month-$day $time");
	if (! $base_start) {
		wp_send_json_error(['message' => __('Invalid start time.', 'woodmart-child')]);
	}

	$interval = sanitize_text_field($_POST['interval'] ?? 'weekly');
	$count    = max(1, absint($_POST['count'] ?? 1));
	$max_count = max(1, intval(get_post_meta($product_id, '_wc_appointment_max_repeat_count', true) ?: 2));
	$count    = min($count, $max_count);
	$staff_id = absint($_POST['staff_id'] ?? 0);

	// Resolve the customer timezone (from the appointments_time_zone cookie) so the
	// preview shows the same local times the client picked on the slot picker.
	$customer_tz = isset($_COOKIE['appointments_time_zone']) ? sanitize_text_field(wp_unslash($_COOKIE['appointments_time_zone'])) : '';
	if (! in_array($customer_tz, timezone_identifiers_list(), true)) {
		$customer_tz = wc_timezone_string();
	}

	$duration_sec = wellness_recurring_duration_seconds($product, $_POST['duration_option'] ?? '');
	list($unit, $mult) = wellness_recurring_interval_map($interval);

	$max_date  = $product->get_max_date_a();
	$max_tstamp = strtotime("+{$max_date['value']} {$max_date['unit']}");

	$results = [];
	// Include the first (booked) session plus each follow-up, so the client sees the full schedule.
	for ($i = 0; $i <= $count; $i++) {
		$target_start = $i === 0 ? $base_start : strtotime('+' . ($i * $mult) . ' ' . $unit, $base_start);
		if ($target_start === false || $target_start > $max_tstamp) {
			continue;
		}
		$target_end = $target_start + $duration_sec;
		$shifted    = false;

		$available = wellness_recurring_slot_available($product, $target_start, $target_end, $staff_id);

		if (! $available) {
			// Shift to the next available slot (same logic as create_wc_appointment).
			while ($target_start + $duration_sec <= $max_tstamp) {
				$target_start += $duration_sec;
				$target_end    = $target_start + $duration_sec;
				if (wellness_recurring_slot_available($product, $target_start, $target_end, $staff_id)) {
					$shifted = true;
					break;
				}
			}
		}

		$results[] = [
			'display' => wellness_tz_format($target_start, $customer_tz, 'F j, Y \a\t g:i A'),
			'date'    => date_i18n(wc_appointments_date_format(), $target_start),
			'time'    => date_i18n(wc_appointments_time_format(), $target_start),
			'shifted' => $shifted,
			'notice'  => $shifted ? __('The exact slot was unavailable; we reserved the next available time.', 'woodmart-child') : '',
		];
	}

	wp_send_json(['sessions' => $results]);
}

/**
 * Whether a given range is bookable (no staff conflict, within availability rules).
 */
function wellness_recurring_slot_available($product, $start, $end, $staff_id)
{
	$res = wc_appointments_get_total_available_appointments_for_range(
		$product,
		$start,
		$end,
		$staff_id ? $staff_id : null,
		1
	);
	return $res && ! is_wp_error($res) && ! empty($res);
}

/**
 * Frontend JS/CSS for the recurring toggle + live schedule preview.
 */
add_action('woocommerce_after_appointment_form_output', 'wellness_recurring_form_js', 25, 2);
function wellness_recurring_form_js($position, $product_id)
{
	if ($position !== 'after') return;

	$product = wc_get_product($product_id);
	if (! $product || ! is_wc_appointment_product($product)) return;

	$enable = get_post_meta($product_id, '_wc_appointment_enable_recurring', true);
	$enable = ($enable === '') ? 'yes' : $enable; // default ON
	if ($enable !== 'yes') return;

	$nonce     = wp_create_nonce('wellness_recurring_preview');
	$ajax_url  = admin_url('admin-ajax.php');
	$max_count = max(1, intval(get_post_meta($product_id, '_wc_appointment_max_repeat_count', true) ?: 2));
	$reminder_days = wellness_get_recurring_payment_reminder_days();
?>
	<style>
		/* Hide repeat interval + count until the toggle is checked. */
		#wc-appointments-appointment-form .wellness-recurring-lead { display: none; }
		#wc-appointments-appointment-form .wellness-recurring-preview {
			margin: 10px 0;
			padding: 10px 12px;
			background: #f4f8ff;
			border-left: 4px solid #4f7cff;
			border-radius: 4px;
			color: #333;
			font-size: 14px;
		}
		#wc-appointments-appointment-form .wellness-recurring-preview ul { margin: 6px 0 0; padding-left: 18px; }
		#wc-appointments-appointment-form .wellness-recurring-preview em { color: #b54708; font-style: normal; display: block; }
	</style>
	<script>
	window.WELLNESS_RECURRING = {
		ajax: '<?php echo esc_js($ajax_url); ?>',
		nonce: '<?php echo esc_js($nonce); ?>',
		max_count: <?php echo (int) $max_count; ?>
	};
	(function() {
		if (typeof jQuery === 'undefined') { return; }
		jQuery(function($) {
			'use strict';
			var TIMEOUT = null;

		function isOn($form) {
			return $form.find('input[name="wc_appointments_field_recurring"]').is(':checked');
		}

		function toggle($form) {
			var on = isOn($form);
			$form.find('.wellness-recurring-lead').toggle(on);
			if (!on) {
				$form.find('.wellness-recurring-preview').hide().empty();
			} else {
				update($form);
			}
		}

		function payload($form) {
			var $picker = $form.find('.wc-appointments-date-picker');
			return {
				product_id: $picker.find('.picker').data('product_id') || $form.find('input[name="add-to-cart"]').val() || $form.find('input[name="appointable-product-id"]').val() || 0,
				day: $form.find('input[name="wc_appointments_field_start_date_day"]').val(),
				month: $form.find('input[name="wc_appointments_field_start_date_month"]').val(),
				year: $form.find('input[name="wc_appointments_field_start_date_year"]').val(),
				time: $form.find('input[name="wc_appointments_field_start_date_time"]').val(),
				interval: $form.find('select[name="wc_appointments_field_recurring_interval"]').val(),
				count: $form.find('input[name="wc_appointments_field_recurring_count"]').val(),
				staff_id: $form.find('select[name="wc_appointments_field_staff"]').val() || '',
				duration_option: $form.find('select[name="wc_appointments_field_duration_option"]').val() || ''
			};
		}

		function render(res) {
			var $pv = $('#wc-appointments-appointment-form .wellness-recurring-preview');
			if (!res || !res.sessions || !res.sessions.length) { $pv.hide().empty(); return; }
			var html = '<strong><?php echo esc_js(__('Your upcoming sessions:', 'woodmart-child')); ?></strong><ul>';
			$.each(res.sessions, function(i, s) {
				html += '<li>' + (s.display || (s.date + ' at ' + s.time)) + (s.shifted ? '<em>' + s.notice + '</em>' : '') + '</li>';
			});
			html += '</ul>';
			html += '<p class="wellness-recurring-preview-note"><?php echo esc_js(sprintf(
				__('Each recurring session is confirmed only after payment — please pay at least %d day(s) before your appointment.', 'woodmart-child'),
				$reminder_days
			)); ?></p>';
			$pv.html(html).show();
		}

		function request($form) {
			var p = payload($form);
			if (!p.product_id || !p.day || !p.month || !p.year || !p.time || !p.interval || !p.count) return;
			$.post(WELLNESS_RECURRING.ajax, $.extend({ action: 'wellness_recurring_preview', nonce: WELLNESS_RECURRING.nonce }, p))
				.done(render)
				.fail(function() {
					$('#wc-appointments-appointment-form .wellness-recurring-preview').hide().empty();
				});
		}

		function update($form) {
			clearTimeout(TIMEOUT);
			TIMEOUT = setTimeout(function() { request($form); }, 250);
		}

		$(document).on('change', '#wc-appointments-appointment-form input[name="wc_appointments_field_recurring"]', function() {
			toggle($(this).closest('#wc-appointments-appointment-form'));
		});

		$(document).on('change', '#wc-appointments-appointment-form select[name="wc_appointments_field_recurring_interval"], #wc-appointments-appointment-form input[name="wc_appointments_field_recurring_count"], #wc-appointments-appointment-form input[name="wc_appointments_field_start_date_time"], #wc-appointments-appointment-form input[name="wc_appointments_field_start_date_day"], #wc-appointments-appointment-form input[name="wc_appointments_field_start_date_month"], #wc-appointments-appointment-form input[name="wc_appointments_field_start_date_year"]', function() {
			var $form = $(this).closest('#wc-appointments-appointment-form');
			if (isOn($form)) update($form);
		});

			$(function() {
				var $form = $('#wc-appointments-appointment-form');
				if (!$form.length) return;
				if (!$form.find('.wellness-recurring-preview').length) {
					$form.append('<div class="wellness-recurring-preview" style="display:none;"></div>');
				}
				toggle($form);
			});
		});
	})();
	</script>
<?php
}


// ═══════════════════════════════════════════════════════════════════════════════
// Task 3 — First-time Customer Intake Form on Checkout
// ═══════════════════════════════════════════════════════════════════════════════

// ── Phase 1: Register custom post type 'customer_intake_form' ────────────

add_action('init', 'wellness_register_intake_cpt', 20);
function wellness_register_intake_cpt()
{
	register_post_type('customer_intake_form', [
		'labels'              => [
			'name'               => __('Intake Forms', 'woodmart-child'),
			'singular_name'      => __('Intake Form', 'woodmart-child'),
			'add_new'            => __('Add New', 'woodmart-child'),
			'add_new_item'       => __('Add New Intake Form', 'woodmart-child'),
			'edit_item'          => __('View Intake Form', 'woodmart-child'),
			'search_items'       => __('Search Intake Forms', 'woodmart-child'),
			'not_found'          => __('No intake forms found.', 'woodmart-child'),
		],
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => false, // added as submenu below
		'supports'            => ['title', 'custom-fields'],
		'capability_type'     => 'post',
		'map_meta_cap'        => true,
		'has_archive'         => false,
		'exclude_from_search' => true,
		'publicly_queryable'  => false,
	]);
}


// ── Phase 2: Condition check — should we show the intake form? ──────────

/**
 * Determine whether the intake form should appear at checkout for the current
 * customer.  Returns false (skip) when the billing email already has a
 * customer_intake_form CPT entry OR has any completed/processing orders.
 *
 * @return bool
 */
function wellness_should_show_intake_form()
{
	// Debug flag — force intake form to show for testing.
	if (defined('WELLNESS_INTAKE_DEBUG') && WELLNESS_INTAKE_DEBUG) {
		return true;
	}

	static $result = null;
	if ($result !== null) {
		return $result;
	}

	// Resolve the billing email from whatever source is available.
	$email = '';

	if (is_user_logged_in()) {
		$user  = wp_get_current_user();
		$email = get_user_meta($user->ID, 'billing_email', true) ?: $user->user_email;
	}

	// During checkout POST the billing_email field may already be set.
	if (empty($email) && ! empty($_POST['billing_email'])) {
		$email = sanitize_email(wp_unslash($_POST['billing_email']));
	}

	// AJAX update_checkout may not have user context — rely on POST.
	if (empty($email) && wp_doing_ajax() && ! empty($_POST['post_data'])) {
		parse_str($_POST['post_data'], $post_data);
		if (! empty($post_data['billing_email'])) {
			$email = sanitize_email($post_data['billing_email']);
		}
	}

	// Can't determine email — show the form (edge case).
	if (empty($email)) {
		$result = true;
		return $result;
	}

	// Check 1: Existing intake form for this email.
	$existing = get_posts([
		'post_type'      => 'customer_intake_form',
		'post_status'    => 'publish',
		'meta_key'       => '_intake_email',
		'meta_value'     => $email,
		'posts_per_page' => 1,
		'fields'         => 'ids',
	]);
	if (! empty($existing)) {
		$result = false;
		return $result;
	}

	// Check 2: Any completed/processing orders by billing email.
	// $orders = wc_get_orders([
	// 	'billing_email'  => $email,
	// 	'status'         => ['wc-completed', 'wc-processing'],
	// 	'limit'          => 1,
	// 	'return'         => 'ids',
	// ]);
	// if (! empty($orders)) {
	// 	$result = false;
	// 	return $result;
	// }

	$result = true;
	return $result;
}


// ── Phase 3: Inject intake fields into checkout ─────────────────────────

/**
 * Add intake form fields to the WooCommerce checkout fields array.
 */
add_filter('woocommerce_checkout_fields', 'wellness_add_intake_checkout_fields', 99999);
function wellness_add_intake_checkout_fields($fields)
{
	if (! wellness_should_show_intake_form()) {
		return $fields;
	}

	$comm_options = [
		'phone_call' => __('Phone Call', 'woodmart-child'),
		'email'      => __('Email', 'woodmart-child'),
		'text'       => __('Text Messages', 'woodmart-child'),
		'whatsapp'   => __('WhatsApp Message', 'woodmart-child'),
	];

	$fields['intake'] = [
		// ── Section header ──────────────────────────────────────────────
		'intake_section_header' => [
			'type'     => 'intake_section',
			'label'    => __('Client Information', 'woodmart-child'),
			'priority' => 5,
		],

		// ── Personal ────────────────────────────────────────────────────
		'intake_client_name' => [
			'type'              => 'text',
			'label'             => __('Client Name', 'woodmart-child'),
			'required'          => false,
			'class'             => ['form-row-wide'],
			'priority'          => 10,
		],
		'intake_birth_date' => [
			'type'              => 'text',
			'label'             => __('Birth Date', 'woodmart-child'),
			'placeholder'       => __('DD/MM/YYYY', 'woodmart-child'),
			'required'          => false,
			'class'             => ['form-row-wide'],
			'priority'          => 20,
		],
		'intake_address' => [
			'type'              => 'text',
			'label'             => __('Current Address', 'woodmart-child'),
			'required'          => false,
			'class'             => ['form-row-wide'],
			'priority'          => 30,
		],

		// ── Contact phones ──────────────────────────────────────────────
		'intake_mobile' => [
			'type'              => 'text',
			'label'             => __('Mobile', 'woodmart-child'),
			'required'          => false,
			'class'             => ['form-row-first'],
			'priority'          => 40,
		],
		'intake_mobile_text' => [
			'type'              => 'checkbox',
			'label'             => __('OK to text message', 'woodmart-child'),
			'required'          => false,
			'class'             => ['form-row-last'],
			'priority'          => 45,
		],
		'intake_home_phone' => [
			'type'              => 'text',
			'label'             => __('Home Phone', 'woodmart-child'),
			'required'          => false,
			'class'             => ['form-row-first'],
			'priority'          => 50,
		],
		'intake_home_voicemail' => [
			'type'              => 'checkbox',
			'label'             => __('OK to leave detailed voice message', 'woodmart-child'),
			'required'          => false,
			'class'             => ['form-row-last'],
			'priority'          => 55,
		],
		'intake_work_phone' => [
			'type'              => 'text',
			'label'             => __('Work Phone', 'woodmart-child'),
			'required'          => false,
			'class'             => ['form-row-first'],
			'priority'          => 60,
		],
		'intake_work_voicemail' => [
			'type'              => 'checkbox',
			'label'             => __('OK to leave voice message', 'woodmart-child'),
			'required'          => false,
			'class'             => ['form-row-last'],
			'priority'          => 65,
		],
		'intake_work_reminders' => [
			'type'              => 'checkbox',
			'label'             => __('Appointment reminders', 'woodmart-child'),
			'required'          => false,
			'class'             => ['form-row-wide'],
			'priority'          => 67,
		],

		// ── Preferred communication ─────────────────────────────────────
		'intake_preferred_comm' => [
			'type'              => 'select',
			'label'             => __('Preferred way of communication for appointment reminders', 'woodmart-child'),
			'required'          => false,
			'options'           => $comm_options,
			'class'             => ['form-row-wide'],
			'priority'          => 70,
		],

		// ── Emergency contact section ───────────────────────────────────
		'intake_emergency_header' => [
			'type'     => 'intake_subsection',
			'label'    => __('Emergency Contact', 'woodmart-child'),
			'priority' => 75,
		],
		'intake_emergency_name' => [
			'type'              => 'text',
			'label'             => __('Contact Name', 'woodmart-child'),
			'required'          => false,
			'class'             => ['form-row-first'],
			'priority'          => 80,
		],
		'intake_emergency_relation' => [
			'type'              => 'text',
			'label'             => __('Relationship to Client', 'woodmart-child'),
			'required'          => false,
			'class'             => ['form-row-last'],
			'priority'          => 85,
		],
		'intake_emergency_home_phone' => [
			'type'              => 'text',
			'label'             => __('Home Phone', 'woodmart-child'),
			'required'          => false,
			'class'             => ['form-row-first'],
			'priority'          => 90,
		],
		'intake_emergency_mobile' => [
			'type'              => 'text',
			'label'             => __('Mobile', 'woodmart-child'),
			'required'          => false,
			'class'             => ['form-row-last'],
			'priority'          => 95,
		],
		'intake_emergency_voicemail' => [
			'type'              => 'checkbox',
			'label'             => __('OK to leave detailed voice message', 'woodmart-child'),
			'required'          => false,
			'class'             => ['form-row-first'],
			'priority'          => 100,
		],
		'intake_emergency_text' => [
			'type'              => 'checkbox',
			'label'             => __('OK to text message', 'woodmart-child'),
			'required'          => false,
			'class'             => ['form-row-last'],
			'priority'          => 105,
		],

		// ── Additional information section ──────────────────────────────
		'intake_additional_header' => [
			'type'     => 'intake_subsection',
			'label'    => __('Additional Information', 'woodmart-child'),
			'priority' => 110,
		],
		'intake_current_services' => [
			'type'              => 'select',
			'label'             => __('Are you currently receiving psychological services, professional counseling, psychiatric services, or any other mental health services?', 'woodmart-child'),
			'required'          => false,
			'options'           => ['yes' => __('Yes', 'woodmart-child'), 'no' => __('No', 'woodmart-child')],
			'class'             => ['form-row-wide'],
			'priority'          => 115,
		],
		'intake_past_counseling' => [
			'type'              => 'select',
			'label'             => __('Have you had any Counseling/psychotherapy services in the past?', 'woodmart-child'),
			'required'          => false,
			'options'           => ['yes' => __('Yes', 'woodmart-child'), 'no' => __('No', 'woodmart-child')],
			'class'             => ['form-row-wide'],
			'priority'          => 120,
		],
		'intake_medications' => [
			'type'              => 'select',
			'label'             => __('Are you currently taking any psychiatric prescription medications?', 'woodmart-child'),
			'required'          => false,
			'options'           => ['yes' => __('Yes', 'woodmart-child'), 'no' => __('No', 'woodmart-child')],
			'class'             => ['form-row-wide'],
			'priority'          => 125,
		],
		'intake_expectations' => [
			'type'              => 'textarea',
			'label'             => __('What are your expectations of therapy?', 'woodmart-child'),
			'required'          => false,
			'class'             => ['form-row-wide'],
			'priority'          => 130,
		],

		// ── Referral source ─────────────────────────────────────────────
		'intake_referral_header' => [
			'type'     => 'intake_subsection',
			'label'    => __('How did you hear about The Wellness Hub?', 'woodmart-child'),
			'priority' => 135,
		],
		'intake_referral_friend_name' => [
			'type'              => 'text',
			'label'             => __('Friend (name)', 'woodmart-child'),
			'required'          => false,
			'class'             => ['form-row-wide'],
			'priority'          => 140,
		],
		'intake_referral_doctor_name' => [
			'type'              => 'text',
			'label'             => __('Doctor/Therapist (name)', 'woodmart-child'),
			'required'          => false,
			'class'             => ['form-row-wide'],
			'priority'          => 145,
		],
		'intake_referral_family' => [
			'type'              => 'checkbox',
			'label'             => __('Family', 'woodmart-child'),
			'required'          => false,
			'class'             => ['form-row-wide'],
			'priority'          => 150,
		],
		'intake_referral_location' => [
			'type'              => 'checkbox',
			'label'             => __('Location is close to home/work', 'woodmart-child'),
			'required'          => false,
			'class'             => ['form-row-wide'],
			'priority'          => 155,
		],
		'intake_referral_search' => [
			'type'              => 'checkbox',
			'label'             => __('Online Search', 'woodmart-child'),
			'required'          => false,
			'class'             => ['form-row-wide'],
			'priority'          => 160,
		],
		'intake_referral_facebook' => [
			'type'              => 'checkbox',
			'label'             => __('Our Facebook Page', 'woodmart-child'),
			'required'          => false,
			'class'             => ['form-row-wide'],
			'priority'          => 165,
		],
	];

	return $fields;
}


// ── Render section header rows in the checkout form ─────────────────────
// WooCommerce does not natively support "section header" field types, so we
// inject <h3>/<h4> headings before the rows that carry type 'intake_section'
// or 'intake_subsection'.

add_action('woocommerce_before_checkout_form', 'wellness_intake_section_headers_css', 5);
function wellness_intake_section_headers_css()
{
	if (! wellness_should_show_intake_form()) {
		return;
	}
?>
	<style>
		/* ── Hide WooCommerce default table-row output for section headers ── */
		.woocommerce-checkout .intake-section-header th,
		.woocommerce-checkout .intake-subsection-header th {
			display: none;
		}

		.woocommerce-checkout .intake-section-header td,
		.woocommerce-checkout .intake-subsection-header td {
			padding-left: 0;
		}
	</style>
<?php
}

add_filter('woocommerce_checkout_fields', 'wellness_mark_section_header_rows', 100000);
function wellness_mark_section_header_rows($fields)
{
	if (empty($fields['intake'])) {
		return $fields;
	}
	foreach ($fields['intake'] as $key => &$field) {
		if (isset($field['type']) && $field['type'] === 'intake_section') {
			$field['class'][] = 'intake-section-header';
			$field['required'] = false;
		}
		if (isset($field['type']) && $field['type'] === 'intake_subsection') {
			$field['class'][] = 'intake-subsection-header';
			$field['required'] = false;
		}
	}
	unset($field);
	return $fields;
}


// ── Render intake fields in 5 granular phases ──────────────────────────
// Each phase ≤ 5 fields for manageable step size.

add_action('woocommerce_checkout_after_customer_details', 'wellness_intake_phase_personal', 7);
function wellness_intake_phase_personal()
{
	wellness_intake_render_phase([
		'intake_client_name',
		'intake_birth_date',
		'intake_address',
		'intake_mobile',
		'intake_mobile_text',
	], __('Personal Details', 'woodmart-child'));
}

add_action('woocommerce_checkout_after_customer_details', 'wellness_intake_phase_phones', 17);
function wellness_intake_phase_phones()
{
	wellness_intake_render_phase([
		'intake_home_phone',
		'intake_home_voicemail',
		'intake_work_phone',
		'intake_work_voicemail',
		'intake_preferred_comm',
	], __('Phone & Preferences', 'woodmart-child'));
}

add_action('woocommerce_checkout_after_customer_details', 'wellness_intake_phase_emergency', 27);
function wellness_intake_phase_emergency()
{
	wellness_intake_render_phase([
		'intake_emergency_name',
		'intake_emergency_relation',
		'intake_emergency_home_phone',
		'intake_emergency_mobile',
		'intake_emergency_voicemail',
	], __('Emergency Contact', 'woodmart-child'));
}

add_action('woocommerce_checkout_after_customer_details', 'wellness_intake_phase_health', 37);
function wellness_intake_phase_health()
{
	wellness_intake_render_phase([
		'intake_current_services',
		'intake_past_counseling',
		'intake_medications',
		'intake_expectations',
		'intake_emergency_text',
	], __('Health Background', 'woodmart-child'));
}

add_action('woocommerce_checkout_after_customer_details', 'wellness_intake_phase_referral', 47);
function wellness_intake_phase_referral()
{
	wellness_intake_render_phase([
		'intake_referral_friend_name',
		'intake_referral_doctor_name',
		'intake_referral_family',
		'intake_referral_location',
		'intake_referral_search',
		'intake_referral_facebook',
	], __('How You Heard About Us', 'woodmart-child'));
}

function wellness_intake_render_phase($keys, $heading = '')
{
	if (! wellness_should_show_intake_form()) return;

	$checkout = WC()->checkout();
	$fields   = $checkout->get_checkout_fields('intake');
	if (empty($fields)) return;

	echo '<div class="wellness-intake-fields">';
	if ($heading) {
		echo '<h4 class="wellness-intake-subsection-heading">' . esc_html($heading) . '</h4>';
	}
	foreach ($keys as $key) {
		if (! isset($fields[$key])) continue;
		wellness_render_intake_field($key, $fields[$key], $checkout);
	}
	echo '</div>';
}

function wellness_render_intake_field($key, $field, $checkout)
{
	$classes = isset($field['class']) ? (array) $field['class'] : [];
	if (in_array('intake-section-header', $classes, true) || in_array('intake-subsection-header', $classes, true)) {
		return; // skip — we render our own heading
	}

	$type        = $field['type'] ?? 'text';
	$label       = $field['label'] ?? '';
	$required    = ! empty($field['required']);
	$value       = $checkout->get_value($key);
	$placeholder = $field['placeholder'] ?? '';
	$options     = $field['options'] ?? [];

	echo '<div class="wellness-field wellness-field--' . esc_attr($type) . '">';

	if ($type === 'checkbox') {
		// ── Pill-toggle checkbox ─────────────────────────────────────
		echo '<label class="wellness-toggle">';
		echo '<input type="checkbox" name="' . esc_attr($key) . '" id="' . esc_attr($key) . '" value="1" ' . checked($value, '1', false) . ' />';
		echo '<span class="wellness-toggle__track"><span class="wellness-toggle__thumb"></span></span>';
		echo '<span class="wellness-toggle__label">' . esc_html($label) . '</span>';
		echo '</label>';
	} elseif ($type === 'select') {
		// ── Standard select with label above ─────────────────────────
		echo '<label for="' . esc_attr($key) . '" class="wellness-label">' . esc_html($label) . ($required ? ' <span class="wellness-star">*</span>' : '') . '</label>';
		echo '<div class="wellness-select-wrapper">';
		echo '<select name="' . esc_attr($key) . '" id="' . esc_attr($key) . '" class="wellness-select">';
		echo '<option value=""> </option>';
		foreach ($options as $opt_val => $opt_label) {
			echo '<option value="' . esc_attr($opt_val) . '" ' . selected($value, $opt_val, false) . '>' . esc_html($opt_label) . '</option>';
		}
		echo '</select>';
		echo '<span class="wellness-select-chevron"><svg width="12" height="7" viewBox="0 0 12 7"><path d="M1 1l5 5 5-5" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg></span>';
		echo '</div>';
	} elseif ($type === 'textarea') {
		// ── Standard textarea with label above ───────────────────────
		if ($label) {
			echo '<label for="' . esc_attr($key) . '" class="wellness-label">' . esc_html($label) . ($required ? ' <span class="wellness-star">*</span>' : '') . '</label>';
		}
		echo '<textarea name="' . esc_attr($key) . '" id="' . esc_attr($key) . '" class="wellness-textarea" rows="3" placeholder="' . esc_attr($placeholder) . '">' . esc_textarea($value) . '</textarea>';
	} else {
		// ── Standard text input with label above ─────────────────────
		if ($label) {
			echo '<label for="' . esc_attr($key) . '" class="wellness-label">' . esc_html($label) . ($required ? ' <span class="wellness-star">*</span>' : '') . '</label>';
		}
		echo '<input type="' . esc_attr($type) . '" name="' . esc_attr($key) . '" id="' . esc_attr($key) . '" class="wellness-input" value="' . esc_attr($value) . '" placeholder="' . esc_attr($placeholder) . '" ' . ($required ? 'required' : '') . ' />';
	}

	echo '</div>';
}


// ── Phase 7: Validate required intake fields ────────────────────────────

// add_action('woocommerce_checkout_process', 'wellness_validate_intake_fields');
// function wellness_validate_intake_fields()
// {
// 	if (! wellness_should_show_intake_form()) {
// 		return;
// 	}

// 	if (empty($_POST['intake_client_name'])) {
// 		wc_add_notice(__('Please enter your full name in the Client Information section.', 'woodmart-child'), 'error');
// 	}

// 	if (empty($_POST['intake_mobile'])) {
// 		wc_add_notice(__('Please enter your mobile number in the Client Information section.', 'woodmart-child'), 'error');
// 	}
// }


// ── Phase 4: Save intake form to CPT on order processed ─────────────────

add_action('woocommerce_checkout_order_processed', 'wellness_save_intake_form', 20);
function wellness_save_intake_form($order_id)
{
	if (! wellness_should_show_intake_form()) {
		return;
	}

	$order = wc_get_order($order_id);
	if (! $order) {
		return;
	}

	$email = $order->get_billing_email();
	if (empty($email)) {
		return;
	}

	// Double-check: don't create a duplicate if one was already created
	// between the render and the submit (race condition edge case).
	$existing = get_posts([
		'post_type'      => 'customer_intake_form',
		'post_status'    => 'publish',
		'meta_key'       => '_intake_email',
		'meta_value'     => $email,
		'posts_per_page' => 1,
		'fields'         => 'ids',
	]);
	if (! empty($existing)) {
		return;
	}

	$client_name = isset($_POST['intake_client_name'])
		? sanitize_text_field(wp_unslash($_POST['intake_client_name']))
		: '';

	$post_id = wp_insert_post([
		'post_type'   => 'customer_intake_form',
		'post_title'  => trim($client_name . ' — ' . wp_date('Y-m-d')),
		'post_status' => 'publish',
	]);

	if (is_wp_error($post_id) || ! $post_id) {
		return;
	}

	// ── Simple text / select fields ─────────────────────────────────────
	$text_fields = [
		'intake_client_name',
		'intake_birth_date',
		'intake_address',
		'intake_mobile',
		'intake_home_phone',
		'intake_work_phone',
		'intake_preferred_comm',
		'intake_emergency_name',
		'intake_emergency_relation',
		'intake_emergency_home_phone',
		'intake_emergency_mobile',
		'intake_current_services',
		'intake_past_counseling',
		'intake_medications',
		'intake_referral_friend_name',
		'intake_referral_doctor_name',
	];

	foreach ($text_fields as $field_key) {
		if (isset($_POST[$field_key])) {
			$value = sanitize_text_field(wp_unslash($_POST[$field_key]));
			update_post_meta($post_id, '_' . $field_key, $value);
		}
	}

	// ── Textarea (expectations) ─────────────────────────────────────────
	if (isset($_POST['intake_expectations'])) {
		$value = sanitize_textarea_field(wp_unslash($_POST['intake_expectations']));
		update_post_meta($post_id, '_intake_expectations', $value);
	}

	// ── Checkbox fields (stored as 'yes' / 'no') ─────────────────────────
	$checkbox_fields = [
		'intake_mobile_text',
		'intake_home_voicemail',
		'intake_work_voicemail',
		'intake_work_reminders',
		'intake_emergency_voicemail',
		'intake_emergency_text',
		'intake_referral_family',
		'intake_referral_location',
		'intake_referral_search',
		'intake_referral_facebook',
	];

	foreach ($checkbox_fields as $field_key) {
		$value = ! empty($_POST[$field_key]) ? 'yes' : 'no';
		update_post_meta($post_id, '_' . $field_key, $value);
	}

	// ── Linkage meta ────────────────────────────────────────────────────
	update_post_meta($post_id, '_intake_email', $email);
	update_post_meta($post_id, '_intake_order_id', $order_id);
	update_post_meta($post_id, '_intake_customer_id', $order->get_customer_id());
}


// ── Phase 5: Admin submenu & CPT list table customizations ──────────────

add_action('admin_menu', 'wellness_intake_admin_submenu', 20);
function wellness_intake_admin_submenu()
{
	add_submenu_page(
		'edit.php?post_type=wc_appointment',
		__('Intake Forms', 'woodmart-child'),
		__('Intake Forms', 'woodmart-child'),
		'edit_posts', // shop_staff has this capability
		'edit.php?post_type=customer_intake_form'
	);
}

// ── Custom columns on the CPT list table ────────────────────────────────

add_filter('manage_customer_intake_form_posts_columns', 'wellness_intake_list_columns');
function wellness_intake_list_columns($columns)
{
	$new = [];
	foreach ($columns as $key => $label) {
		$new[$key] = $label;
		if ($key === 'title') {
			$new['intake_email']  = __('Email', 'woodmart-child');
			$new['intake_phone']  = __('Phone', 'woodmart-child');
			$new['intake_order']  = __('Order', 'woodmart-child');
		}
	}
	unset($new['date']);
	return $new;
}

add_action('manage_customer_intake_form_posts_custom_column', 'wellness_intake_list_column_content', 10, 2);
function wellness_intake_list_column_content($column, $post_id)
{
	switch ($column) {
		case 'intake_email':
			$email = get_post_meta($post_id, '_intake_email', true);
			echo $email ? sprintf(
				'<a href="mailto:%1$s">%1$s</a>',
				esc_html($email)
			) : '<span aria-hidden="true">—</span>';
			break;

		case 'intake_phone':
			$mobile = get_post_meta($post_id, '_intake_mobile', true);
			echo $mobile ? esc_html($mobile) : '<span aria-hidden="true">—</span>';
			break;

		case 'intake_order':
			$order_id = (int) get_post_meta($post_id, '_intake_order_id', true);
			if ($order_id) {
				$order = wc_get_order($order_id);
				if ($order) {
					printf(
						'<a href="%s">#%s</a>',
						esc_url($order->get_edit_order_url()),
						esc_html($order->get_order_number())
					);
				} else {
					echo '#' . absint($order_id);
				}
			} else {
				echo '<span aria-hidden="true">—</span>';
			}
			break;
	}
}

// ── Read-only metabox on single CPT edit screen ────────────────────────

add_action('add_meta_boxes', 'wellness_intake_view_metabox');
function wellness_intake_view_metabox()
{
	add_meta_box(
		'wellness_intake_details',
		__('Intake Form Details', 'woodmart-child'),
		'wellness_render_intake_metabox',
		'customer_intake_form',
		'normal',
		'high'
	);
}

function wellness_render_intake_metabox($post)
{
	$fields = [
		__('Client Name', 'woodmart-child')          => '_intake_client_name',
		__('Birth Date', 'woodmart-child')           => '_intake_birth_date',
		__('Current Address', 'woodmart-child')      => '_intake_address',
		__('Mobile', 'woodmart-child')               => '_intake_mobile',
		__('OK to text (mobile)', 'woodmart-child')   => '_intake_mobile_text',
		__('Home Phone', 'woodmart-child')           => '_intake_home_phone',
		__('OK voicemail (home)', 'woodmart-child')   => '_intake_home_voicemail',
		__('Work Phone', 'woodmart-child')           => '_intake_work_phone',
		__('OK voicemail (work)', 'woodmart-child')   => '_intake_work_voicemail',
		__('Appt reminders (work)', 'woodmart-child') => '_intake_work_reminders',
		__('Preferred Communication', 'woodmart-child') => '_intake_preferred_comm',
		__('Emergency — Name', 'woodmart-child')            => '_intake_emergency_name',
		__('Emergency — Relation', 'woodmart-child')        => '_intake_emergency_relation',
		__('Emergency — Home Phone', 'woodmart-child')      => '_intake_emergency_home_phone',
		__('Emergency — Mobile', 'woodmart-child')          => '_intake_emergency_mobile',
		__('Emergency — OK voicemail', 'woodmart-child')    => '_intake_emergency_voicemail',
		__('Emergency — OK to text', 'woodmart-child')      => '_intake_emergency_text',
		__('Currently receiving services?', 'woodmart-child') => '_intake_current_services',
		__('Past counseling?', 'woodmart-child')              => '_intake_past_counseling',
		__('Taking psychiatric meds?', 'woodmart-child')      => '_intake_medications',
		__('Expectations of therapy', 'woodmart-child')       => '_intake_expectations',
		__('Referral — Friend', 'woodmart-child')             => '_intake_referral_friend_name',
		__('Referral — Doctor/Therapist', 'woodmart-child')   => '_intake_referral_doctor_name',
		__('Referral — Family', 'woodmart-child')             => '_intake_referral_family',
		__('Referral — Location', 'woodmart-child')           => '_intake_referral_location',
		__('Referral — Online Search', 'woodmart-child')      => '_intake_referral_search',
		__('Referral — Facebook', 'woodmart-child')           => '_intake_referral_facebook',
	];

	$checkbox_fields = [
		'_intake_mobile_text',
		'_intake_home_voicemail',
		'_intake_work_voicemail',
		'_intake_work_reminders',
		'_intake_emergency_voicemail',
		'_intake_emergency_text',
		'_intake_referral_family',
		'_intake_referral_location',
		'_intake_referral_search',
		'_intake_referral_facebook',
	];

	$yn_fields = [
		'_intake_current_services',
		'_intake_past_counseling',
		'_intake_medications',
	];

	$comm_labels = [
		'phone_call' => __('Phone Call', 'woodmart-child'),
		'email'      => __('Email', 'woodmart-child'),
		'text'       => __('Text Messages', 'woodmart-child'),
		'whatsapp'   => __('WhatsApp Message', 'woodmart-child'),
	];

	$order_id = (int) get_post_meta($post->ID, '_intake_order_id', true);
	$email    = get_post_meta($post->ID, '_intake_email', true);

	echo '<div style="max-width:700px;">';

	if ($order_id) {
		$order = wc_get_order($order_id);
		if ($order) {
			printf(
				'<p><strong>%s:</strong> <a href="%s">#%s</a></p>',
				esc_html__('Linked Order', 'woodmart-child'),
				esc_url($order->get_edit_order_url()),
				esc_html($order->get_order_number())
			);
		}
	}
	if ($email) {
		printf(
			'<p><strong>%s:</strong> <a href="mailto:%1$s">%1$s</a></p>',
			esc_html__('Email', 'woodmart-child'),
			esc_html($email)
		);
	}

	echo '<table class="widefat striped" style="margin-top:12px;"><tbody>';

	foreach ($fields as $label => $meta_key) {
		$raw = get_post_meta($post->ID, $meta_key, true);

		if (in_array($meta_key, $checkbox_fields, true)) {
			$display = ($raw === 'yes')
				? '<span style="color:#2e7d32;">&#10003; ' . esc_html__('Yes', 'woodmart-child') . '</span>'
				: '<span style="color:#aaa;">—</span>';
		} elseif (in_array($meta_key, $yn_fields, true)) {
			$display = ($raw === 'yes' || $raw === 'no')
				? esc_html(ucfirst($raw))
				: '<span style="color:#aaa;">—</span>';
		} elseif ($meta_key === '_intake_preferred_comm') {
			$display = isset($comm_labels[$raw])
				? esc_html($comm_labels[$raw])
				: '<span style="color:#aaa;">—</span>';
		} elseif ($meta_key === '_intake_expectations') {
			$display = $raw ? '<p style="white-space:pre-wrap;margin:4px 0;">' . esc_html($raw) . '</p>' : '<span style="color:#aaa;">—</span>';
		} else {
			$display = $raw ? esc_html($raw) : '<span style="color:#aaa;">—</span>';
		}

		printf(
			'<tr><th style="width:220px;text-align:left;padding:6px 8px;">%s</th><td style="padding:6px 8px;">%s</td></tr>',
			esc_html($label),
			$display
		);
	}

	echo '</tbody></table></div>';
}


// ── Phase 6: Show intake summary on the admin order detail page ─────────

add_action('woocommerce_admin_order_data_after_billing_address', 'wellness_show_intake_on_order');
function wellness_show_intake_on_order($order)
{
	$email = $order->get_billing_email();
	if (empty($email)) {
		return;
	}

	$intake = get_posts([
		'post_type'      => 'customer_intake_form',
		'post_status'    => 'publish',
		'meta_key'       => '_intake_email',
		'meta_value'     => $email,
		'posts_per_page' => 1,
	]);

	if (empty($intake)) {
		return;
	}

	$post   = $intake[0];
	$edit_url = get_edit_post_link($post->ID, '');

	$name     = get_post_meta($post->ID, '_intake_client_name', true);
	$birth    = get_post_meta($post->ID, '_intake_birth_date', true);
	$mobile   = get_post_meta($post->ID, '_intake_mobile', true);
	$home     = get_post_meta($post->ID, '_intake_home_phone', true);
	$pref_comm = get_post_meta($post->ID, '_intake_preferred_comm', true);
	$emerg_name = get_post_meta($post->ID, '_intake_emergency_name', true);
	$emerg_mob  = get_post_meta($post->ID, '_intake_emergency_mobile', true);
	$meds        = get_post_meta($post->ID, '_intake_medications', true);
	$expectations = get_post_meta($post->ID, '_intake_expectations', true);

	$comm_labels = [
		'phone_call' => __('Phone Call', 'woodmart-child'),
		'email'      => __('Email', 'woodmart-child'),
		'text'       => __('Text Messages', 'woodmart-child'),
		'whatsapp'   => __('WhatsApp Message', 'woodmart-child'),
	];

?>
	<div style="clear:both;margin-top:16px;padding:12px;background:#f9f9f9;border:1px solid #e5e5e5;border-radius:4px;">
		<h3 style="margin:0 0 8px;">
			<?php esc_html_e('Client Intake Form', 'woodmart-child'); ?>
			<?php if ($edit_url): ?>
				<a href="<?php echo esc_url($edit_url); ?>" style="font-weight:400;font-size:12px;margin-left:8px;">
					<?php esc_html_e('View full form →', 'woodmart-child'); ?>
				</a>
			<?php endif; ?>
		</h3>
		<table style="width:100%;border-collapse:collapse;">
			<?php if ($name): ?>
				<tr>
					<td style="padding:2px 8px 2px 0;font-weight:600;width:100px;"><?php esc_html_e('Name', 'woodmart-child'); ?></td>
					<td><?php echo esc_html($name); ?></td>
				</tr>
			<?php endif; ?>
			<?php if ($birth): ?>
				<tr>
					<td style="padding:2px 8px 2px 0;font-weight:600;"><?php esc_html_e('Birth Date', 'woodmart-child'); ?></td>
					<td><?php echo esc_html($birth); ?></td>
				</tr>
			<?php endif; ?>
			<?php if ($mobile): ?>
				<tr>
					<td style="padding:2px 8px 2px 0;font-weight:600;"><?php esc_html_e('Mobile', 'woodmart-child'); ?></td>
					<td><?php echo esc_html($mobile); ?></td>
				</tr>
			<?php endif; ?>
			<?php if ($home): ?>
				<tr>
					<td style="padding:2px 8px 2px 0;font-weight:600;"><?php esc_html_e('Home', 'woodmart-child'); ?></td>
					<td><?php echo esc_html($home); ?></td>
				</tr>
			<?php endif; ?>
			<?php if ($pref_comm && isset($comm_labels[$pref_comm])): ?>
				<tr>
					<td style="padding:2px 8px 2px 0;font-weight:600;"><?php esc_html_e('Preferred', 'woodmart-child'); ?></td>
					<td><?php echo esc_html($comm_labels[$pref_comm]); ?></td>
				</tr>
			<?php endif; ?>
			<?php if ($emerg_name): ?>
				<tr>
					<td style="padding:2px 8px 2px 0;font-weight:600;"><?php esc_html_e('Emergency', 'woodmart-child'); ?></td>
					<td><?php echo esc_html($emerg_name); ?><?php echo $emerg_mob ? ' — ' . esc_html($emerg_mob) : ''; ?></td>
				</tr>
			<?php endif; ?>
			<?php if ($meds): ?>
				<tr>
					<td style="padding:2px 8px 2px 0;font-weight:600;"><?php esc_html_e('Medications', 'woodmart-child'); ?></td>
					<td><?php echo esc_html(ucfirst($meds)); ?></td>
				</tr>
			<?php endif; ?>
		</table>
		<?php if ($expectations): ?>
			<p style="margin:8px 0 0;font-style:italic;color:#555;"><?php echo esc_html(wp_trim_words($expectations, 30, '…')); ?></p>
		<?php endif; ?>
	</div>
<?php
}


/**
 * Fetch intake form data for a client email and return an HTML summary table
 * suitable for inclusion in admin/therapist notification emails.
 *
 * @param string $email  Client billing email.
 * @return string  HTML table rows, or empty string if no intake found.
 */
function wellness_get_intake_summary_for_email($email)
{
	if (empty($email)) {
		return '';
	}

	$intake = get_posts([
		'post_type'      => 'customer_intake_form',
		'post_status'    => 'publish',
		'meta_key'       => '_intake_email',
		'meta_value'     => $email,
		'posts_per_page' => 1,
	]);

	if (empty($intake)) {
		return '';
	}

	$post_id = $intake[0]->ID;

	$rows = [
		__('Client Name', 'woodmart-child')            => '_intake_client_name',
		__('Birth Date', 'woodmart-child')             => '_intake_birth_date',
		__('Address', 'woodmart-child')                => '_intake_address',
		__('Mobile', 'woodmart-child')                 => '_intake_mobile',
		__('Home Phone', 'woodmart-child')             => '_intake_home_phone',
		__('Work Phone', 'woodmart-child')             => '_intake_work_phone',
		__('Preferred Comm', 'woodmart-child')          => '_intake_preferred_comm',
		__('Emergency Contact', 'woodmart-child')       => '_intake_emergency_name',
		__('Emergency Relation', 'woodmart-child')      => '_intake_emergency_relation',
		__('Emergency Mobile', 'woodmart-child')        => '_intake_emergency_mobile',
		__('Current Services', 'woodmart-child')        => '_intake_current_services',
		__('Past Counseling', 'woodmart-child')         => '_intake_past_counseling',
		__('Psychiatric Meds', 'woodmart-child')        => '_intake_medications',
	];

	$comm_labels = [
		'phone_call' => __('Phone Call', 'woodmart-child'),
		'email'      => __('Email', 'woodmart-child'),
		'text'       => __('Text Messages', 'woodmart-child'),
		'whatsapp'   => __('WhatsApp Message', 'woodmart-child'),
	];

	$html = '';

	foreach ($rows as $label => $meta_key) {
		$raw = get_post_meta($post_id, $meta_key, true);
		if (empty($raw) || $raw === 'no') {
			continue;
		}

		$display = $raw;
		if ($meta_key === '_intake_preferred_comm' && isset($comm_labels[$raw])) {
			$display = $comm_labels[$raw];
		} elseif (in_array($raw, ['yes', 'no'], true)) {
			$display = ucfirst($raw);
		}

		$html .= '<tr>';
		$html .= '<th style="text-align:left;background:#f7f7f7;width:38%;padding:10px 14px;font-weight:600;">' . esc_html($label) . '</th>';
		$html .= '<td style="text-align:left;padding:10px 14px;">' . esc_html($display) . '</td>';
		$html .= '</tr>';
	}

	// Referral sources
	$referral_sources = [
		'_intake_referral_friend_name' => __('Friend (name)', 'woodmart-child'),
		'_intake_referral_doctor_name' => __('Doctor/Therapist (name)', 'woodmart-child'),
		'_intake_referral_family'      => __('Family', 'woodmart-child'),
		'_intake_referral_location'    => __('Location', 'woodmart-child'),
		'_intake_referral_search'      => __('Online Search', 'woodmart-child'),
		'_intake_referral_facebook'    => __('Facebook', 'woodmart-child'),
	];

	$referral_items = [];
	foreach ($referral_sources as $meta_key => $label) {
		$raw = get_post_meta($post_id, $meta_key, true);
		if ($meta_key === '_intake_referral_friend_name' || $meta_key === '_intake_referral_doctor_name') {
			if (! empty($raw)) {
				$referral_items[] = $label . ': ' . $raw;
			}
		} elseif ($raw === 'yes') {
			$referral_items[] = $label;
		}
	}

	if (! empty($referral_items)) {
		$html .= '<tr>';
		$html .= '<th style="text-align:left;background:#f7f7f7;width:38%;padding:10px 14px;font-weight:600;">' . esc_html__('Referral', 'woodmart-child') . '</th>';
		$html .= '<td style="text-align:left;padding:10px 14px;">' . esc_html(implode(', ', $referral_items)) . '</td>';
		$html .= '</tr>';
	}

	// Expectations (trimmed)
	$expectations = get_post_meta($post_id, '_intake_expectations', true);
	if (! empty($expectations)) {
		$html .= '<tr>';
		$html .= '<th style="text-align:left;background:#f7f7f7;width:38%;padding:10px 14px;font-weight:600;">' . esc_html__('Expectations', 'woodmart-child') . '</th>';
		$html .= '<td style="text-align:left;padding:10px 14px;font-style:italic;">' . esc_html(wp_trim_words($expectations, 40, '…')) . '</td>';
		$html .= '</tr>';
	}

	if (empty($html)) {
		return '';
	}

	return $html;
}


// ═══════════════════════════════════════════════════════════════════════════════
// Multi-Step Checkout — AJAX endpoint for real-time intake condition check
// ═══════════════════════════════════════════════════════════════════════════════

add_action('wp_ajax_wellness_check_intake', 'wellness_ajax_check_intake');
add_action('wp_ajax_nopriv_wellness_check_intake', 'wellness_ajax_check_intake');
function wellness_ajax_check_intake()
{
	$email = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';

	if (empty($email)) {
		wp_send_json(['show_intake' => false, 'reason' => 'no_email']);
	}

	// Check 1: Existing intake form for this email.
	$existing = get_posts([
		'post_type'      => 'customer_intake_form',
		'post_status'    => 'publish',
		'meta_key'       => '_intake_email',
		'meta_value'     => $email,
		'posts_per_page' => 1,
		'fields'         => 'ids',
	]);
	if (! empty($existing)) {
		wp_send_json(['show_intake' => false, 'reason' => 'existing_intake']);
	}

	// Check 2: Any completed/processing orders by billing email.
	$orders = wc_get_orders([
		'billing_email' => $email,
		'status'        => ['wc-completed', 'wc-processing'],
		'limit'         => 1,
		'return'        => 'ids',
	]);
	if (! empty($orders)) {
		wp_send_json(['show_intake' => false, 'reason' => 'existing_orders']);
	}

	wp_send_json(['show_intake' => true]);
}


// ═══════════════════════════════════════════════════════════════════════════════
// Multi-Step Checkout — Step 1 is in form-checkout.php template.
// Steps 2-6 (intake phases) render via hooks below.
// ═══════════════════════════════════════════════════════════════════════════════

// ── Step helpers (intake phases) ──────────────────────────────────────

/**
 * Return an SVG icon for a given checkout step number (1–7).
 *
 * Icons are 16×16 stroke-based, matching the existing back/next arrow style.
 */
function wellness_step_icon($num)
{
	$icons = [
		1 => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
		2 => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><path d="M9 14l2 2 4-4"/></svg>',
		3 => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>',
		4 => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
		5 => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>',
		6 => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',
		7 => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>',
	];

	return isset($icons[$num]) ? $icons[$num] : '<span>' . (int) $num . '</span>';
}

function wellness_intake_step($num, $title, $back, $next = null)
{
	if (! wellness_should_show_intake_form()) {
		if ($num === 2) echo '<input type="hidden" id="wellness-intake-skipped" value="1" />';
		return false;
	}
	$hidden = ($num > 1) ? ' wellness-step-hidden' : '';

	// ── Progress dots row (only once at step 2) ──────────────────────
	// if ($num === 2) {
	// 	echo '<div class="wellness-progress" aria-label="' . esc_attr__('Intake form progress', 'woodmart-child') . '">';
	// 	for ($i = 2; $i <= 6; $i++) {
	// 		$active = ($i === 2) ? ' wellness-progress__dot--active' : '';
	// 		$done   = '';
	// 		echo '<span class="wellness-progress__dot' . $active . $done . '" data-step="' . $i . '">';
	// 		echo '<span class="wellness-progress__dot-inner"></span>';
	// 		echo '</span>';
	// 	}
	// 	echo '</div>';
	// }

	echo '<div class="wellness-checkout-step' . $hidden . '" id="wellness-step-' . $num . '" data-step="' . $num . '" role="region" aria-label="' . esc_attr(sprintf(__('Step %d: %s', 'woodmart-child'), $num, $title)) . '">';
	echo '<div class="wellness-step-header">';
	echo '<span class="wellness-step-number" aria-hidden="true">' . wellness_step_icon($num) . '</span>';
	echo '<span class="wellness-step-title">' . esc_html($title) . '</span>';
	echo '<span class="wellness-step-divider"></span>';
	echo '</div>';
	echo '<div class="wellness-step-body">';
	return true;
}

function wellness_intake_step_close($num, $back, $next = null)
{
	if (! wellness_should_show_intake_form()) {
		return;
	}

	echo '<div class="wellness-step-actions">';
	echo '<button type="button" class="wellness-btn-back" data-back="' . $back . '" aria-label="' . esc_attr__('Go back to previous step', 'woodmart-child') . '">';
	echo '<svg class="wellness-btn-back__icon" width="16" height="16" viewBox="0 0 16 16"><path d="M10 3L5 8l5 5" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>';
	echo '<span>' . esc_html__('Back', 'woodmart-child') . '</span>';
	echo '</button>';
	if ($next) {
		echo '<button type="button" class="wellness-btn-next" data-next="' . $next . '">';
		echo '<span>' . esc_html__('Continue', 'woodmart-child') . '</span>';
		echo '<svg class="wellness-btn-next__icon" width="16" height="16" viewBox="0 0 16 16"><path d="M6 3l5 5-5 5" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg>';
		echo '</button>';
	}
	echo '</div>';
	echo '</div></div>'; // .wellness-step-body, .wellness-checkout-step
}

// ── Step 2: Personal Details ──────────────────────────────────────────

add_action('woocommerce_checkout_after_customer_details', 'wellness_step2_open', 5);
function wellness_step2_open()
{
	wellness_intake_step(2, __('Personal Details', 'woodmart-child'), 1, 3);
}
add_action('woocommerce_checkout_after_customer_details', 'wellness_step2_close', 10);
function wellness_step2_close()
{
	wellness_intake_step_close(2, 1, 3);
}

// ── Step 3: Phone & Preferences ───────────────────────────────────────

add_action('woocommerce_checkout_after_customer_details', 'wellness_step3_open', 15);
function wellness_step3_open()
{
	wellness_intake_step(3, __('Phone & Preferences', 'woodmart-child'), 2, 4);
}
add_action('woocommerce_checkout_after_customer_details', 'wellness_step3_close', 20);
function wellness_step3_close()
{
	wellness_intake_step_close(3, 2, 4);
}

// ── Step 4: Emergency Contact ─────────────────────────────────────────

add_action('woocommerce_checkout_after_customer_details', 'wellness_step4_open', 25);
function wellness_step4_open()
{
	wellness_intake_step(4, __('Emergency Contact', 'woodmart-child'), 3, 5);
}
add_action('woocommerce_checkout_after_customer_details', 'wellness_step4_close', 30);
function wellness_step4_close()
{
	wellness_intake_step_close(4, 3, 5);
}

// ── Step 5: Health Background ─────────────────────────────────────────

add_action('woocommerce_checkout_after_customer_details', 'wellness_step5_open', 35);
function wellness_step5_open()
{
	wellness_intake_step(5, __('Health Background', 'woodmart-child'), 4, 6);
}
add_action('woocommerce_checkout_after_customer_details', 'wellness_step5_close', 40);
function wellness_step5_close()
{
	wellness_intake_step_close(5, 4, 6);
}

// ── Step 6: How You Heard ─────────────────────────────────────────────

add_action('woocommerce_checkout_after_customer_details', 'wellness_step6_open', 45);
function wellness_step6_open()
{
	wellness_intake_step(6, __('How You Heard About Us', 'woodmart-child'), 5, null);
}
add_action('woocommerce_checkout_after_customer_details', 'wellness_step6_close', 50);
function wellness_step6_close()
{
	wellness_intake_step_close(6, 5, 7);
}

// Note: Order review stays always visible in its own column — no wrapper.

// ── Unhook payment from the right column (order review) ──────────────────
// WooCommerce hooks woocommerce_checkout_payment() to
// woocommerce_checkout_order_review at priority 20. We remove it so the
// right column shows only the order summary, and render payment in Step 7.
add_action('wp_loaded', function () {
	remove_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);
});

// ── Step 7: Payment ─────────────────────────────────────────────────────

add_action('wellness_checkout_payment_step', 'wellness_step7_render', 10);
function wellness_step7_render()
{
	// Always render the payment step, regardless of intake form visibility.
?>
	<div class="wellness-checkout-step wellness-step-hidden" id="wellness-step-7" data-step="7" role="region" aria-label="<?php echo esc_attr__('Step 7: Payment', 'woodmart-child'); ?>">
		<div class="wellness-step-header">
			<span class="wellness-step-number" aria-hidden="true"><?php echo wellness_step_icon(7); ?></span>
			<span class="wellness-step-title"><?php esc_html_e('Payment', 'woodmart-child'); ?></span>
			<span class="wellness-step-divider"></span>
		</div>
		<div class="wellness-step-body">
			<?php woocommerce_checkout_payment(); ?>
			<div class="wellness-step-actions">
				<button type="button" class="wellness-btn-back" data-back="6" aria-label="<?php echo esc_attr__('Go back to previous step', 'woodmart-child'); ?>">
					<svg class="wellness-btn-back__icon" width="16" height="16" viewBox="0 0 16 16">
						<path d="M10 3L5 8l5 5" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round" />
					</svg>
					<span><?php esc_html_e('Back', 'woodmart-child'); ?></span>
				</button>
			</div>
		</div>
	</div>
<?php
}


// ═══════════════════════════════════════════════════════════════════════════════
// Multi-Step Checkout — JavaScript (injected on checkout page)
// Styles are in form-checkout.php
// ═══════════════════════════════════════════════════════════════════════════════

add_action('wp_footer', 'wellness_multistep_checkout_js', 20);
function wellness_multistep_checkout_js()
{
	if (! is_checkout()) {
		return;
	}

	$ajax_url = admin_url('admin-ajax.php');
	$nonce    = wp_create_nonce('wellness_check_intake');
?>
	<script>
		(function($) {
			'use strict';

			if (!$('body').hasClass('woocommerce-checkout')) return;

			var $steps = {};
			for (var i = 1; i <= 7; i++) {
				$steps[i] = $('#wellness-step-' + i);
			}
			if (!$steps[1].length) return;

			var currentStep = 1;
			var intakeSkip = $('#wellness-intake-skipped').length > 0;
			var checkingEmail = false;

			function hideAll() {
				for (var i = 1; i <= 7; i++) $steps[i].addClass('wellness-step-hidden');
			}

		/**
		 * Reveal a hidden step and reset off-screen positioning so embedded
		 * payment iframes (Stripe Elements, Paymob Pixel) can measure their
		 * container.  The CSS class uses position:absolute + left:-9999px
		 * instead of display:none to preserve layout-box dimensions.
		 */
		function revealStep($step) {
			$step.css({ position: '', left: '', top: '', visibility: '' });
			$step.removeClass('wellness-step-hidden');
			// Help iframe-based payment widgets recalculate dimensions.
			window.dispatchEvent(new Event('resize'));
		}

		revealStep($steps[1]);

		function updateProgress(step) {
			$('.wellness-progress__dot').each(function() {
				var s = parseInt($(this).data('step'), 10);
				$(this).removeClass('wellness-progress__dot--active wellness-progress__dot--done');
				if (s < step) $(this).addClass('wellness-progress__dot--done');
				if (s === step) $(this).addClass('wellness-progress__dot--active');
			});
		}

		function goToStep(step) {
			if (!$steps[step] || !$steps[step].length) return;
			hideAll();
			revealStep($steps[step]);
			// Entrance animation
			$steps[step].addClass('wellness-step-entering');
			setTimeout(function() {
				$steps[step].removeClass('wellness-step-entering');
			}, 400);
			currentStep = step;
			updateProgress(step);
			$('html, body').animate({
				scrollTop: $steps[step].offset().top - 80
			}, 350, 'swing');
		}

			// ── Floating-label: mark filled selects ────────────────────
			$(document).on('change', '.wellness-select', function() {
				$(this).toggleClass('has-value', $(this).val() !== '');
			});
			$('.wellness-select').each(function() {
				$(this).toggleClass('has-value', $(this).val() !== '');
			});

			function checkIntake(email, callback) {
				if (checkingEmail) return;
				checkingEmail = true;
				$.post('<?php echo esc_js($ajax_url); ?>', {
					action: 'wellness_check_intake',
					email: email,
					_ajax_nonce: '<?php echo esc_js($nonce); ?>'
				}, function(r) {
					checkingEmail = false;
					if (r && r.show_intake) {
						intakeSkip = false;
						for (var i = 2; i <= 6; i++) $steps[i].show();
					} else {
						intakeSkip = true;
						for (var i = 2; i <= 6; i++) $steps[i].hide();
					}
					callback();
				}).fail(function() {
					checkingEmail = false;
					intakeSkip = false;
					for (var i = 2; i <= 6; i++) $steps[i].show();
					callback();
				});
			}

			$(document).on('click', '.wellness-btn-next', function(e) {
				e.preventDefault();
				var $btn = $(this);
				var next = parseInt($btn.data('next'), 10);

				if (next === 2) {
					var email = $('#billing_email').val();
					if (!email || email.indexOf('@') < 0) {
						$('#billing_email').addClass('wellness-input--shake');
						setTimeout(function() {
							$('#billing_email').removeClass('wellness-input--shake');
						}, 600);
						$('#billing_email').focus();
						return;
					}
					$btn.addClass('wellness-loading');
					checkIntake(email, function() {
						$btn.removeClass('wellness-loading');
						goToStep(intakeSkip ? 7 : 2);
					});
				} else {
					goToStep(next);
				}
			});

			$(document).on('click', '.wellness-btn-back', function(e) {
				e.preventDefault();
				var back = parseInt($(this).data('back'), 10);
				// When intake was skipped, go back to step 1 from the payment step
				if (back === 6 && intakeSkip) {
					goToStep(1);
				} else {
					goToStep(back);
				}
			});

			// ── Enter key advances to next step ─────────────────────────
			$('.wellness-checkout-step').on('keydown', '.wellness-input, .wellness-select', function(e) {
				if (e.key === 'Enter') {
					e.preventDefault();
					var $step = $(this).closest('.wellness-checkout-step');
					var $nextBtn = $step.find('.wellness-btn-next');
					if ($nextBtn.length) $nextBtn.click();
				}
			});

		})(jQuery);
	</script>
<?php
}
