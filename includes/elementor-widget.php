<?php
// Se este arquivo for chamado diretamente, aborte.
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
	public function get_keywords() { return [ 'timeline', 'history', 'events', 'custom timeline', 'wtp', 'linha do tempo' ]; }
    public function get_custom_help_url() { return admin_url( 'edit.php?post_type=wtp_timeline' ); }

	protected function _register_controls() {
		$this->start_controls_section(
			'section_content',
			['label' => __( 'Conteúdo da Timeline', 'wp-timeline-pro' )]
		);

		$timelines_options = [];
		$timelines = get_posts( [
            'post_type' => 'wtp_timeline', 'posts_per_page' => -1,
            'orderby' => 'title', 'order' => 'ASC', 'post_status' => 'publish',
        ] );

		if ( ! empty( $timelines ) ) {
			foreach ( $timelines as $timeline ) $timelines_options[ $timeline->ID ] = $timeline->post_title;
		} else {
			$timelines_options[''] = __( 'Nenhuma timeline publicada encontrada.', 'wp-timeline-pro' );
		}

		$this->add_control( 'selected_timeline_id', [
            'label' => __( 'Selecione a Timeline', 'wp-timeline-pro' ),
            'type' => \Elementor\Controls_Manager::SELECT,
            'options' => $timelines_options,
            'default' => !empty($timelines_options) && key($timelines_options) !== '' ? key($timelines_options) : '',
            'description' => __( 'Escolha qual timeline publicada você deseja exibir.', 'wp-timeline-pro' ) .
                            (empty($timelines) ? ' <a href="' . esc_url(admin_url('post-new.php?post_type=wtp_timeline')) . '" target="_blank">' . __('Criar Nova Timeline', 'wp-timeline-pro') . '</a>' : ''),
            'label_block' => true,
        ]);
        $this->add_control( 'edit_timeline_link', [
            'type' => \Elementor\Controls_Manager::BUTTON, 'separator' => 'before',
            'button_type' => 'default', 'text' => __( 'Editar Timeline Selecionada', 'wp-timeline-pro' ),
            'event' => 'wtp:editTimeline', 'condition' => ['selected_timeline_id!' => ''],
        ]);
		$this->end_controls_section();

        $this->start_controls_section( 'section_style_overrides', [
            'label' => __( 'Substituições de Estilo (Opcional)', 'wp-timeline-pro' ),
            'tab' => \Elementor\Controls_Manager::TAB_STYLE,
        ]);
        $this->add_control( 'override_styles_notice', [
            'type' => \Elementor\Controls_Manager::RAW_HTML,
            'raw' => __( 'Use estes controles para substituir os estilos definidos na timeline original. Deixe em branco para usar os estilos da timeline.', 'wp-timeline-pro' ),
            'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
        ]);

        $google_fonts = function_exists('wtp_get_google_fonts_list') ? wtp_get_google_fonts_list() : ['inherit' => 'Padrão do Tema'];
        $font_weights = function_exists('wtp_get_font_weights') ? wtp_get_font_weights() : ['' => 'Padrão'];
        $animations = function_exists('wtp_get_animation_styles') ? wtp_get_animation_styles() : ['none' => 'Nenhuma'];

        $this->add_control( 'override_no_card_format', [
            'label' => __( 'Exibir sem Card de Fundo?', 'wp-timeline-pro' ),
            'type' => \Elementor\Controls_Manager::SWITCHER, 'label_on' => __( 'Sim', 'wp-timeline-pro' ),
            'label_off' => __( 'Não', 'wp-timeline-pro' ), 'return_value' => 'yes', 'default' => '',
        ]);

        // Fontes Descrição
        $this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [
            'name' => 'override_desc_typography',
            'label' => __( 'Tipografia da Descrição', 'wp-timeline-pro' ),
            'scheme' => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_3, // Text
            'selector' => '{{WRAPPER}} .wtp-timeline-item-description',
             'fields_options' => [
                'font_family' => ['default' => ''], // Para permitir herdar
                'font_weight' => ['default' => ''],
                // 'font_size' já está coberto pelo grupo
            ],
        ]);
        $this->add_control( 'override_text_color_desc', [
            'label' => __( 'Cor do Texto da Descrição', 'wp-timeline-pro' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .wtp-timeline-item-description' => 'color: {{VALUE}};'],
        ]);

        // Fontes Ano
         $this->add_group_control( \Elementor\Group_Control_Typography::get_type(), [
            'name' => 'override_year_typography',
            'label' => __( 'Tipografia do Ano', 'wp-timeline-pro' ),
            'scheme' => \Elementor\Core\Schemes\Typography::TYPOGRAPHY_2, // Secondary Headline
            'selector' => '{{WRAPPER}} .wtp-timeline-item-year',
            'fields_options' => [
                'font_family' => ['default' => ''],
                'font_weight' => ['default' => ''],
            ],
        ]);
        $this->add_control( 'override_text_color_year', [
            'label' => __( 'Cor do Texto do Ano', 'wp-timeline-pro' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => ['{{WRAPPER}} .wtp-timeline-item-year' => 'color: {{VALUE}};'],
        ]);

        // Estilos Gerais
        $this->add_control( 'override_line_color', [
            'label' => __( 'Cor da Linha da Timeline', 'wp-timeline-pro' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .wtp-timeline-line' => 'background-color: {{VALUE}};',
                '{{WRAPPER}} .wtp-timeline-item-marker' => 'border-color: {{VALUE}};',
            ],
        ]);
        $this->add_control( 'override_item_bg_color', [
            'label' => __( 'Cor de Fundo do Card do Item', 'wp-timeline-pro' ),
            'type' => \Elementor\Controls_Manager::COLOR,
            'selectors' => [
                '{{WRAPPER}} .wtp-timeline-item-content-inner' => 'background-color: {{VALUE}};',
                '{{WRAPPER}} .wtp-timeline-item.wtp-item-left .wtp-timeline-item-content-inner::after' => 'border-left-color: {{VALUE}};',
                '{{WRAPPER}} .wtp-timeline-item.wtp-item-right .wtp-timeline-item-content-inner::after' => 'border-right-color: {{VALUE}};',
                '{{WRAPPER}} @media screen and (max-width: 768px) .wtp-timeline-item .wtp-timeline-item-content-inner::after' => 'border-right-color: {{VALUE}} !important; border-left-color: transparent !important;',
            ],
            'condition' => ['override_no_card_format!' => 'yes']
        ]);

        // Animações
        $this->add_control( 'override_animation_left', [
            'label' => __( 'Animação Itens Esquerda', 'wp-timeline-pro' ),
            'type' => \Elementor\Controls_Manager::SELECT, 'options' => $animations, 'default' => '',
        ]);
        $this->add_control( 'override_animation_right', [
            'label' => __( 'Animação Itens Direita', 'wp-timeline-pro' ),
            'type' => \Elementor\Controls_Manager::SELECT, 'options' => $animations, 'default' => '',
        ]);
		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$timeline_id = $settings['selected_timeline_id'];

		if ( empty( $timeline_id ) ) {
            if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                $this->render_editor_placeholder(__( 'Por favor, selecione uma timeline para exibir.', 'wp-timeline-pro' ));
            }
			return;
		}

        $style_overrides = [];
        // Coleta de overrides (os Group_Control_Typography já vêm com os nomes corretos para font_family, font_weight, font_size etc.)
        if ( !empty( $settings['override_desc_typography_font_family'] ) ) $style_overrides['font_family_desc'] = $settings['override_desc_typography_font_family'];
        if ( !empty( $settings['override_desc_typography_font_weight'] ) ) $style_overrides['font_weight_desc'] = $settings['override_desc_typography_font_weight'];
        if ( !empty( $settings['override_desc_typography_font_size']['size'] ) ) $style_overrides['font_size_desc'] = $settings['override_desc_typography_font_size']['size']; // Unidade já vem com o tamanho
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
            // ... (tratamento de erro)
        }
	}
    protected function render_editor_placeholder( $message, $type = 'warning' ) { /* ... */ }
	protected function _content_template() { /* ... (como estava antes, para AJAX preview) ... */ }
}
