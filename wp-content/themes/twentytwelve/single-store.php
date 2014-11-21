<?php get_header(); ?>
<header id="masthead" class="site-header" role="banner">
		<hgroup>
        <!-- STORE DETAIL HEADER -->
        <?php
            $token = explode('/', cpx_current_url());
            if(strpos(cpx_current_url(), 'percent-off') || strpos(cpx_current_url(), 'dolar-off') || strpos(cpx_current_url(), 'free-shipping'))
            {
                $type_off = str_replace('-', ' ', end($token));
                $type_off = str_replace('dolar', '$', $type_off);
                $type_off = str_replace('percent', '%', $type_off);
                if(strpos($type_off, '$'))
                {
                    $t = explode(' ', $type_off);
                    $like = $t[0].$t[1].' '.$t[2];
                }
                else if(strpos($type_off, '%'))
                {
                    $t = explode(' ', $type_off);
                    $like = $t[0].'\\'.$t[1].' '.$t[2];
                }
                else if(strpos($type_off, 'shipping'))
                {
                    $t = explode(' ', $type_off);
                    $like = 'Free Shipping';
                }
                $qr = "
                SELECT p1.post_id AS cp_id FROM wp_postmeta p1, wp_postmeta p2 WHERE p1.`post_id` = p2.`post_id` AND
                p1.meta_key = 'coupon_typeoff_metadata' AND p1.meta_value LIKE '%$like%'
                AND p2.`meta_key` = 'store_id_metadata' AND p2.`meta_value` = {$post->ID}
                ";
                global $wpdb;
                $arr_coupons = $wpdb->get_results($qr, ARRAY_A);

                $store_title = get_post_field('post_title', $post->ID);
                $store_title .= ' '.str_replace('\\', '', $like);
                $store_home = get_post_meta($post->ID, 'store_homepage_metadata', true);
                $store_logo = get_post_meta($post->ID, 'store_img_metadata', true);
                if(function_exists('bcn_display'))
                {
                    $breadcrum = bcn_display(true).' / '.$store_title;
                }
            }
            else
            {
                $arr_coupons = cpx_get_latest_cp_in_store($post->ID, 1000);
                $store_title = get_post_field('post_title', $post->ID);
                $store_home = get_post_meta($post->ID, 'store_homepage_metadata', true);
                $store_logo = get_post_meta($post->ID, 'store_img_metadata', true);
                if(function_exists('bcn_display'))
                {
                    $breadcrum = bcn_display(true);
                }
            }
            $latest_cp_title = cpx_get_latest_cp_in_store($post->ID);
        ?>
            <h1 class="site-title"><a href="<?php echo cpx_current_url(); ?>" title="<?php echo $store_title; ?>" rel="home"><?php echo $store_title; ?></a></h1>
			<p class="site-description">
            Best Online <?php echo $store_title; ?> in <?php echo date('F Y'); ?> are updated and verified. <?php if($latest_cp_title): ?> Today's top <?php echo $store_title; ?>: <?php echo $latest_cp_title; ?>. <?php endif; ?>
            </p>
		</hgroup>
        <div class="breadcrumbs" style="margin-top: 30px;">
        <?php echo $breadcrum; ?>
        </div>
		<?php if ( get_header_image() ) : ?>
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><img src="<?php header_image(); ?>" class="header-image" width="<?php echo get_custom_header()->width; ?>" height="<?php echo get_custom_header()->height; ?>" alt="" /></a>
		<?php endif; ?>
</header><!-- #masthead -->

<div id="main" class="wrapper">
	<div id="primary" class="site-content">
    <?php if(current_user_can('edit_post')) echo edit_post_link('Edit Store','','',$post->ID); ?>
		<div id="content" role="main">
        <a href="" title="<?php echo $store_title; ?>">
            <img alt="<?php echo $store_title.' logo'; ?>" style="float: left;margin-right: 10px;width: 100px;height: 50px;" src="<?php echo $store_logo; ?>" />
        </a>
        <h2 style="margin-bottom: 10px;font-size: 2.0em;"><?php echo $store_title; ?> <?php echo date('F Y'); ?></h2>
        <p>
        Best online <?php echo $store_title; ?> in <?php echo date('F Y'); ?>, updated daily. You can find and share all <?php echo $store_title; ?> for savings at online store <?php echo $store_home; ?>
        </p>
        <hr />
<!-- PRINT COUPONS -->
        <?php
        if(count($arr_coupons) > 0)
        {
            foreach($arr_coupons as $c)
            {
                savings_print_coupon($c['cp_id']);
            }
        }
        ?>
<!-- #PRINT COUPONS -->

		</div><!-- #content -->
	</div><!-- #primary -->
<?php get_sidebar(); ?>
<?php get_footer(); ?>