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
    SELECT ID FROM wp_posts WHERE post_type = 'store' AND post_status = 'pending' AND ( post_author = 0 OR post_author = 1)
    ORDER BY RAND() LIMIT 0,{$numberPost}
    ";
    // Get stores have coupons only
//    $qr = "
//    SELECT ID FROM wp_posts WHERE post_type = 'store' AND post_status = 'pending' AND ( post_author = 0 OR post_author = 1)
//    AND ID IN (SELECT meta_value FROM wp_postmeta WHERE meta_key = 'store_id_metadata')
//    ORDER BY RAND() LIMIT 0,{$numberPost}
//    ";
    $rs = $wpdb->get_results($qr, ARRAY_A);
    if(count($rs) > 0){
        $strIDs = '';
        foreach ($rs as $k=>$r) {
            if($k < count($rs) - 1){
                $strIDs .= $r['ID'] . ',';
            }else{
                $strIDs .= $r['ID'];
            }
        }
        $qrCoupon = "SELECT post_id FROM wp_postmeta WHERE meta_key = 'store_id_metadata' AND meta_value IN ({$strIDs})";
        $rsCoupons = $wpdb->get_results($qrCoupon);
        if(count($rsCoupons) > 0){
            $strIDs .= ',';
            foreach ($rsCoupons as $k=>$c) {
                if($k < count($rsCoupons) - 1){
                    $strIDs .= $c->post_id . ',';
                }else{
                    $strIDs .= $c->post_id;
                }
            }
        }
        $qrUpdateAuthor = "UPDATE wp_posts SET post_author = {$currentUserId} WHERE ID IN({$strIDs})";
        $wpdb->query($qrUpdateAuthor);
    }
    echo count($rs);
 }
 // GET MY STORES
 else if($_POST['action'] == 'getMyStores'){
    $currentUserId = $_POST['currentUserId'];
    global $wpdb;
    $qr = "SELECT * FROM wp_posts WHERE post_type = 'store' AND post_author = {$currentUserId} AND post_status IN ('publish', 'pending', 'draft')";
    $rs = $wpdb->get_results($qr, ARRAY_A);
    $arr = array();
    if(count($rs)){
        foreach ($rs as $r) {
            $editLink = get_edit_post_link($r['ID']);
            $title = $r['post_title'];
            $countCouponsByStatus = countCouponsByStatus($r['ID']);
            $num = '';
            if($countCouponsByStatus){
                $num = "($countCouponsByStatus)";
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
 // FIX
 else if($_POST['action'] == 'fix'){
    $currentUserId = $_POST['currentUserId'];
    global $wpdb;
    $qrGetID = "
    SELECT post_id FROM wp_postmeta WHERE meta_key = 'store_id_metadata' AND meta_value IN
    (SELECT ID FROM wp_posts WHERE post_author = {$currentUserId} AND post_type = 'store')";
    $rs = $wpdb->get_results($qrGetID);
    if(count($rs) > 0){
        $str = '';
        foreach ($rs as $k=>$r) {
            if($k < count($rs) - 1){
                $str .= $r->post_id . ',';
            }else{
                $str .= $r->post_id;
            }
        }
        $qrUpdate = "UPDATE wp_posts SET post_author = {$currentUserId} WHERE ID IN ({$str})";
        $wpdb->query($qrUpdate);
    }
 }