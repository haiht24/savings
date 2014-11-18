<?php
	add_action('init', 'create_store_post_type');
	function create_store_post_type()
	{
		// REGISTER CATEGORY TAXONOMY
		register_taxonomy('store_category', array('store'), array(
			'label' => __('Store Categories'),
			'show_ui' => true,
			'rewrite' => array('slug' => 'discounts'),
			'hierarchical' => true));
		// REGISTER STORE CUSTOM POST TYPE
		register_post_type('store', array(
			'labels' => array(
				'name' => __('Stores'),
				'singular_name' => __('Store'),
				'add_new' => _x('Add New Store', 'store'),
				'add_new_item' => __('Add New Store'),
				'edit_item' => __('Edit Store'),
				'new_item' => __('New Store'),
				'view_item' => __('View Store'),
				'search_items' => __('Search Store')),
			//'rewrite' => array('slug' => 'view-store'),
			'public' => true,
			'publicly_queryable' => true,
			'has_archive' => true,
			'hierarchical' => false,
			'supports' => array(
				'title',
				'editor',
				'author',
				'cats',
				'revisions')));
	}
	/** ****************************************************************************************************************
	 * Remove the slug from published post permalinks. Only affect our CPT though.
	 */
	function vipx_remove_cpt_slug($post_link, $post, $leavename)
	{
		if (!in_array($post->post_type, array('store')) || 'publish' != $post->post_status)
			return $post_link;
		$post_link = str_replace('/' . $post->post_type . '/', '/', $post_link);
		return $post_link;
	}
	add_filter('post_type_link', 'vipx_remove_cpt_slug', 10, 3);
	/**
	 * Some hackery to have WordPress match postname to any of our public post types
	 * All of our public post types can have /post-name/ as the slug, so they better be unique across all posts
	 * Typically core only accounts for posts and pages where the slug is /post-name/
	 */
	function vipx_parse_request_tricksy($query)
	{
		// Only noop the main query
		if (!$query->is_main_query())
			return;
		// Only noop our very specific rewrite rule match
		if (2 != count($query->query) || !isset($query->query['page']))
			return;
		// 'name' will be set if post permalinks are just post_name, otherwise the page rule will match
		if (!empty($query->query['name']))
			$query->set('post_type', array(
				'post',
				'store',
				'page'));
	}
	add_action('pre_get_posts', 'vipx_parse_request_tricksy');
?>