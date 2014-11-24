<?php
 $parse_uri = explode('wp-content', $_SERVER['SCRIPT_FILENAME']);
 require_once ($parse_uri[0] . 'wp-load.php');
 require_once (get_template_directory() . '/ajax/bot_libs/simple_html_dom.php');
 //echo '<pre>';var_dump($_POST);echo '</pre>';
 if($_POST['action'] == 'getRandomStores'){
    $numberPost = $_POST['numberPost'];
    $currentUserId = $_POST['currentUserId'];
    global $wpdb;
//    $qr = "
//    SELECT ID,post_title FROM wp_posts WHERE post_type = 'store' AND post_status = 'pending' AND post_author = 0
//    ORDER BY RAND() LIMIT 0,{$numberPost}
//    ";
    $qr = "
    SELECT ID,post_title FROM wp_posts WHERE post_type = 'store' AND post_status = 'pending' AND post_author = 0
    AND ID IN (SELECT meta_value FROM wp_postmeta WHERE meta_key = 'store_id_metadata')
    ORDER BY RAND() LIMIT 0,{$numberPost}
    ";
    $rs = $wpdb->get_results($qr, ARRAY_A);
    if(count($rs) > 0){
        foreach ($rs as $r) {
            $storeId = $r['ID'];
            $qrUpdatePostAuthor = "UPDATE wp_posts SET post_author = {$currentUserId} WHERE ID = {$storeId}";
            $wpdb->query($qrUpdatePostAuthor);
            updateCouponAuthor($storeId, $currentUserId);
        }
    }
    var_dump($rs);
    //echo json_encode($rs);
 }