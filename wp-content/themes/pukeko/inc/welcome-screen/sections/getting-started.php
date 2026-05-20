<?php
/**
 * Getting started template
 */
$customizer_url = admin_url() . 'customize.php';
?>

<div class="feature-section three-col">
	<div class="col">
		<h3><?php esc_html_e( '1. Install recommended plugins', 'pukeko' ); ?></h3>
		<p><?php esc_html_e( 'To take full advantage of everything Pukeko has to offer, we receommend you to install a number of WordPress plugins.', 'pukeko' ); ?></p>
			<p><a href="<?php echo admin_url( 'themes.php?page=pukeko-welcome&tab=recommended_plugins' ); ?>"><?php esc_html_e( 'See all recommended plugins', 'pukeko' ); ?></a>
			</p>
	</div><!--/.col-->

	<div class="col">
		<h3><?php esc_html_e( '2. See the documentation', 'pukeko' ); ?></h3>
		<p><?php esc_html_e( 'Even if you\'re a long-time WordPress user, we  still recommend you to have a look at our detailed theme documentation.', 'pukeko' ) ?></p>
		<p>
			<a target="_blank"
				 href="<?php esc_html_e( 'https://www.elmastudio.de/en/themes/docs/pukeko/', 'pukeko' ); ?>"><?php esc_html_e( 'See detailed documentation', 'pukeko' ); ?></a>
		</p>
	</div><!--/.col-->

	<div class="col">
		<h3><?php esc_html_e( '3. Start to Customize', 'pukeko' ); ?></h3>
		<p><?php esc_html_e( 'With the help of the the WordPress Customizer, you can easily customize your Pukeko theme.', 'pukeko' ); ?></p>
		<p><a target="_blank" href="<?php echo esc_url( $customizer_url ); ?>"
				class="button button-primary"><?php esc_html_e( 'Go to the Customizer', 'pukeko' ); ?></a>
		</p>
	</div><!--/.col-->

	<div class="welcome-video">

</div><!--/.welcome-video -->
</div><!--/.feature-section-->
