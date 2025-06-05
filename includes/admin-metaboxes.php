<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * List of Google Fonts for selection.
 * This list can be expanded or loaded from an API/JSON in the future.
 * Format: 'Font Name' => 'CSS Font Family String'
 */
function wtp_get_google_fonts_list() {
    return apply_filters('wtp_google_fonts_list', array(
        'Arial, sans-serif' => 'Arial, Helvetica, sans-serif',
        'Verdana, sans-serif' => 'Verdana, Geneva, sans-serif',
        'Tahoma, sans-serif' => 'Tahoma, Geneva, sans-serif',
        'Trebuchet MS, sans-serif' => '"Trebuchet MS", Helvetica, sans-serif',
        'Times New Roman, serif' => '"Times New Roman", Times, serif',
        'Georgia, serif' => 'Georgia, serif',
        'Garamond, serif' => 'Garamond, serif',
        'Courier New, monospace' => '"Courier New", Courier, monospace',
        'Brush Script MT, cursive' => '"Brush Script MT", cursive',
        // Popular Google Fonts
        'Roboto' => '"Roboto", sans-serif',
        'Open Sans' => '"Open Sans", sans-serif',
        'Lato' => '"Lato", sans-serif',
        'Montserrat' => '"Montserrat", sans-serif',
        'Oswald' => '"Oswald", sans-serif',
        'Source Sans Pro' => '"Source Sans Pro", sans-serif',
        'Raleway' => '"Raleway", sans-serif',
        'PT Sans' => '"PT Sans", sans-serif',
        'Noto Sans' => '"Noto Sans", sans-serif',
        'Merriweather' => '"Merriweather", serif',
        'Playfair Display' => '"Playfair Display", serif',
        'Nunito Sans' => '"Nunito Sans", sans-serif',
    ));
}

/**
 * List of animation styles for timeline items.
 */
function wtp_get_animation_styles() {
    return apply_filters('wtp_animation_styles', array(
        'none' => __( 'None', 'wp-timeline-pro' ),
        'fade-in' => __( 'Fade In', 'wp-timeline-pro' ),
        'slide-in-left' => __( 'Slide In from Left', 'wp-timeline-pro' ),
        'slide-in-right' => __( 'Slide In from Right', 'wp-timeline-pro' ),
        'slide-in-up' => __( 'Slide In from Bottom', 'wp-timeline-pro' ),
        'zoom-in' => __( 'Zoom In', 'wp-timeline-pro' ),
    ));
}


/**
 * Adds metaboxes for the 'wtp_timeline' CPT.
 */
function wtp_add_timeline_metaboxes() {
	add_meta_box(
		'wtp_timeline_settings_metabox',
		__( 'Timeline Settings', 'wp-timeline-pro' ),
		'wtp_timeline_settings_metabox_callback',
		'wtp_timeline', // CPT
		'normal',       // Context
		'high'          // Priority
	);
    add_meta_box(
		'wtp_timeline_shortcode_metabox',
		__( 'Timeline Shortcode', 'wp-timeline-pro' ),
		'wtp_timeline_shortcode_metabox_callback',
		'wtp_timeline',
		'side', // Side context
		'default'
	);
    add_meta_box(
        'wtp_timeline_items_manager_metabox', // Renamed to reflect management
        __( 'Manage Timeline Items', 'wp-timeline-pro' ),
        'wtp_timeline_items_manager_metabox_callback', // New callback
        'wtp_timeline',
        'normal',
        'low'
    );
}
// The 'wtp_add_timeline_metaboxes' function is called via 'register_meta_box_cb' in the CPT.

/**
 * Callback for the timeline settings metabox.
 */
function wtp_timeline_settings_metabox_callback( $post ) {
	wp_nonce_field( 'wtp_save_timeline_settings_data', 'wtp_timeline_settings_nonce' );

    // Meta for Description styles
    $font_family_desc = get_post_meta( $post->ID, '_wtp_font_family_desc', true );
    $font_weight_desc = get_post_meta( $post->ID, '_wtp_font_weight_desc', true );
    $font_size_desc = get_post_meta( $post->ID, '_wtp_font_size_desc', true );
    $text_color_desc = get_post_meta( $post->ID, '_wtp_text_color_desc', true );

    // Meta for Year styles
    $font_family_year = get_post_meta( $post->ID, '_wtp_font_family_year', true );
    $font_weight_year = get_post_meta( $post->ID, '_wtp_font_weight_year', true );
	$year_font_size  = get_post_meta( $post->ID, '_wtp_year_font_size', true ); // Existing
	$year_text_color = get_post_meta( $post->ID, '_wtp_year_text_color', true ); // Existing

    // Meta for Title styles (existing, may or may not be used by the shortcode directly)
	$title_font_size = get_post_meta( $post->ID, '_wtp_title_font_size', true );
	$title_text_color= get_post_meta( $post->ID, '_wtp_title_text_color', true );

    // Meta for general timeline styles
	$line_color      = get_post_meta( $post->ID, '_wtp_line_color', true );
    $marker_color    = get_post_meta( $post->ID, '_wtp_marker_color', true );
    $item_bg_color   = get_post_meta( $post->ID, '_wtp_item_bg_color', true );

    // Meta for specific animations
    $animation_style_left = get_post_meta( $post->ID, '_wtp_animation_style_left', true );
    $animation_style_right = get_post_meta( $post->ID, '_wtp_animation_style_right', true );

    // Meta for no card format
    $no_card_format = get_post_meta( $post->ID, '_wtp_no_card_format', true );

    $google_fonts = wtp_get_google_fonts_list();
    $animations = wtp_get_animation_styles();
    $font_weights = wtp_get_font_weights(); // Added function

	?>
    <table class="form-table">
        <tbody>
            <tr><td colspan="2"><h3><?php _e('Item Description Styles', 'wp-timeline-pro'); ?></h3></td></tr>
            <tr>
                <th><label for="wtp_font_family_desc"><?php _e( 'Description Font:', 'wp-timeline-pro' ); ?></label></th>
                <td>
                    <select id="wtp_font_family_desc" name="wtp_font_family_desc" class="widefat wtp-select2-font">
                        <option value=""><?php _e( 'Theme Default', 'wp-timeline-pro' ); ?></option>
                        <?php foreach ( $google_fonts as $name => $css_value ) : ?>
                            <option value="<?php echo esc_attr( $css_value ); ?>" <?php selected( $font_family_desc, $css_value ); ?> data-font-name="<?php echo esc_attr( $name ); ?>">
                                <?php echo esc_html( $name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="wtp_font_weight_desc"><?php _e( 'Description Font Weight:', 'wp-timeline-pro' ); ?></label></th>
                <td>
                    <select id="wtp_font_weight_desc" name="wtp_font_weight_desc" class="widefat">
                        <?php foreach ( $font_weights as $value => $label ) : ?>
                            <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $font_weight_desc, $value ); ?>>
                                <?php echo esc_html( $label ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="wtp_font_size_desc"><?php _e( 'Description Font Size (px):', 'wp-timeline-pro' ); ?></label></th>
                <td><input type="number" id="wtp_font_size_desc" name="wtp_font_size_desc" value="<?php echo esc_attr( $font_size_desc ); ?>" class="small-text" placeholder="14"/></td>
            </tr>
            <tr>
                <th><label for="wtp_text_color_desc"><?php _e( 'Description Text Color:', 'wp-timeline-pro' ); ?></label></th>
                <td><input type="text" id="wtp_text_color_desc" name="wtp_text_color_desc" value="<?php echo esc_attr( $text_color_desc ); ?>" class="wtp-color-picker" data-default-color="#333333" /></td>
            </tr>

            <tr><td colspan="2"><h3><?php _e('Item Year Styles', 'wp-timeline-pro'); ?></h3></td></tr>
            <tr>
                <th><label for="wtp_font_family_year"><?php _e( 'Year Font:', 'wp-timeline-pro' ); ?></label></th>
                <td>
                    <select id="wtp_font_family_year" name="wtp_font_family_year" class="widefat wtp-select2-font">
                        <option value=""><?php _e( 'Theme Default', 'wp-timeline-pro' ); ?></option>
                        <?php foreach ( $google_fonts as $name => $css_value ) : ?>
                            <option value="<?php echo esc_attr( $css_value ); ?>" <?php selected( $font_family_year, $css_value ); ?> data-font-name="<?php echo esc_attr( $name ); ?>">
                                <?php echo esc_html( $name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="wtp_font_weight_year"><?php _e( 'Year Font Weight:', 'wp-timeline-pro' ); ?></label></th>
                <td>
                    <select id="wtp_font_weight_year" name="wtp_font_weight_year" class="widefat">
                        <?php foreach ( $font_weights as $value => $label ) : ?>
                            <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $font_weight_year, $value ); ?>>
                                <?php echo esc_html( $label ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="wtp_year_font_size"><?php _e( 'Year Font Size (px):', 'wp-timeline-pro' ); ?></label></th>
                <td><input type="number" id="wtp_year_font_size" name="wtp_year_font_size" value="<?php echo esc_attr( $year_font_size ); ?>" class="small-text" placeholder="18"/></td>
            </tr>
            <tr>
                <th><label for="wtp_year_text_color"><?php _e( 'Year Text Color:', 'wp-timeline-pro' ); ?></label></th>
                <td><input type="text" id="wtp_year_text_color" name="wtp_year_text_color" value="<?php echo esc_attr( $year_text_color ); ?>" class="wtp-color-picker" data-default-color="#555555" /></td>
            </tr>

            <tr><td colspan="2"><h3><?php _e('Item Title Styles', 'wp-timeline-pro'); ?></h3></td></tr>
            <tr>
                <th><label for="wtp_title_font_size"><?php _e( 'Item Title Font Size (px):', 'wp-timeline-pro' ); ?></label></th>
                <td><input type="number" id="wtp_title_font_size" name="wtp_title_font_size" value="<?php echo esc_attr( $title_font_size ); ?>" class="small-text" placeholder="16"/></td>
            </tr>
            <tr>
                <th><label for="wtp_title_text_color"><?php _e( 'Item Title Text Color:', 'wp-timeline-pro' ); ?></label></th>
                <td><input type="text" id="wtp_title_text_color" name="wtp_title_text_color" value="<?php echo esc_attr( $title_text_color ); ?>" class="wtp-color-picker" data-default-color="#222222" /></td>
            </tr>

            <tr><td colspan="2"><h3><?php _e('General Timeline Styles', 'wp-timeline-pro'); ?></h3></td></tr>
            <tr>
                <th><label for="wtp_line_color"><?php _e( 'Timeline Line Color:', 'wp-timeline-pro' ); ?></label></th>
                <td><input type="text" id="wtp_line_color" name="wtp_line_color" value="<?php echo esc_attr( $line_color ); ?>" class="wtp-color-picker" data-default-color="#cccccc"/></td>
            </tr>
            <tr>
                <th><label for="wtp_marker_color"><?php _e( 'Marker Color on Line:', 'wp-timeline-pro' ); ?></label></th>
                <td><input type="text" id="wtp_marker_color" name="wtp_marker_color" value="<?php echo esc_attr( $marker_color ); ?>" class="wtp-color-picker" data-default-color="#ffffff"/>
                <p class="description"><?php _e( 'Marker fill color. The border will use the line color.', 'wp-timeline-pro' ); ?></p></td>
            </tr>
            <tr class="wtp-card-bg-setting">
                <th><label for="wtp_item_bg_color"><?php _e( 'Item Card Background Color:', 'wp-timeline-pro' ); ?></label></th>
                <td><input type="text" id="wtp_item_bg_color" name="wtp_item_bg_color" value="<?php echo esc_attr( $item_bg_color ); ?>" class="wtp-color-picker" data-default-color="#f9f9f9"/></td>
            </tr>
             <tr>
                <th><label for="wtp_no_card_format"><?php _e( 'Disable Card Format:', 'wp-timeline-pro' ); ?></label></th>
                <td><input type="checkbox" id="wtp_no_card_format" name="wtp_no_card_format" value="yes" <?php checked( $no_card_format, 'yes' ); ?> />
                    <p class="description"><?php _e( 'Check to remove background and borders from timeline item cards.', 'wp-timeline-pro' ); ?></p>
                </td>
            </tr>

            <tr><td colspan="2"><h3><?php _e('Animation Styles', 'wp-timeline-pro'); ?></h3></td></tr>
            <tr>
                <th><label for="wtp_animation_style_left"><?php _e( 'Animation Left Items:', 'wp-timeline-pro' ); ?></label></th>
                <td>
                    <select id="wtp_animation_style_left" name="wtp_animation_style_left" class="widefat">
                        <?php foreach ( $animations as $value => $label ) : ?>
                            <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $animation_style_left, $value ); ?>>
                                <?php echo esc_html( $label ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="wtp_animation_style_right"><?php _e( 'Animation Right Items:', 'wp-timeline-pro' ); ?></label></th>
                <td>
                    <select id="wtp_animation_style_right" name="wtp_animation_style_right" class="widefat">
                        <?php foreach ( $animations as $value => $label ) : ?>
                            <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $animation_style_right, $value ); ?>>
                                <?php echo esc_html( $label ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
        </tbody>
    </table>
	<?php
}

/**
 * Callback for the timeline shortcode metabox.
 */
function wtp_timeline_shortcode_metabox_callback( $post ) {
    ?>
    <p><?php _e( 'Copy and paste this shortcode into your page builder or content:', 'wp-timeline-pro' ); ?></p>
    <input type="text" readonly="readonly" value="[custom_timeline id=&quot;<?php echo $post->ID; ?>&quot;]" class="widefat wtp-shortcode-input" onfocus="this.select();" />
    <?php
}

/**
 * Callback for the timeline items manager metabox.
 */
function wtp_timeline_items_manager_metabox_callback( $post ) {
    $timeline_id = $post->ID;
    wp_nonce_field( 'wtp_manage_timeline_items_nonce', 'wtp_timeline_items_manager_nonce' );
    ?>
    <div id="wtp-timeline-items-list-container">
        <?php wtp_render_timeline_items_list_ajax( $timeline_id ); // Function to render the list ?>
    </div>

    <h4><?php _e( 'Add New Item:', 'wp-timeline-pro' ); ?></h4>
    <table class="form-table" id="wtp-add-item-form">
        <tbody>
            <tr>
                <th><label for="wtp_new_item_year"><?php _e( 'Year:', 'wp-timeline-pro' ); ?></label></th>
                <td><input type="text" id="wtp_new_item_year" name="wtp_new_item_year" class="regular-text" placeholder="<?php esc_attr_e( 'E.g., 2024', 'wp-timeline-pro' ); ?>"/></td>
            </tr>
            <tr>
                <th><label for="wtp_new_item_title"><?php _e( 'Item Title:', 'wp-timeline-pro' ); ?></label></th>
                <td><input type="text" id="wtp_new_item_title" name="wtp_new_item_title" class="widefat" required /></td>
            </tr>
            <tr>
                <th><label for="wtp_new_item_description"><?php _e( 'Description:', 'wp-timeline-pro' ); ?></label></th>
                <td><textarea id="wtp_new_item_description" name="wtp_new_item_description" rows="5" class="widefat"></textarea></td>
            </tr>
            <tr>
                <th></th>
                <td>
                    <button type="button" id="wtp-add-new-item-button" class="button button-primary" data-timeline-id="<?php echo esc_attr( $timeline_id ); ?>">
                        <?php _e( 'Add Item to Timeline', 'wp-timeline-pro' ); ?>
                    </button>
                    <span class="spinner" style="float: none; vertical-align: middle;"></span>
                </td>
            </tr>
        </tbody>
    </table>
    <p><small><?php _e( 'Items are ordered by the "Order" field. You can edit the order of each item by clicking "Edit".', 'wp-timeline-pro' ); ?></small></p>
    <?php
}

/**
 * Helper function to render the list of items (used on initial load and AJAX).
 * This function will also be called by the AJAX handler.
 */
function wtp_render_timeline_items_list_ajax( $timeline_id ) {
    $args = array(
        'post_type' => 'wtp_timeline_item',
        'posts_per_page' => -1,
        'meta_key' => '_wtp_parent_timeline_id',
        'meta_value' => $timeline_id,
        'orderby' => 'menu_order title', // Order by 'menu_order' then by title
        'order' => 'ASC'
    );
    $timeline_items = get_posts( $args );

    echo '<h4>' . __( 'Current Items:', 'wp-timeline-pro' ) . '</h4>';
    if ( ! empty( $timeline_items ) ) {
        echo '<ul id="wtp-current-items-list" class="wtp-styled-list">';
        foreach ( $timeline_items as $item ) {
            $edit_link = get_edit_post_link( $item->ID );
            $item_year = get_post_meta( $item->ID, '_wtp_item_year', true );
            $item_order = $item->menu_order;

            echo '<li data-item-id="' . esc_attr( $item->ID ) . '">';
            echo '<strong>' . esc_html( $item->post_title ) . '</strong> (' . esc_html( $item_year ) . ')';
            echo ' - <small>' . sprintf(__( 'Order: %s', 'wp-timeline-pro' ), esc_html($item_order)) . '</small>';
            echo ' <a href="' . esc_url( $edit_link ) . '" class="button button-small">' . __( 'Edit', 'wp-timeline-pro' ) . '</a>';
            echo ' <button type="button" class="button button-small button-link-delete wtp-delete-item-button" data-item-id="' . esc_attr( $item->ID ) . '" data-nonce="' . esc_attr(wp_create_nonce('wtp_delete_item_nonce_' . $item->ID)) . '">' . __( 'Delete', 'wp-timeline-pro' ) . '</button>';
            echo '</li>';
        }
        echo '</ul>';
    } else {
        echo '<p id="wtp-no-items-message">' . __( 'No items added to this timeline yet.', 'wp-timeline-pro' ) . '</p>';
    }
}


/**
 * Saves timeline metabox data.
 */
function wtp_save_timeline_settings_data( $post_id ) {
	if ( ! isset( $_POST['wtp_timeline_settings_nonce'] ) || ! wp_verify_nonce( $_POST['wtp_timeline_settings_nonce'], 'wtp_save_timeline_settings_data' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;
    if ( 'wtp_timeline' !== get_post_type( $post_id ) ) return;

    // Define the fields to be saved and their sanitization types
    $fields_to_process = array(
        // Description Fields
        'wtp_font_family_desc' => 'sanitize_text_field',
        'wtp_font_weight_desc' => 'sanitize_text_field',
        'wtp_font_size_desc'   => 'absint',
        'wtp_text_color_desc'  => 'sanitize_hex_color',
        // Year Fields
        'wtp_font_family_year' => 'sanitize_text_field',
        'wtp_font_weight_year' => 'sanitize_text_field',
        'wtp_year_font_size'   => 'absint', // Existing
        'wtp_year_text_color'  => 'sanitize_hex_color', // Existing
        // Title Fields (Existentes)
        'wtp_title_font_size'  => 'absint',
        'wtp_title_text_color' => 'sanitize_hex_color',
        // General Style Fields (Existentes)
        'wtp_line_color'       => 'sanitize_hex_color',
        'wtp_marker_color'     => 'sanitize_hex_color',
        'wtp_item_bg_color'    => 'sanitize_hex_color',
        // Animation Fields
        'wtp_animation_style_left'  => 'sanitize_text_field',
        'wtp_animation_style_right' => 'sanitize_text_field',
    );

    foreach ( $fields_to_process as $field_key_post => $sanitize_callback ) {
        $meta_key = '_' . $field_key_post; // e.g., _wtp_font_family_desc

        if ( isset( $_POST[$field_key_post] ) ) {
            $value = $_POST[$field_key_post];
            // For empty strings from text inputs or select, treat as "delete meta"
            if ( $value === '' && $sanitize_callback !== 'is_checkbox' && $sanitize_callback !== 'sanitize_hex_color') { // Empty colors are fine, they become 'inherit' or default in shortcode
                 delete_post_meta( $post_id, $meta_key );
            } elseif ( function_exists( $sanitize_callback ) ) {
                update_post_meta( $post_id, $meta_key, call_user_func( $sanitize_callback, $value ) );
            } else {
                // Fallback to sanitize_text_field if the callback is not recognized (unlikely for defined ones)
                update_post_meta( $post_id, $meta_key, sanitize_text_field( $value ) );
            }
        } else {
            // If the field is not set in POST at all (e.g. some specific cases, or if it was removed from form)
            // and it's not a checkbox (checkboxes are handled next), delete its meta.
             if ($sanitize_callback !== 'is_checkbox') {
                 delete_post_meta( $post_id, $meta_key );
             }
        }
    }

    // Handle 'wtp_no_card_format' checkbox separately
    $meta_key_no_card = '_wtp_no_card_format';
    if ( isset( $_POST['wtp_no_card_format'] ) && $_POST['wtp_no_card_format'] === 'yes' ) {
        update_post_meta( $post_id, $meta_key_no_card, 'yes' );
    } else {
        update_post_meta( $post_id, $meta_key_no_card, 'no' );
    }
}
add_action( 'save_post_wtp_timeline', 'wtp_save_timeline_settings_data' );

/**
 * Adds metaboxes for the 'wtp_timeline_item' CPT.
 */
function wtp_add_timeline_item_metaboxes() {
	add_meta_box(
		'wtp_timeline_item_details_metabox',
		__( 'Timeline Item Details', 'wp-timeline-pro' ),
		'wtp_timeline_item_details_metabox_callback',
		'wtp_timeline_item',
		'normal',
		'high'
	);
}

/**
 * Callback for the timeline item details metabox.
 */
function wtp_timeline_item_details_metabox_callback( $post ) {
	wp_nonce_field( 'wtp_save_timeline_item_details_data', 'wtp_timeline_item_details_nonce' );

	$item_year = get_post_meta( $post->ID, '_wtp_item_year', true );
    $parent_timeline_id = get_post_meta( $post->ID, '_wtp_parent_timeline_id', true );

    if ( empty( $parent_timeline_id ) && isset( $_GET['wtp_parent_id'] ) ) {
        $parent_timeline_id = absint( $_GET['wtp_parent_id'] );
    }

	?>
    <table class="form-table">
        <tbody>
            <tr>
                <th><label for="wtp_item_year"><?php _e( 'Year:', 'wp-timeline-pro' ); ?></label></th>
                <td><input type="text" id="wtp_item_year" name="wtp_item_year" value="<?php echo esc_attr( $item_year ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'E.g., 2023', 'wp-timeline-pro' ); ?>"/></td>
            </tr>
            <tr>
                <th><label for="wtp_parent_timeline_id"><?php _e( 'Associate with Timeline:', 'wp-timeline-pro' ); ?></label></th>
                <td>
                    <select name="wtp_parent_timeline_id" id="wtp_parent_timeline_id" class="widefat">
                        <option value=""><?php _e( '-- Select a Timeline --', 'wp-timeline-pro' ); ?></option>
                        <?php
                        $timelines = get_posts( array( 'post_type' => 'wtp_timeline', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );
                        if ( $timelines ) {
                            foreach ( $timelines as $timeline_opt ) {
                                echo '<option value="' . esc_attr( $timeline_opt->ID ) . '" ' . selected( $parent_timeline_id, $timeline_opt->ID, false ) . '>' . esc_html( $timeline_opt->post_title ) . '</option>';
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
        </tbody>
    </table>
    <p><em><?php _e('Use the "Order" field in the "Page Attributes" box (usually on the right) to set the order of this item in the timeline. The main description of the item is managed by the standard WordPress editor above.', 'wp-timeline-pro'); ?></em></p>
	<?php
}

/**
 * Saves timeline item metabox data.
 */
function wtp_save_timeline_item_details_data( $post_id ) {
	if ( ! isset( $_POST['wtp_timeline_item_details_nonce'] ) || ! wp_verify_nonce( $_POST['wtp_timeline_item_details_nonce'], 'wtp_save_timeline_item_details_data' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;
    if ( 'wtp_timeline_item' !== get_post_type( $post_id ) ) return;

	if ( isset( $_POST['wtp_item_year'] ) ) {
		update_post_meta( $post_id, '_wtp_item_year', sanitize_text_field( $_POST['wtp_item_year'] ) );
	}
    if ( isset( $_POST['wtp_parent_timeline_id'] ) ) {
        update_post_meta( $post_id, '_wtp_parent_timeline_id', absint( $_POST['wtp_parent_timeline_id'] ) );
    }
}
add_action( 'save_post_wtp_timeline_item', 'wtp_save_timeline_item_details_data' );

/**
 * Returns a list of common font weights.
 *
 * @return array Associative array of font weights, value => label.
 */
function wtp_get_font_weights() {
    return apply_filters('wtp_font_weights_list', array(
        ''        => __( 'Default (from theme/font)', 'wp-timeline-pro' ),
        'normal'  => __( 'Normal (400)', 'wp-timeline-pro' ),
        'bold'    => __( 'Bold (700)', 'wp-timeline-pro' ),
        '100'     => __( '100 - Thin / Hairline', 'wp-timeline-pro' ),
        '200'     => __( '200 - Extra Light / Ultra Light', 'wp-timeline-pro' ),
        '300'     => __( '300 - Light', 'wp-timeline-pro' ),
        '400'     => __( '400 - Normal / Regular', 'wp-timeline-pro' ),
        '500'     => __( '500 - Medium', 'wp-timeline-pro' ),
        '600'     => __( '600 - Semi Bold / Demi Bold', 'wp-timeline-pro' ),
        '700'     => __( '700 - Bold', 'wp-timeline-pro' ),
        '800'     => __( '800 - Extra Bold / Ultra Bold', 'wp-timeline-pro' ),
        '900'     => __( '900 - Black / Heavy', 'wp-timeline-pro' ),
    ));
}
?>
