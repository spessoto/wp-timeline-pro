<?php
// Se este arquivo for chamado diretamente, aborte.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Lista de Google Fonts para seleção.
 * Esta lista pode ser expandida ou carregada de uma API/JSON no futuro.
 * Formato: 'Nome da Fonte' => 'CSS Font Family String'
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
        // Google Fonts Populares
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
 * Lista de estilos de animação para os itens da timeline.
 */
function wtp_get_animation_styles() {
    return apply_filters('wtp_animation_styles', array(
        'none' => __( 'Nenhuma', 'wp-timeline-pro' ),
        'fade-in' => __( 'Fade In', 'wp-timeline-pro' ),
        'slide-in-left' => __( 'Slide In da Esquerda', 'wp-timeline-pro' ),
        'slide-in-right' => __( 'Slide In da Direita', 'wp-timeline-pro' ),
        'slide-in-up' => __( 'Slide In de Baixo', 'wp-timeline-pro' ),
        'zoom-in' => __( 'Zoom In', 'wp-timeline-pro' ),
    ));
}


/**
 * Adiciona as metaboxes para o CPT 'wtp_timeline'.
 */
function wtp_add_timeline_metaboxes() {
	add_meta_box(
		'wtp_timeline_settings_metabox',
		__( 'Configurações da Timeline', 'wp-timeline-pro' ),
		'wtp_timeline_settings_metabox_callback',
		'wtp_timeline', // CPT
		'normal',       // Contexto
		'high'          // Prioridade
	);
    add_meta_box(
		'wtp_timeline_shortcode_metabox',
		__( 'Shortcode da Timeline', 'wp-timeline-pro' ),
		'wtp_timeline_shortcode_metabox_callback',
		'wtp_timeline',
		'side', // Contexto lateral
		'default'
	);
    add_meta_box(
        'wtp_timeline_items_manager_metabox', // Renomeado para refletir o gerenciamento
        __( 'Gerenciar Itens da Timeline', 'wp-timeline-pro' ),
        'wtp_timeline_items_manager_metabox_callback', // Nova callback
        'wtp_timeline',
        'normal',
        'low'
    );
}
// A função 'wtp_add_timeline_metaboxes' é chamada via 'register_meta_box_cb' no CPT.

/**
 * Callback para a metabox de configurações da timeline.
 */
function wtp_timeline_settings_metabox_callback( $post ) {
	wp_nonce_field( 'wtp_save_timeline_settings_data', 'wtp_timeline_settings_nonce' );

	$font_family     = get_post_meta( $post->ID, '_wtp_font_family', true );
	$font_size       = get_post_meta( $post->ID, '_wtp_font_size', true );
	$year_font_size  = get_post_meta( $post->ID, '_wtp_year_font_size', true );
	$title_font_size = get_post_meta( $post->ID, '_wtp_title_font_size', true );
	$text_color      = get_post_meta( $post->ID, '_wtp_text_color', true );
	$year_text_color = get_post_meta( $post->ID, '_wtp_year_text_color', true );
	$title_text_color= get_post_meta( $post->ID, '_wtp_title_text_color', true );
	$line_color      = get_post_meta( $post->ID, '_wtp_line_color', true );
    $marker_color    = get_post_meta( $post->ID, '_wtp_marker_color', true );
    $item_bg_color   = get_post_meta( $post->ID, '_wtp_item_bg_color', true );
    $animation_style = get_post_meta( $post->ID, '_wtp_animation_style', true );

    $google_fonts = wtp_get_google_fonts_list();
    $animations = wtp_get_animation_styles();

	?>
    <table class="form-table">
        <tbody>
            <tr>
                <th><label for="wtp_font_family"><?php _e( 'Fonte Principal:', 'wp-timeline-pro' ); ?></label></th>
                <td>
                    <select id="wtp_font_family" name="wtp_font_family" class="widefat wtp-select2-font">
                        <option value=""><?php _e( 'Padrão do Tema', 'wp-timeline-pro' ); ?></option>
                        <?php foreach ( $google_fonts as $name => $css_value ) : ?>
                            <option value="<?php echo esc_attr( $css_value ); ?>" <?php selected( $font_family, $css_value ); ?> data-font-name="<?php echo esc_attr( $name ); ?>">
                                <?php echo esc_html( $name ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description"><?php _e( 'Selecione a família de fontes para o texto da descrição. Algumas fontes são carregadas do Google Fonts.', 'wp-timeline-pro' ); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="wtp_font_size"><?php _e( 'Tamanho da Fonte da Descrição (px):', 'wp-timeline-pro' ); ?></label></th>
                <td><input type="number" id="wtp_font_size" name="wtp_font_size" value="<?php echo esc_attr( $font_size ); ?>" class="small-text" placeholder="14"/></td>
            </tr>
             <tr>
                <th><label for="wtp_year_font_size"><?php _e( 'Tamanho da Fonte do Ano (px):', 'wp-timeline-pro' ); ?></label></th>
                <td><input type="number" id="wtp_year_font_size" name="wtp_year_font_size" value="<?php echo esc_attr( $year_font_size ); ?>" class="small-text" placeholder="18"/></td>
            </tr>
            <tr>
                <th><label for="wtp_title_font_size"><?php _e( 'Tamanho da Fonte do Título do Item (px):', 'wp-timeline-pro' ); ?></label></th>
                <td><input type="number" id="wtp_title_font_size" name="wtp_title_font_size" value="<?php echo esc_attr( $title_font_size ); ?>" class="small-text" placeholder="16"/></td>
            </tr>
            <tr>
                <th><label for="wtp_text_color"><?php _e( 'Cor do Texto da Descrição:', 'wp-timeline-pro' ); ?></label></th>
                <td><input type="text" id="wtp_text_color" name="wtp_text_color" value="<?php echo esc_attr( $text_color ); ?>" class="wtp-color-picker" data-default-color="#333333" /></td>
            </tr>
             <tr>
                <th><label for="wtp_year_text_color"><?php _e( 'Cor do Texto do Ano:', 'wp-timeline-pro' ); ?></label></th>
                <td><input type="text" id="wtp_year_text_color" name="wtp_year_text_color" value="<?php echo esc_attr( $year_text_color ); ?>" class="wtp-color-picker" data-default-color="#555555" /></td>
            </tr>
            <tr>
                <th><label for="wtp_title_text_color"><?php _e( 'Cor do Texto do Título do Item:', 'wp-timeline-pro' ); ?></label></th>
                <td><input type="text" id="wtp_title_text_color" name="wtp_title_text_color" value="<?php echo esc_attr( $title_text_color ); ?>" class="wtp-color-picker" data-default-color="#222222" /></td>
            </tr>
            <tr>
                <th><label for="wtp_line_color"><?php _e( 'Cor da Linha da Timeline:', 'wp-timeline-pro' ); ?></label></th>
                <td><input type="text" id="wtp_line_color" name="wtp_line_color" value="<?php echo esc_attr( $line_color ); ?>" class="wtp-color-picker" data-default-color="#cccccc"/></td>
            </tr>
             <tr>
                <th><label for="wtp_marker_color"><?php _e( 'Cor do Marcador na Linha:', 'wp-timeline-pro' ); ?></label></th>
                <td><input type="text" id="wtp_marker_color" name="wtp_marker_color" value="<?php echo esc_attr( $marker_color ); ?>" class="wtp-color-picker" data-default-color="#ffffff"/>
                <p class="description"><?php _e( 'Cor de preenchimento do marcador. A borda usará a cor da linha.', 'wp-timeline-pro' ); ?></p></td>
            </tr>
            <tr>
                <th><label for="wtp_item_bg_color"><?php _e( 'Cor de Fundo do Card do Item:', 'wp-timeline-pro' ); ?></label></th>
                <td><input type="text" id="wtp_item_bg_color" name="wtp_item_bg_color" value="<?php echo esc_attr( $item_bg_color ); ?>" class="wtp-color-picker" data-default-color="#f9f9f9"/></td>
            </tr>
            <tr>
                <th><label for="wtp_animation_style"><?php _e( 'Animação dos Itens:', 'wp-timeline-pro' ); ?></label></th>
                <td>
                    <select id="wtp_animation_style" name="wtp_animation_style" class="widefat">
                        <?php foreach ( $animations as $value => $label ) : ?>
                            <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $animation_style, $value ); ?>>
                                <?php echo esc_html( $label ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description"><?php _e( 'Selecione o estilo de animação para os itens ao aparecerem na tela.', 'wp-timeline-pro' ); ?></p>
                </td>
            </tr>
        </tbody>
    </table>
	<?php
}

/**
 * Callback para a metabox de shortcode da timeline.
 */
function wtp_timeline_shortcode_metabox_callback( $post ) {
    ?>
    <p><?php _e( 'Copie e cole este shortcode no seu construtor de páginas ou conteúdo:', 'wp-timeline-pro' ); ?></p>
    <input type="text" readonly="readonly" value="[custom_timeline id=&quot;<?php echo $post->ID; ?>&quot;]" class="widefat wtp-shortcode-input" onfocus="this.select();" />
    <?php
}

/**
 * Callback para a metabox de gerenciamento de itens da timeline.
 */
function wtp_timeline_items_manager_metabox_callback( $post ) {
    $timeline_id = $post->ID;
    wp_nonce_field( 'wtp_manage_timeline_items_nonce', 'wtp_timeline_items_manager_nonce' );
    ?>
    <div id="wtp-timeline-items-list-container">
        <?php wtp_render_timeline_items_list_ajax( $timeline_id ); // Função para renderizar a lista ?>
    </div>

    <h4><?php _e( 'Adicionar Novo Item:', 'wp-timeline-pro' ); ?></h4>
    <table class="form-table" id="wtp-add-item-form">
        <tbody>
            <tr>
                <th><label for="wtp_new_item_year"><?php _e( 'Ano:', 'wp-timeline-pro' ); ?></label></th>
                <td><input type="text" id="wtp_new_item_year" name="wtp_new_item_year" class="regular-text" placeholder="Ex: 2024"/></td>
            </tr>
            <tr>
                <th><label for="wtp_new_item_title"><?php _e( 'Título do Item:', 'wp-timeline-pro' ); ?></label></th>
                <td><input type="text" id="wtp_new_item_title" name="wtp_new_item_title" class="widefat" required /></td>
            </tr>
            <tr>
                <th><label for="wtp_new_item_description"><?php _e( 'Descrição:', 'wp-timeline-pro' ); ?></label></th>
                <td><textarea id="wtp_new_item_description" name="wtp_new_item_description" rows="5" class="widefat"></textarea></td>
            </tr>
            <tr>
                <th></th>
                <td>
                    <button type="button" id="wtp-add-new-item-button" class="button button-primary" data-timeline-id="<?php echo esc_attr( $timeline_id ); ?>">
                        <?php _e( 'Adicionar Item à Timeline', 'wp-timeline-pro' ); ?>
                    </button>
                    <span class="spinner" style="float: none; vertical-align: middle;"></span>
                </td>
            </tr>
        </tbody>
    </table>
    <p><small><?php _e( 'Os itens são ordenados pelo campo "Ordem". Você pode editar a ordem de cada item clicando em "Editar".', 'wp-timeline-pro' ); ?></small></p>
    <?php
}

/**
 * Função auxiliar para renderizar a lista de itens (usada na carga inicial e AJAX).
 * Esta função será chamada pelo AJAX handler também.
 */
function wtp_render_timeline_items_list_ajax( $timeline_id ) {
    $args = array(
        'post_type' => 'wtp_timeline_item',
        'posts_per_page' => -1,
        'meta_key' => '_wtp_parent_timeline_id',
        'meta_value' => $timeline_id,
        'orderby' => 'menu_order title', // Ordena por 'menu_order' e depois por título
        'order' => 'ASC'
    );
    $timeline_items = get_posts( $args );

    echo '<h4>' . __( 'Itens Atuais:', 'wp-timeline-pro' ) . '</h4>';
    if ( ! empty( $timeline_items ) ) {
        echo '<ul id="wtp-current-items-list" class="wtp-styled-list">';
        foreach ( $timeline_items as $item ) {
            $edit_link = get_edit_post_link( $item->ID );
            $item_year = get_post_meta( $item->ID, '_wtp_item_year', true );
            $item_order = $item->menu_order;

            echo '<li data-item-id="' . esc_attr( $item->ID ) . '">';
            echo '<strong>' . esc_html( $item->post_title ) . '</strong> (' . esc_html( $item_year ) . ')';
            echo ' - <small>' . sprintf(__( 'Ordem: %s', 'wp-timeline-pro' ), esc_html($item_order)) . '</small>';
            echo ' <a href="' . esc_url( $edit_link ) . '" class="button button-small">' . __( 'Editar', 'wp-timeline-pro' ) . '</a>';
            echo ' <button type="button" class="button button-small button-link-delete wtp-delete-item-button" data-item-id="' . esc_attr( $item->ID ) . '" data-nonce="' . esc_attr(wp_create_nonce('wtp_delete_item_nonce_' . $item->ID)) . '">' . __( 'Excluir', 'wp-timeline-pro' ) . '</button>';
            echo '</li>';
        }
        echo '</ul>';
    } else {
        echo '<p id="wtp-no-items-message">' . __( 'Nenhum item adicionado a esta timeline ainda.', 'wp-timeline-pro' ) . '</p>';
    }
}


/**
 * Salva os dados das metaboxes da timeline.
 */
function wtp_save_timeline_settings_data( $post_id ) {
	if ( ! isset( $_POST['wtp_timeline_settings_nonce'] ) || ! wp_verify_nonce( $_POST['wtp_timeline_settings_nonce'], 'wtp_save_timeline_settings_data' ) ) return;
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( ! current_user_can( 'edit_post', $post_id ) ) return;
    if ( 'wtp_timeline' !== get_post_type( $post_id ) ) return;

    $fields_to_save = array(
        'wtp_font_family',
        'wtp_font_size',
        'wtp_year_font_size',
        'wtp_title_font_size',
        'wtp_text_color',
        'wtp_year_text_color',
        'wtp_title_text_color',
        'wtp_line_color',
        'wtp_marker_color',
        'wtp_item_bg_color',
        'wtp_animation_style',
    );

    foreach ($fields_to_save as $field_key_post) {
        $meta_key = '_' . $field_key_post; // _wtp_font_family
        if ( isset( $_POST[$field_key_post] ) ) {
            $value = $_POST[$field_key_post];
            if ( strpos( $field_key_post, '_color' ) !== false ) {
                update_post_meta( $post_id, $meta_key, sanitize_hex_color( $value ) );
            } elseif ( strpos( $field_key_post, '_size' ) !== false ) {
                 update_post_meta( $post_id, $meta_key, absint( $value ) );
            } else {
                update_post_meta( $post_id, $meta_key, sanitize_text_field( $value ) );
            }
        } else {
            // Se o campo não estiver presente no POST (ex: checkbox desmarcado), você pode querer deletar o meta ou salvar um valor padrão.
            // Para campos de texto/select, se não vier, pode ser que não deva ser alterado ou limpo.
            // Neste caso, se não vier, não atualizamos, mantendo o valor anterior ou nenhum.
        }
    }
}
add_action( 'save_post_wtp_timeline', 'wtp_save_timeline_settings_data' );

/**
 * Adiciona as metaboxes para o CPT 'wtp_timeline_item'.
 */
function wtp_add_timeline_item_metaboxes() {
	add_meta_box(
		'wtp_timeline_item_details_metabox',
		__( 'Detalhes do Item da Timeline', 'wp-timeline-pro' ),
		'wtp_timeline_item_details_metabox_callback',
		'wtp_timeline_item',
		'normal',
		'high'
	);
}

/**
 * Callback para a metabox de detalhes do item da timeline.
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
                <th><label for="wtp_item_year"><?php _e( 'Ano:', 'wp-timeline-pro' ); ?></label></th>
                <td><input type="text" id="wtp_item_year" name="wtp_item_year" value="<?php echo esc_attr( $item_year ); ?>" class="regular-text" placeholder="Ex: 2023"/></td>
            </tr>
            <tr>
                <th><label for="wtp_parent_timeline_id"><?php _e( 'Associar a Timeline:', 'wp-timeline-pro' ); ?></label></th>
                <td>
                    <select name="wtp_parent_timeline_id" id="wtp_parent_timeline_id" class="widefat">
                        <option value=""><?php _e( '-- Selecione uma Timeline --', 'wp-timeline-pro' ); ?></option>
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
    <p><em><?php _e('Use o campo "Ordem" na caixa "Atributos da Página" (geralmente à direita) para definir a ordem deste item na timeline. A descrição principal do item é gerenciada pelo editor padrão do WordPress acima.', 'wp-timeline-pro'); ?></em></p>
	<?php
}

/**
 * Salva os dados das metaboxes do item da timeline.
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
?>
