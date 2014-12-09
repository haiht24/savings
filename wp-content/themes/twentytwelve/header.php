<!DOCTYPE html>
<!--[if IE 7]>
<html class="ie ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 7) | !(IE 8)  ]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta name="viewport" content="width=device-width" />
<?php
if(is_singular('store')){
    $storeName = get_post_field('post_title', $post->ID);
    $lasest_cp_id = cpx_get_latest_cp_in_store($post->ID, 1, 'id');
    if($lasest_cp_id){
        $lastestCpDiscountValue = get_post_meta($lasest_cp_id, 'coupon_discount_metadata', true);
    }
    $arrCategories = wp_get_post_terms($post->ID, array('store_category'));
    $strCatList = '';
    if($arrCategories){
        foreach ($arrCategories as $k => $c) {
            if($k < count($arrCategories) - 1){
                $strCatList .= $c->name . ', ';
            }else{
                $strCatList .= $c->name;
            }
        }
    }
    $countPublishedCoupon = countCouponsByStatus($post->ID, 'publish');
    $currentMonthYear = date('F Y');
    $storeDescription = "{$storeName} coupon for today's Hot Deal: Get {$lastestCpDiscountValue} Off Sale {$strCatList}. Get {$countPublishedCoupon} {$storeName} Coupon codes and coupons for {$currentMonthYear}";
}
?>
<title><?php wp_title( '|', true, 'right' ); ?></title>
<?php if(is_singular('store')): ?>
<meta name=description content="<?php echo $storeDescription; ?>"/>
<?php endif; ?>
<link rel="shortcut icon" href="<?php echo get_stylesheet_directory_uri(); ?>/favicon.ico" />
<link rel="profile" href="http://gmpg.org/xfn/11" />
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />
<?php // Loads HTML5 JavaScript file to add support for HTML5 elements in older IE versions. ?>
<!--[if lt IE 9]>
<script src="<?php echo get_template_directory_uri(); ?>/js/html5.js" type="text/javascript"></script>
<![endif]-->
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<div id="page" class="hfeed site">
