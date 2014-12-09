<?php get_header(); ?>
<header id="masthead" class="site-header" role="banner">
		<hgroup>
        <!-- CATEGORY HEADER -->
        <?php
        $term =	$wp_query->queried_object;
        $cat_name = $term->name;
        $cat_name .= ' Coupon Codes';
        ?>
            <h1 class="site-title"><a href="" title="<?php echo $cat_name; ?>" rel="home"><?php echo $cat_name . " Discount Codes ";echo date('F Y'); ?></a></h1>
			<p class="site-description">
            Savings with <?php echo $cat_name; ?> in <?php echo date('F Y'); ?>. <?php if($latest_cp_title): ?> Today's top <?php echo $cat_name; ?>: <?php echo $latest_cp_title; ?>. <?php endif; ?>
            </p>
		</hgroup>

        <div class="breadcrumbs" style="margin-top: 30px;">
            <?php if(function_exists('bcn_display'))
            {
                bcn_display();
            }?>
        </div>
		<?php if ( get_header_image() ) : ?>
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><img src="<?php header_image(); ?>" class="header-image" width="<?php echo get_custom_header()->width; ?>" height="<?php echo get_custom_header()->height; ?>" alt="" /></a>
		<?php endif;?>
</header><!-- #masthead -->

<div id="main" class="wrapper">
    <div id="primary" class="site-content">
        <div id="content" role="main">
<?php
    global $wpdb;
    $cat_name = $term;
    $t = get_term_by('name', $cat_name->name, 'store_category');

    $cat_id = $t->term_id;
    // Get stores in this category
    $arr_stores = get_posts( array(
        'post_type' => 'store',
        'showposts' => -1,
        'tax_query' => array(
            array(
                'taxonomy' => 'store_category',
                'field' => 'slug',
                'terms' => $t->slug
            )
        )
    ) );

    foreach ($arr_stores as $s):
    // Store info
    $st_title = get_post_field('post_title', $s->ID);
    $cp_id = cpx_get_latest_cp_in_store($s->ID, 1, 'ID');
    // If store have published coupon => alter store title with [store_name(removed "Coupon Codes") + coupon title]
    if($cp_id)
        $st_alter_title = str_replace('Coupon Codes', '', $st_title) . ' ' . cpx_get_latest_cp_in_store($s->ID);
    else
        $st_alter_title = $st_title;

    $st_permalink = get_post_field('post_name', $s->ID);
    $st_permalink = home_url().'/'.$st_permalink;
    $st_logo = get_post_meta($s->ID, 'store_img_metadata', true);
    // Coupon info

    $cp_content = get_post_field('post_content', $cp_id);
    $cp_code = get_post_meta($cp_id, 'coupon_code_metadata', true);
    $cp_use_today = get_post_meta($cp_id, 'use_today_metadata', true);
    if(!$cp_use_today)
        $cp_use_today = 1;
?>
    <article>
		<header class="entry-header">
        <div class="item-thumb">
            <a title="<?php echo $st_title; ?>" href="<?php echo $st_permalink; ?>">
                <img alt="<?php echo $st_title . ' logo'; ?>" src="<?php echo $st_logo; ?>" style="width: 100px;height: 50px;" />
            </a>
        </div>
        <div class="item-title">
			<h3 class="entry-title"><?php echo $st_alter_title; ?></h3>
        </div>
		</header>
        <?php
        // PRINT COUPON DETAIL IF STORE HAVE PUBLISHED COUPON
        if($cp_id): ?>
		<div class="entry-content">
			<p><?php echo $cp_content; ?></p>
            <p>Coupon Code: <span class="badge"><?php echo $cp_code; ?></span></p>
            <p>(<?php echo $cp_use_today; ?> used today)</p>
            <p><a title="<?php echo $st_title; ?>" href="<?php echo $st_permalink;?>">More <?php echo $st_title . ' Coupon Codes'; ?></a></p>
		</div>
        <?php else: ?>
        <div class="entry-content">
			<p><?php echo limit_string(get_post_field('post_content', $s->ID), 200, '...'); ?></p>
            <p><a title="<?php echo $st_title; ?>" href="<?php echo $st_permalink;?>">More <?php echo $st_title . ' Coupon Codes'; ?></a></p>
		</div>
        <?php endif; ?>
        <?php if(current_user_can('edit_post')) echo edit_post_link('Edit Store','','',$s->ID); ?>
	</article><!-- #post-0 -->
    <?php
    endforeach;
?>
    </div>
</div>
<?php
	get_sidebar();
?>
<?php
	get_footer();
?>