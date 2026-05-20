<?php
/**
 * Pukeko child theme functions and definitions
 */

/*-----------------------------------------------------------------------------------*/
/* Include the parent theme style.css
/*-----------------------------------------------------------------------------------*/

add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );
function theme_enqueue_styles() {
		wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );

}

add_action( 'init', 'create_team_posttype' );
function create_team_posttype() {
 $slides = array(
		'name'              => 'Team',
		'singular_name'     => 'Member',
		'search_items'      => 'Search Member',
		'all_items'         => 'All Members',
		'edit_item'         => 'Edit Member',
		'update_item'       => 'Update Member',
		'add_new_item'      => 'Add New Member',
		'new_item_name'     => 'New Member',
		'menu_name'         => 'Team'
	);
  register_post_type( 'team',
    array(
      'labels' => $slides,
      'public' => true,
      'has_archive' => true,
	  'show_admin_column' => true,
      'rewrite' => array('slug' => 'team'),
	  'supports' => array('title','editor','thumbnail')
    )
  );
}
function team_categories() {
 $labels = array(
   'name'              => _x( 'Team Categories', 'taxonomy general name' ),
   'singular_name'     => _x( 'Team Category', 'taxonomy singular name' ),
   'search_items'      => __( 'Search Team Categories' ),
   'all_items'         => __( 'All Team Categories' ),
   'parent_item'       => __( 'Parent Team Category' ),
   'parent_item_colon' => __( 'Parent Team Category:' ),
   'edit_item'         => __( 'Edit Team Category' ), 
   'update_item'       => __( 'Update Team Category' ),
   'add_new_item'      => __( 'Add New Team Category' ),
   'new_item_name'     => __( 'New Team Category' ),
   'menu_name'         => __( 'Team Categories' ),
 );
 $args = array(
   'labels' => $labels,
   'hierarchical' => true,
   'show_admin_column' => true,
 );
 register_taxonomy('team_category', 'team', $args);
}
add_action( 'init', 'team_categories', 0 );




add_action( 'init', 'create_modalities_posttype' );
function create_modalities_posttype() {
 $modality = array(
		'name'              => 'Modalities',
		'singular_name'     => 'Modality',
		'search_items'      => 'Search Modality',
		'all_items'         => 'All Modalities',
		'edit_item'         => 'Edit Modality',
		'update_item'       => 'Update Modality',
		'add_new_item'      => 'Add New Modality',
		'new_item_name'     => 'New Modality',
		'menu_name'         => 'Modalities'
	);
  register_post_type( 'modality',
    array(
      'labels' => $modality,
      'public' => true,
      'has_archive' => true,
	  'show_admin_column' => true,
      'rewrite' => array('slug' => 'modality'),
	  'supports' => array('title','editor','thumbnail')
    )
  );
}
function modality_categories() {
 $labels = array(
   'name'              => _x( 'Modality Categories', 'taxonomy general name' ),
   'singular_name'     => _x( 'Modality Category', 'taxonomy singular name' ),
   'search_items'      => __( 'Search Modality Categories' ),
   'all_items'         => __( 'All Modality Categories' ),
   'parent_item'       => __( 'Parent Modality Category' ),
   'parent_item_colon' => __( 'Parent Modality Category:' ),
   'edit_item'         => __( 'Edit Modality Category' ), 
   'update_item'       => __( 'Update Modality Category' ),
   'add_new_item'      => __( 'Add New Modality Category' ),
   'new_item_name'     => __( 'New Modality Category' ),
   'menu_name'         => __( 'Modality Categories' ),
 );
 $args = array(
   'labels' => $labels,
   'hierarchical' => true,
    'show_admin_column' => true,
 );
 register_taxonomy('modality_category', 'modality', $args);
}
add_action( 'init', 'modality_categories', 0 );



add_action( 'init', 'create_testimonial_posttype' );
function create_testimonial_posttype() {
 $testimonial = array(
		'name'              => 'Testimonials',
		'singular_name'     => 'Testimonial',
		'search_items'      => 'Search Testimonials',
		'all_items'         => 'All Testimonials',
		'edit_item'         => 'Edit Testimonial',
		'update_item'       => 'Update Testimonial',
		'add_new_item'      => 'Add New Testimonial',
		'new_item_name'     => 'New Testimonial',
		'menu_name'         => 'Testimonials'
	);
  register_post_type( 'testimonial',
    array(
      'labels' => $testimonial,
      'public' => true,
      'has_archive' => true,
	  'show_admin_column' => true,
      'rewrite' => array('slug' => 'testimonial'),
	  'supports' => array('title','editor','thumbnail')
    )
  );
}
function testimonial_categories() {
 $labels = array(
   'name'              => _x( 'Testimonial Categories', 'taxonomy general name' ),
   'singular_name'     => _x( 'Testimonial Category', 'taxonomy singular name' ),
   'search_items'      => __( 'Search Testimonial Categories' ),
   'all_items'         => __( 'All Testimonial Categories' ),
   'parent_item'       => __( 'Parent Testimonial Category' ),
   'parent_item_colon' => __( 'Parent Testimonial Category:' ),
   'edit_item'         => __( 'Edit Testimonial Category' ), 
   'update_item'       => __( 'Update Testimonial Category' ),
   'add_new_item'      => __( 'Add New Testimonial Category' ),
   'new_item_name'     => __( 'New Testimonial Category' ),
   'menu_name'         => __( 'Testimonial Categories' ),
 );
 $args = array(
   'labels' => $labels,
   'hierarchical' => true,
   'show_admin_column' => true,
 );
 register_taxonomy('testimonial_category', 'testimonial', $args);
}
add_action( 'init', 'testimonial_categories', 0 );


add_action( 'init', 'create_press_posttype' );
function create_press_posttype() {
 $press = array(
		'name'              => 'Press',
		'singular_name'     => 'Press Post',
		'search_items'      => 'Search Press Posts',
		'all_items'         => 'All Press Posts',
		'edit_item'         => 'Edit Press Post',
		'update_item'       => 'Update Press Post',
		'add_new_item'      => 'Add New Press Post',
		'new_item_name'     => 'New Press Post',
		'menu_name'         => 'Press'
	);
  register_post_type( 'press',
    array(
      'labels' => $press,
      'public' => true,
      'has_archive' => true,
	  'show_admin_column' => true,
      'rewrite' => array('slug' => 'press-post'),
	  'supports' => array('title','editor','thumbnail')
    )
  );
}
function press_categories() {
 $labels = array(
   'name'              => _x( 'Press Post Categories', 'taxonomy general name' ),
   'singular_name'     => _x( 'Press Post Category', 'taxonomy singular name' ),
   'search_items'      => __( 'Search Press Post Categories' ),
   'all_items'         => __( 'All Press Post Categories' ),
   'parent_item'       => __( 'Parent Press Post Category' ),
   'parent_item_colon' => __( 'Parent Press Post Category:' ),
   'edit_item'         => __( 'Edit Press Post Category' ), 
   'update_item'       => __( 'Update Press Post Category' ),
   'add_new_item'      => __( 'Add New Press Post Category' ),
   'new_item_name'     => __( 'New Press Post Category' ),
   'menu_name'         => __( 'Press Post Categories' ),
 );
 $args = array(
   'labels' => $labels,
   'hierarchical' => true,
   'show_admin_column' => true,
 );
 register_taxonomy('press_category', 'press', $args);
}
add_action( 'init', 'press_categories', 0 );


function dynamic_select_field_values_modalities ( $scanned_tag, $replace ) {  
  
    if ( $scanned_tag['name'] != 'modalities' )  
	
		return $scanned_tag;

	$rows = get_terms( array(
				'taxonomy' => 'team_category',
				'hide_empty' => false,
			) );
  
    if ( ! $rows )  
        return $scanned_tag;

    foreach ( $rows as $row ) {  
        $scanned_tag['raw_values'][] = $row->slug . '|' . $row->name;
    }

    $pipes = new WPCF7_Pipes($scanned_tag['raw_values']);

    $scanned_tag['values'] = $pipes->collect_befores();
    $scanned_tag['labels'] = $pipes->collect_afters();
    $scanned_tag['pipes'] = $pipes;
  
    return $scanned_tag;  
}  

add_filter( 'wpcf7_form_tag', 'dynamic_select_field_values_modalities', 10, 2); 

function dynamic_select_field_values_cousellor ( $scanned_tag, $replace ) {  
  
    if ( $scanned_tag['name'] != 'cousellor' )  
        return $scanned_tag;

    $rows = get_posts(
    	array ( 
	     'post_type' => 'team',  
	     'numberposts' => -1,  
	     'orderby' => 'title',  
	     'order' => 'ASC' 
        )
    );  
  
    if ( ! $rows )  
        return $scanned_tag;

    foreach ( $rows as $row ) {  
        $scanned_tag['raw_values'][] = $row->post_title . '|' . $row->post_title;
    }

    $pipes = new WPCF7_Pipes($scanned_tag['raw_values']);

    $scanned_tag['values'] = $pipes->collect_befores();
    $scanned_tag['labels'] = $pipes->collect_afters();
    $scanned_tag['pipes'] = $pipes;
  
    return $scanned_tag;  
}  

add_filter( 'wpcf7_form_tag', 'dynamic_select_field_values_cousellor', 10, 2); 


add_action('woocommerce_bookings_after_booking_base_cost', 'add_usd_booking_base_cost', 10, 1);
function add_usd_booking_base_cost($product_id) {
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
function add_usd_booking_block_cost($product_id) {
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

add_action('woocommerce_bookings_after_display_cost', 'add_usd_display_cost', 10, 1);
function add_usd_display_cost($product_id) {
  $usd_display_cost = get_post_meta($product_id, '_wc_usd_display_cost', true);
  woocommerce_wp_text_input(array(
    'id'                => '_wc_usd_display_cost',
    'label'             => __('Display cost (USD)', 'woocommerce-bookings'),
    'description'       => __('The cost is displayed to the user on the frontend in USD. Leave blank to have it calculated for you. If a booking has varying costs, this will be prefixed with the word "from:".', 'woocommerce-bookings'),
    'value'             => $usd_display_cost,
    'type'              => 'number',
    'desc_tip'          => true,
    'custom_attributes' => array(
      'min'  => '',
      'step' => '0.01',
    ),
  ));
}

add_action('woocommerce_process_product_meta', 'save_usd_booking_fields', 10, 1);
function save_usd_booking_fields($product_id) {
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
}

add_filter('woocommerce_product_get_cost', 'get_usd_booking_cost', 999999, 2);
function get_usd_booking_cost($cost, $product) {
  if (is_admin() && !defined('DOING_AJAX')) {
    return $cost;
  }

  // get user country and if it's egypt, return the cost as is
  $user_country_code = WC_Geolocation::geolocate_ip();
  if (empty($user_country_code) || $user_country_code['country'] == 'EG') {
    return $cost;
  }

  // get the usd cost
  $usd_cost = get_post_meta($product->get_id(), '_wc_usd_booking_cost', true);
  if (empty($usd_cost)) {
    return $cost;
  }

  return $usd_cost;
}

add_filter('woocommerce_product_get_block_cost', 'get_usd_booking_block_cost', 999999, 2);
function get_usd_booking_block_cost($cost, $product) {
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
function get_usd_display_cost($cost, $product) {
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


add_filter('woocommerce_currency', 'change_woocommerce_currency', 10, 1);
function change_woocommerce_currency($currency)
{
  // get user country name
  $user_country_code = WC_Geolocation::geolocate_ip();
  if (!empty($user_country_code) && $user_country_code['country'] != 'EG') {
    return 'USD';
  }

  return $currency;
}