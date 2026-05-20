<?php

// ═══════════════════════════════════════════════════════════════════════════════
// EMAIL TESTING — force all outgoing emails to a single address
// Comment out the add_filter lines below when done testing.
// ═══════════════════════════════════════════════════════════════════════════════
define('WELLNESS_TEST_EMAIL', 'heba.f.fawzy@gmail.com'); // ← change this

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

add_filter('woocommerce_product_get_price', 'get_usd_booking_cost', 999999, 2);
function get_usd_booking_cost($cost, $product)
{
	if (is_admin() && !defined('DOING_AJAX')) {
		return $cost;
	}

	// get user country and if it's egypt, return the cost as is
	$user_country_code = WC_Geolocation::geolocate_ip();
	if (empty($user_country_code) || $user_country_code['country'] == 'EG') {
		return $cost;
	}

	// get the usd cost
	// if sale price is set, use it
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

	// get user country and if it's egypt, return the cost as is
	$user_country_code = WC_Geolocation::geolocate_ip();
	if (empty($user_country_code) || $user_country_code['country'] == 'EG') {
		return $cost;
	}

	// get the usd cost
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

	// get user country and if it's egypt, return the cost as is
	$user_country_code = WC_Geolocation::geolocate_ip();
	if (empty($user_country_code) || $user_country_code['country'] == 'EG') {
		return $cost;
	}

	// get the usd cost
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

	// get user country and if it's egypt, return the cost as is
	$user_country_code = WC_Geolocation::geolocate_ip();
	if (empty($user_country_code) || $user_country_code['country'] == 'EG') {
		return $cost;
	}

	// get the usd cost
	$usd_cost = get_post_meta($product->get_id(), '_wc_usd_display_cost', true);
	if (empty($usd_cost)) {
		return $cost;
	}

	return $usd_cost;
}


add_filter('woocommerce_currency', 'change_woocommerce_currency', 999999, 1);
function change_woocommerce_currency($currency)
{
	// get user country name
	$user_country_code = WC_Geolocation::geolocate_ip();
	error_log('user country code: ' . var_export($user_country_code, true));
	if (empty($user_country_code) || $user_country_code['country'] == 'EG') {
		error_log('returning egp');
		return $currency;
	}

	error_log('returning usd');

	return 'USD';
}

//add_filter('woocommerce_currency_symbol', 'change_woocommerce_currency_symbol', 999999, 2);
function change_woocommerce_currency_symbol($currency_symbol, $currency)
{
	// get user country name
	$user_country_code = WC_Geolocation::geolocate_ip();
	error_log('user country code symbol: ' . var_export($user_country_code, true));
	if (empty($user_country_code) || $user_country_code['country'] == 'EG') {
		error_log('returning egp symbol');
		return $currency_symbol;
	}

	return '$';
}


// add_filter('woocommerce_product_addons_option_price_raw', function ($option_price, $option) {

//     if (class_exists('WOOCS')) {
//         global $WOOCS;
//         $option_price = $WOOCS->woocs_exchange_value(floatval($option_price));
//     }

//     return $option_price;
// }, 10, 2);

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
	$_wc_appointment_booking_type = get_post_meta($product_id, '_wc_appointment_booking_type', true) ?? 'single';
	$recurring_type = get_post_meta($product_id, '_wc_appointment_recurring_type', true);

	$recurring_types = array(
		'weekly' => 'Weekly',
		'every_other_week' => 'Every Other Week',
		'monthly' => 'Monthly',
	);

	$recurring_type_options = '<option value="">Select Recurring Type</option>';
	foreach ($recurring_types as $key => $value) {
		$selected = $recurring_type == $key ? 'selected' : '';
		$recurring_type_options .= "<option value='$key' $selected>$value</option>";
	}

	$recurring_type_html = '<div class="options_group">';

	// 	$recurring_type_html .= '
	//   <p class="form-field">
	//     <label for="_wc_appointment_booking_type">Booking Type</label>
	//     <select name="_wc_appointment_booking_type" id="_wc_appointment_booking_type">
	//     <option value="single" ' . selected($_wc_appointment_booking_type, 'single', false) . '>Single</option>
	//       <option value="recurring" ' . selected($_wc_appointment_booking_type, 'recurring', false) . '>Recurring</option>
	//     </select>
	//   </p>';

	// 	$recurring_type_html .= '
	//   <p class="form-field">
	//     <label for="_wc_appointment_recurring_type">Recurring Type</label>
	//     <select name="_wc_appointment_recurring_type" id="_wc_appointment_recurring_type">' .
	// 		$recurring_type_options
	// 		. '
	//     </select>
	//   </p>';

	// 	$recurring_end_period_length = get_post_meta($product_id, '_wc_appointment_recurring_end_length', true) ?? 1;
	// 	$recurring_end_period_unit = get_post_meta($product_id, '_wc_appointment_recurring_end_unit', true) ?? 'month';

	// 	// recurring end period. one month from the first appointment, or 3 months from the first appointment, or 6 months from the first appointment
	// 	$recurring_type_html .= '
	//   <p class="form-field">
	//     <label for="_wc_appointment_recurring_end_length">Recurring End Period</label>
	//     <input type="number" name="_wc_appointment_recurring_end_length" id="_wc_appointment_recurring_end_length" value="' . $recurring_end_period_length . '" />
	//     <select name="_wc_appointment_recurring_end_unit" id="_wc_appointment_recurring_end_unit">
	//       <option value="month" ' . selected($recurring_end_period_unit, 'month', false) . '>Month</option>
	//       <option value="year" ' . selected($recurring_end_period_unit, 'year', false) . '>Year</option>
	//     </select>
	//   </p>';

	$max_repeat_count = get_post_meta($product_id, '_wc_appointment_max_repeat_count', true);
	$max_repeat_count = ($max_repeat_count !== '') ? intval($max_repeat_count) : 2;

	$recurring_type_html .= '
  <p class="form-field">
    <label for="_wc_appointment_max_repeat_count">Max Repeat Count</label>
    <input type="number" name="_wc_appointment_max_repeat_count" id="_wc_appointment_max_repeat_count" value="' . $max_repeat_count . '" min="1" />
    <span class="description">Maximum number of times a customer can repeat this appointment (default: 2).</span>
  </p>';

	$recurring_type_html .= '</div>';

	echo $recurring_type_html;
}
add_action('save_post', 'save_wellness_product_meta_box');
function save_wellness_product_meta_box($post_id)
{
	if (array_key_exists('_wc_appointment_recurring_type', $_POST)) {
		update_post_meta(
			$post_id,
			'_wc_appointment_recurring_type',
			$_POST['_wc_appointment_recurring_type']
		);
	}

	if (array_key_exists('_wc_appointment_booking_type', $_POST)) {
		update_post_meta(
			$post_id,
			'_wc_appointment_booking_type',
			$_POST['_wc_appointment_booking_type']
		);
	}

	if (array_key_exists('_wc_appointment_recurring_end_length', $_POST)) {
		update_post_meta(
			$post_id,
			'_wc_appointment_recurring_end_length',
			$_POST['_wc_appointment_recurring_end_length']
		);
	}

	if (array_key_exists('_wc_appointment_recurring_end_unit', $_POST)) {
		update_post_meta(
			$post_id,
			'_wc_appointment_recurring_end_unit',
			$_POST['_wc_appointment_recurring_end_unit']
		);
	}

	if (array_key_exists('_wc_appointment_max_repeat_count', $_POST)) {
		update_post_meta(
			$post_id,
			'_wc_appointment_max_repeat_count',
			absint($_POST['_wc_appointment_max_repeat_count'])
		);
	}
}

// add to product description that the product is recurring
add_action('woocommerce_single_product_summary', 'add_recurring_product_description', 20);
add_action('woocommerce_before_add_to_cart_button', 'add_recurring_product_description', 20);
function add_recurring_product_description()
{
	global $product;
	$product_id = $product->get_id();
	$booking_type = get_post_meta($product_id, '_wc_appointment_booking_type', true);
	$recurring_type = get_post_meta($product_id, '_wc_appointment_recurring_type', true);

	if ($booking_type == 'recurring') {
		$recurring_types = array(
			'weekly' => 'Weekly',
			'every_other_week' => 'Every Other Week',
			'monthly' => 'Monthly',
		);

		$end_period_length = get_post_meta($product_id, '_wc_appointment_recurring_end_length', true) ?? 1;
		$end_period_unit = get_post_meta($product_id, '_wc_appointment_recurring_end_unit', true) ?? 'month';

		if ($end_period_length && $end_period_unit) {
			$end_period_label = $end_period_length . ' ' . $end_period_unit;
			echo "<p>This is a recurring product, it will be repeated " . $recurring_types[$recurring_type] . " from the first appointment till " . $end_period_label . ".</p>";
		} else {
			echo "<p>This is a recurring product, it will be repeated " . $recurring_types[$recurring_type] . " from the first appointment.</p>";
		}
	}
}

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
function get_product_addon_value($from_status, $to_status, $appointment_id)
{
	// Only trigger on the paid transition to avoid duplicates on subsequent status changes (e.g. paid -> confirmed).
	if ($to_status !== 'paid') {
		return;
	}

	$appointment = get_wc_appointment($appointment_id);
	$product_id = $appointment->get_product_id();
	$product = wc_get_product($product_id);
	$addons = $appointment->get_addons();
	if ($appointment->get_parent_id() <= 0) {
		if (empty($addons)) {
			return;
		}

		// addons values is usually a string like this
		// <ul class="wc-item-meta"><li><strong class="wc-item-meta-label">Repeat Appointment:</strong> <p>Yes</p>
		// </li><li><strong class="wc-item-meta-label">Number:</strong> <p>1</p>
		// </li><li><strong class="wc-item-meta-label">Interval:</strong> <p>Weekly</p>
		// </li></ul>
		// extract repeat appointment value from string <strong class="wc-item-meta-label">Repeat Appointment:</strong> <p>Yes</p> using regex after Repeat Appointment:
		$repeat_count = preg_match_all('!Repeat Appointment:</strong> <p>(.*?)</p>!', $addons, $matches);
		if ($repeat_count > 0) {
			$repeat = $matches[1][0];
		} else {
			return;
		}

		if ($repeat != 'Yes') {
			return;
		}

		// Extract number of repeats requested by customer (default: 1 if addon is absent/zero).
		$count_match = preg_match_all('!Number:</strong> <p>(.*?)</p>!', $addons, $count_matches);
		$user_count  = ($count_match > 0 && intval($count_matches[1][0]) > 0) ? intval($count_matches[1][0]) : 1;

		// Cap against the product-level maximum (default: 2).
		$max_count    = max(1, intval(get_post_meta($product_id, '_wc_appointment_max_repeat_count', true) ?: 2));
		$actual_count = min($user_count, $max_count);

		// Extract interval from the Interval addon field.
		$interval_count = preg_match_all('!Interval:</strong> <p>(.*?)</p>!', $addons, $matches);
		if ($interval_count > 0) {
			$interval_label = $matches[1][0];
		} else {
			return;
		}

		if (in_array($interval_label, ['Week', 'Weekly'])) {
			$interval_unit       = 'week';
			$interval_multiplier = 1;
		} elseif (in_array($interval_label, ['Bi-Weekly', 'Biweekly', 'Every Other Week'])) {
			$interval_unit       = 'week';
			$interval_multiplier = 2;
		} elseif (in_array($interval_label, ['Month', 'Monthly'])) {
			$interval_unit       = 'month';
			$interval_multiplier = 1;
		} else {
			return;
		}

		// Confirm that the follow-up appointments aren't already created.
		$appointments = get_posts(array(
			'post_type' => 'wc_appointment',
			'meta_query' => array(
				array(
					'key' => '_appointment_product_id',
					'value' => $product_id,
				),
				array(
					'key' => '_appointment_parent_id',
					'value' => $appointment_id,
				),
			),
		));

		if (!empty($appointments)) {
			return;
		}

		// Capture the original start/end once before looping.
		$original_start = $appointment->get_start();
		$original_end   = $appointment->get_end();

		$order_id  = $appointment->get_order_id();
		$order     = wc_get_order($order_id);
		$currency  = $order->get_currency();

		if ($currency == 'USD') {
			$sale_price = $product->get_meta('_wc_usd_display_sale_price');
			if (!empty($sale_price)) {
				$order_total = $sale_price;
			} else {
				$order_total = $product->get_meta('_wc_usd_display_cost');
			}
		} else {
			$order_total = $product->get_price();
		}

		$customer_id = $order->get_customer_id();

		// Create one follow-up appointment + order per repeat.
		for ($i = 1; $i <= $actual_count; $i++) {
			$offset = '+' . ($i * $interval_multiplier) . ' ' . $interval_unit;

			$new_appointment_data = array(
				'start_date'  => strtotime($offset, $original_start),
				'end_date'    => strtotime($offset, $original_end),
				'staff_ids'   => $appointment->get_staff_ids(),
				'parent_id'   => $appointment_id,
				'customer_id' => $appointment->get_customer_id(),
				'post_parent' => $order_id,
			);

			if ($appointment->is_all_day()) {
				$new_appointment_data['all_day'] = true;
			}

			$new_appointment = create_wc_appointment(
				$product_id,
				$new_appointment_data,
				$appointment->get_status(),
				false
			);

			$new_order = wc_create_order(array(
				'customer_id' => $customer_id,
				'created_via' => 'recurring',
				'parent'      => $order_id,
			));

			$new_order->set_address($order->get_address('billing'), 'billing');
			$new_order->set_address($order->get_address('shipping'), 'shipping');
			$new_order->set_created_via('recurring');
			$new_order->set_currency($currency);
			$new_order->set_payment_method($order->get_payment_method());
			$new_order->set_payment_method_title($order->get_payment_method_title());

			if (! wc_tax_enabled()) {
				$new_order->set_shipping_tax(0);
				$new_order->set_cart_tax(0);
			}

			$new_order->calculate_totals();
			$new_order_id = $new_order->get_id();

			$item_id = wc_add_order_item(
				$new_order_id,
				[
					'order_item_name' => $product->get_title(),
					'order_item_type' => 'line_item',
				]
			);

			wc_update_order_item_meta($item_id, '_qty', 1);
			wc_update_order_item_meta($item_id, '_tax_class', $product->get_tax_class());
			wc_update_order_item_meta($item_id, '_product_id', $product->get_id());
			wc_update_order_item_meta($item_id, '_variation_id', '');
			wc_update_order_item_meta($item_id, '_line_subtotal', $order_total);
			wc_update_order_item_meta($item_id, '_line_total', $order_total);
			wc_update_order_item_meta($item_id, '_line_tax', 0);
			wc_update_order_item_meta($item_id, '_line_subtotal_tax', 0);

			$new_order->calculate_totals();
			$new_order->set_total($order_total);
			$new_order->set_created_via('appointments');
			$new_order->save();

			$new_appointment->set_order_id($new_order_id);
			$new_appointment->set_order_item_id($item_id);
			$new_appointment->save();

			$new_appointment->maybe_schedule_event('reminder');
			$new_appointment->maybe_schedule_event('complete');
		}
	}
}

add_action('woocommerce_appointment_status_changed', 'get_product_addon_value', 10, 3);
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

	error_log('Checking appointment cancellation policy for appointment ID: ' . $appointment_id . ' with allowed period: ' . $allowed_period . ' hours and cancellation fee: ' . $cancellation_fee);

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

	error_log('Appointment cancellation is outside the allowed period, adding cancellation fee to order ID: ' . $order_id);


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
 */
add_filter('woocommerce_product_addons_get_items', 'wellness_inject_repeat_number_addon', 10, 2);
function wellness_inject_repeat_number_addon($addons, $product_id)
{
	// Only inject if this product has repeat configured (has _wc_appointment_max_repeat_count or is appointable).
	$post_type = get_post_type($product_id);
	if (!in_array($post_type, ['product', 'wc_appointment'], true)) {
		return $addons;
	}

	// Don't duplicate if a Number field already exists.
	foreach ($addons as $addon) {
		if (isset($addon['name']) && strtolower(trim($addon['name'])) === 'number') {
			return $addons;
		}
	}

	$saved_max   = get_post_meta($product_id, '_wc_appointment_max_repeat_count', true);
	$max_allowed = ($saved_max !== '') ? max(1, intval($saved_max)) : 2;

	$addons[] = array(
		'name'              => 'Number',
		'title_format'      => 'label',
		'description'       => 'How many times would you like to repeat this appointment? (max: ' . $max_allowed . ')',
		'type'              => 'custom_text',
		'display'           => '',
		'position'          => count($addons),
		'required'          => 0,
		'restrictions'      => 1,
		'restrictions_type' => 'any_integer',
		'adjust_price'      => 0,
		'price_type'        => 'flat_fee',
		'price'             => '',
		'min'               => 1,
		'max'               => $max_allowed,
		'options'           => array(),
	);

	return $addons;
}


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
