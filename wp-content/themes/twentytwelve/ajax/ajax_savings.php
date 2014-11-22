<?php
 $parse_uri = explode('wp-content', $_SERVER['SCRIPT_FILENAME']);
 require_once ($parse_uri[0] . 'wp-load.php');
 require_once (get_template_directory() . '/ajax/bot_libs/simple_html_dom.php');
 // GET STORES
 if ($_POST['action'] == 'get_store') {
     $homeUrl = "http://savings.com";
     $cat_id = $_POST['cat_id'];
     $currentPageNumber = $_POST['pageNum'];
     $t = get_term_by('id', $cat_id, 'store_category');

     //$cat_id = 702;
     $catUrl = get_tax_meta($cat_id, 'category_url');
     // If empty cat URL, create custom url using cat name
     if(!$catUrl){
        $createCatSlug = str_replace(",", "-", $t->name);
        $createCatSlug = str_replace("'", "", $createCatSlug);
        $createCatSlug = str_replace("&", "and", $createCatSlug);
        $createCatSlug = str_replace(" ", "-", $createCatSlug);
        $catUrl = "http://www.savings.com/c-{$createCatSlug}-coupons.html";
     }
     if(!$catUrl){
        die('Empty category URL with ID = ' . $cat_id);
     }

     $catUrl = str_replace('.html', '', $catUrl);
     $catUrl = $catUrl . '-' . $currentPageNumber . '.html';
     $html = file_get_html($catUrl);
     if (!$html) {
         die('Can not get HTML content. Plz try again');
     }

     // Find parent class : contain name and store url
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
         array_push($arrStores, $singleStore);
     }
     // Add store to database
     $arrNewStores = array();
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
                     array_push($arrNewStores, '(' . $cat_id . ' | ' . $s['name'] . ')');

                     if ($t) {
                         wp_set_object_terms($newStoreId, array($t->name), 'store_category');
                     }
                     add_post_meta($newStoreId, 'store_url_metadata', $s['url'], true);
                 }
             }
         }
     }
     $result = array();
     $hasNextButton = sizeof($html->find('a[class="button next"]'));
     $result['hasNextButton'] = $hasNextButton;
     $result['hasDisableNextButton'] = sizeof($html->find('a[class="button next disabled"]'));
     $result['currentPageNumber'] = $currentPageNumber;
     $result['numAdded'] = $numAdded;
     $result['newStores'] = $arrNewStores;
     if ($result['hasDisableNextButton'] == 1 || $hasNextButton == 0) {
         // Mark as getted stores
         $tax_meta = new Tax_Meta_Class(array());
         $tax_meta->save_field($cat_id, array('id' => 'already_get_store'), '', 'yes');
     }

     echo json_encode($result);
 }
 // LOAD STORES
 if ($_POST['action'] == 'loadStores') {
     $arrStores = savings_printStoresNotGetCoupons();
     echo json_encode($arrStores);
 }
 if($_POST['action'] == 'test'){
    $storeURL = 'http://www.retailmenot.com';
    $html = file_get_html($storeURL);
    echo $html;
 }
 // GET COUPONS
 if ($_POST['action'] == 'getCoupons') {
     $c = 0;
     $storeID = $_POST['storeID'];
     $storeURL = $_POST['storeURL'];
     //$storeURL = 'http://www.retailmenot.com/view/amazon.com';
     //$storeURL = 'http://www.savings.com/m-Kmart-coupons.html';
     //$storeURL = 'http://www.savings.com/m-SuperStarTickets-coupons.html';
     $last_number_coupon = get_post_meta($storeID, 'last_number_coupon', true);

     $curl = curl_init();
     curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
     curl_setopt($curl, CURLOPT_HEADER, false);
     curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
     curl_setopt($curl, CURLOPT_URL, $storeURL);
     curl_setopt($curl, CURLOPT_REFERER, $storeURL);
     curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
     $str = curl_exec($curl);
     curl_close($curl);
     $html = new simple_html_dom();
     $html->load($str);
     if (!$html) {
         die('Can not get Html content. Plz try again');
     }

     // Get store logo
     $storeLogo = '';
     foreach ($html->find('div[class="entity-logo"] img') as $a) {
         $storeLogo = $a->getAttribute('src');
     }
     if ($storeLogo) {
         // upload logo to server
         $logo = uploadLogoToServer($storeLogo, getStoreName($storeID));
         add_post_meta($storeID, 'store_img_metadata', $logo, true);
     }
     // Get store description and update to DB
     $storeDesc = $html->find('div[data-id="text-full"]', 0)->plaintext;
     if ($storeDesc) {
         wp_update_post(array('ID' => $storeID, 'post_content' => $storeDesc));
     }
     // Get store Home page
     $storeHomePage = '';
     foreach ($html->find('div[class="merchant-links module"] li') as $li) {
         $liValue = trim($li->find('a', 0)->plaintext);
         if (strpos('Home Page', $liValue) >= 0) {
             $storeHomePage = $li->find('a', 0)->href;
             break;
         }
     }
     if ($storeDesc) {
         update_post_meta($storeID, 'store_homepage_metadata', $storeHomePage);
     }
     // Re-Update parent categories of store
     $storeBreadcrum = $html->find('div[class="breadcrumb clearfix"] a');
     $arrCategoriesName = array();
     foreach ($storeBreadcrum as $a) {
         $reCategoryName = trim(str_replace('&', 'and', $a->plaintext));
         if ($reCategoryName != 'Categories') {
             array_push($arrCategoriesName, $reCategoryName);
         }
     }
     if (count($arrCategoriesName) > 0) {
         wp_add_object_terms($storeID, $arrCategoriesName, 'store_category');
     }
     /**
      * GET COUPONS OF STORE
      */
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
             //return;
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
     $countExpiredCouponAdded = 0;
     foreach ($html->find('div[class="module-deal expired revealCode"]') as $div) {
         savings_addExpiredCoupon($div, $storeID);
     }
     $html->clear();
     unset($html);
 }
 // GET CATEGORIES
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
     //$url = 'http://www.savings.com/c-Nightlife-coupons.html';
     $catID = $_POST['categoryID'];
     $keywordAfterSlug = $_POST['keyword'];
     // Mark as checked
     $tax_meta = new Tax_Meta_Class(array());
     $tax_meta->save_field($catID, array('id' => 'checked'), '', 'yes');

     $html = file_get_html($url);
     // find div Related category
     $html_input_new_category = '';
     $arr = array();
     $arr_newcat = array();
     $catsContainer = $html->find('div[class="category-nav section"] li[class="child"]');

     if (sizeof($catsContainer)) {
         foreach ($catsContainer as $c) {
             $catName = '';
             $catName = str_replace('&ndash;', '', $c->plaintext);
             $catName = str_replace('&nbsp;', '', $catName);
             $catName = strip_tags(trim($catName));
             $catUrl = $c->find('a', 0)->href;
             if ($catUrl)
                 $catUrl = $home . $catUrl;
             else
                 return;

             // Add new category if not exist
             $term = wp_insert_term($catName, 'store_category', array('slug' => $catName . $keywordAfterSlug,
                     'parent' => $catID));
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

     if (count($arr_newcat) > 0) {
         $arr['new_cat'] = $arr_newcat;
         echo json_encode($arr);
     } else {
         echo count($arr_newcat);
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
 if ($_POST['action'] == 'loadCatNotGetStores') {
     echo json_encode(print_cat_not_getted_stores('array'));
 }
 // Reset last number coupon
 if ($_POST['action'] == 'reset_lastnumbercoupon') {
     global $wpdb;
     $qr = "DELETE FROM wp_postmeta WHERE meta_key = 'last_number_coupon'";
     $rs = $wpdb->query($qr);
 }
 // Delete stores
 if ($_POST['action'] == 'deleteStores') {
     global $wpdb;
     $qrDelMeta = "
    DELETE FROM wp_postmeta WHERE post_id IN
	(SELECT ID FROM wp_posts WHERE post_type='store');
    ";
     $qrDelStores = "DELETE FROM wp_posts WHERE post_type='store';";
     $wpdb->query($qrDelMeta);
     $wpdb->query($qrDelStores);
 }
 // Delete coupons
 if ($_POST['action'] == 'deleteCoupons') {
     global $wpdb;
     $qrDelMeta = "
    DELETE FROM wp_postmeta WHERE post_id IN
	(SELECT ID FROM wp_posts WHERE post_type='coupon');
    ";
     $qrDelCoupons = "DELETE FROM wp_posts WHERE post_type='coupon';";
     $wpdb->query($qrDelMeta);
     $wpdb->query($qrDelCoupons);
 }
 // Process html and add new coupon
 function savings_addNewCoupon($data, $storeId) {
     $cpCode = $data->find('input[class="code"]', 0)->value;
     $cpTitle = trim($data->find('div[class="content"] h3 a', 0)->plaintext);
     $cpTitle = str_replace("'", "", $cpTitle);
     if (check_exist_coupon_title_origin($cpTitle) > 0) {
         return 0;
     }
     $cpContent = trim(str_replace('more info', '', $data->find('p[class="desc"]', 0)->plaintext));
     $cpContentMore = trim($data->find('div[class="details-full"] p', 0)->plaintext);
     $cpContent .= $cpContentMore;
     $cpExpire = '';
     foreach ($data->find('ul[class="dates"] li') as $s) {
         if (strpos($s->plaintext, 'Expires:')) {
             $cpExpire = $s->plaintext;
             $cpExpire = trim(str_replace('Expires:', '', $cpExpire));
             break;
         }
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
         // Add origin coupon title
         add_post_meta($newCouponId, 'origin_title_metadata', $cpTitle, true);
     }
     return $newCouponId;
 }
 // Process html and get all expired coupons
 function savings_addExpiredCoupon($data, $storeId) {
     if (sizeof($data->find('div[class="wrapper-code-reveal"]'))) {
         $cpCode = $data->find('input[class="code"]', 0)->value;
     }
     $cpTitle = trim($data->find('p[class="title"]', 0)->plaintext);
     $cpTitle = str_replace("'", "", $cpTitle);
     if (check_exist_coupon_title_origin($cpTitle) > 0) {
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
         // Add origin coupon title
         add_post_meta($newCouponId, 'origin_title_metadata', $cpTitle, true);
     }
     return $newCouponId;
 }
?>