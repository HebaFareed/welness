<?php
/**
 * One-time seeding script — Test Therapists (EGP + USD + Location Based).
 *
 * Creates three `shop_staff` test therapists and three hidden appointment
 * products (recurring + session-type/duration options enabled) so the recurring,
 * session-type, and EGP/USD currency flows can be tested end-to-end. Products
 * are set to catalog visibility "Hidden" so they are reachable only by direct
 * URL. The location-based therapist (staff_currency '') exercises the
 * geolocation-fallback currency path; ?diagnose=1 prints geolocation/currency
 * resolution under normal, simulated-Cloudflare, and spoofed-header conditions.
 *
 * This file defines the seed/clean functions used by the WooCommerce →
 * "Test Therapist Fixtures" admin page (see functions.php). It is never run
 * directly; passwords are stored in transients, not printed.
 *
 * @package woodmart-child
 */

// No direct access. Only the WooCommerce → "Test Therapist Fixtures" admin
// page (see functions.php) includes this file; it is never run directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Forbidden: run the Test Therapist Fixtures admin page in wp-admin.' );
}

/**
 * Config for the two test therapists.
 *
 * @return array
 */
function seed_get_fixtures() {
	return array(
		array(
			'username'       => 'test_therapist_egp',
			'email'          => 'test.egp@wellnesshub-eg.com',
			'display_name'   => 'Test Therapist (EGP)',
			'first_name'     => 'Test',
			'last_name'      => 'Therapist (EGP)',
			'staff_currency' => 'EGP',
			'timezone'       => 'Africa/Cairo',
			'product_slug'   => 'test-therapist-egp',
			'product_title'  => 'Test Therapist (EGP)',
			'price_egp'      => 1500,
			'price_usd'      => 60,
		),
		array(
			'username'       => 'test_therapist_usd',
			'email'          => 'test.usd@wellnesshub-eg.com',
			'display_name'   => 'Test Therapist (USD)',
			'first_name'     => 'Test',
			'last_name'      => 'Therapist (USD)',
			'staff_currency' => 'USD',
			'timezone'       => 'America/New_York',
			'product_slug'   => 'test-therapist-usd',
			'product_title'  => 'Test Therapist (USD)',
			'price_egp'      => 1500,
			'price_usd'      => 60,
		),
		array(
			'username'       => 'test_therapist_location',
			'email'          => 'test.location@wellnesshub-eg.com',
			'display_name'   => 'Test Therapist (Location Based)',
			'first_name'     => 'Test',
			'last_name'      => 'Therapist (Location)',
			'staff_currency' => '',
			'timezone'       => 'Africa/Cairo',
			'product_slug'   => 'test-therapist-location',
			'product_title'  => 'Test Therapist (Location Based)',
			'price_egp'      => 1500,
			'price_usd'      => 60,
		),
	);
}

/**
 * Simulate Cloudflare request headers for a given country so geolocation can be
 * verified without a real proxied request. Sets the $_SERVER superglobals that
 * wellness_geolocate_ip() and WC_Geolocation read.
 *
 * @param string $country ISO 3166-1 alpha-2 country code, or '' to clear.
 */
function seed_simulate_cloudflare_headers( $country = 'EG' ) {
	if ( '' === $country ) {
		unset( $_SERVER['HTTP_CF_IPCOUNTRY'], $_SERVER['HTTP_CF_RAY'], $_SERVER['HTTP_CF_CONNECTING_IP'] );
		return;
	}
	$_SERVER['HTTP_CF_IPCOUNTRY']     = strtoupper( $country );
	$_SERVER['HTTP_CF_RAY']           = 'test-1-2-3'; // present ⇒ proxied by Cloudflare.
	$_SERVER['HTTP_CF_CONNECTING_IP'] = '198.51.100.10';
}

/**
 * Print the resolved country/currency under normal, simulated-Cloudflare, and
 * spoofed-header conditions. Useful for the currency verification checklist
 * (deterministic CF geolocation + spoof guard). Load with ?diagnose=1.
 */
function seed_diagnose_currency() {
	echo "\n== Geolocation (no CF headers) ==\n";
	echo '  country: ' . var_export( wellness_get_location_country(), true ) . "\n";
	echo '  currency: ' . wellness_get_location_currency() . "\n";

	seed_simulate_cloudflare_headers( 'EG' );
	echo "\n== Geolocation (simulated CF-IPCountry=EG, proxied) ==\n";
	echo '  country: ' . wellness_get_location_country() . "\n";
	echo '  currency: ' . wellness_get_location_currency() . "\n";

	// Spoof guard: CF-IPCountry present but NOT proxied (no CF-RAY) → ignored.
	unset( $_SERVER['HTTP_CF_RAY'], $_SERVER['HTTP_CF_CONNECTING_IP'] );
	$_SERVER['HTTP_CF_IPCOUNTRY'] = 'US';
	echo "\n== Spoof guard (CF-IPCountry=US but no CF-RAY) — should be ignored ==\n";
	echo '  country: ' . ( wellness_get_location_country() ?: '(none → falls to geo)' ) . "\n";

	seed_simulate_cloudflare_headers( '' );
}

/**
 * Session-type (duration option) presets shared by both products.
 *
 * @return array
 */
function seed_get_duration_options() {
	return array(
		array( 'label' => 'Individual – 30 min', 'duration' => 30, 'price_egp' => 500,  'price_usd' => 15 ),
		array( 'label' => 'Couples – 60 min',    'duration' => 60, 'price_egp' => 900,  'price_usd' => 27 ),
		array( 'label' => 'Family – 90 min',     'duration' => 90, 'price_egp' => 1200, 'price_usd' => 36 ),
	);
}

/**
 * Get the test staff user ID by email (0 when not found).
 *
 * @param string $email Email.
 * @return int
 */
function seed_get_staff_by_email( $email ) {
	$user = get_user_by( 'email', $email );
	return $user ? (int) $user->ID : 0;
}

/**
 * Create (or reuse) a `shop_staff` test therapist and set currency/timezone.
 *
 * @param array $cfg Fixture config.
 * @return int|WP_Error
 */
function seed_ensure_staff( $cfg ) {
	$user_id = seed_get_staff_by_email( $cfg['email'] );
	if ( $user_id ) {
		// Idempotent: keep meta in sync.
		update_user_meta( $user_id, '_staff_currency', $cfg['staff_currency'] );
		update_user_meta( $user_id, 'timezone_string', $cfg['timezone'] );
		return $user_id;
	}

	$password = wp_generate_password( 16, true, true );
	$user_id  = wp_insert_user(
		array(
			'user_login'   => $cfg['username'],
			'user_email'   => $cfg['email'],
			'user_pass'    => $password,
			'first_name'   => $cfg['first_name'],
			'last_name'    => $cfg['last_name'],
			'display_name' => $cfg['display_name'],
			'role'         => 'shop_staff',
		)
	);

	if ( is_wp_error( $user_id ) ) {
		return $user_id;
	}

	update_user_meta( $user_id, '_staff_currency', $cfg['staff_currency'] );
	update_user_meta( $user_id, 'timezone_string', $cfg['timezone'] );

	// Never echo a generated password. Store it so an admin can read it once;
	// it is removed by `clean`. Retrieve with get_transient('wellness_seed_password_'.$user_id).
	set_transient( 'wellness_seed_password_' . $user_id, $password, DAY_IN_SECONDS );

	return (int) $user_id;
}

/**
 * Create (or reuse) the hidden appointment product for a test therapist.
 *
 * @param array $cfg              Fixture config.
 * @param int   $staff_id         Staff user ID.
 * @param array $duration_options Duration-option presets.
 * @return int|WP_Error
 */
function seed_ensure_product( $cfg, $staff_id, $duration_options ) {
	$product_id = wc_get_product_id_by_slug( $cfg['product_slug'] );
	if ( $product_id ) {
		$product = wc_get_product( $product_id );
		if ( $product ) {
			seed_sync_product( $product, $cfg, $staff_id, $duration_options );
			return (int) $product_id;
		}
	}

	$product = new WC_Product_Appointment();
	$product->set_name( $cfg['product_title'] );
	$product->set_slug( $cfg['product_slug'] );
	$product->set_status( 'publish' );
	$product->set_description( 'QA TEST therapist — not part of the live team. Used to test the recurring and session-type booking flows.' );
	$product->set_short_description( 'TEST — QA only.' );
	$product->set_regular_price( (string) $cfg['price_egp'] );
	$product->set_catalog_visibility( 'hidden' );

	// Appointment slots: 50-minute blocks every 30 minutes, qty 1.
	$product->set_duration( 50 );
	$product->set_duration_unit( 'minute' );
	$product->set_interval( 30 );
	$product->set_interval_unit( 'minute' );
	$product->set_qty( 1 );
	$product->set_qty_min( 1 );
	$product->set_qty_max( 1 );
	$product->set_min_date( 1 );
	$product->set_min_date_unit( 'day' );
	$product->set_max_date( 2 );
	$product->set_max_date_unit( 'month' );
	$product->set_requires_confirmation( false );
	$product->set_customer_timezones( true );
	$product->set_staff_assignment( 'automatic' );
	$product->set_staff_ids( array( $staff_id ) );
	$product->set_staff_base_costs( array( $staff_id => 0 ) );
	$product->set_staff_qtys( array( $staff_id => 1 ) );
	$product->save();

	$product_id = $product->get_id();
	if ( ! $product_id ) {
		return new WP_Error( 'seed_product', 'Product could not be created.' );
	}

	seed_sync_product( $product, $cfg, $staff_id, $duration_options );

	return (int) $product_id;
}

/**
 * Sync the child-theme meta that drives recurring + session-type features.
 *
 * @param WC_Product $product          Product object.
 * @param array      $cfg              Fixture config.
 * @param int        $staff_id         Staff user ID.
 * @param array      $duration_options Duration-option presets.
 */
function seed_sync_product( $product, $cfg, $staff_id, $duration_options ) {
	$product_id = $product->get_id();

	// Recurring (child theme: `_wc_appointment_enable_recurring` default yes).
	update_post_meta( $product_id, '_wc_appointment_enable_recurring', 'yes' );
	update_post_meta( $product_id, '_wc_appointment_max_repeat_count', 3 );

	// Session types / duration options.
	update_post_meta( $product_id, '_wc_appointment_enable_duration_options', 'yes' );
	update_post_meta( $product_id, '_wc_appointment_duration_options', wp_json_encode( $duration_options ) );

	// USD meta (used when the staff `_staff_currency` resolves to USD).
	update_post_meta( $product_id, '_wc_usd_display_cost', $cfg['price_usd'] );
	update_post_meta( $product_id, '_wc_usd_booking_block_cost', $cfg['price_usd'] );
	update_post_meta( $product_id, '_wc_usd_booking_cost', 0 );
}

/**
 * Check whether a staff member already has availability rules.
 *
 * @param int $staff_id Staff user ID.
 * @return bool
 */
function seed_has_staff_availability( $staff_id ) {
	global $wpdb;
	$count = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(*) FROM {$wpdb->prefix}wc_appointments_availability WHERE kind = 'availability#staff' AND kind_id = %d",
			$staff_id
		)
	);
	return $count > 0;
}

/**
 * Create staff availability (weekdays 09:00–17:00) if none exists yet.
 *
 * @param int    $staff_id     Staff user ID.
 * @param string $display_name Display name for the rule titles.
 */
function seed_ensure_availability( $staff_id, $display_name ) {
	if ( seed_has_staff_availability( $staff_id ) ) {
		return;
	}

	// Weekdays available (1 = Mon ... 7 = Sun).
	$days = new WC_Appointments_Availability();
	$days->set_kind( 'availability#staff' );
	$days->set_kind_id( (string) $staff_id );
	$days->set_title( $display_name . ' — Weekdays' );
	$days->set_range_type( 'days' );
	$days->set_from_range( '1' );
	$days->set_to_range( '5' );
	$days->set_appointable( 'yes' );
	$days->set_priority( 10 );
	$days->set_qty( 1 );
	$days->save();

	// Working hours 09:00–17:00.
	$time = new WC_Appointments_Availability();
	$time->set_kind( 'availability#staff' );
	$time->set_kind_id( (string) $staff_id );
	$time->set_title( $display_name . ' — Working hours' );
	$time->set_range_type( 'time' );
	$time->set_from_range( '09:00' );
	$time->set_to_range( '17:00' );
	$time->set_appointable( 'yes' );
	$time->set_priority( 10 );
	$time->set_qty( 1 );
	$time->save();
}

/**
 * Remove the seeded fixtures (products, staff users, availability, links).
 *
 * @param array $fixtures Fixture config.
 */
function seed_clean( $fixtures ) {
	global $wpdb;

	foreach ( $fixtures as $cfg ) {
		$product_id = wc_get_product_id_by_slug( $cfg['product_slug'] );
		if ( $product_id ) {
			$wpdb->delete( $wpdb->prefix . 'wc_appointment_relationships', array( 'product_id' => $product_id ) );
			wp_delete_post( $product_id, true );
			echo "Removed product: {$cfg['product_slug']} (ID {$product_id})\n";
		}

		$staff_id = seed_get_staff_by_email( $cfg['email'] );
		if ( $staff_id ) {
			$wpdb->delete( $wpdb->prefix . 'wc_appointments_availability', array( 'kind' => 'availability#staff', 'kind_id' => $staff_id ) );
			$wpdb->delete( $wpdb->prefix . 'wc_appointment_relationships', array( 'staff_id' => $staff_id ) );
			delete_transient( 'wellness_seed_password_' . $staff_id );
			$reassign = get_current_user_id() ? get_current_user_id() : null;
			wp_delete_user( $staff_id, $reassign );
			echo "Removed staff: {$cfg['username']} (ID {$staff_id})\n";
		}
	}

	echo "\nDone cleaning fixtures.\n";
}

/**
 * Create all fixtures and print a summary.
 *
 * @param array $fixtures         Fixture config.
 * @param array $duration_options Duration-option presets.
 */
function seed_run( $fixtures, $duration_options ) {
	echo "== Seeding test therapists ==\n\n";

	// Seed a fallback USD→EGP rate (Phase C) so conversion never falls to 0.
	if ( ! get_option( 'wellness_usd_egp_fallback' ) ) {
		update_option( 'wellness_usd_egp_fallback', 30.0 );
		echo "  set fallback USD→EGP rate: 30.0\n";
	}

	foreach ( $fixtures as $cfg ) {
		echo "• {$cfg['product_title']}\n";

		$staff_id = seed_ensure_staff( $cfg );
		if ( is_wp_error( $staff_id ) ) {
			echo '  ERROR staff: ' . $staff_id->get_error_message() . "\n";
			continue;
		}
		echo "  staff ID: {$staff_id} (currency {$cfg['staff_currency']}, tz {$cfg['timezone']})\n";

		seed_ensure_availability( $staff_id, $cfg['display_name'] );

		$product_id = seed_ensure_product( $cfg, $staff_id, $duration_options );
		if ( is_wp_error( $product_id ) ) {
			echo '  ERROR product: ' . $product_id->get_error_message() . "\n";
			continue;
		}
		echo "  product ID: {$product_id}\n";
		echo '  URL: ' . get_permalink( $product_id ) . "\n\n";
	}

	echo "Done. Verify the fixtures above. Script is kept in the repo for future seeding.\n";
}

// ── Entry point ────────────────────────────────────────────────────────
// No entry point here. The seed/clean functions are invoked from the
// WooCommerce → "Test Therapist Fixtures" admin page (see functions.php).
