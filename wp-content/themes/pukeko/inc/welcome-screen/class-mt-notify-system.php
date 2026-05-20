<?php

if ( ! class_exists( 'MT_Notify_System' ) ) {
	/**
	 * Class MT_Notify_System
	 */
	class MT_Notify_System {
		/**
		 * @param $ver
		 *
		 * @return mixed
		 */
		public static function version_check( $ver ) {
			$pukeko = wp_get_theme();

			return version_compare( $pukeko['Version'], $ver, '>=' );
		}

		/**
		 * @return bool
		 */
		public static function is_not_static_page() {
			return 'page' == get_option( 'show_on_front' ) ? true : false;
		}

		/**
		 * @return bool
		 */
		public static function has_widgets() {
			if ( ! is_active_sidebar( 'homepage-slider' ) && ! is_active_sidebar( 'content-area' ) ) {
				return false;
			}

			return true;
		}

		/**
		 * @return bool
		 */
		public static function newmsag_has_posts() {
			$args  = array(
				's' => 'Gary Johns: \'What is Aleppo\'',
			);
			$query = get_posts( $args );

			if ( ! empty( $query ) ) {
				return true;
			}

			return false;
		}

		/**
		 * @return bool
		 */
		public static function has_content() {
			$check = array(
				'widgets' => self::has_widgets(),
				'posts'   => self::newmsag_has_posts(),
			);

			if ( $check['widgets'] && $check['posts'] ) {
				return true;
			}

			return false;
		}



//wordpress.org/plugins/one-click-demo-import/
		/**
		 * @return bool
		 */
		public static function check_demo_import() {
			if ( file_exists( ABSPATH . 'wp-content/plugins/one-click-demo-importr/one-click-demo-import.php' ) ) {
				get_template_part( ABSPATH . 'wp-admin/includes/plugin.php' );

				return is_plugin_active( 'one-click-demo-importr/one-click-demo-importphp' );
			}

			return false;
		}

		/**
		 * @return bool
		 */
		public static function check_plugin_is_installed( $slug ) {
			if ( file_exists( ABSPATH . 'wp-content/plugins/' . $slug . '/' . $slug . '.php' ) ) {
				return true;
			}

			return false;
		}

		/**
		 * @return bool
		 */
		public static function check_plugin_is_active( $slug ) {
			if ( file_exists( ABSPATH . 'wp-content/plugins/' . $slug . '/' . $slug . '.php' ) ) {
				get_template_part( ABSPATH . 'wp-admin/includes/plugin.php' );

				return is_plugin_active( $slug . '/' . $slug . '.php' );
			}
		}

		public static function has_import_plugin( $slug = null ) {
			$return = self::has_content();

			if ( $return ) {
				return true;
			}
			$check = array(
				'installed' => self::check_plugin_is_installed( $slug ),
				'active'    => self::check_plugin_is_active( $slug ),
			);

			if ( ! $check['installed'] || ! $check['active'] ) {
				return false;
			}

			return true;
		}

		public static function has_import_plugins() {
			$check = array(
				'one-click-demo-importr'       => array(
					'installed' => false,
					'active' => false,
				),
				'widget-importer-exporter' => array(
					'installed' => false,
					'active' => false,
				),
			);

			$content = self::has_content();
			$return  = false;
			if ( $content ) {
				return true;
			}

			$stop = false;
			foreach ( $check as $plugin => $val ) {
				if ( $stop ) {
					continue;
				}

				$check[ $plugin ]['installed'] = self::check_plugin_is_installed( $plugin );
				$check[ $plugin ]['active']    = self::check_plugin_is_active( $plugin );

				if ( ! $check[ $plugin ]['installed'] || ! $check[ $plugin ]['active'] ) {
					$return = true;
					$stop   = true;
				}
			}

			return $return;
		}

		public static function widget_importer_exporter_title() {
			$installed = self::check_plugin_is_installed( 'one-click-demo-import' );
			if ( ! $installed ) {
				return __( 'Install: One Click Demo Import Plugin', 'pukeko' );
			}

			$active = self::check_plugin_is_active( 'one-click-demo-import' );
			if ( $installed && ! $active ) {
				return __( 'Activate: One Click Demo Import Plugin', 'pukeko' );
			}

			return __( 'Install: one-click-demo-import Plugin', 'pukeko' );
		}

		public static function wordpress_importer_title() {
			$installed = self::check_plugin_is_installed( 'one-click-demo-import' );
			if ( ! $installed ) {
				return __( 'Install: One Click Demo Import', 'pukeko' );
			}

			$active = self::check_plugin_is_active( 'one-click-demo-import' );
			if ( $installed && ! $active ) {
				return __( 'Activate: One Click Demo Import', 'pukeko' );
			}

			return __( 'Install: One Click Demo Import', 'pukeko' );
		}

		/**
		 * @return string
		 */
		public static function wordpress_importer_description() {
			$installed = self::check_plugin_is_installed( 'one-click-demo-import' );
			if ( ! $installed ) {
				return __( 'Please install the One Click Demo Import plugin to create the demo content.', 'pukeko' );
			}

			$active = self::check_plugin_is_active( 'one-click-demo-import' );
			if ( $installed && ! $active ) {
				return __( 'Please activate the One Click Demo Import plugin to create the demo content.', 'pukeko' );
			}

			return __( 'Please install the One Click Demo Import plugin to create the demo content.', 'pukeko' );
		}

		public static function widget_importer_exporter_description() {
			$installed = self::check_plugin_is_installed( 'one-click-demo-import' );
			if ( ! $installed ) {
				return __( 'Please install theOne Click Demo Import plugin to create the demo content', 'pukeko' );
			}

			$active = self::check_plugin_is_active( 'one-click-demo-import' );
			if ( $installed && ! $active ) {
				return __( 'Please activate the One Click Demo Import plugin to create the demo content.', 'pukeko' );
			}

			return __( 'Please install the One Click Demo Import plugin to create the demo content', 'pukeko' );

		}

		public static function create_plugin_requirement_title( $install_text, $activate_text, $plugin_slug ) {

			if ( '' == $plugin_slug ) {
				return;
			}
			if ( '' == $install_text && '' == $activate_text ) {
				return;
			}
			if ( '' == $install_text &&  '' == $activate_text ) {
				$install_text = $activate_text;
			} elseif ( '' == $activate_text &&  '' == $install_text ) {
				$activate_text = $install_text;
			}

			$installed = self::check_plugin_is_installed( $plugin_slug );
			if ( ! $installed ) {
				return $install_text;
			} elseif ( ! self::check_plugin_is_active( $plugin_slug ) && $installed ) {
				return $activate_text;
			} else {
				return '';
			}

		}

		/**
		 * @return bool
		 */
		public static function is_not_template_front_page() {
			$page_id = get_option( 'page_on_front' );

			return get_page_template_slug( $page_id ) == 'page-templates/frontpage-template.php' ? true : false;
		}
	}
}// End if().
