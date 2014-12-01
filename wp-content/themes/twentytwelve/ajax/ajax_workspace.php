<?php
 $parse_uri = explode('wp-content', $_SERVER['SCRIPT_FILENAME']);
 require_once ($parse_uri[0] . 'wp-load.php');
 require_once (get_template_directory() . '/ajax/bot_libs/simple_html_dom.php');

 // GET RANDOM STORES AND UPDATE STORES AND COUPONS TO CURRENT AUTHOR
 if($_POST['action'] == 'getRandomStores'){
    $numberPost = $_POST['numberPost'];
    $currentUserId = $_POST['currentUserId'];
    global $wpdb;
    $qr = "
    SELECT ID,post_title FROM wp_posts WHERE post_type = 'store' AND post_status = 'pending' AND ( post_author = 0 OR post_author = 1)
    ORDER BY RAND() LIMIT 0,{$numberPost}
    ";
    // Get stores have coupons only
//    $qr = "
//    SELECT ID,post_title FROM wp_posts WHERE post_type = 'store' AND post_status = 'pending' AND ( post_author = 0 OR post_author = 1)
//    AND ID IN (SELECT meta_value FROM wp_postmeta WHERE meta_key = 'store_id_metadata')
//    ORDER BY RAND() LIMIT 0,{$numberPost}
//    ";
    $rs = $wpdb->get_results($qr, ARRAY_A);
    if(count($rs) > 0){
        foreach ($rs as $r) {
            $storeId = $r['ID'];
            $qrUpdatePostAuthor = "UPDATE wp_posts SET post_author = {$currentUserId} WHERE ID = {$storeId}";
            $wpdb->query($qrUpdatePostAuthor);
            updateCouponAuthor($storeId, $currentUserId);
        }
    }
    echo count($rs);
 }
 // GET MY STORES
 if($_POST['action'] == 'getMyStores'){
    $currentUserId = $_POST['currentUserId'];
    global $wpdb;
    $qr = "SELECT * FROM wp_posts WHERE post_type = 'store' AND post_author = {$currentUserId}";
    $rs = $wpdb->get_results($qr, ARRAY_A);
    $arr = array();
    if(count($rs)){
        foreach ($rs as $r) {
            $editLink = get_edit_post_link($r['ID']);
            $title = $r['post_title'];
            $countPendingCoupons = getPendingCoupons($r['ID']);
            $num = '';
            if($countPendingCoupons){
                $num = "($countPendingCoupons)";
            }
            $color = '';
            if($r['post_status'] == 'pending'){
                $color = 'style = "color:red"';
            }else if($r['post_status'] == 'draft'){
                $color = 'style = "color:grey"';
            }
            $data = "<a target = '_blank' {$color} title = '{$r["post_status"]}' href = '{$editLink}'>{$title}<span style = 'color:red'> {$num}</span></a>";
            array_push($arr, $data);
        }
    }
    echo json_encode($arr);
 }