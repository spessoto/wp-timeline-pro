<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * AJAX handler to add a new item to the timeline.
 */
add_action( 'wp_ajax_wtp_add_timeline_item', 'wtp_ajax_add_timeline_item_callback' );
function wtp_ajax_add_timeline_item_callback() {
    // Verify nonce for security
    check_ajax_referer( 'wtp_manage_timeline_items_nonce', 'nonce' );

    // Get and sanitize POST data
    $timeline_id = isset( $_POST['timeline_id'] ) ? absint( $_POST['timeline_id'] ) : 0;
    $item_year   = isset( $_POST['year'] ) ? sanitize_text_field( $_POST['year'] ) : '';
    $item_title  = isset( $_POST['title'] ) ? sanitize_text_field( $_POST['title'] ) : ''; // Will be empty from quick-add form
    $item_desc   = isset( $_POST['description'] ) ? wp_kses_post( $_POST['description'] ) : ''; // Allows safe HTML

    // Validate timeline ID first
    if ( ! $timeline_id ) {
        wp_send_json_error( array( 'message' => __( 'Invalid Timeline ID.', 'wp-timeline-pro' ) ) );
        return;
    }

    // Check user permissions for the specific timeline
    if ( ! current_user_can( 'edit_post', $timeline_id ) ) {
        wp_send_json_error( array( 'message' => __( 'You do not have permission to add items to this timeline.', 'wp-timeline-pro' ) ) );
        return;
    }

    // Title is no longer strictly required from this AJAX form, an empty title is acceptable for wp_insert_post.
    // The JS part (Subtask 3) was modified to send title: '' when the title field was removed from quick-add form (Subtask 4).

    // Create the new timeline item post
    $new_item_args = array(
        'post_title'   => $item_title, // If empty, WordPress handles it (e.g., "(no title)")
        'post_content' => $item_desc,
        'post_status'  => 'publish', // Publish immediately
        'post_type'    => 'wtp_timeline_item',
    );
    $new_item_id = wp_insert_post( $new_item_args );

    if ( is_wp_error( $new_item_id ) ) {
        wp_send_json_error( array( 'message' => $new_item_id->get_error_message() ) );
        return;
    }

    // Save item metadata
    update_post_meta( $new_item_id, '_wtp_item_year', $item_year );
    update_post_meta( $new_item_id, '_wtp_parent_timeline_id', $timeline_id );

    // Determine order (menu_order) - new item goes to the end
    $existing_items_args = array(
        'post_type' => 'wtp_timeline_item',
        'posts_per_page' => 1, // Only the last item
        'meta_key' => '_wtp_parent_timeline_id',
        'meta_value' => $timeline_id,
        'orderby' => 'menu_order',
        'order' => 'DESC'
    );
    $last_item_query = new WP_Query($existing_items_args); // Use WP_Query for best practice
    $new_order = 0;
    if ($last_item_query->have_posts()) {
        $last_item = $last_item_query->posts[0];
        $new_order = $last_item->menu_order + 1;
    }
    wp_update_post(array('ID' => $new_item_id, 'menu_order' => $new_order));
    wp_reset_postdata(); // Important after custom loops with WP_Query


    // Get the updated list of items to send back
    ob_start();
    // The wtp_render_timeline_items_list_ajax function should exist in admin-metaboxes.php
    if (function_exists('wtp_render_timeline_items_list_ajax')) {
        wtp_render_timeline_items_list_ajax( $timeline_id );
    } else {
        // Fallback or error message if the function is not available
        echo '<p>' . esc_html__( 'Error: Item list rendering function not available.', 'wp-timeline-pro' ) . '</p>';
    }
    $items_html = ob_get_clean();

    wp_send_json_success( array(
        'message' => __( 'Item added successfully! You can edit it to add a title.', 'wp-timeline-pro' ), // Updated message
        'items_html' => $items_html
    ) );

    wp_die(); // Required to correctly end WordPress AJAX requests
}


/**
 * AJAX handler to delete a timeline item.
 */
add_action( 'wp_ajax_wtp_delete_timeline_item', 'wtp_ajax_delete_timeline_item_callback' );
function wtp_ajax_delete_timeline_item_callback() {
    $item_id = isset( $_POST['item_id'] ) ? absint( $_POST['item_id'] ) : 0;
    // Verify item-specific nonce for security
    check_ajax_referer( 'wtp_delete_item_nonce_' . $item_id, 'nonce' );

    if ( ! $item_id ) {
        wp_send_json_error( array( 'message' => __( 'Invalid item ID.', 'wp-timeline-pro' ) ) );
        return;
    }

    // Check user permissions to delete this specific post
    if ( ! current_user_can( 'delete_post', $item_id ) ) {
        wp_send_json_error( array( 'message' => __( 'You do not have permission to delete this item.', 'wp-timeline-pro' ) ) );
        return;
    }

    // Get parent timeline ID before deleting, to reload the correct list
    $timeline_id = get_post_meta( $item_id, '_wtp_parent_timeline_id', true );

    // Delete the item post (moves to trash by default)
    $deleted = wp_delete_post( $item_id, false ); // false to not force deletion (goes to trash)

    if ( ! $deleted ) {
        wp_send_json_error( array( 'message' => __( 'Error deleting item.', 'wp-timeline-pro' ) ) );
        return;
    }

    // Get the updated list of items to send back
    ob_start();
    if ($timeline_id && function_exists('wtp_render_timeline_items_list_ajax')) {
        wtp_render_timeline_items_list_ajax( absint($timeline_id) );
    } else {
        // Fallback or error message
        echo '<p>' . esc_html__( 'Item list could not be reloaded.', 'wp-timeline-pro') . '</p>';
    }
    $items_html = ob_get_clean();

    wp_send_json_success( array(
        'message' => __( 'Item deleted successfully!', 'wp-timeline-pro' ),
        'items_html' => $items_html
    ) );

    wp_die();
}

/**
 * AJAX handler to get the timeline items list (used for refresh if necessary).
 */
add_action( 'wp_ajax_wtp_get_timeline_items_list', 'wtp_ajax_get_timeline_items_list_callback' );
function wtp_ajax_get_timeline_items_list_callback() {
    check_ajax_referer( 'wtp_manage_timeline_items_nonce', 'nonce' ); // Reusing the manager nonce

    $timeline_id = isset( $_POST['timeline_id'] ) ? absint( $_POST['timeline_id'] ) : 0;

    if ( ! $timeline_id ) { // Check if timeline_id is valid before permission check
        wp_send_json_error( array( 'message' => __( 'Timeline ID not provided.', 'wp-timeline-pro' ) ) );
        return;
    }

    if ( ! current_user_can( 'edit_post', $timeline_id ) ) { // Specific permission check
        wp_send_json_error( array( 'message' => __( 'You do not have permission to view items for this timeline.', 'wp-timeline-pro' ) ) );
        return;
    }

    ob_start();
    if (function_exists('wtp_render_timeline_items_list_ajax')) {
        wtp_render_timeline_items_list_ajax( $timeline_id );
    } else {
        echo '<p>' . esc_html__( 'Error: Item list rendering function not available.', 'wp-timeline-pro' ) . '</p>';
    }
    $items_html = ob_get_clean();

    wp_send_json_success( array( 'items_html' => $items_html ) );
    wp_die();
}

/**
 * AJAX handler to render the timeline preview for Elementor.
 */
add_action( 'wp_ajax_wtp_get_elementor_preview', 'wtp_ajax_get_elementor_preview_callback' );
function wtp_ajax_get_elementor_preview_callback() {
    // Verify nonce for security
    check_ajax_referer( 'wtp_elementor_preview_nonce', 'nonce' );

    $timeline_id = isset( $_POST['timeline_id'] ) ? absint( $_POST['timeline_id'] ) : 0;

    if ( ! $timeline_id ) { // Check if timeline_id is valid before permission check
        wp_send_json_error( array( 'message' => __( 'Timeline ID not provided.', 'wp-timeline-pro' ) ) );
        return;
    }

    // Check user permissions for the specific timeline
    if ( ! current_user_can( 'edit_post', $timeline_id ) ) {
        wp_send_json_error( array( 'message' => __( 'You do not have permission to preview this timeline.', 'wp-timeline-pro' ) ) );
        return;
    }

    // Elementor settings are sent as a JSON string.
    // JavaScript already filters to send only relevant settings (override_*).
    $elementor_settings_json = isset( $_POST['elementor_settings'] ) ? stripslashes( $_POST['elementor_settings'] ) : '{}';
    $elementor_settings = json_decode( $elementor_settings_json, true );

    // Build style overrides array from received Elementor settings
    $style_overrides = [];
    // Keys in $elementor_settings should already be override keys (e.g., 'override_line_color')
    // The wtp_render_timeline_shortcode function expects keys without the 'override_' prefix within the 'style_overrides' array.
    if ( isset( $elementor_settings['override_line_color'] ) && !empty( $elementor_settings['override_line_color'] ) ) {
        $style_overrides['line_color'] = $elementor_settings['override_line_color'];
    }
    if ( isset( $elementor_settings['override_item_bg_color'] ) && !empty( $elementor_settings['override_item_bg_color'] ) ) {
        $style_overrides['item_bg_color'] = $elementor_settings['override_item_bg_color'];
    }
    if ( isset( $elementor_settings['override_text_color'] ) && !empty( $elementor_settings['override_text_color'] ) ) {
        $style_overrides['text_color'] = $elementor_settings['override_text_color'];
    }
    if ( isset( $elementor_settings['override_title_text_color'] ) && !empty( $elementor_settings['override_title_text_color'] ) ) {
        $style_overrides['title_text_color'] = $elementor_settings['override_title_text_color'];
    }
    if ( isset( $elementor_settings['override_year_text_color'] ) && !empty( $elementor_settings['override_year_text_color'] ) ) {
        $style_overrides['year_text_color'] = $elementor_settings['override_year_text_color'];
    }
    // Add other overrides here if defined in the widget and sent by JS

    // Calls the shortcode rendering function, which now accepts 'style_overrides'
    if ( function_exists( 'wtp_render_timeline_shortcode' ) ) {
        $timeline_html = wtp_render_timeline_shortcode( array(
            'id' => $timeline_id,
            'style_overrides' => $style_overrides,
            // No need to pass 'elementor_settings' here, as we have already extracted the overrides
        ) );
        wp_send_json_success( array( 'html' => $timeline_html ) );
    } else {
        wp_send_json_error( array( 'message' => __( 'Timeline rendering function not found.', 'wp-timeline-pro' ) ) );
    }

    wp_die();
}

?>
