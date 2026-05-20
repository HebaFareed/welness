<?php
/**
 * WooCommerce checkout customizations.
 *
 * @since 1.6.0
 *
 * @package woo-additional-terms
 */

namespace Woo_Additional_Terms\WooCommerce\Block;

use Exception;
use WP_Error;
use WC_Order;
use WP_REST_Request;
use Woo_Additional_Terms\Admin;
use Automattic\WooCommerce\StoreApi\Schemas\V1\CheckoutSchema;

/**
 * Checkout class.
 */
class Checkout {

	/**
	 * Setup hooks and filters.
	 *
	 * @since 1.6.0
	 *
	 * @return void
	 */
	public function setup() {

		add_action( 'woocommerce_blocks_loaded', array( $this, 'extend_store_api' ) );
		add_action( 'woocommerce_store_api_checkout_update_order_from_request', array( $this, 'process_acceptance' ), 10, 2 );
		add_action( 'woo_additional_terms_checkout_save_acceptance', array( $this, 'save_acceptance' ), 10, 2 );
	}

	/**
	 * Add schema Store API to support posted data.
	 * Registers the checkout endpoint extension to inform our frontend component about the result of
	 * the validity of the additional terms checkbox and react accordingly.
	 *
	 * @since 1.5.0
	 *
	 * @throws Exception When the schema callback is not callable.
	 *
	 * @return void
	 */
	public function extend_store_api() {

		woocommerce_store_api_register_endpoint_data(
			array(
				'endpoint'        => CheckoutSchema::IDENTIFIER,
				'namespace'       => Block::NAME,
				'data_callback'   => null,
				'schema_type'     => ARRAY_A,
				'schema_callback' => fn() => array(
					'data' => array(
						'description' => __( 'Whether additional terms were accepted.', 'woo-additional-terms' ),
						'type'        => 'string',
						'context'     => array( 'view', 'edit' ),
						'arg_options' => array(
							'validate_callback' => function( $value ) {
								if ( ! is_string( $value ) ) {
									return new WP_Error(
										'api-error',
										__( 'Invalid value type for additional terms.', 'woo-additional-terms' )
									);
								}

								$allowed = array( 'yes', 'no', '1', '0', 'true', 'false', '' );

								if ( ! in_array( strtolower( $value ), $allowed, true ) ) {
									return new WP_Error(
										'api-error',
										__( 'Invalid value for additional terms.', 'woo-additional-terms' )
									);
								}

								return true;
							},
						),
					),
				),
			)
		);
	}

	/**
	 * Fires after an order saved into the database.
	 * We will update the post meta.
	 *
	 * @since 1.5.0
	 *
	 * @param WC_Order        $order   Order ID or order object.
	 * @param WP_REST_Request $request The API request currently being processed.
	 *
	 * @return void
	 */
	public function process_acceptance( $order, $request ) {

		if ( ! isset( $request['extensions'], $request['extensions'][ Block::NAME ] ) ) {
			return;
		}

		$raw = $request['extensions'][ Block::NAME ]['data'] ?? null;

		if ( null === $raw || '' === $raw ) {
			$has_accepted = null;
		} else {
			$has_accepted = wc_string_to_bool( $raw );
		}

		/**
		 * Fires after additional terms submissions is about to be saved.
		 *
		 * @since 1.6.1
		 *
		 * @param null|bool $has_accepted Whether the additional terms has been accepted.
		 * @param WC_Order  $order        Order object.
		 */
		do_action( 'woo_additional_terms_checkout_save_acceptance', $has_accepted, $order );
	}

	/**
	 * Stores additional terms submissions after a new order being processed.
	 *
	 * @since 1.6.1
	 *
	 * @param null|bool $has_accepted Whether the additional terms has been accepted.
	 * @param WC_Order  $order        Order object.
	 *
	 * @return void
	 */
	public function save_acceptance( $has_accepted, $order ) {

		// Leave if the order is not an instance of WC_Order.
		if ( ! $order instanceof WC_Order || is_null( $has_accepted ) ) {
			return;
		}

		// Add order note based on acceptance status.
		$note_message = $has_accepted
			? __( 'Customer accepted the additional terms.', 'woo-additional-terms' )
			: __( 'Customer did not accept the additional terms.', 'woo-additional-terms' );

		$order->add_order_note( $note_message );

		// Save the additional terms checkbox value as order meta.
		$order->update_meta_data( Admin\Order::META_KEY, wc_bool_to_string( $has_accepted ) );
		$order->save();
	}
}
