<?php
 $parse_uri = explode('wp-content', $_SERVER['SCRIPT_FILENAME']);
 require_once ($parse_uri[0] . 'wp-load.php');
 require_once (get_template_directory() . '/ajax/bot_libs/simple_html_dom.php');

 if ($_POST['action'] == 'get_store') {
     $homeUrl = "http://savings.com";
     //$cat_id = $_POST['cat_id'];
     //$cat_name = $_POST['cat_name'];
     $currentPageNumber = $_POST['pageNum'];
     $cat_id = 2;
     $cat_name = 'apparel-accessories';

     $catUrl = get_tax_meta($cat_id, 'category_url');
     $catUrl = str_replace('.html', '', $catUrl);
     $catUrl = $catUrl . '-' . $currentPageNumber . '.html';
     $html = file_get_html($catUrl);

     // Find parent class : contain store logo,name and store url
     $storeDivParent = $html->find('.module-deal');
     $arrStores = array();
     foreach ($storeDivParent as $sp) {
         $singleStore = array();
         // Link to store detail page
         foreach ($sp->find('input[name="property-merchant-url"]') as $a) {
             $singleStore['url'] = $a->value;
         }
         // Store name
         foreach ($sp->find('input[name="property-merchant-name"]') as $n) {
             $singleStore['name'] = str_replace("'", "", $n->value);
         }
         // Store logo
         foreach ($sp->find('img') as $l) {
             $logo = explode('?', $l->getAttribute('data-original'));
             $singleStore['logo'] = $logo[0];
         }
         array_push($arrStores, $singleStore);
     }
     // Add store to database
     if (count($arrStores) > 0) {
         $numAdded = 0;
         foreach ($arrStores as $s) {
             if (check_exist_title($s['name']) == 0) {
                 $postArgs = array(
                     'post_title' => $s['name'],
                     'post_status' => 'pending',
                     'post_type' => 'store');
                 $newStoreId = wp_insert_post($postArgs);
                 // Add store metadata
                 if ($newStoreId) {
                     $numAdded++;
                     wp_set_object_terms($newStoreId, array($cat_name), 'store_category');
                     add_post_meta($newStoreId, 'store_url_metadata', $s['url'], true);
                     add_post_meta($newStoreId, 'store_img_metadata', $s['logo'], true);
                 }
             }
         }
     }
     $result = array();
     $result['isNext'] = sizeof($html->find('a[class="button next disabled"]'));
     $result['currentPageNumber'] = $currentPageNumber;
     $result['numAdded'] = $numAdded;
     echo json_encode($result);
 }
 if ($_POST['action'] == 'loadStores') {
     $arrStores = savings_printStoresNotGetCoupons();
     echo json_encode($arrStores);
 }
 if ($_POST['action'] == 'getCoupons') {
     $c = 0;
     $storeID = $_POST['storeID'];
     $storeURL = $_POST['storeURL'];
     $last_number_coupon = get_post_meta($storeID, 'last_number_coupon', true);

     //$storeURL = "http://www.savings.com/m-Amazon-coupons.html";
     if (!file_get_html($storeURL)) {
         die('Error at: ' . $storeURL);
     }
     $html = file_get_html($storeURL);
     // Get store description and update to DB
     $storeDesc = $html->find('div[data-id="text-full"]', 0)->plaintext;
     wp_update_post(array('ID' => $storeID, 'post_content' => $storeDesc));
     // Get coupons
     // If store have new coupons
     $countCurrentCoupons = count($html->find('div[class="hasCode"]'));
     if ($last_number_coupon) {
         if ($last_number_coupon < $countCurrentCoupons) {
             $number_new_coupon = $countCurrentCoupons - $last_number_coupon;
             for ($i = 0; $i < $number_new_coupon; $i++) {
                 $divNewCoupon = $html->find('div[class="hasCode"]', $i);
                 if (savings_addNewCoupon($divNewCoupon, $storeID) > 0) {
                     $c++;
                 }
             }
             update_post_meta($storeID, 'last_number_coupon', $countCurrentCoupons);
             update_post_meta($storeID, 'is_get_coupon', 1);
             echo $c;
             return;
         }
     } else { // First time get coupons
         foreach ($html->find('div[class="hasCode"]') as $div) {
             if (savings_addNewCoupon($div, $storeID) > 0) {
                 $c++;
             }
         }
         // Mark store as getted coupon
         update_post_meta($storeID, 'is_get_coupon', 1);
         update_post_meta($storeID, 'last_number_coupon', $c);
         echo $c;
     }
     // Add expired coupons
     foreach ($html->find('div[class="module-deal expired revealCode"]') as $div) {
         savings_addExpiredCoupon($div, $storeID);
     }
 }
 // Get categories
 if ($_POST['action'] == 'get_categories') {
     $home = 'http://www.savings.com';
     $keywordAfterSlug = $_POST['keyword'];
     $html = file_get_html($home);
     $catList = $html->find('div[class="categories-list"] li');
     $c = 0;
     // Add parent categories
     foreach ($catList as $cat) {
         $catName = trim(str_replace('&amp;', 'and', $cat->plaintext));
         $catName = strip_tags($catName);
         $catUrl = $home . $cat->find('a', 0)->href;
         $term = wp_insert_term($catName, // the term
             'store_category', // the taxonomy
             array( //'description'=> $url.$post_content,
                 'slug' => $catName . $keywordAfterSlug //,'parent'=> $parent_term_id
                 ));
         if (!$term->errors) {
             $c++;
             $tax_meta = new Tax_Meta_Class(array());
             $tax_meta->save_field($term['term_id'], array('id' => 'category_url'), '', $catUrl);
             $tax_meta->save_field($term['term_id'], array('id' => 'checked'), '', 'no');
         }
     }
 }
 if ($_POST['action'] == 'printCatsNotCheck') {
     echo json_encode(print_category_not_check('array'));
 }
 if ($_POST['action'] == 'get_other_cat') {
     $home = 'http://www.savings.com';
     $url = $_POST['categoryURL'];
     //$url = 'http://www.savings.com/c-Apparel-and-Accessories-coupons.html';
     $catID = $_POST['categoryID'];
     $keywordAfterSlug = $_POST['keyword'];

     $html = file_get_html($url);
     // find div Related category
     $html_input_new_category = '';
     $arr = array();
     $arr_newcat = array();
     $catsContainer = $html->find('div[class="category-nav section"] li[class="child"]');

     if (sizeof($catsContainer)) {
         foreach ($catsContainer as $c) {
             $catName = str_replace('&ndash;', '', $c->plaintext);
             $catName = str_replace('&nbsp;', '', $catName);
             $catName = strip_tags(trim($catName));
             $catUrl = $c->find('a', 0)->href;
             if($catUrl)
                $catUrl = $home . $catUrl;
             else
                return;

             // Add new category if not exist
             $term = wp_insert_term($catName, 'store_category', array('slug' => $catName . $keywordAfterSlug, 'parent'=> $catID));
             if (!$term->errors) {
                 $term_id = $term['term_id'];
                 $html_input_new_category = "<input class='cat' id='{$term_id}' value='$catUrl' type='hidden'>";

                 array_push($arr_newcat, $html_input_new_category);

                 $tax_meta = new Tax_Meta_Class(array());
                 $tax_meta->save_field($term_id, array('id' => 'category_url'), '', $catUrl);
                 $tax_meta->save_field($term_id, array('id' => 'checked'), '', 'no');
             }
         }
     }
     // Mark as checked
     $tax_meta = new Tax_Meta_Class(array());
     $tax_meta->save_field($catID, array('id' => 'checked'), '', 'yes');
     if(count($arr_newcat) > 0){
        $arr['new_cat'] = $arr_newcat;
        echo json_encode($arr);
     }
     else{
        echo 'empty';
     }

 }
 // Reset check stores is get coupon

 if ($_POST['action'] == 'reset_check_isgetcoupon') {
     global $wpdb;
     $qr = "select post_id from wp_postmeta where meta_key='is_get_coupon' ";
     $rs = $wpdb->get_results($qr);
     if (count($rs) > 0) {
         foreach ($rs as $r) {
             delete_post_meta($r->post_id, 'is_get_coupon');
         }
     }
 }
 if ($_POST['action'] == 'reset_check_cat') {
     $terms = get_terms('store_category', array(
         'hide_empty' => 0,
         'orderby' => 'id',
         'order' => 'ASC'));
     if (count($terms) > 0) {
         foreach ($terms as $t) {
             $tax_meta = new Tax_Meta_Class(array());
             $tax_meta->save_field($t->term_id, array('id' => 'checked'), '', 'no');
         }
     }
 }
 // Reset last number coupon

 if ($_POST['action'] == 'reset_lastnumbercoupon') {
     global $wpdb;
     $qr = "DELETE FROM wp_postmeta WHERE meta_key = 'last_number_coupon'";
     $rs = $wpdb->query($qr);
 }
 // Process html and add new coupon
 function savings_addNewCoupon($data, $storeId) {
     $cpCode = $data->find('input[class="code"]', 0)->value;
     $cpTitle = trim($data->find('div[class="content"] h3 a', 0)->plaintext);
     if (check_exist_title($cpTitle) > 0) {
         return 0;
     }
     $cpContent = trim(str_replace('more info', '', $data->find('p[class="desc"]', 0)->plaintext));
     $cpContentMore = trim($data->find('div[class="details-full"] p', 0)->plaintext);
     $cpContent .= $cpContentMore;
     $isFieldExpire = $data->find('ul[class="dates"] li strong', 0)->plaintext;
     $cpExpire = '';
     if (strpos($isFieldExpire, 'Expires') >= 0) {
         $cpExpire = trim($data->find('ul[class="dates"] li span', 0)->plaintext);
     }
     // Add new coupon
     $couponArgs = array(
         'post_title' => $cpTitle,
         'post_content' => $cpContent,
         'post_type' => 'coupon',
         'post_status' => 'pending');
     $newCouponId = wp_insert_post($couponArgs);
     // Add coupon meta
     if ($newCouponId > 0) {
         add_post_meta($newCouponId, 'store_id_metadata', $storeId, true);
         if ($cpCode)
             add_post_meta($newCouponId, 'coupon_code_metadata', $cpCode, true);
         if ($cpExpire)
             add_post_meta($newCouponId, 'coupon_expire_date_metadata', $cpExpire, true);
     }
     return $newCouponId;
 }
 // Process html and get all expired coupons
 function savings_addExpiredCoupon($data, $storeId) {
     if (sizeof($data->find('div[class="wrapper-code-reveal"]'))) {
         $cpCode = $data->find('input[class="code"]', 0)->value;
     }
     $cpTitle = trim($data->find('p[class="title"]', 0)->plaintext);
     if (check_exist_title($cpTitle) > 0) {
         return 0;
     }
     $cpContent = trim($data->find('p[class="desc"]', 0)->plaintext);
     // Add new coupon
     $couponArgs = array(
         'post_title' => $cpTitle,
         'post_content' => $cpContent,
         'post_type' => 'coupon',
         'post_status' => 'pending');
     $newCouponId = wp_insert_post($couponArgs);
     // Add coupon meta
     if ($newCouponId > 0) {
         add_post_meta($newCouponId, 'store_id_metadata', $storeId, true);
         if ($cpCode)
             add_post_meta($newCouponId, 'coupon_code_metadata', $cpCode, true);
         add_post_meta($newCouponId, 'coupon_expire_date_metadata', 'Expired', true);
     }
     return $newCouponId;
 }
?>