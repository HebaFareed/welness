<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Plugin Name: WooCommerce Admin SQL modification Example
 *
 * @package WooCommerce\Admin
 */

 /**
  * Logic for WooCommerce dashboard display.
  */
class WC_Appointments_Admin_Analytics {

	/**
	 * Hook in additional reporting to WooCommerce dashboard widget
	 */
	public function __construct() {
		// Add the dashboard widget text
		add_filter( 'admin_init', [ $this, 'add_staff_settings' ] );

		// Add the query argument `staff` to the query.
		add_filter( 'woocommerce_analytics_revenue_query_args', [ $this, 'analytics_staff_query_args' ] );
		add_filter( 'woocommerce_analytics_orders_query_args', [ $this, 'analytics_staff_query_args' ] );
		add_filter( 'woocommerce_analytics_orders_stats_query_args', [ $this, 'analytics_staff_query_args' ] );
		add_filter( 'woocommerce_analytics_products_query_args', [ $this, 'analytics_staff_query_args' ] );
		add_filter( 'woocommerce_analytics_products_stats_query_args', [ $this, 'analytics_staff_query_args' ] );
		add_filter( 'woocommerce_analytics_categories_query_args', [ $this, 'analytics_staff_query_args' ] );
		add_filter( 'woocommerce_analytics_coupons_query_args', [ $this, 'analytics_staff_query_args' ] );
		add_filter( 'woocommerce_analytics_coupons_stats_query_args', [ $this, 'analytics_staff_query_args' ] );
		add_filter( 'woocommerce_analytics_taxes_query_args', [ $this, 'analytics_staff_query_args' ] );
		add_filter( 'woocommerce_analytics_taxes_stats_query_args', [ $this, 'analytics_staff_query_args' ] );

		// Filter appointments on reports by product type.
		add_filter( 'woocommerce_analytics_products_query_args', [ $this, 'analytics_products_query_args' ] );
		add_filter( 'woocommerce_analytics_products_stats_query_args', [ $this, 'analytics_products_query_args' ] );

		// Filter appointments on reports by staff and|or product type.
		add_filter( 'woocommerce_analytics_clauses_join', [ $this, 'analytics_clauses_join' ], 10, 2 );
		add_filter( 'woocommerce_analytics_clauses_where', [ $this, 'analytics_clauses_where' ], 10, 2 );
	}

	/**
	 * Add the query argument `staff` for caching purposes. Otherwise, a
	 * change of the currency will return the previous request's data.
	 *
	 * @param array $args query arguments.
	 * @return array augmented query arguments.
	 */
	public function add_staff_settings() {
		$staff = WC_Appointments_Admin::get_appointment_staff(); #all staff

		// Default.
		$providers[] = [
			'label' => esc_html__( 'All staff', 'woocommerce-appointments' ),
			'value' => 'all',
		];

		foreach ( $staff as $staff_member ) {
			$providers[] = [
				'label' => strval( $staff_member->display_name ), #convert to string
				'value' => strval( $staff_member->ID ), #convert to string
			];
		}

		#error_log( var_export( $providers, true ) );

		$data_registry = Automattic\WooCommerce\Blocks\Package::container()->get(
			Automattic\WooCommerce\Blocks\Assets\AssetDataRegistry::class
		);

		$data_registry->add( 'multiStaff', $providers );
	}

	/**
	 * Add the query argument `staff` for caching purposes. Otherwise, a
	 * change of the staff will return the previous request's data.
	 *
	 * @param array $args query arguments.
	 *
	 * @return array augmented query arguments.
	 */
	public function analytics_staff_query_args( $args ) {
		$staff = 'all';

		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['staff'] ) ) {
			$staff = wc_clean( wp_unslash( $_GET['staff'] ) );
		}
		// phpcs:enable

		$args['staff'] = $staff;

		#error_log( var_export( $args, true ) );

		return $args;
	}

	/**
	 * Appointable Products filtered under the Analytics page.
	 *
	 * @param array $args Query arguments.
	 *
	 * @return array Updated query arguments.
	 */
	public function analytics_products_query_args( $args ) {
		if ( 'appointments' === wc_clean( wp_unslash( $_GET['filter'] ?? '' ) ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Recommended
			if ( isset( $args['meta_query'] ) ) {
				$args['meta_query'][] = [
					'key'     => '_wc_appointment_availability',
					'compare' => 'EXISTS',
				];
			} else {
				$args['meta_query'] = [
					'key'     => '_wc_appointment_availability',
					'compare' => 'EXISTS',
				];
			}
		}

		#error_log( var_export( $args, true ) );

		return $args;
	}

	/**
	 * Appointable Products filtered via JOIN clause(s).
	 *
	 * @param array $clauses The existing clauses.
	 * @param array $context The context of the clause.
	 *
	 * @return array Updated clauses.
	 */
	public function analytics_clauses_join( $clauses, $context ) {
		global $wpdb;

		// Filter by 'appointment' product type.
		if ( 'appointments' === wc_clean( wp_unslash( $_GET['filter'] ?? '' ) ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Recommended
			$clauses[] = " JOIN {$wpdb->prefix}term_relationships ON {$wpdb->prefix}wc_order_product_lookup.product_id = {$wpdb->prefix}term_relationships.object_id";
			$clauses[] = " JOIN {$wpdb->prefix}term_taxonomy ON {$wpdb->prefix}term_taxonomy.term_taxonomy_id = {$wpdb->prefix}term_relationships.term_taxonomy_id";
			$clauses[] = " JOIN {$wpdb->prefix}terms ON {$wpdb->prefix}term_taxonomy.term_id = {$wpdb->prefix}terms.term_id";
		}

		// Filter by staff.
		if ( isset( $_GET['staff'] ) ) {
			$clauses[] = " JOIN {$wpdb->prefix}posts ON {$wpdb->prefix}wc_order_stats.order_id = {$wpdb->prefix}posts.post_parent";
			$clauses[] = " JOIN {$wpdb->prefix}postmeta ON {$wpdb->prefix}postmeta.post_id = {$wpdb->prefix}posts.ID";
		}

		#error_log( var_export( $clauses, true ) );

		return $clauses;
	}

	/**
	 * Appointable Products filtered via WHERE clause(s).
	 *
	 * @param array $clauses The existing clauses.
	 * @param array $context The context of the clause.
	 *
	 * @return array Updated clauses.
	 */
	public function analytics_clauses_where( $clauses, $context ) {
		global $wpdb;

		// Filter by 'appointment' product type.
		if ( 'appointments' === wc_clean( wp_unslash( $_GET['filter'] ?? '' ) ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.NonceVerification.Recommended
			$clauses[] = " AND {$wpdb->prefix}term_taxonomy.taxonomy = 'product_type'";
			$clauses[] = " AND {$wpdb->prefix}terms.slug = 'appointment'";
		}

		// Filter by staff.
		if ( isset( $_GET['staff'] ) ) {
			$staff = sanitize_text_field( wp_unslash( $_GET['staff'] ) );

			$clauses[] = " AND {$wpdb->prefix}postmeta.meta_key = '_appointment_staff_id' AND {$wpdb->prefix}postmeta.meta_value = '{$staff}'";
		}

		#error_log( var_export( $clauses, true ) );

		return $clauses;
	}
}

new WC_Appointments_Admin_Analytics();
