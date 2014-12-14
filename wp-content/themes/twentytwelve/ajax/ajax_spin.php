<?php
 $parse_uri = explode('wp-content', $_SERVER['SCRIPT_FILENAME']);
 require_once ($parse_uri[0] . 'wp-load.php');
 require_once (get_template_directory() . '/ajax/bot_libs/simple_html_dom.php');
 if ($_POST['action'] == 'api_quota') {
     $data['email_address'] = $_POST['email_address'];
     $data['api_key'] = $_POST['api_key'];
     $data['action'] = $_POST['action'];
     $api_response = spinrewriter_api_post($data);
     var_dump($api_response);
 }

 function spinrewriter_api_post($data) {
     $data_raw = "";
     foreach ($data as $key => $value) {
         $data_raw = $data_raw . $key . "=" . urlencode($value) . "&";
     }

     $ch = curl_init();
     curl_setopt($ch, CURLOPT_URL, "http://www.spinrewriter.com/action/api");
     curl_setopt($ch, CURLOPT_POST, true);
     curl_setopt($ch, CURLOPT_POSTFIELDS, $data_raw);
     curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
     $response = trim(curl_exec($ch));
     curl_close($ch);
     return $response;
 }
