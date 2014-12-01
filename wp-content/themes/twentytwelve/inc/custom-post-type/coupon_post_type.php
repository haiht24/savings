<?php
	add_action('init', 'create_coupon_post_type');
	function create_coupon_post_type()
	{
        // Register Tag
        register_taxonomy('coupon_tag', array('coupon'), array(
    		'label' => __('Coupon Tag'),
    		'show_ui' => true,
    		'rewrite' => array('slug' => 'coupon_tags'),
    		'hierarchical' => false
        ));
		register_post_type('coupon', array(
			'labels' => array(
				'name' => __('Coupon'),
				'singular_name' => __('Coupon'),
				'add_new' => _x('Add New Coupon', 'coupon'),
				'add_new_item' => __('Add New Coupon'),
				'edit_item' => __('Edit Coupon'),
				'new_item' => __('New Coupon'),
				'view_item' => __('View Coupon'),
				'search_items' => __('Search Coupon')),
			//'rewrite' => array('slug' => 'view-coupon'),
			'public' => true,
			'publicly_queryable' => true,
			'has_archive' => true,
			'hierarchical' => false,
			'supports' => array(
				'title',
				'editor',
				'author',
				//'cats',
				'revisions'
                )));
	}
?>