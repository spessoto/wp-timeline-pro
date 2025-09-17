<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Registers the [custom_timeline id="X"] shortcode.
 */
function wtp_register_timeline_shortcode() {
	add_shortcode( 'custom_timeline', 'wtp_render_timeline_shortcode' );
}
add_action( 'init', 'wtp_register_timeline_shortcode' );

/**
 * Renders the timeline HTML.
 */
function wtp_render_timeline_shortcode( $atts ) {
	$atts = shortcode_atts(
        array(
            'id' => 0,
            'style_overrides' => array(),
        ),
        $atts,
        'custom_timeline'
    );

	$timeline_id = absint( $atts['id'] );
    $style_overrides = (array) $atts['style_overrides'];

	if ( ! $timeline_id || 'wtp_timeline' !== get_post_type( $timeline_id ) ) {
		return current_user_can( 'edit_posts' ) ? '<p class="wtp-error">' . __( 'Error: Invalid or missing Timeline ID.', 'wp-timeline-pro' ) . '</p>' : '';
	}

	// CPT Settings
	$cpt_font_family_desc  = get_post_meta( $timeline_id, '_wtp_font_family_desc', true );
    $cpt_font_weight_desc  = get_post_meta( $timeline_id, '_wtp_font_weight_desc', true );
	$cpt_font_size_desc    = get_post_meta( $timeline_id, '_wtp_font_size_desc', true );
	$cpt_text_color_desc   = get_post_meta( $timeline_id, '_wtp_text_color_desc', true );

    $cpt_font_family_year  = get_post_meta( $timeline_id, '_wtp_font_family_year', true );
    $cpt_font_weight_year  = get_post_meta( $timeline_id, '_wtp_font_weight_year', true );
	$cpt_font_size_year    = get_post_meta( $timeline_id, '_wtp_year_font_size', true ); // Corrected to match meta key saved in admin
	$cpt_text_color_year   = get_post_meta( $timeline_id, '_wtp_text_color_year', true );

    // Item Title styles were removed from timeline settings
    // $cpt_title_font_size  = get_post_meta( $timeline_id, '_wtp_title_font_size', true ); // REMOVED
    // $cpt_title_text_color = get_post_meta( $timeline_id, '_wtp_title_text_color', true ); // REMOVED

	$cpt_line_color       = get_post_meta( $timeline_id, '_wtp_line_color', true );
    $cpt_marker_color     = get_post_meta( $timeline_id, '_wtp_marker_color', true );
    $cpt_item_bg_color    = get_post_meta( $timeline_id, '_wtp_item_bg_color', true );
    $cpt_no_card_format   = get_post_meta( $timeline_id, '_wtp_no_card_format', true );
    $cpt_anim_left        = get_post_meta( $timeline_id, '_wtp_animation_style_left', true );
    $cpt_anim_right       = get_post_meta( $timeline_id, '_wtp_animation_style_right', true );

    // Determine final values (CPT vs Elementor Overrides)
    $final_font_family_desc  = !empty($style_overrides['font_family_desc']) ? $style_overrides['font_family_desc'] : $cpt_font_family_desc;
    $final_font_weight_desc  = isset($style_overrides['font_weight_desc']) ? $style_overrides['font_weight_desc'] : $cpt_font_weight_desc; // isset to allow '0' or ''
    $final_font_size_desc    = !empty($style_overrides['font_size_desc']) ? $style_overrides['font_size_desc'] : $cpt_font_size_desc;
    $final_text_color_desc   = !empty($style_overrides['text_color_desc']) ? $style_overrides['text_color_desc'] : $cpt_text_color_desc;

    $final_font_family_year  = !empty($style_overrides['font_family_year']) ? $style_overrides['font_family_year'] : $cpt_font_family_year;
    $final_font_weight_year  = isset($style_overrides['font_weight_year']) ? $style_overrides['font_weight_year'] : $cpt_font_weight_year;
    $final_font_size_year    = !empty($style_overrides['font_size_year']) ? $style_overrides['font_size_year'] : $cpt_font_size_year;
    $final_text_color_year   = !empty($style_overrides['text_color_year']) ? $style_overrides['text_color_year'] : $cpt_text_color_year;

    // Item Title no longer has global style settings, will use default or theme CSS.
    // $final_title_font_size  = !empty($style_overrides['title_font_size']) ? $style_overrides['title_font_size'] : $cpt_title_font_size; // REMOVED
    // $final_title_text_color = !empty($style_overrides['title_text_color']) ? $style_overrides['title_text_color'] : $cpt_title_text_color; // REMOVED


    $final_line_color       = !empty($style_overrides['line_color']) ? $style_overrides['line_color'] : $cpt_line_color;
    $final_marker_color     = !empty($style_overrides['marker_color']) ? $style_overrides['marker_color'] : $cpt_marker_color;
    $final_item_bg_color    = !empty($style_overrides['item_bg_color']) ? $style_overrides['item_bg_color'] : $cpt_item_bg_color;
    $final_no_card_format   = isset($style_overrides['no_card_format']) ? $style_overrides['no_card_format'] : $cpt_no_card_format;
    $final_anim_left        = !empty($style_overrides['animation_left']) ? $style_overrides['animation_left'] : $cpt_anim_left;
    $final_anim_right       = !empty($style_overrides['animation_right']) ? $style_overrides['animation_right'] : $cpt_anim_right;


    // Enqueue Google Fonts
    if ( ! empty( $final_font_family_desc ) && function_exists('wtp_enqueue_selected_google_font') ) {
        wtp_enqueue_selected_google_font( $final_font_family_desc );
    }
    if ( ! empty( $final_font_family_year ) && $final_font_family_year !== $final_font_family_desc && function_exists('wtp_enqueue_selected_google_font') ) {
        wtp_enqueue_selected_google_font( $final_font_family_year );
    }


    // CSS Variables for inline styles
    $css_font_family_desc  = !empty($final_font_family_desc) ? esc_attr($final_font_family_desc) : 'inherit';
    $css_font_weight_desc  = !empty($final_font_weight_desc) ? esc_attr($final_font_weight_desc) : 'inherit';
    $css_font_size_desc    = !empty($final_font_size_desc) ? absint($final_font_size_desc) . 'px' : 'inherit';
    $css_text_color_desc   = !empty($final_text_color_desc) ? sanitize_hex_color($final_text_color_desc) : 'inherit';

    $css_font_family_year  = !empty($final_font_family_year) ? esc_attr($final_font_family_year) : 'inherit';
    $css_font_weight_year  = !empty($final_font_weight_year) ? esc_attr($final_font_weight_year) : 'inherit';
    $css_font_size_year    = !empty($final_font_size_year) ? absint($final_font_size_year) . 'px' : '1.3em'; // Default for year
    $css_text_color_year   = !empty($final_text_color_year) ? sanitize_hex_color($final_text_color_year) : '#555555';


    $css_line_color       = !empty($final_line_color) ? sanitize_hex_color($final_line_color) : '#cccccc';
    $css_marker_bg_color  = !empty($final_marker_color) ? sanitize_hex_color($final_marker_color) : '#ffffff';
    $css_item_bg_color    = !empty($final_item_bg_color) ? sanitize_hex_color($final_item_bg_color) : '#f9f9f9';
    $is_no_card_format    = ($final_no_card_format === 'yes');


	$args_items = array(
		'post_type'      => 'wtp_timeline_item',
		'posts_per_page' => -1,
		'meta_key'       => '_wtp_parent_timeline_id',
		'meta_value'     => $timeline_id,
		'orderby'        => 'menu_order title',
		'order'          => 'ASC',
	);
	$timeline_items_query = new WP_Query( $args_items );

	if ( ! $timeline_items_query->have_posts() ) {
		return current_user_can( 'edit_posts' ) ? '<p class="wtp-notice">' . __( 'No items found for this timeline.', 'wp-timeline-pro' ) . '</p>' : '';
	}

    $unique_timeline_id_attr = 'wtp-timeline-' . esc_attr( $timeline_id ) . '-' . uniqid();
    $container_classes = ['wtp-timeline-container'];
    if ($is_no_card_format) {
        $container_classes[] = 'wtp-no-card-format';
    }

	ob_start();
	?>
	<div class="<?php echo esc_attr(implode(' ', $container_classes)); ?>" id="<?php echo $unique_timeline_id_attr; ?>">
        <style>
            #<?php echo $unique_timeline_id_attr; ?>.wtp-timeline-container .wtp-timeline-line {
                background-color: <?php echo $css_line_color; ?>;
            }
            #<?php echo $unique_timeline_id_attr; ?>.wtp-timeline-container .wtp-timeline-item-content-inner {
                <?php if (!$is_no_card_format): ?>
                background-color: <?php echo $css_item_bg_color; ?>;
                <?php endif; ?>
            }
            #<?php echo $unique_timeline_id_attr; ?>.wtp-timeline-container .wtp-timeline-item-description {
                font-family: <?php echo $css_font_family_desc; ?>;
                font-weight: <?php echo $css_font_weight_desc; ?>;
                font-size: <?php echo $css_font_size_desc; ?>;
                color: <?php echo $css_text_color_desc; ?>;
            }
            #<?php echo $unique_timeline_id_attr; ?>.wtp-timeline-container .wtp-timeline-item-year {
                font-family: <?php echo $css_font_family_year; ?>;
                font-weight: <?php echo $css_font_weight_year; ?>;
                font-size: <?php echo $css_font_size_year; ?>;
                color: <?php echo $css_text_color_year; ?>;
            }
            /* Item title no longer has global styles, will inherit or be styled by general CSS */
            #<?php echo $unique_timeline_id_attr; ?>.wtp-timeline-container .wtp-timeline-item-marker {
                border-color: <?php echo $css_line_color; ?>;
                background-color: <?php echo $css_marker_bg_color; ?>;
            }
            <?php if (!$is_no_card_format): ?>
            #<?php echo $unique_timeline_id_attr; ?>.wtp-timeline-container .wtp-timeline-item.wtp-item-left .wtp-timeline-item-content-inner::after {
                border-left-color: <?php echo $css_item_bg_color; ?>;
            }
            #<?php echo $unique_timeline_id_attr; ?>.wtp-timeline-container .wtp-timeline-item.wtp-item-right .wtp-timeline-item-content-inner::after {
                border-right-color: <?php echo $css_item_bg_color; ?>;
            }
             @media screen and (max-width: 768px) {
                #<?php echo $unique_timeline_id_attr; ?>.wtp-timeline-container .wtp-timeline-item.wtp-item-left .wtp-timeline-item-content-inner::after,
                #<?php echo $unique_timeline_id_attr; ?>.wtp-timeline-container .wtp-timeline-item.wtp-item-right .wtp-timeline-item-content-inner::after {
                     border-right-color: <?php echo $css_item_bg_color; ?> !important;
                     border-left-color: transparent !important;
                }
            }
            <?php endif; ?>
        </style>

		<div class="wtp-timeline-line"></div>
		<div class="wtp-timeline-items">
			<?php
			$item_count = 0;
			while ( $timeline_items_query->have_posts() ) : $timeline_items_query->the_post();
				$item_id    = get_the_ID();
				$item_year  = get_post_meta( $item_id, '_wtp_item_year', true );
                $item_title = get_the_title(); // Title is obtained from the CPT
				$item_content = apply_filters('the_content', get_the_content());

				$alignment_class = ( $item_count % 2 == 0 ) ? 'wtp-item-left' : 'wtp-item-right';
                $current_item_animation = ($alignment_class === 'wtp-item-left') ? $final_anim_left : $final_anim_right;
                $data_animation = !empty($current_item_animation) ? esc_attr($current_item_animation) : 'none';

                $item_classes = ['wtp-timeline-item', $alignment_class];
                // The wtp-item-no-card class is not necessary here as styles are applied via .wtp-timeline-container.wtp-no-card-format
                // if ($is_no_card_format) { $item_classes[] = 'wtp-item-no-card'; }

				?>
				<div class="<?php echo esc_attr(implode(' ', $item_classes)); ?>" data-animation="<?php echo $data_animation; ?>">
					<div class="wtp-timeline-item-marker"></div>
					<div class="wtp-timeline-item-content">
                        <div class="wtp-timeline-item-content-inner">
                            <?php if ( ! empty( $item_year ) ) : ?>
                                <div class="wtp-timeline-item-year"><?php echo esc_html( $item_year ); ?></div>
                            <?php endif; ?>
                            <?php if ( ! empty( $item_title ) ) : ?>
                                <h3 class="wtp-timeline-item-title"><?php echo esc_html( $item_title ); ?></h3>
                            <?php endif; ?>
                            <div class="wtp-timeline-item-description">
                                <?php echo $item_content; ?>
                            </div>
                        </div>
					</div>
				</div>
				<?php
				$item_count++;
			endwhile;
			wp_reset_postdata();
			?>
		</div>
	</div>
	<?php
	return ob_get_clean();
}
?>
