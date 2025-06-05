<?php
// Se este arquivo for chamado diretamente, aborte.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Manipulador AJAX para adicionar um novo item à timeline.
 */
add_action( 'wp_ajax_wtp_add_timeline_item', 'wtp_ajax_add_timeline_item_callback' );
function wtp_ajax_add_timeline_item_callback() {
    // Verificar nonce para segurança
    check_ajax_referer( 'wtp_manage_timeline_items_nonce', 'nonce' );

    // Verificar permissões do usuário
    if ( ! current_user_can( 'edit_posts' ) ) { // Ou uma capacidade mais específica se definida
        wp_send_json_error( array( 'message' => __( 'Você não tem permissão para adicionar itens.', 'wp-timeline-pro' ) ) );
        return;
    }

    // Obter e sanitizar dados do POST
    $timeline_id = isset( $_POST['timeline_id'] ) ? absint( $_POST['timeline_id'] ) : 0;
    $item_year   = isset( $_POST['year'] ) ? sanitize_text_field( $_POST['year'] ) : '';
    $item_title  = isset( $_POST['title'] ) ? sanitize_text_field( $_POST['title'] ) : '';
    $item_desc   = isset( $_POST['description'] ) ? wp_kses_post( $_POST['description'] ) : ''; // Permite HTML seguro

    if ( ! $timeline_id || empty( $item_title ) ) {
        wp_send_json_error( array( 'message' => __( 'ID da Timeline e Título do Item são obrigatórios.', 'wp-timeline-pro' ) ) );
        return;
    }

    // Criar o novo post do item da timeline
    $new_item_args = array(
        'post_title'   => $item_title,
        'post_content' => $item_desc,
        'post_status'  => 'publish', // Publicar imediatamente
        'post_type'    => 'wtp_timeline_item',
    );
    $new_item_id = wp_insert_post( $new_item_args );

    if ( is_wp_error( $new_item_id ) ) {
        wp_send_json_error( array( 'message' => $new_item_id->get_error_message() ) );
        return;
    }

    // Salvar os metadados do item
    update_post_meta( $new_item_id, '_wtp_item_year', $item_year );
    update_post_meta( $new_item_id, '_wtp_parent_timeline_id', $timeline_id );

    // Determinar a ordem (menu_order) - novo item vai para o final
    $existing_items_args = array(
        'post_type' => 'wtp_timeline_item',
        'posts_per_page' => 1, // Apenas o último item
        'meta_key' => '_wtp_parent_timeline_id',
        'meta_value' => $timeline_id,
        'orderby' => 'menu_order',
        'order' => 'DESC'
    );
    $last_item_query = new WP_Query($existing_items_args); // Usar WP_Query para melhor prática
    $new_order = 0;
    if ($last_item_query->have_posts()) {
        $last_item = $last_item_query->posts[0];
        $new_order = $last_item->menu_order + 1;
    }
    wp_update_post(array('ID' => $new_item_id, 'menu_order' => $new_order));
    wp_reset_postdata(); // Importante após loops personalizados com WP_Query


    // Obter a lista atualizada de itens para enviar de volta
    ob_start();
    // A função wtp_render_timeline_items_list_ajax deve existir em admin-metaboxes.php
    if (function_exists('wtp_render_timeline_items_list_ajax')) {
        wtp_render_timeline_items_list_ajax( $timeline_id );
    } else {
        // Fallback ou mensagem de erro se a função não estiver disponível
        echo '<p>' . esc_html__( 'Erro: Função de renderização da lista de itens não disponível.', 'wp-timeline-pro' ) . '</p>';
    }
    $items_html = ob_get_clean();

    wp_send_json_success( array(
        'message' => __( 'Item adicionado com sucesso!', 'wp-timeline-pro' ),
        'items_html' => $items_html
    ) );

    wp_die(); // Obrigatório para terminar corretamente as requisições AJAX do WordPress
}


/**
 * Manipulador AJAX para excluir um item da timeline.
 */
add_action( 'wp_ajax_wtp_delete_timeline_item', 'wtp_ajax_delete_timeline_item_callback' );
function wtp_ajax_delete_timeline_item_callback() {
    $item_id = isset( $_POST['item_id'] ) ? absint( $_POST['item_id'] ) : 0;
    // Verificar nonce específico do item para segurança
    check_ajax_referer( 'wtp_delete_item_nonce_' . $item_id, 'nonce' );

    if ( ! $item_id ) {
        wp_send_json_error( array( 'message' => __( 'ID do item inválido.', 'wp-timeline-pro' ) ) );
        return;
    }

    // Verificar permissões do usuário para excluir este post específico
    if ( ! current_user_can( 'delete_post', $item_id ) ) {
        wp_send_json_error( array( 'message' => __( 'Você não tem permissão para excluir este item.', 'wp-timeline-pro' ) ) );
        return;
    }

    // Obter o ID da timeline pai antes de excluir, para recarregar a lista correta
    $timeline_id = get_post_meta( $item_id, '_wtp_parent_timeline_id', true );

    // Excluir o post do item (mover para a lixeira por padrão)
    $deleted = wp_delete_post( $item_id, false ); // false para não forçar a exclusão (vai para lixeira)

    if ( ! $deleted ) {
        wp_send_json_error( array( 'message' => __( 'Erro ao excluir o item.', 'wp-timeline-pro' ) ) );
        return;
    }

    // Obter a lista atualizada de itens para enviar de volta
    ob_start();
    if ($timeline_id && function_exists('wtp_render_timeline_items_list_ajax')) {
        wtp_render_timeline_items_list_ajax( absint($timeline_id) );
    } else {
        // Fallback ou mensagem de erro
        echo '<p>' . esc_html__( 'Lista de itens não pôde ser recarregada.', 'wp-timeline-pro') . '</p>';
    }
    $items_html = ob_get_clean();

    wp_send_json_success( array(
        'message' => __( 'Item excluído com sucesso!', 'wp-timeline-pro' ),
        'items_html' => $items_html
    ) );

    wp_die();
}

/**
 * Manipulador AJAX para obter a lista de itens da timeline (usado para refresh se necessário).
 */
add_action( 'wp_ajax_wtp_get_timeline_items_list', 'wtp_ajax_get_timeline_items_list_callback' );
function wtp_ajax_get_timeline_items_list_callback() {
    check_ajax_referer( 'wtp_manage_timeline_items_nonce', 'nonce' ); // Reutilizando o nonce do manager

    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( array( 'message' => __( 'Acesso negado.', 'wp-timeline-pro' ) ) );
        return;
    }

    $timeline_id = isset( $_POST['timeline_id'] ) ? absint( $_POST['timeline_id'] ) : 0;
    if ( ! $timeline_id ) {
        wp_send_json_error( array( 'message' => __( 'ID da Timeline não fornecido.', 'wp-timeline-pro' ) ) );
        return;
    }

    ob_start();
    if (function_exists('wtp_render_timeline_items_list_ajax')) {
        wtp_render_timeline_items_list_ajax( $timeline_id );
    } else {
        echo '<p>' . esc_html__( 'Erro: Função de renderização da lista de itens não disponível.', 'wp-timeline-pro' ) . '</p>';
    }
    $items_html = ob_get_clean();

    wp_send_json_success( array( 'items_html' => $items_html ) );
    wp_die();
}

/**
 * Manipulador AJAX para renderizar a pré-visualização da timeline para o Elementor.
 */
add_action( 'wp_ajax_wtp_get_elementor_preview', 'wtp_ajax_get_elementor_preview_callback' );
function wtp_ajax_get_elementor_preview_callback() {
    // Verificar nonce para segurança
    check_ajax_referer( 'wtp_elementor_preview_nonce', 'nonce' );

    // Verificar permissões do usuário
    if ( ! current_user_can( 'edit_posts' ) ) {
        wp_send_json_error( array( 'message' => __( 'Você não tem permissão para visualizar este conteúdo.', 'wp-timeline-pro' ) ) );
        return;
    }

    $timeline_id = isset( $_POST['timeline_id'] ) ? absint( $_POST['timeline_id'] ) : 0;
    // As configurações do Elementor são enviadas como uma string JSON.
    // O JavaScript já filtra para enviar apenas as configurações relevantes (override_*).
    $elementor_settings_json = isset( $_POST['elementor_settings'] ) ? stripslashes( $_POST['elementor_settings'] ) : '{}';
    $elementor_settings = json_decode( $elementor_settings_json, true );

    if ( ! $timeline_id ) {
        wp_send_json_error( array( 'message' => __( 'ID da Timeline não fornecido.', 'wp-timeline-pro' ) ) );
        return;
    }

    // Montar array de substituições de estilo a partir das configurações do Elementor recebidas
    $style_overrides = [];
    // As chaves em $elementor_settings já devem ser as chaves de override (ex: 'override_line_color')
    // A função wtp_render_timeline_shortcode espera as chaves sem o prefixo 'override_' dentro do array 'style_overrides'.
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
    // Adicionar outros overrides aqui se foram definidos no widget e enviados pelo JS

    // Chama a função de renderização do shortcode, que agora aceita 'style_overrides'
    if ( function_exists( 'wtp_render_timeline_shortcode' ) ) {
        $timeline_html = wtp_render_timeline_shortcode( array(
            'id' => $timeline_id,
            'style_overrides' => $style_overrides,
            // Não é necessário passar 'elementor_settings' aqui, pois já extraímos os overrides
        ) );
        wp_send_json_success( array( 'html' => $timeline_html ) );
    } else {
        wp_send_json_error( array( 'message' => __( 'Função de renderização da timeline não encontrada.', 'wp-timeline-pro' ) ) );
    }

    wp_die();
}

?>
