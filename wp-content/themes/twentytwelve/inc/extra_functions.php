<?php
	include_once ('load_custom_post_type.php');
	include_once ('load_metadata.php');
    include_once ('load_widgets.php');
	////////////////////////////////////////////CRAWL FUNCTIONS//////////////////////////////////////////////////////
    function _get_attachment_id_from_src($guid) {
        global $wpdb;
        $query = "SELECT ID FROM {$wpdb->posts} WHERE guid='$guid'";
        $id = $wpdb->get_var($query);
        return $id;
    }
    function getImageType($imgSrc){
        $image_type = explode('/', $imgSrc);
        $image_type = $image_type[count($image_type) - 1];
        $image_type = explode('.', $image_type);
        return $image_type[1];
    }
    function uploadLogoToServer($imgSrc, $storeName){
        $image_type = getImageType($imgSrc);
        $storeName = str_replace(' ', '_', $storeName);

        $fileName = $storeName.'_coupon_codes_logo.'.$image_type;
        $uploadDir = wp_upload_dir();
        $uploadPath = $uploadDir['path'] . '/' . $fileName;
        $guid = $uploadDir['url'] . '/' . $fileName;

        $contents = file_get_contents($imgSrc);
		$savefile = fopen($uploadPath, 'w');
		fwrite($savefile, $contents);
		fclose($savefile);
        return $guid;
    }

    function append_slug($data)
	{
		global $post_ID;
		if (!empty($data['post_name']) && $data['post_type'] == 'store')
		{
            $keyword_after_storename_slug = get_option('store_slug');
            if(!$keyword_after_storename_slug)
            {
                $keyword_after_storename_slug = 'discount codes';
            }
			if ($keyword_after_storename_slug)
            {
                $new_store_slug = $data['post_title'] . '-' . $keyword_after_storename_slug;
            }
			else
            {
                $new_store_slug = $data['post_title'] . '-' . 'coupons';
            }

			$data['post_name'] = sanitize_title($new_store_slug, $post_ID);
		}
		return $data;
	}
    add_filter('wp_insert_post_data', 'append_slug', 10);
    function cpx_get_user_role_name()
    {
    	$user = new WP_User(get_current_user_id());
    	return array_shift($user->roles);
    }
	// CHECK EXIST POST TITLE
	function check_exist_title($title)
	{
		global $wpdb;
		$qr = "SELECT post_title FROM wp_posts WHERE post_status NOT IN('inherit','trash') AND post_title = '{$title}'";
		$rs = $wpdb->get_row($qr, 'ARRAY_A');
		return count($rs);
	}
	// PRINT CATEGORY NOT CHECK
	function print_category_not_check($returnType = '')
	{
		$terms = get_terms('store_category', array(
			'hide_empty' => 0,
			'orderby' => 'id',
			'order' => 'ASC'));
		if (count($terms) > 0)
		{
            $arr = array();
			foreach ($terms as $t)
			{
				$checked = get_tax_meta($t->term_id, 'checked');
				if ($checked == 'no')
				{
					$url = get_tax_meta($t->term_id, 'category_url');
					$ip = "<input class='cat' id='{$t->term_id}' value='$url' type='hidden'>";
                    if($returnType == 'array'){
                        array_push($arr, $ip);
                    }else{
                        echo $ip;
                    }
				}
			}
            if($returnType == 'array'){
                return $arr;
            }
		}
	}
	// PRINT CATEGORY NOT PARENTING
	function print_cat_not_parenting()
	{
		$terms = get_terms('store_category', array(
			'hide_empty' => 0,
			'orderby' => 'id',
			'order' => 'ASC'));
		if (count($terms) > 0)
		{
			foreach ($terms as $t)
			{
				$parenting = get_tax_meta($t->term_id, 'parenting');
				if ($parenting != 1)
				{
					$url = get_tax_meta($t->term_id, 'category_url');
					$ip = "<input class='cat_parenting' id='{$t->term_id}' cat_name='{$t->name} Coupon Codes' value='$url' type='hidden'>";
					echo $ip;
				}
			}
		}
	}
	// PRINT CATEGORY NOT GET STORES
	function print_cat_not_getted_stores($returnType = '')
	{
		$terms = get_terms('store_category', array(
			'hide_empty' => 0,
			'orderby' => 'id',
			'order' => 'ASC'));
        $arr = array();
		if (count($terms) > 0)
		{
			foreach ($terms as $t)
			{
				$vl = get_tax_meta($t->term_id, 'already_get_store');
				if ($vl == '')
				{
					$ip = "<input class='catNotGetStore' id='{$t->term_id}' value='{$t->name}' type='hidden'>";
                    if($returnType == 'array')
                        array_push($arr, $ip);
                    else
					   echo $ip;
				}
			}
		}
        if($returnType == 'array'){
            return $arr;
        }
	}
	// PRINT STORES NOT GET COUPON
	function print_stores_not_get_coupon()
	{
		global $wpdb;
		$qr = "select ID from wp_posts where post_type='store' order by ID ASC";
		$rs = $wpdb->get_results($qr);
		if (count($rs) > 0)
		{
			foreach ($rs as $r)
			{
				$getted_coupon = get_post_meta($r->ID, 'is_get_coupon', true);
				if (!$getted_coupon)
				{
					$store_url = get_post_meta($r->ID, 'store_url_metadata', true);
					$ip = "<input class='store_not_get_coupon' id='{$r->ID}' value='{$store_url}' type='hidden'>";
                    echo $ip;
				}
			}
		}
	}
    function savings_printStoresNotGetCoupons(){
        global $wpdb;
		$qr = "select ID from wp_posts where post_type='store' and post_status not in('auto-draft', 'inherit') order by ID ASC";
		$rs = $wpdb->get_results($qr);
        $arrStores = array();
		if (count($rs) > 0)
		{
			foreach ($rs as $r)
			{
				$getted_coupon = get_post_meta($r->ID, 'is_get_coupon', true);
				if (!$getted_coupon)
				{
					$store_url = get_post_meta($r->ID, 'store_url_metadata', true);
					$singleStore = array();
                    $singleStore['url'] = $store_url;
                    $singleStore['id'] = $r->ID;
                    array_push($arrStores, $singleStore);
				}
			}
		}
        return $arrStores;
    }
	// GET EXPIRE DATE
	function get_expire_date($cp_content)
	{
		$expireDate = '';
		$cp_content = str_replace('.', ' ', $cp_content);
		// Array keyword before expire date
		$args = array(
			'Comes to an end',
			'Runs out on',
			'Finishes on',
			'Stops',
			'Expires on',
			'Ends');
		foreach ($args as $a)
		{
			if (strpos($cp_content, $a))
			{
				$end = strpos($cp_content, '-');
				if (!$end)
				{
					$end = strlen($cp_content);
				}
				$expireDate = substr($cp_content, strpos($cp_content, $a), $end);
				$expireDate = explode('-', $expireDate);
				$expireDate = str_replace($a, '', $expireDate[0]);
				$expireDate = trim($expireDate);
				return $expireDate;
				break;
			}
		}
	}
	// DELETE POST
	function delete_post($post_type = 'post')
	{
		$qr = "select ID from wp_posts where post_type='{$post_type}'";
		global $wpdb;
		$rs = $wpdb->get_results($qr);
		if (count($rs) > 0)
		{
			foreach ($rs as $r)
			{
				wp_delete_post($r->ID);
			}
		}
		return count($rs);
	}
	/**
	 * ADMIN SCRIPTS
	 */
	function add_jquery_admin()
	{
		global $parent_file;
		if ($parent_file == 'edit.php?post_type=coupon')
		{
			wp_enqueue_script('jquery_coupon_page', get_template_directory_uri() .
				'/ajax/admin/admin_coupon.js');
		}
	}
	add_filter('admin_head', 'add_jquery_admin');
    add_action('admin_head','theme_directory');
    function theme_directory()
    { ?>
        <script type="text/javascript">
        var cpx_theme_uri = '<?php echo get_template_directory_uri()?>';
        var cpx_site_url = '<?php echo site_url();?>';
        </script>
    <?php
    }
	/**
	 * /////////////////////////////////////////////////////////////////////////////////
	 */
	function cpx_get_latest_cp_by_store()
	{
		global $wpdb;
		$qr = "
        SELECT post_id AS cp_id, meta_value AS st_id FROM wp_postmeta WHERE meta_key = 'store_id_metadata'
        AND post_id IN (SELECT ID FROM wp_posts WHERE post_type = 'coupon' AND post_status='publish')
        AND meta_value IN (SELECT ID FROM wp_posts WHERE post_type = 'store' AND post_status='publish')
        GROUP BY meta_value ORDER BY meta_id DESC LIMIT 0,20
        ";
		$rs = $wpdb->get_results($qr, ARRAY_A);
		if (count($rs) > 0)
			return $rs;
		else
			return 0;
	}
    function cpx_get_latest_cp_in_store($st_id, $number = 1, $return_what = 'post_title')
    {
        global $wpdb;
		$qr = "
        SELECT post_id AS cp_id FROM wp_postmeta WHERE
        (meta_key = 'store_id_metadata' AND meta_value={$st_id})
        AND post_id IN (SELECT ID FROM wp_posts WHERE post_type = 'coupon' AND post_status='publish')
        AND meta_value IN (SELECT ID FROM wp_posts WHERE post_type = 'store' AND post_status='publish')
        ORDER BY meta_id DESC LIMIT 0,$number
        ";
		$rs = $wpdb->get_results($qr, ARRAY_A);
		if (count($rs) > 0)
		{
            if($number == 1)
            {
                if($return_what == 'post_title')
                    return get_post_field('post_title', $rs[0]['cp_id']);
                else
                    return $rs[0]['cp_id'];
            }
            else if($number > 1)
            {
                return $rs;
            }
		}
    }
    function cpx_current_url()
	{
		$pageURL = 'http';
		if ($_SERVER["HTTPS"] == "on")
		{
			$pageURL .= "s";
		}
		$pageURL .= "://";
		if ($_SERVER["SERVER_PORT"] != "80")
		{
			$pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
		}
		else
		{
			$pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
		}
		return $pageURL;
	}
    /**
     * REMOVE DEFAULT WORDPRESS WIDGETS
     */
    if(is_admin())
    {
        add_action('admin_footer','rm_default_wpwg');
    }
    function rm_default_wpwg()
    { ?>
        <script type="text/javascript">
        jQuery(document).ready(function($)
        {
            if(pagenow == 'widgets')
            {
                $('div[id*="archives"]').remove();
                $('div[id*="calendar"]').remove();
                $('div[id*="categories"]').remove();
                $('div[id*="nav_menu"]').remove();
                $('div[id*="meta"]').remove();
                $('div[id*="pages"]').remove();
                $('div[id*="recent-comments"]').remove();
                $('div[id*="recent-posts"]').remove();
                $('div[id*="rss"]').remove();
                $('div[id*="search"]').remove();
                $('div[id*="tag_cloud"]').remove();
                $('div[id*="text"]').remove();
                $('div[id*="recent-posts"]').remove();
            }
        })
        </script>
    <?php
    }
    function cpx_get_relate_store($store_id, $itemsToShow)
	{
		// get the custom post type's taxonomy terms
		$custom_taxterms = wp_get_object_terms($store_id, 'store_category', array('fields' => 'ids'));
		// arguments
		$args = array(
			'post_type' => 'store',
			'post_status' => 'publish',
			'posts_per_page' => $itemsToShow,
			'orderby' => 'rand',
			'tax_query' => array(array(
					'taxonomy' => 'store_category',
					'field' => 'id',
					'terms' => $custom_taxterms)));
		$related_items = new WP_Query($args);
		$arr_st_ids = array();
		if ($related_items->have_posts())
		{
			foreach ($related_items->posts as $p)
			{
				array_push($arr_st_ids, (int)$p->ID);
			}
		}
		return $arr_st_ids;
	}
    function cpx_print_coupon($coupon_id, $print_type_off = 1)
    {
        $arr_expire_prefix = array(
			'Comes to an end',
			'Runs out on',
			'Finishes on',
			'Stops',
			'Expires on',
			'Ends');
        $cp_title = get_post_field('post_title', $coupon_id);

        $st_id = get_post_meta($coupon_id, 'store_id_metadata', true);
        $st_permalink = get_permalink($st_id);
        $cp_content = get_post_field('post_content', $coupon_id);

        // Create filter Type Off
        $cp_type_off = get_post_meta($coupon_id, 'coupon_typeoff_metadata', true);
        $result = cpx_filter_coupon_type_off($cp_type_off, $st_permalink, $cp_content);
        $cp_content = $result['content'];

        $cp_code = get_post_meta($coupon_id, 'coupon_code_metadata', true);
        $cp_use_today = get_post_meta($coupon_id, 'use_today_metadata', true);
        $cp_expire = get_post_meta($coupon_id, 'coupon_expire_date_metadata', true);

        if(!$cp_use_today)
            $cp_use_today = 1;
        ?>
        <article>
			<header class="entry-header">
				<h3 class="entry-title"><?php echo $cp_title; ?></h3>
			</header>
			<div class="entry-content">
				<p><?php echo $cp_content; ?>
                <?php //if($cp_expire){echo $arr_expire_prefix[array_rand($arr_expire_prefix)].' '.$cp_expire;}
                if($print_type_off == 1):
                    if($result['percent_off'] || $result['dolar_off'] || $result['free']) {echo " - Coupon Type: ".$result['percent_off'].' '.$result['dolar_off'].' '.$result['free'];}
                endif;
                ?>
                </p>
                <p>Coupon Code: <?php echo $cp_code; ?></p>
                <p>(<?php echo $cp_use_today; ?> used today)</p>
			</div><!-- .entry-content -->
        <?php if(current_user_can('edit_post')) echo edit_post_link('Edit Coupon','','',$coupon_id); ?>
		</article><!-- #post-0 -->
        <?php
    }
    function cpx_filter_coupon_type_off($cp_type_off, $st_permalink, $cp_content = '')
    {
        $arr_result = array();
        if(strpos($cp_content, '- Coupon Type'))
        {
            $cp_content = str_replace(substr($cp_content, strpos($cp_content, '- Coupon Type')), '', $cp_content);
            $arr_result['content'] = $cp_content;
        }
        if($cp_type_off)
        {
            preg_match("/(?<digit>\d+)[%] Off/", $cp_type_off, $arr_percent_off);
            preg_match("/(?<digit>\d+)[$] Off/", $cp_type_off, $arr_dolar_off);
            preg_match("/Free Shipping/", $cp_type_off, $arr_free_shipping);
            if($arr_percent_off)
            {
                $percent_off = $arr_percent_off[0];
            }
            if($arr_dolar_off)
            {
                $dolar_off = $arr_dolar_off[0];
            }
            if($arr_free_shipping)
            {
                $free_shipping = $arr_free_shipping[0];
            }

            if($percent_off)
            {
                $slug_percent_off = str_replace('%', ' percent', $percent_off);
                $slug_percent_off = sanitize_title($slug_percent_off);
                $filter_percent_off = "<a href='{$st_permalink}/{$slug_percent_off}'>$percent_off</a>";
                $arr_result['percent_off'] = $filter_percent_off;
            }
            if($dolar_off)
            {
                $slug_dolar_off = str_replace('$',' dolar', $dolar_off);
                $slug_dolar_off = sanitize_title($slug_dolar_off);
                $filter_dolar_off = "<a href='{$st_permalink}/{$slug_dolar_off}'>$dolar_off</a>";
                $arr_result['dolar_off'] = $filter_dolar_off;
            }
            if($free_shipping)
            {
                $filter_freeshipping = "<a href='{$st_permalink}/free-shipping'>Free Shipping</a>";
                $arr_result['free'] = $filter_freeshipping;
            }
        }
        return $arr_result;
    }