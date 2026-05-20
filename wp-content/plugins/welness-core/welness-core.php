<?php
/**
 * Plugin Name: Welness Core
 * Plugin URI: https://yourwebsite.com/welness-core
 * Description: Core functionality for the Welness theme/site
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://yourwebsite.com
 * Text Domain: welness-core
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    die;
}

// Define plugin constants
define('WELNESS_CORE_VERSION', '1.0.0');
define('WELNESS_CORE_PATH', plugin_dir_path(__FILE__));
define('WELNESS_CORE_URL', plugin_dir_url(__FILE__));

/**
 * The code that runs during plugin activation.
 */
function activate_welness_core() {
    // Activation code here
}
register_activation_hook(__FILE__, 'activate_welness_core');

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_welness_core() {
    // Deactivation code here
}
register_deactivation_hook(__FILE__, 'deactivate_welness_core');

/**
 * Load plugin textdomain.
 */
function welness_core_load_textdomain() {
    load_plugin_textdomain('welness-core', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'welness_core_load_textdomain');

/**
 * Implementation of plugin functionality.
 */
function welness_core_init() {
    // // Custom post types
    // require_once WELNESS_CORE_PATH . 'includes/post-types.php';
    
    // // Custom taxonomies
    // require_once WELNESS_CORE_PATH . 'includes/taxonomies.php';
    
    // // Shortcodes
    // require_once WELNESS_CORE_PATH . 'includes/shortcodes.php';
    
    // // Widgets
    // require_once WELNESS_CORE_PATH . 'includes/widgets.php';

    // actions and filters
    // add_action('woocommerce_account_content', 'show_upcoming_appointments');
}
add_action('init', 'welness_core_init');

/**
 * Enqueue scripts and styles.
 */
function welness_core_enqueue_scripts() {
    // wp_enqueue_style('welness-core-css', WELNESS_CORE_URL . 'assets/css/welness-core.css', array(), WELNESS_CORE_VERSION);
    // wp_enqueue_script('welness-core-js', WELNESS_CORE_URL . 'assets/js/welness-core.js', array('jquery'), WELNESS_CORE_VERSION, true);
}
add_action('wp_enqueue_scripts', 'welness_core_enqueue_scripts');

