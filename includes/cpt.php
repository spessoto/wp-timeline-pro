<?php
// Se este arquivo for chamado diretamente, aborte.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Registra o Custom Post Type 'Timeline'.
 */
function wtp_register_timeline_cpt() {
	$labels = array(
		'name'               => _x( 'Timelines', 'post type general name', 'wp-timeline-pro' ),
		'singular_name'      => _x( 'Timeline', 'post type singular name', 'wp-timeline-pro' ),
		'menu_name'          => _x( 'Timelines', 'admin menu', 'wp-timeline-pro' ),
		'name_admin_bar'     => _x( 'Timeline', 'add new on admin bar', 'wp-timeline-pro' ),
		'add_new'            => _x( 'Adicionar Nova', 'timeline', 'wp-timeline-pro' ),
		'add_new_item'       => __( 'Adicionar Nova Timeline', 'wp-timeline-pro' ),
		'new_item'           => __( 'Nova Timeline', 'wp-timeline-pro' ),
		'edit_item'          => __( 'Editar Timeline', 'wp-timeline-pro' ),
		'view_item'          => __( 'Ver Timeline', 'wp-timeline-pro' ),
		'all_items'          => __( 'Todas as Timelines', 'wp-timeline-pro' ),
		'search_items'       => __( 'Procurar Timelines', 'wp-timeline-pro' ),
		'parent_item_colon'  => __( 'Timeline Pai:', 'wp-timeline-pro' ),
		'not_found'          => __( 'Nenhuma timeline encontrada.', 'wp-timeline-pro' ),
		'not_found_in_trash' => __( 'Nenhuma timeline encontrada na lixeira.', 'wp-timeline-pro' )
	);

	$args = array(
		'labels'             => $labels,
		'public'             => false, // Não será visível diretamente no front-end, apenas via shortcode
		'publicly_queryable' => false,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'timeline' ),
		'capability_type'    => 'post',
		'has_archive'        => false,
		'hierarchical'       => false,
		'menu_position'      => 20, // Abaixo de 'Páginas'
		'menu_icon'          => 'dashicons-backup', // Um ícone de exemplo
		'supports'           => array( 'title' ),
        'register_meta_box_cb' => 'wtp_add_timeline_metaboxes', // Função para adicionar metaboxes
	);

	register_post_type( 'wtp_timeline', $args );
}
add_action( 'init', 'wtp_register_timeline_cpt' );

/**
 * Registra o Custom Post Type 'Item da Timeline'.
 */
function wtp_register_timeline_item_cpt() {
	$labels = array(
		'name'               => _x( 'Itens da Timeline', 'post type general name', 'wp-timeline-pro' ),
		'singular_name'      => _x( 'Item da Timeline', 'post type singular name', 'wp-timeline-pro' ),
		'menu_name'          => _x( 'Itens da Timeline', 'admin menu', 'wp-timeline-pro' ),
		'name_admin_bar'     => _x( 'Item da Timeline', 'add new on admin bar', 'wp-timeline-pro' ),
		'add_new'            => _x( 'Adicionar Novo Item', 'timeline item', 'wp-timeline-pro' ),
		'add_new_item'       => __( 'Adicionar Novo Item da Timeline', 'wp-timeline-pro' ),
		'new_item'           => __( 'Novo Item da Timeline', 'wp-timeline-pro' ),
		'edit_item'          => __( 'Editar Item da Timeline', 'wp-timeline-pro' ),
		'view_item'          => __( 'Ver Item da Timeline', 'wp-timeline-pro' ),
		'all_items'          => __( 'Todos os Itens da Timeline', 'wp-timeline-pro' ),
		'search_items'       => __( 'Procurar Itens da Timeline', 'wp-timeline-pro' ),
		'parent_item_colon'  => __( 'Item Pai:', 'wp-timeline-pro' ), // Usado para hierarquia, se aplicável
		'not_found'          => __( 'Nenhum item da timeline encontrado.', 'wp-timeline-pro' ),
		'not_found_in_trash' => __( 'Nenhum item da timeline encontrado na lixeira.', 'wp-timeline-pro' )
	);

	$args = array(
		'labels'             => $labels,
		'public'             => false,
		'publicly_queryable' => false,
		'show_ui'            => true,
        'show_in_menu'       => 'edit.php?post_type=wtp_timeline', // Mostra como submenu de 'Timelines'
		// 'show_in_menu'       => false, // Ou oculte e gerencie via a tela da Timeline principal
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'timeline-item' ),
		'capability_type'    => 'post',
		'has_archive'        => false,
		'hierarchical'       => false, // Itens não são hierárquicos entre si, mas podem ser 'filhos' de uma timeline
		'menu_position'      => null,
		'supports'           => array( 'title', 'editor', 'page-attributes' ), // 'editor' para a descrição, 'page-attributes' para ordem
        'register_meta_box_cb' => 'wtp_add_timeline_item_metaboxes',
	);

	register_post_type( 'wtp_timeline_item', $args );
}
add_action( 'init', 'wtp_register_timeline_item_cpt' );
?>
