<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * WTP Timeline Elementor Widget.
 */
class WTP_Timeline_Elementor_Widget extends \Elementor\Widget_Base {

	public function get_name() { return 'wtp_timeline'; }
	public function get_title() { return __( 'WP Timeline Pro', 'wp-timeline-pro' ); }
	public function get_icon() { return 'eicon-time-line'; }
	public function get_categories() { return [ 'general' ];}
	public function get_keywords() { return [ 'timeline', 'history', 'events', 'custom timeline', 'wtp' ]; } // Removed 'linha do tempo'
    public function get_custom_help_url() { return admin_url( 'edit.php?post_type=wtp_timeline' ); }

	protected function _register_controls() {
		$this->start_controls_section(
			'section_content',
			['label' => __( 'Timeline Content', 'wp-timeline-pro' )]
		);

		$timelines_options = [];
		$timelines = get_posts( [
            'post_type' => 'wtp_timeline', 'posts_per_page' => -1,
            'orderby' => 'title', 'order' => 'ASC', 'post_status' => 'publish',
        ] );

		if ( ! empty( $timelines ) ) {
			foreach ( $timelines as $timeline ) $timelines_options[ $timeline->ID ] = $timeline->post_title;
		} else {
			$timelines_options[''] = __( 'No published timelines found.', 'wp-timeline-pro' );
		}

		$this->add_control( 'selected_timeline_id', [
            'label' => __( 'Select Timeline', 'wp-timeline-pro' ),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => $timelines_options,
            'default' => !empty($timelines_options) && key($timelines_options) !== '' ? key($timelines_options) : '',
            'description' => __( 'Choose which published timeline you want to display.', 'wp-timeline-pro' ) .
                            (empty($timelines) ? ' <a href="' . esc_url(admin_url('post-new.php?post_type=wtp_timeline')) . '" target="_blank">' . __('Create New Timeline', 'wp-timeline-pro') . '</a>' : ''),
            'label_block' => true,
        ]);
        $this->add_control( 'edit_timeline_link', [
            'type' => \Elementor\Controls_Manager::BUTTON, 'separator' => 'before',
            'button_type' => 'default', 'text' => __( 'Edit Selected Timeline', 'wp-timeline-pro' ),
            'event' => 'wtp:editTimeline', 'condition' => ['selected_timeline_id!' => ''],
        ]);
		$this->end_controls_section();

        $this->start_controls_section( 'section_style_overrides', [
            'label' => __( 'Style Overrides (Optional)', 'wp-timeline-pro' ),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE,
        ]);
        $this->add_control( 'override_styles_notice', [
            'type' => \Elementor\Controls_Manager::RAW_HTML,
            'raw' => __( 'Use these controls to override the styles defined in the original timeline. Leave blank to use the timeline\'s styles.', 'wp-timeline-pro' ),
            'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
        ]);

        $google_fonts = function_exists('wtp_get_google_fonts_list') ? wtp_get_google_fonts_list() : ['inherit' => __( 'Theme Default', 'wp-timeline-pro' )];
        $font_weights = function_exists('wtp_get_font_weights') ? wtp_get_font_weights() : ['' => __( 'Default', 'wp-timeline-pro' )];
        $animations = function_exists('wtp_get_animation_styles') ? wtp_get_animation_styles() : ['none' => __( 'None', 'wp-timeline-pro' )];

        $this->add_control( 'override_no_card_format', [
            'label' => __( 'Display without Card Background?', 'wp-timeline-pro' ),
            'type' => \Elementor\Controls_Manager::SWITCHER, 'label_on' => __( 'Yes', 'wp-timeline-pro' ),
            'label_off' => __( 'No', 'wp-timeline-pro' ), 'return_value' => 'yes', 'default' => '',
        ]);

        // Description Fonts
        $this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [
            'name' => 'override_desc_typography',
            'label' => __( 'Description Typography', 'wp-timeline-pro' ),
            'scheme' => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_3, // Text
            'selector' => '{{WRAPPER}} .wtp-timeline-item-description',
             'fields_options' => [
                'font_family' => ['default' => ''], // To allow inheritance
                'font_weight' => ['default' => ''],
                // 'font_size' is already covered by the group
            ],
        ]);
        $this->add_control( 'override_text_color_desc', [
            'label' => __( 'Description Text Color', 'wp-timeline-pro' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .wtp-timeline-item-description' => 'color: {{VALUE}};'],
        ]);

        // Year Fonts
         $this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [
            'name' => 'override_year_typography',
            'label' => __( 'Year Typography', 'wp-timeline-pro' ),
            'scheme' => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_2, // Secondary Headline
            'selector' => '{{WRAPPER}} .wtp-timeline-item-year',
            'fields_options' => [
                'font_family' => ['default' => ''],
                'font_weight' => ['default' => ''],
            ],
        ]);
        $this->add_control( 'override_text_color_year', [
            'label' => __( 'Year Text Color', 'wp-timeline-pro' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .wtp-timeline-item-year' => 'color: {{VALUE}};'],
        ]);

        // General Styles
        $this->add_control( 'override_line_color', [
            'label' => __( 'Timeline Line Color', 'wp-timeline-pro' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .wtp-timeline-line' => 'background-color: {{VALUE}};',
                '{{WRAPPER}} .wtp-timeline-item-marker' => 'border-color: {{VALUE}};',
            ],
        ]);
        $this->add_control( 'override_item_bg_color', [
            'label' => __( 'Item Card Background Color', 'wp-timeline-pro' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .wtp-timeline-item-content-inner' => 'background-color: {{VALUE}};',
                '{{WRAPPER}} .wtp-timeline-item.wtp-item-left .wtp-timeline-item-content-inner::after' => 'border-left-color: {{VALUE}};',
                '{{WRAPPER}} .wtp-timeline-item.wtp-item-right .wtp-timeline-item-content-inner::after' => 'border-right-color: {{VALUE}};',
                '{{WRAPPER}} @media screen and (max-width: 768px) .wtp-timeline-item .wtp-timeline-item-content-inner::after' => 'border-right-color: {{VALUE}} !important; border-left-color: transparent !important;',
            ],
            'condition' => ['override_no_card_format!' => 'yes']
        ]);

        // Animations
        $this->add_control( 'override_animation_left', [
            'label' => __( 'Animation Left Items', 'wp-timeline-pro' ),
            'type' => \Elementor\Controls_Manager::SELECT, 'options' => $animations, 'default' => '',
        ]);
        $this->add_control( 'override_animation_right', [
            'label' => __( 'Animation Right Items', 'wp-timeline-pro' ),
            'type' => \Elementor\Controls_Manager::SELECT, 'options' => $animations, 'default' => '',
        ]);
		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$timeline_id = $settings['selected_timeline_id'];

		if ( empty( $timeline_id ) ) {
            if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                $this->render_editor_placeholder(__( 'Please select a timeline to display.', 'wp-timeline-pro' ));
            }
			return;
		}

        $style_overrides = [];
        // Collect overrides (Group_Control_Typography already comes with correct names for font_family, font_weight, font_size etc.)
        if ( !empty( $settings['override_desc_typography_font_family'] ) ) $style_overrides['font_family_desc'] = $settings['override_desc_typography_font_family'];
        if ( !empty( $settings['override_desc_typography_font_weight'] ) ) $style_overrides['font_weight_desc'] = $settings['override_desc_typography_font_weight'];
        if ( !empty( $settings['override_desc_typography_font_size']['size'] ) ) $style_overrides['font_size_desc'] = $settings['override_desc_typography_font_size']['size']; // Unit already comes with the size
        if ( !empty( $settings['override_text_color_desc'] ) ) $style_overrides['text_color_desc'] = $settings['override_text_color_desc'];

        if ( !empty( $settings['override_year_typography_font_family'] ) ) $style_overrides['font_family_year'] = $settings['override_year_typography_font_family'];
        if ( !empty( $settings['override_year_typography_font_weight'] ) ) $style_overrides['font_weight_year'] = $settings['override_year_typography_font_weight'];
        if ( !empty( $settings['override_year_typography_font_size']['size'] ) ) $style_overrides['font_size_year'] = $settings['override_year_typography_font_size']['size'];
        if ( !empty( $settings['override_text_color_year'] ) ) $style_overrides['text_color_year'] = $settings['override_text_color_year'];

        if ( !empty( $settings['override_line_color'] ) )       $style_overrides['line_color'] = $settings['override_line_color'];
        if ( !empty( $settings['override_item_bg_color'] ) )    $style_overrides['item_bg_color'] = $settings['override_item_bg_color'];
        if ( isset( $settings['override_no_card_format'] ) )     $style_overrides['no_card_format'] = $settings['override_no_card_format'];
        if ( !empty( $settings['override_animation_left'] ) )   $style_overrides['animation_left'] = $settings['override_animation_left'];
        if ( !empty( $settings['override_animation_right'] ) )  $style_overrides['animation_right'] = $settings['override_animation_right'];

        if ( function_exists( 'wtp_render_timeline_shortcode' ) ) {
            echo wtp_render_timeline_shortcode( [ 'id' => $timeline_id, 'style_overrides' => $style_overrides ] );
        } else {
            // ... (error handling)
        }
	}
    protected function render_editor_placeholder( $message, $type = 'warning' ) {
        // In a real scenario, you would output HTML for the placeholder
        echo '<div class="elementor-alert elementor-alert-' . esc_attr( $type ) . '">' . esc_html( $message ) . '</div>';
    }
	protected function _content_template() { /* ... (as before, for AJAX preview) ... */ }
}
