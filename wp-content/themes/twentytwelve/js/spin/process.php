<?php
 // Load Wordpress libs
 $parse_uri = explode('wp-content', $_SERVER['SCRIPT_FILENAME']);
 require_once ($parse_uri[0] . 'wp-load.php');
 require_once (get_template_directory() . '/ajax/bot_libs/simple_html_dom.php');
 // Get POST data
 $postdata = file_get_contents("php://input");
 $request = json_decode($postdata);
 // Load SpinRewriter API

 // Config Email and ApiKey
 $email = $request->configEmail;
 $api_key = $request->apiKey;
 $oldDomain = $request->oldDomain;
 $newDomain = $request->newDomain;
 $protectKeyword = $request->protectKeyword;
 // Spin content
 if ($request->action == '') {
     $arrStores = getRandomStores(1);
     $arr = array();
     foreach ($arrStores as $s) {
         $cpOfStore = getCouponsByStore($s->ID, array('pending'), -1, 'object');
         $content = $s->post_content;
         $arr['old'] = $content;
         if ($content) {
             $content = str_replace($oldDomain, $newDomain, $content);
             $api_response = spinContent($email, $api_key, false, $content, $s->post_title . ',' . $protectKeyword);
             // Update
             $myPost = array(
                 'ID' => $s->ID,
                 'post_content' => $api_response['response'],
                 'post_status' => 'publish');
             wp_update_post($myPost);
             update_post_meta($s->ID, 'oldStoreContent_metadata', $content);

             if (count($cpOfStore) > 0) {
                 foreach ($cpOfStore as $cp) {
                     $cpContentToSpin = strtolower($cp->post_title) . '||' . $cp->post_content;
                     $spinResponse = spinContent($email, $api_key, false, $cpContentToSpin, $protectKeyword);

                     $spinedCouponContent = '';
                     $spinedCouponTitle = '';

                     $arrSpinResponse = explode('||', $spinResponse['response']);
                     $spinedCouponTitle = $arrSpinResponse[0];
                     $spinedCouponContent = $arrSpinResponse[1];

                     $myCP = array(
                         'ID' => $cp->ID,
                         'post_title' => $spinedCouponTitle,
                         'post_content' => $spinedCouponContent,
                         'post_status' => 'publish');
                     wp_update_post($myCP);
                     update_post_meta($cp->ID, 'oldCouponContent_metadata', $cpContentToSpin);
                 }
             }
             $arr['spinned'] = $api_response['response'];
         }
     }
     echo json_encode($arr);
 }
 // Spin now!
 if ($request->action == 'spin') {
     $arrMess = array();
     $myID = $request->myID;
     if (!$myID) {
         return;
     }
     $postType = get_post_field('post_type', $myID);
     $title = get_post_field('post_title', $myID);
     $content = get_post_field('post_content', $myID);
     $content = str_replace($oldDomain, $newDomain, $content);

     // Update store content
     if ($postType == 'store') {
         $api_response = spinContent($email, $api_key, false, $content, $title . ',' . $protectKeyword);
         $spinAvaiable = $api_response['api_requests_available'];
         if ($spinAvaiable == 0) {
             $arrMess['isStop'] = 1;
         }

         $myPost = array(
             'ID' => $myID,
             'post_content' => $api_response['response'],
             'post_status' => 'publish');
         wp_update_post($myPost);
         update_post_meta($myID, 'oldStoreContent_metadata', $content);
     }
     if ($postType == 'coupon') {
         $cpContentToSpin = strtolower($title) . '||' . $content;
         $spinResponse = spinContent($email, $api_key, false, $cpContentToSpin, $protectKeyword);
         $spinAvaiable = $spinResponse['api_requests_available'];
         if ($spinAvaiable == 0) {
             $arrMess['isStop'] = 1;
         }

         $spinedCouponContent = '';
         $spinedCouponTitle = '';
         $arrSpinResponse = explode('||', $spinResponse['response']);
         $spinedCouponTitle = $arrSpinResponse[0];
         $spinedCouponContent = $arrSpinResponse[1];

         $myCP = array(
             'ID' => $myID,
             'post_title' => $spinedCouponTitle,
             'post_content' => $spinedCouponContent,
             'post_status' => 'publish');
         wp_update_post($myCP);
         update_post_meta($myID, 'oldCouponContent_metadata', $cpContentToSpin);
     }
     $arrMess['ID'] = $myID;
     echo json_encode($arrMess);
 }
 // Before spin
 if ($request->action == 'beforeSpin') {
     $getType = $request->getType;
     $arr = array();
     if ($getType == 'stores') {
         $arrStores = getRandomStores(300, 'store', array('pending'), 0, 'ID');
         $arr = $arrStores;

         // Neu lay du store co desc
         if (count($arr) < 300 || !$arr) {
             // Neu ko du lay them store ko co desc
             if ($arr) {
                 $arr = array_merge($arrStores, getRandomStores(300 - count($arrStores), 'store', array('pending'), 1,
                     'ID'));
             } else {
                 $arr = getRandomStores(300 - count($arrStores), 'store', array('pending'), 1, 'ID');
             }
         }
         // Neu store ko du 300, lay them coupon
         if (!$arr || count($arr) < 300) {
             if (count($arr) < 300 && $arr) {
                 $arr = array_merge($arr, getRandomPosts(300 - count($arr), array('coupon'), array('pending'), 'ID'));
             } else {
                 $arr = getRandomPosts(300 - count($arr), array('coupon'), array('pending'), 'ID');
             }
         }
     }
     else if($getType == 'coupons'){
        // GET COUPONS IN PUBLISHED STORES
        $publishedStores = getRandomStores(-1, 'store', array('publish'), -1, 'ID');
        $meta_key_in = array('store_id_metadata', 'IN', $publishedStores);
        $arrCoupons = getRandomPosts(300, array('coupon'), array('pending'), 'ID', $meta_key_in);
        $arr = $arrCoupons;
     }
     echo json_encode($arr);
 }
 // Mark store empty description
 if ($request->action == 'markStoreEmptyDesc') {
     doItNow();
 }
 // Count remain
 if ($request->action == 'remain') {
     $api_response = spinContent($email, $api_key, true);
     echo json_encode($api_response);
 }
 // Save config
 if ($request->action == 'saveConfig') {
     $email = $request->configEmail;
     $apiKey = $request->apiKey;
     update_option('spinEmail', $email);
     update_option('spinApiKey', $apiKey);
 }
 // Load config
 if ($request->action == 'loadConfig') {
     echo json_encode(array('spinEmail' => get_option('spinEmail'), 'spinApiKey' => get_option('spinApiKey')));
 }
