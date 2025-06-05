<?php
/**
 * Plugin Name: WP Timeline Pro
 * Plugin URI:  https://example.com/wp-timeline-pro
 * Description: Creates custom, feature-rich timelines with shortcodes for WordPress. Includes options for customizing fonts, colors, animations, item management, and an Elementor widget.
 * Version:     1.2.0
 * Author:      Your Name Here
 * Author URI:  https://example.com
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wp-timeline-pro
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// Define plugin constants
define( 'WTP_VERSION', '1.2.0' ); // Updated version
define( 'WTP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WTP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Include necessary files
require_once WTP_PLUGIN_DIR . 'includes/cpt.php';
require_once WTP_PLUGIN_DIR . 'includes/admin-metaboxes.php';
require_once WTP_PLUGIN_DIR . 'includes/shortcodes.php';
require_once WTP_PLUGIN_DIR . 'includes/enqueue.php';
require_once WTP_PLUGIN_DIR . 'includes/ajax-handlers.php';

/**
 * Function to register the Elementor widget.
 */
function wtp_register_elementor_widget( $widgets_manager ) {
    // Check if Elementor is active
	if ( ! did_action( 'elementor/loaded' ) ) {
		return;
	}

	require_once WTP_PLUGIN_DIR . 'includes/elementor-widget.php';
	$widgets_manager->register( new \WTP_Timeline_Elementor_Widget() );
}
add_action( 'elementor/widgets/register', 'wtp_register_elementor_widget' ); // Correct hook for Elementor 3.5+
// For older versions of Elementor, the hook was 'elementor/widgets/widgets_registered'.
// 'elementor/widgets/register' is the most current.

/**
 * Function to run on plugin activation.
 */
function wtp_activate_plugin() {
    wtp_register_timeline_cpt();
    wtp_register_timeline_item_cpt();
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'wtp_activate_plugin' );

/**
 * Function to run on plugin deactivation.
 */
function wtp_deactivate_plugin() {
    flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'wtp_deactivate_plugin' );

/**
 * Loads the text domain for translation.
 */
function wtp_load_textdomain() {
    load_plugin_textdomain( 'wp-timeline-pro', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'wtp_load_textdomain' );

/**
 * Adds a link to the plugin settings on the plugins page.
 */
function wtp_add_settings_link( $links ) {
    $settings_link = '<a href="edit.php?post_type=wtp_timeline">' . __( 'Manage Timelines', 'wp-timeline-pro' ) . '</a>';
    array_unshift( $links, $settings_link );
    return $links;
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wtp_add_settings_link' );

/**
 * Checks if Elementor is active and displays a notice if it is not.
 * This is useful if the widget is a central part of the plugin.
 */
function wtp_check_elementor_active() {
    if ( ! did_action( 'elementor/loaded' ) ) {
        add_action( 'admin_notices', function() {
            ?>
            <div class="notice notice-warning is-dismissible">
                <p><?php _e( 'WP Timeline Pro plugin requires Elementor to function correctly with its widget. Please install and activate Elementor.', 'wp-timeline-pro' ); ?></p>
            </div>
            <?php
        });
    }
}
// We could call wtp_check_elementor_active() on 'admin_init', but since the widget is only registered
// if Elementor is loaded, the notice may not be strictly necessary unless
// other functionalities critically depend on Elementor.
// For now, conditional widget registration is sufficient.

?>
