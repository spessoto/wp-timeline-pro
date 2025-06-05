<?php
// Se este arquivo for chamado diretamente, aborte.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Enfileira scripts e estilos para o admin.
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
            'nonce'    => wp_create_nonce( 'wtp_admin_ajax_nonce' ),
            'i18n'     => array(
                'confirm_delete_item' => __( 'Tem certeza que deseja excluir este item da timeline? Esta ação não pode ser desfeita diretamente aqui.', 'wp-timeline-pro' ),
                'error_adding_item'   => __( 'Erro ao adicionar o item', 'wp-timeline-pro' ),
                'error_deleting_item' => __( 'Erro ao excluir o item', 'wp-timeline-pro' ),
                'ajax_error'          => __( 'Erro na requisição AJAX', 'wp-timeline-pro' ),
                'title_required'      => __( 'O título do item é obrigatório.', 'wp-timeline-pro' ),
            ),
        );
        wp_localize_script( 'wtp-admin-script', 'wtp_admin_vars', $admin_vars );
	}

    // Enfileirar scripts para o editor do Elementor
    if ( isset( $_GET['action'] ) && $_GET['action'] === 'elementor' ) {
        wp_enqueue_script(
            'wtp-elementor-preview-script',
            WTP_PLUGIN_URL . 'assets/js/elementor-preview.js',
            [ 'jquery', 'elementor-frontend' ], // Depende do frontend do Elementor
            WTP_VERSION,
            true
        );
        wp_localize_script(
            'wtp-elementor-preview-script',
            'wtp_elementor_preview_vars',
            [
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'wtp_elementor_preview_nonce' ),
                'i18n'     => [
                    'select_timeline'       => __( 'Por favor, selecione uma timeline.', 'wp-timeline-pro' ),
                    'loading_preview'       => __( 'Carregando pré-visualização...', 'wp-timeline-pro' ),
                    'error_loading_preview' => __( 'Erro ao carregar pré-visualização.', 'wp-timeline-pro' ),
                    'ajax_error'            => __( 'Erro na requisição AJAX', 'wp-timeline-pro' ),
                ],
            ]
        );
    }
}
add_action( 'admin_enqueue_scripts', 'wtp_admin_enqueue_scripts_styles' );
// Para scripts do editor Elementor, o hook 'elementor/editor/after_enqueue_scripts' também é uma opção,
// mas 'admin_enqueue_scripts' com a verificação de $_GET['action'] === 'elementor' é comum.

/**
 * Enfileira estilos e fontes para o front-end.
 */
function wtp_frontend_enqueue_styles() {
    global $post;
    $load_assets = false;

    if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'custom_timeline' ) ) {
        $load_assets = true;
    }

    // Se estiver no modo de pré-visualização do Elementor, carregue os assets
    if ( class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
        // Verifique se o widget está na página (isto é mais complexo e pode não ser necessário
        // se o CSS for bem isolado e não muito pesado)
        // Por agora, vamos assumir que se estivermos no preview, podemos carregar.
        $load_assets = true;
    }


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
 * Adiciona Google Fonts selecionadas ao cabeçalho do front-end.
 */
function wtp_enqueue_selected_google_font( $font_family_css_string ) {
    if ( empty( $font_family_css_string ) || $font_family_css_string === 'inherit' ) {
        return;
    }
    $font_name = explode( ',', $font_family_css_string )[0];
    $font_name = trim( $font_name, '"\'' );

    $system_fonts = array('Arial', 'Verdana', 'Tahoma', 'Trebuchet MS', 'Times New Roman', 'Georgia', 'Garamond', 'Courier New', 'Brush Script MT');
    $is_google_font_candidate = true; // Assume que é, a menos que seja explicitamente de sistema

    foreach($system_fonts as $sf) {
        if (stripos($font_family_css_string, $sf) !== false) {
            $is_google_font_candidate = false;
            break;
        }
    }
    // Se for uma família genérica, também não é Google Font
    if (stripos($font_family_css_string, 'sans-serif') !== false && count(explode(',', $font_family_css_string)) === 1) $is_google_font_candidate = false;
    if (stripos($font_family_css_string, 'serif') !== false && count(explode(',', $font_family_css_string)) === 1) $is_google_font_candidate = false;
    if (stripos($font_family_css_string, 'monospace') !== false && count(explode(',', $font_family_css_string)) === 1) $is_google_font_candidate = false;


    if (!$is_google_font_candidate) {
        // Verifica se, apesar de ser de sistema, está na nossa lista de Google Fonts (ex: Roboto listado mas já presente)
        // Esta lógica pode ser simplificada se assumirmos que as fontes da lista que não são de sistema SÃO Google Fonts.
        $google_fonts_list = wtp_get_google_fonts_list(); // Função de admin-metaboxes.php
         if (function_exists('wtp_get_google_fonts_list')) {
            $google_fonts_list = wtp_get_google_fonts_list();
            $is_actually_google_font = false;
            foreach($google_fonts_list as $g_name => $g_css) {
                if ($g_css === $font_family_css_string && !in_array($g_name, $system_fonts)) {
                     $font_name = $g_name;
                     $is_actually_google_font = true;
                     break;
                }
            }
            if (!$is_actually_google_font) return;
        } else {
            return; // Se a função não existir, não podemos verificar
        }
    }


    $font_url = 'https://fonts.googleapis.com/css?family=' . urlencode( $font_name ) . ':400,700&display=swap';
    wp_enqueue_style( 'wtp-google-font-' . sanitize_title( $font_name ), $font_url, array(), null );
}
?>
