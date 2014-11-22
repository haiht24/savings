<?php
 $parse_uri = explode('wp-content', $_SERVER['SCRIPT_FILENAME']);
 require_once ($parse_uri[0] . 'wp-load.php');

// Get all coupons of current store
 if($_POST['storeId']){
    $storeId = $_POST['storeId'];
    global $wpdb;
    $rs = $wpdb->get_results("SELECT post_id as ID FROM wp_postmeta WHERE meta_key = 'store_id_metadata' AND meta_value = {$storeId}");
    $arrCoupons = array();
    if(count($rs) > 0){
        foreach ($rs as $c) {
            $couponTitle = get_post_field('post_title', $c->ID);
            $couponStatus = get_post_field('post_status', $c->ID);
            $color = '';
            if($couponStatus == 'draft'){
                $color = "style='color:grey'";
            }else if($couponStatus == 'pending'){
                $color = "style='color:red'";
            }
            $couponTitle = str_replace("'", "", $couponTitle);
            $editLink = get_edit_post_link($c->ID);
            $link = "<a target = '_blank' href='{$editLink}' title = '{$couponStatus}' {$color} >{$couponTitle}</a>";
            array_push($arrCoupons, $link);
        }
    }
    echo json_encode($arrCoupons);
 }
?>