<?php
 if (!in_array(cpx_get_user_role_name(), array('administrator', 'editor'))) {
     die('You do not have permission to access this page!');
 }
 wp_head();
 wp_enqueue_script('workspace', get_template_directory_uri() . '/js/workspace.js');
 $objUser = get_user_by('id', get_current_user_id());
?>
<title>Workspace: <?php echo $objUser->display_name; ?></title>
<script>
    var tempDirUri = '<?php echo get_template_directory_uri() ?>';
    var currentUserId = '<?php echo get_current_user_id() ?>';
</script>
<style type="text/css">
    .wrap-main{width: 100%;}
    .div-right{width: 30%; float: right;border-left: 1px solid white; padding: 10px;}
    .div-right a{line-height: 20px; text-decoration: none;}
    .div-center{width: 65%; float: left;border-right: none; padding: 10px;}
</style>
<div class="wrap-main">
<!-- Center -->
    <div class="div-center">
        <label for="numberPost">Number of posts</label>
        <input id="numberPost" value="10" placeholder="Enter number of random stores" />
        <button id="btnGetRandomStores">Get Random Stores</button><span id="mess"></span>
    </div>
<!-- Right -->
    <div class="div-right">
    <h1>Mine<span id="number"></span></h1>
    <div id="mine" style='height: 500px; overflow-y: scroll;'></div>
    </div>
</div>

<?php wp_footer(); ?>