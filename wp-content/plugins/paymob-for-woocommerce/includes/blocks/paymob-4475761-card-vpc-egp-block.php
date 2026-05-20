<?php
if ( ! class_exists( 'WC_Paymob_4475761_Card_VPC_EGP_Blocks' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'gateway-blocks.php';

	final class WC_Paymob_4475761_Card_VPC_EGP_Blocks extends Paymob_Gateway_Blocks {

		public function __construct() {
			$this->name = 'paymob-4475761-card-vpc-egp';
		}
	}

}
