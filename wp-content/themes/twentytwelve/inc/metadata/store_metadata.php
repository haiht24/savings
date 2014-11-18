<?php
	add_action('admin_init', 'create_store_metadata');
	function create_store_metadata()
	{
		// Create metabox
		$store_meta_box = array(
			'id' => 'box_store_metadata',
			'title' => 'Store Metadata',
			'desc' => '',
			'pages' => array('store'),
			'context' => 'normal',
			'priority' => 'high',
			'fields' => array(
                // All coupon type off
				array(
					'id' => 'store_type_off_metadata',
					'label' => 'All Type Off',
					'desc' => '',
					'std' => '',
					'type' => 'text',
					'class' => '',
					'choices' => ''),
				// Store URL
				array(
					'id' => 'store_url_metadata',
					'label' => 'Store URL',
					'desc' => '',
					'std' => '',
					'type' => 'text',
					'class' => '',
					'choices' => ''),
                // Store Homepage
				array(
					'id' => 'store_homepage_metadata',
					'label' => 'Store Home page',
					'desc' => '',
					'std' => '',
					'type' => 'text',
					'class' => '',
					'choices' => ''),
				// IMG
				array(
					'id' => 'store_img_metadata',
					'label' => 'Store Image',
					'desc' => '',
					'std' => '',
					'type' => 'upload',
					'class' => '',
					'choices' => ''),
                // Get coupon?
                array(
					'id' => 'is_get_coupon',
					'label' => 'Getted coupon ?',
					'desc' => '',
					'std' => '',
					'type' => 'text',
					'class' => '',
					'choices' => ''),
                // Last number coupon
                array(
					'id' => 'last_number_coupon',
					'label' => 'Last number coupon',
					'desc' => '',
					'std' => '',
					'type' => 'text',
					'class' => '',
					'choices' => ''),
                // Store view count
                array(
					'id' => 'store_view_count_metadata',
					'label' => 'View Count',
					'desc' => '',
					'std' => 1,
					'type' => 'text',
					'class' => '',
					'choices' => '')
                    ));
		ot_register_meta_box($store_meta_box);
	}
