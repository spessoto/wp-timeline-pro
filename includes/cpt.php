<?php
// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Registers the 'Timeline' Custom Post Type.
 */
function wtp_register_timeline_cpt() {
	$labels = array(
		'name'               => _x( 'Timelines', 'post type general name', 'wp-timeline-pro' ),
		'singular_name'      => _x( 'Timeline', 'post type singular name', 'wp-timeline-pro' ),
		'menu_name'          => _x( 'Timelines', 'admin menu', 'wp-timeline-pro' ),
		'name_admin_bar'     => _x( 'Timeline', 'add new on admin bar', 'wp-timeline-pro' ),
		'add_new'            => _x( 'Add New', 'timeline', 'wp-timeline-pro' ),
		'add_new_item'       => __( 'Add New Timeline', 'wp-timeline-pro' ),
		'new_item'           => __( 'New Timeline', 'wp-timeline-pro' ),
		'edit_item'          => __( 'Edit Timeline', 'wp-timeline-pro' ),
		'view_item'          => __( 'View Timeline', 'wp-timeline-pro' ),
		'all_items'          => __( 'All Timelines', 'wp-timeline-pro' ),
		'search_items'       => __( 'Search Timelines', 'wp-timeline-pro' ),
		'parent_item_colon'  => __( 'Parent Timeline:', 'wp-timeline-pro' ),
		'not_found'          => __( 'No timelines found.', 'wp-timeline-pro' ),
		'not_found_in_trash' => __( 'No timelines found in Trash.', 'wp-timeline-pro' )
	);

	$args = array(
		'labels'             => $labels,
		'public'             => false, // Will not be visible directly on the front-end, only via shortcode
		'publicly_queryable' => false,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'timeline' ),
		'capability_type'    => 'post',
		'has_archive'        => false,
		'hierarchical'       => false,
		'menu_position'      => 20, // Below 'Pages'
		'menu_icon'          => 'dashicons-list-view', // Changed icon
		'supports'           => array( 'title' ),
        'register_meta_box_cb' => 'wtp_add_timeline_metaboxes', // Function to add metaboxes
	);

	register_post_type( 'wtp_timeline', $args );
}
add_action( 'init', 'wtp_register_timeline_cpt' );

/**
 * Registers the 'Timeline Item' Custom Post Type.
 */
function wtp_register_timeline_item_cpt() {
	$labels = array(
		'name'               => _x( 'Timeline Items', 'post type general name', 'wp-timeline-pro' ),
		'singular_name'      => _x( 'Timeline Item', 'post type singular name', 'wp-timeline-pro' ),
		'menu_name'          => _x( 'Timeline Items', 'admin menu', 'wp-timeline-pro' ),
		'name_admin_bar'     => _x( 'Timeline Item', 'add new on admin bar', 'wp-timeline-pro' ),
		'add_new'            => _x( 'Add New Item', 'timeline item', 'wp-timeline-pro' ),
		'add_new_item'       => __( 'Add New Timeline Item', 'wp-timeline-pro' ),
		'new_item'           => __( 'New Timeline Item', 'wp-timeline-pro' ),
		'edit_item'          => __( 'Edit Timeline Item', 'wp-timeline-pro' ),
		'view_item'          => __( 'View Timeline Item', 'wp-timeline-pro' ),
		'all_items'          => __( 'All Timeline Items', 'wp-timeline-pro' ),
		'search_items'       => __( 'Search Timeline Items', 'wp-timeline-pro' ),
		'parent_item_colon'  => __( 'Parent Item:', 'wp-timeline-pro' ), // Used for hierarchy, if applicable
		'not_found'          => __( 'No timeline items found.', 'wp-timeline-pro' ),
		'not_found_in_trash' => __( 'No timeline items found in Trash.', 'wp-timeline-pro' )
	);

	$args = array(
		'labels'             => $labels,
		'public'             => false,
		'publicly_queryable' => false,
		'show_ui'            => true,
        'show_in_menu'       => 'edit.php?post_type=wtp_timeline', // Show as submenu of 'Timelines'
		// 'show_in_menu'       => false, // Or hide and manage via the main Timeline screen
		'query_var'          => true,
		'rewrite'            => array( 'slug' => 'timeline-item' ),
		'capability_type'    => 'post',
		'has_archive'        => false,
		'hierarchical'       => false, // Items are not hierarchical among themselves, but can be 'children' of a timeline
		'menu_position'      => null,
		'supports'           => array( 'title', 'editor', 'page-attributes' ), // 'editor' for description, 'page-attributes' for order
        'register_meta_box_cb' => 'wtp_add_timeline_item_metaboxes',
	);

	register_post_type( 'wtp_timeline_item', $args );
}
add_action( 'init', 'wtp_register_timeline_item_cpt' );
?>
