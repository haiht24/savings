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

     $catUrl = get_tax_meta($cat_id, 'category_url');
     // If empty cat URL, create custom url using cat name
     if (!$catUrl) {
         $createCatSlug = str_replace(",", "-", $t->name);
         $createCatSlug = str_replace("'", "", $createCatSlug);
         $createCatSlug = str_replace("&", "and", $createCatSlug);
         $createCatSlug = str_replace(" ", "-", $createCatSlug);
         $catUrl = "http://www.savings.com/c-{$createCatSlug}-coupons.html";
     }
     if (!$catUrl) {
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
 if ($_POST['action'] == 'test') {
     //printCatNotHaveUrl();
     $opts = array(
        'http'=>array(
            'method'=>"GET",
            'header'=>"User-Agent: User-Agent:Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/536.5 (KHTML, like Gecko) Chrome/19.0.1084.52 Safari/536.5\r\n"
        )
    );
    $context = stream_context_create($opts);
    $html = file_get_html('http://www.retailmenot.com', false, $context);
    //foreach($html->find('div[id = "recaptcha_widget_div"]') as $c){
//        echo $c;
//    }
    if(sizeof($html->find('div[id = "recaptcha_widget_div"]')) == 0){
        echo $html;
    }
 }
 // GET COUPONS
 if ($_POST['action'] == 'getCoupons') {
     $c = 0;
     $storeID = $_POST['storeID'];
     $storeName = get_post_field('post_title', $storeID);
     $storeURL = $_POST['storeURL'];
     $turnGetCoupon = get_post_meta($storeID, 'get_coupon_turn', true);

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
     // Not re-update store info
     if (!$turnGetCoupon) {
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
         update_post_meta($storeID, 'get_coupon_turn', 1);
     }
     /**
      * GET COUPONS OF STORE
      */
     // Get coupon ids from target site
     $arrTargetSiteCpIds = array();
     foreach ($html->find('.hasCode') as $cp) {
         array_push($arrTargetSiteCpIds, $cp->id);
     }
     if (count($arrTargetSiteCpIds) > 0) {
         foreach ($arrTargetSiteCpIds as $tId) {
             if (checkExistTargetCpId($tId) == 0) {
                 foreach ($html->find('#' . $tId) as $divCoupon) {
                     if (savings_addNewCoupon($divCoupon, $storeID, $storeName, $tId) > 0) {
                         $c++;
                     }
                 }
             }
         }
     }
     // Add expired coupons
     $arrTargetExpireCp = array();
     foreach ($html->find('div[class="module-deal expired revealCode"]') as $exCp) {
         array_push($arrTargetExpireCp, $exCp->id);
     }
     if (count($arrTargetExpireCp) > 0) {
         foreach ($arrTargetExpireCp as $tId) {
             if (checkExistTargetCpId($tId) == 0) {
                 foreach ($html->find('#' . $tId) as $divCoupon) {
                     $isAddExpireCoupon = '';
                     $isAddExpireCoupon = savings_addExpiredCoupon($divCoupon, $storeID);
                     if ($isAddExpireCoupon > 0) {
                         $c++;
                     }
                 }
             }
         }
     }
     // Mark store as getted coupon
     update_post_meta($storeID, 'is_get_coupon', 1);
     // Update Turn get coupon
     if (!$turnGetCoupon)
         update_post_meta($storeID, 'get_coupon_turn', 1);
     else
         update_post_meta($storeID, 'get_coupon_turn', $turnGetCoupon + 1);
     $html->clear();
     unset($html);
     echo $c;
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
 if ($_POST['action'] == 'resetTurnGetCoupon') {
     global $wpdb;
     $qr = "DELETE FROM wp_postmeta WHERE meta_key = 'get_coupon_turn'";
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
?>