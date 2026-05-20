<?php
/**
 * Recommended Plugins
 */
global $pukeko_required_actions, $pukeko_recommended_plugins;
wp_enqueue_style( 'plugin-install' );
wp_enqueue_script( 'plugin-install' );
wp_enqueue_script( 'updates' );
?>

<div class="feature-section recommended-plugins three-col demo-import-boxed" id="plugin-filter">
	<?php

	foreach ( $pukeko_recommended_plugins as $plugin => $prop ) {
		$plugin_file_path = false;
		foreach (get_plugins() as $key => $value) {
			if ( substr($key, 0, strlen($plugin) + 1 ) === $plugin . '/' ) {
				$plugin_file_path = $key;
				break;
			}
		}
		$info   = $this->call_plugin_api( $plugin );
		$icon   = $this->check_for_icon( $info->icons );
		$active = $this->check_active( $plugin_file_path );
		$url    = $this->create_action_link( $active['needs'], $plugin, $plugin_file_path );

		$label = '';

		switch ( $active['needs'] ) {
			case 'install':
				$class = 'install-now button';
				$label = __( 'Install', 'pukeko' );
				break;
			case 'activate':
				$class = 'activate-now button button-primary';
				$label = __( 'Activate', 'pukeko' );
				break;
			case 'deactivate':
				$class = 'deactivate-now button';
				$label = __( 'Deactivate', 'pukeko' );
				break;
		}

		?>
		<div class="col plugin_box">
			<img src="<?php echo esc_attr( $icon ) ?>" alt="plugin box image">
			<span class="version"><?php echo __( 'Version:', 'pukeko' ); ?><?php echo $info->version ?></span>
			<span
				class="separator">|</span> <?php echo wp_kses_post( $info->author ) ?>
			<div
				class="action_bar <?php echo ( 'install' !== $active['needs'] && $active['status'] ) ? 'active' : '' ?>">
				<span
					class="plugin_name"><?php echo ( 'install' !== $active['needs'] && $active['status'] ) ? 'Active: ' : '' ?><?php echo $info->name; ?></span>
			</div>
			<span
				class="plugin-card-<?php echo esc_attr( $plugin ) ?> action_button <?php echo ( 'install' !== $active['needs'] && $active['status'] ) ? 'active' : '' ?>">
				<a data-slug="<?php echo esc_attr( $plugin ) ?>" class="<?php echo $class; ?>"
					 href="<?php echo esc_url( $url ) ?>"> <?php echo $label ?> </a>
			</span>
		</div>
	<?php }// End foreach().
	?>
</div>
