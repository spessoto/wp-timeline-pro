<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Enqueue scripts and styles for the admin.
 */
function wtp_admin_enqueue_scripts_styles( $hook_suffix ) {
	$screen = get_current_screen();

	if ( $screen && ($screen->post_type === 'wtp_timeline' || $screen->post_type === 'wtp_timeline_item') ) {
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );

        if ( ! wp_script_is( 'select2', 'enqueued' ) ) {
            wp_enqueue_style( 'select2', WTP_PLUGIN_URL . 'assets/libs/select2/css/select2.min.css', array(), '4.0.13' );
            wp_enqueue_script( 'select2', WTP_PLUGIN_URL . 'assets/libs/select2/js/select2.min.js', array( 'jquery' ), '4.0.13', true );
        }

        wp_enqueue_style(
            'wtp-admin-style',
            WTP_PLUGIN_URL . 'assets/css/admin-timeline.css',
            array('select2'),
            WTP_VERSION
        );

		wp_enqueue_script(
			'wtp-admin-script',
			WTP_PLUGIN_URL . 'assets/js/admin-timeline.js',
			array( 'jquery', 'wp-color-picker', 'select2' ),
			WTP_VERSION,
			true
		);

        $admin_vars = array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            // 'nonce'    => wp_create_nonce( 'wtp_admin_ajax_nonce' ), // This nonce is unused
            'i18n'     => array(
                'confirm_delete_item' => __( 'Are you sure you want to delete this timeline item? This action cannot be undone directly here.', 'wp-timeline-pro' ),
                'error_adding_item'   => __( 'Error adding item', 'wp-timeline-pro' ),
                'error_deleting_item' => __( 'Error deleting item', 'wp-timeline-pro' ),
                'ajax_error'          => __( 'AJAX request error', 'wp-timeline-pro' ),
            ),
        );
        wp_localize_script( 'wtp-admin-script', 'wtp_admin_vars', $admin_vars );
	}

    // Elementor editor script enqueue block removed.
}
add_action( 'admin_enqueue_scripts', 'wtp_admin_enqueue_scripts_styles' );

/**
 * Enqueue styles and fonts for the front-end.
 */
function wtp_frontend_enqueue_styles() {
    global $post;
    $load_assets = false;

    if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'custom_timeline' ) ) {
        $load_assets = true;
    }

    // Elementor preview mode check removed.

    if ( $load_assets || apply_filters('wtp_force_load_frontend_assets', false) ) {
        wp_enqueue_style(
            'wtp-frontend-style',
            WTP_PLUGIN_URL . 'assets/css/frontend-timeline.css',
            array(),
            WTP_VERSION
        );

        wp_enqueue_script(
            'wtp-frontend-script',
            WTP_PLUGIN_URL . 'assets/js/frontend-timeline.js',
            array('jquery'),
            WTP_VERSION,
            true
        );
    }
}
add_action( 'wp_enqueue_scripts', 'wtp_frontend_enqueue_styles' );

/**
 * Enqueues a selected Google Font if it's determined to be a Google Font.
 *
 * @param string $font_family_css_string The font-family CSS string (e.g., '"Roboto", sans-serif').
 */
function wtp_enqueue_selected_google_font( $font_family_css_string ) {
    if ( empty( $font_family_css_string ) || $font_family_css_string === 'inherit' ) {
        return;
    }

    // Extract the primary font name from the CSS string (e.g., "Roboto" from ""Roboto", sans-serif")
    $font_name = explode( ',', $font_family_css_string )[0];
    $font_name = trim( $font_name, '"\' ' ); // Trim quotes and leading/trailing spaces

    // Basic check against generic CSS fallbacks and common system fonts - these should not be enqueued as Google Fonts.
    // Added common system fonts to this list for robustness.
    $generic_fallbacks = ['serif', 'sans-serif', 'monospace', 'cursive', 'fantasy', 'system-ui', 'arial', 'helvetica', 'verdana', 'tahoma', 'geneva', 'times new roman', 'georgia', 'garamond', 'courier new', 'brush script mt'];
    if (in_array(strtolower($font_name), $generic_fallbacks, true)) {
        return;
    }

    // If, after trimming, the font name is empty (e.g., if the input was just "" or " "), don't proceed.
    if (empty($font_name)) {
        return;
    }

    // Construct the Google Fonts URL with common weights and display=swap.
    // Future improvement: Allow selection/filtering of weights.
    $font_url = 'https://fonts.googleapis.com/css?family=' . urlencode( $font_name ) . ':400,700&display=swap';

    // Use a sanitized version of the font name for the handle to ensure validity.
    $handle = 'wtp-google-font-' . sanitize_title( $font_name );

    // Only enqueue if not already enqueued (WordPress handles this, but it's a good check)
    if ( ! wp_style_is( $handle, 'enqueued' ) ) {
        wp_enqueue_style( $handle, $font_url, array(), null );
    }
}
?>
