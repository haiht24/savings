<?php
$parse_uri = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );
require_once( $parse_uri[0] . 'wp-load.php' );

//require_once('/bot_libs/simple_html_dom.php');
require_once(get_template_directory() . '/ajax/bot_libs/simple_html_dom.php');
//load_template(trailingslashit(get_template_directory()) . 'bot_libs/simple_html_dom.php');
/**
 * CRAWL PARENT CATEGORY
 */
if($_POST['action'] == 'get_cat')
{
    $url = "http://stcouponcodes.com";
    $html = file_get_html($url);
    $div_categories = $html->find('div[class="sidebar-module"]',1)->find('ol[class="list-unstyled"]',0);

    $count = 0;
    foreach ($div_categories->find('li a' ) as $li) {
        $post_title = strip_tags($li);
        $post_content = $li->attr['href'];

        //$parent_term = term_exists( 'fruits', 'store_category' ); // array is returned if taxonomy is given
        //$parent_term_id = $parent_term['term_id']; // get numeric term id
        $term = wp_insert_term(
          $post_title, // the term
          'store_category', // the taxonomy
          array(
            //'description'=> $url.$post_content,
            'slug' => $post_title . '-discount-codes'
            //,'parent'=> $parent_term_id
          )
        );
        if(!$term->errors)
        {
            $count++;
            $tax_meta = new Tax_Meta_Class(array());
            $tax_meta->save_field($term['term_id'],array('id'=>'category_url'),'',$url.$post_content);
            $tax_meta->save_field($term['term_id'],array('id'=>'checked'),'','no');
        }
    }
    echo $count;
}
/**
 *  GET OTHER CATEGORIES
 */
else if($_POST['action'] == 'get_other_cat')
{
    $home = "http://stcouponcodes.com";
    $url = $_POST['categoryURL'];
    $catID = $_POST['categoryID'];
    $c = 0;

    $html = file_get_html($url);
    // find div Related category
    $html_input_new_category = '';
    $arr = array();
    $arr_newcat = array();

    foreach ($html->find('div[class="sidebar-module"]') as $h) {
        foreach ($h->find('h4') as $h4) {
            if($h4->innertext == 'Related Coupon Codes Categories')
            {
                $ol = $h->find('ol[class="list-unstyled"]',0);
                foreach ($ol->find('li a') as $li) {
                    $post_title = strip_tags($li);
                    $post_content = $li->attr['href'];

                    // Add new category if not exist
                    $term = wp_insert_term(
                      $post_title,
                      'store_category',
                      array()
                    );
                    if(!$term->errors)
                    {
                        $c++;
                        $term_id = $term['term_id'];
                        $cat_url = $home.$post_content;
                        $html_input_new_category = "<input class='cat' id='{$term_id}' value='$cat_url' type='hidden'>";

                        array_push($arr_newcat, $html_input_new_category);

                        $tax_meta = new Tax_Meta_Class(array());
                        $tax_meta->save_field($term_id,array('id'=>'category_url'),'',$cat_url);
                        $tax_meta->save_field($term_id,array('id'=>'checked'),'','no');
                    }
                }
                break;
            }
        }
    }
    // Mark as checked
    $tax_meta = new Tax_Meta_Class(array());
    $tax_meta->save_field($catID,array('id'=>'checked'),'','yes');
    $arr['c'] = $c;
    $arr['new_cat'] = $arr_newcat;
    echo json_encode($arr);
}
/**
 * GET STORES
 */
else if($_POST['action'] == 'get_store')
{
    $home = "http://stcouponcodes.com";
    $cat_id = $_POST['cat_id'];
    $cat_name = $_POST['cat_name'];

    $url = get_tax_meta($cat_id,'category_url');
    $html = file_get_html($url);
    $ul = $html->find('ul[class="media-list"]',0);

    $c = 0;
    foreach ($ul->find('li') as $li) {
        $store_obj = $li->find('div[class="media-body"] div a',0);
        $title = str_replace(' »','',str_replace('More ','',$store_obj->innertext));
        $title = str_replace('Coupon', 'Discount', $title);
        // Check exist store title
        if(check_exist_title($title) == 0)
        {
            $href = $home.$store_obj->href;
            $img_obj = $li->find('a[class="pull-left thumbnail"] img',0);
            $img = $img_obj->src;
            // add new store
            $post_args = array(
            'post_title' => $title,
            'post_status' => 'draft',
            'post_type' => 'store'
            );
            $new_post_id = wp_insert_post($post_args);
            if($new_post_id > 0)
            {
                $c++;
                // set category for new store
                wp_set_object_terms($new_post_id,array($cat_name),'store_category');
                add_post_meta($new_post_id,'store_view_count_metadata',1,true);
                add_post_meta($new_post_id,'store_url_metadata',$href,true);
                add_post_meta($new_post_id,'store_img_metadata',$img,true);
            }
        }
    }
    // Mark as getted stores
    $tax_meta = new Tax_Meta_Class(array());
    $tax_meta->save_field($cat_id,array('id'=>'already_get_store'),'','yes');
    echo $c;
}
/**
 *  GET COUPONS
 */
else if($_POST['action'] == 'get_coupon')
{
    $c = 0;
    $storeID = $_POST['store_id'];
    $storeURL = $_POST['store_url'];
    $last_number_coupon = get_post_meta($storeID, 'last_number_coupon', true);
    //$storeURL = "http://stcouponcodes.com/ebags-coupon-codes/";

    $html = file_get_html($storeURL);
    $store_description = $html->find('div[class="media"] p', 0)->innertext;
    $store_homepage = substr($store_description, strpos($store_description, 'online store') + 13);
    // update store content
    wp_update_post(array('ID' => $storeID, 'post_content' => $store_description));
    update_post_meta($storeID, 'store_homepage_metadata', $store_homepage);

    $ul_coupon_list = $html->find('ul[class="media-list"]',0);

    // If have new coupon
    if($last_number_coupon)
    {
        if($last_number_coupon < count($ul_coupon_list->find('li[class="media"]')))
        {
            $number_new_coupon = count($ul_coupon_list->find('li[class="media"]')) - $last_number_coupon;
            for($i = 0; $i< $number_new_coupon; $i++)
            {
                $li_newcp = $ul_coupon_list->find('li[class="media"]', $i);
                $c++;
                add_new_coupon($li_newcp, $storeID);
            }
            update_post_meta($storeID, 'last_number_coupon', count($ul_coupon_list->find('li[class="media"]')));
            update_post_meta($storeID, 'is_get_coupon', 1);
            echo $c;
            return;
        }
    }
    else
    {
        foreach ($ul_coupon_list->find('li[class="media"]') as $li)
        {
            $c++;
            add_new_coupon($li, $storeID);
        }
        // Mark as getted coupon
        update_post_meta($storeID, 'is_get_coupon', 1);
        update_post_meta($storeID, 'last_number_coupon', $c);
        echo $c;
    }
}
/**
 * PARENTING CATEGORIES
 */
else if($_POST['action'] == 'parenting')
{
    $cat_id = $_POST['cat_id'];
    $cat_name = $_POST['cat_name'];
    $cat_url = $_POST['cat_url'];

    $html = file_get_html($cat_url);
    $ol = $html->find('.breadcrumb', 0);
    foreach ($ol->find('li') as $k=>$li) {
        $current_active_cat_name = $li->find('strong', 0)->innertext;
        if($current_active_cat_name)
        {
            $parent_cat_name = $ol->find('li', $k - 1)->find('span', 0)->innertext;
            if($parent_cat_name != 'Home')
            {
                $parent_term = get_term_by('name',$parent_cat_name,'store_category');
                if($parent_term)
                {
                    wp_update_term($cat_id, 'store_category', array('parent' => $parent_term->term_id));
                }
            }
            update_tax_meta($cat_id, 'parenting', 1);
        }
    }
    echo "Updated: cat id: $cat_id | cat name: $cat_name | cat url: $cat_url <br/>";
}
/**
 *  ****************************************RESET VALUES***********************************************************
 */
// Reset check getted stores
else if($_POST['action'] == 'reset_check_get_store')
{
    $terms = get_terms('store_category',array('hide_empty' => 0,'orderby' => 'id', 'order' => 'ASC'));
    if(count($terms) > 0)
    {
        foreach ($terms as $t) {
            $tax_meta = new Tax_Meta_Class(array());
            $tax_meta->save_field($t->term_id,array('id'=>'already_get_store'),'','');
        }
    }
}
// Delete stores
else if($_POST['action'] == 'del_stores')
{
    echo delete_post('store');
}
else if($_POST['action'] == 'del_coupons')
{
    echo delete_post('coupon');
}
// Reset check cat
else if($_POST['action'] == 'reset_check_cat')
{
    $terms = get_terms('store_category',array('hide_empty' => 0,'orderby' => 'id', 'order' => 'ASC'));
    if(count($terms) > 0)
    {
        foreach ($terms as $t) {
            $tax_meta = new Tax_Meta_Class(array());
            $tax_meta->save_field($t->term_id,array('id'=>'checked'),'','no');
        }
    }
}
// Reset check stores is get coupon
else if($_POST['action'] == 'reset_check_isgetcoupon')
{
    global $wpdb;
    $qr = "select post_id from wp_postmeta where meta_key='is_get_coupon' ";
    $rs = $wpdb->get_results($qr);
    if(count($rs) > 0)
    {
        foreach ($rs as $r) {
            delete_post_meta($r->post_id, 'is_get_coupon');
        }
    }
}
// Reset last number coupon
else if($_POST['action'] == 'reset_lastnumbercoupon')
{
    global $wpdb;
    $qr = "DELETE FROM wp_postmeta WHERE meta_key = 'last_number_coupon'";
    $rs = $wpdb->query($qr);
}

function add_new_coupon($li, $storeID)
{
    $cp_title = $li->find('div[class="media-body"] h3', 0)->innertext;
    $cp_content = $li->find('div[class="media-body"] p', 0);
    $cp_content = strip_tags($cp_content);
    $cp_type = '';
    $position_coupon = strpos($cp_content,'Coupon Type:');
    if($position_coupon > 0)
    {
        $cp_type = substr($cp_content, $position_coupon);
        $cp_type = trim(str_replace('Coupon Type:', '', $cp_type));

        $cp_type = str_replace('Off', 'Off,', $cp_type);
        $old_store_meta_value = get_post_meta($storeID, 'store_type_off_metadata', true);
        $new_store_meta_value = $old_store_meta_value . ',' . $cp_type;
        update_post_meta($storeID, 'store_type_off_metadata', $new_store_meta_value);

    }
    // Get expire date
    $expireDate = get_expire_date($cp_content);
    $cp_code = $li->find('span[class="badge"]', 0)->innertext;
    // Add coupon
    if(check_exist_title($cp_title) == 0)
    {
        $coupon_args = array(
        'post_title' => $cp_title,
        'post_content' => $cp_content,
        'post_type' => 'coupon',
        'post_status' => 'draft'
        );
        $coupon_id = wp_insert_post($coupon_args);
        if($coupon_id > 0)
        {
            add_post_meta($coupon_id, 'store_id_metadata', $storeID, true);
            if($cp_code)
                add_post_meta($coupon_id, 'coupon_code_metadata', $cp_code, true);
            if($cp_type)
                add_post_meta($coupon_id, 'coupon_typeoff_metadata', $cp_type, true);
            if($expireDate)
                add_post_meta($coupon_id, 'coupon_expire_date_metadata', $expireDate, true);
        }
    }
}


