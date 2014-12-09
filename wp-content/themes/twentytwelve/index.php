<?php get_header(); ?>
<header id="masthead" class="site-header" role="banner">
		<hgroup>
        <!-- HOME HEADER -->
			<h1 class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php bloginfo( 'name' ); ?></a></h1>
			<h2 class="site-description"><?php bloginfo( 'description' ); ?></h2>
		</hgroup>
		<?php if ( get_header_image() ) : ?>
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><img src="<?php header_image(); ?>" class="header-image" width="<?php echo get_custom_header()->width; ?>" height="<?php echo get_custom_header()->height; ?>" alt="" /></a>
		<?php endif; ?>
</header><!-- #masthead -->

	<div id="main" class="wrapper">
<?php
$rs = cpx_get_latest_cp_by_store();
if($rs != 0):
?>
	<div id="primary" class="site-content">
		<div id="content" role="main">
        <h2 style="margin-bottom: 10px;font-size: 2.0em;">Today's top coupons codes <?php echo the_date('F Y'); ?></h2>
        <?php foreach($rs as $r):
        // Store info
        $st_title = get_post_field('post_title', $r['st_id']);
        $st_permalink = get_post_field('post_name', $r['st_id']);
        $st_logo = get_post_meta($r['st_id'], 'store_img_metadata', true);
        // Coupon info
        $cp_content = get_post_field('post_content', $r['cp_id']);
        $cp_code = get_post_meta($r['cp_id'], 'coupon_code_metadata', true);
        $cp_use_today = get_post_meta($r['cp_id'], 'use_today_metadata', true);
        if(!$cp_use_today)
            $cp_use_today = 1;
        ?>
            <article>
				<header class="entry-header">
        			<div class="item-thumb">
                        <a title="<?php echo $st_title; ?>" href="<?php echo $st_permalink; ?>">
                            <img alt="<?php echo $st_title.' logo'?>" src="<?php echo $st_logo; ?>" style="width: 100px;height: 50px;" />
                        </a>
                    </div>
        			<div class="item-title">
                        <h3 class="entry-title"><?php echo $st_title; ?></h3>
        			</div>
				</header>

				<div class="entry-content">
					<p>
                    <?php echo $cp_content; ?>
                    </p>
                    <p>Coupon Code: <span class="badge"><?php echo $cp_code; ?></span></p>
                    <p>(<?php echo $cp_use_today; ?> used today)</p>
                    <p><a title="<?php echo $st_title; ?>" href="<?php echo $st_permalink;?>">More <?php echo $st_title . ' Coupon Codes'; ?></a></p>
				</div><!-- .entry-content -->
            <?php if(current_user_can('edit_post')) echo edit_post_link('Edit Coupon','','',$r['cp_id']); ?>
            <?php if(current_user_can('edit_post')) echo edit_post_link('Edit Store','','',$r['st_id']); ?>
			</article><!-- #post-0 -->
        <?php endforeach; ?>
		</div><!-- #content -->
	</div><!-- #primary -->
<?php endif; ?>

<?php get_sidebar(); ?>
<?php get_footer(); ?>