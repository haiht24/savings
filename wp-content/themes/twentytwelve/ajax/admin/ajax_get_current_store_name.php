<?php
    $parse_uri = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );
	require_once( $parse_uri[0] . 'wp-load.php' );

    $stid = $_POST['stid'];
    global $wpdb;
    $rs = $wpdb->get_results("select post_title from {$wpdb->posts} where ID={$stid}");
    if($rs)
    {
        echo $rs[0]->post_title;
    }
    else
    {
        echo 0;
    }
?>