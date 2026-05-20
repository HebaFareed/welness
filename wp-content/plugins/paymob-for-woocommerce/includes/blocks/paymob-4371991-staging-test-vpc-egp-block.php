<?php
if ( ! class_exists( 'WC_Paymob_4371991_Staging_Test_VPC_EGP_Blocks' ) ) {
	require_once plugin_dir_path( __FILE__ ) . 'gateway-blocks.php';

	final class WC_Paymob_4371991_Staging_Test_VPC_EGP_Blocks extends Paymob_Gateway_Blocks {

		public function __construct() {
			$this->name = 'paymob-4371991-staging-test-vpc-egp';
		}
	}

}
