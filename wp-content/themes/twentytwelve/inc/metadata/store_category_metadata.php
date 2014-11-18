<?php
	$prefix = '';
	/*
	* configure your meta box
	*/
	$config = array(
		'id' => 'category_url', // meta box id, unique per meta box
		'title' => 'Parent Category Url', // meta box title
		'pages' => array('store_category'), // taxonomy name, accept categories, post_tag and custom taxonomies
		'context' => 'normal', // where the meta box appear: normal (default), advanced, side; optional
		'fields' => array(), // list of meta fields (can be added by field arrays)
		'local_images' => false, // Use local or hosted images (meta box images for add/remove)
		'use_with_theme' => false
			//change path if used with theme set to true, false for a plugin or anything else for a custom path(default false).
			);
	/*
	* Initiate your meta box
	*/
	$my_meta = new Tax_Meta_Class($config);
	/*
	* Add fields to your meta box
	*/
	$my_meta->addText($prefix . 'category_url', array('name' => __('Category URL ', 'tax-meta')));
	$my_meta->addText($prefix . 'checked', array('name' => __('Checked ', 'tax-meta')));
	$my_meta->addText($prefix . 'already_get_store', array('name' => __('Already get Stores ',
			'tax-meta')));
    $my_meta->addText($prefix . 'parenting', array('name' => __('Parenting ', 'tax-meta')));
?>