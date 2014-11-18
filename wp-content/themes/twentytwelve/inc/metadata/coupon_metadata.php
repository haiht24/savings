<?php
	add_action('admin_init', 'create_coupon_metadata');
	function create_coupon_metadata()
	{
		// Create metabox
		$coupon_meta_box = array(
			'id' => 'box_coupon_metadata',
			'title' => 'Coupon Metadata',
			'desc' => '',
			'pages' => array('coupon'),
			'context' => 'normal',
			'priority' => 'high',
			'fields' => array(
                // In store
                array(
					'id' => 'store_id_metadata',
					'label' => 'Store',
					'desc' => '',
					'std' => '',
					'type' => 'text',
					'class' => '',
					'choices' => ''),
                // Use today
                array(
					'id' => 'use_today_metadata',
					'label' => 'Use Today',
					'desc' => '',
					'std' => '',
					'type' => 'text',
					'class' => '',
					'choices' => ''),
				// Code
				array(
					'id' => 'coupon_code_metadata',
					'label' => 'Coupon Code',
					'desc' => '',
					'std' => '',
					'type' => 'text',
					'class' => '',
					'choices' => ''),
                // Discount
                array(
					'id' => 'coupon_discount_metadata',
					'label' => 'Discount',
					'desc' => '% , $',
					'std' => '',
					'type' => 'text',
					'class' => '',
					'choices' => ''),
                // Type off
                array(
					'id' => 'coupon_typeoff_metadata',
					'label' => 'Type OFF',
					'desc' => '',
					'std' => '',
					'type' => 'text',
					'class' => '',
					'choices' => ''),
				// Expire
				array(
					'id' => 'coupon_expire_date_metadata',
					'label' => 'Expire',
					'desc' => 'yyyy/mm/dd',
					'std' => '',
					'type' => 'text',
					'class' => '',
					'choices' => '')

                    ));
		ot_register_meta_box($coupon_meta_box);
	}
