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
    body {margin: 10px;}
    .reset{color:blue;}
</style>
<div>
    <label for="numberPost">Number of posts</label>
    <input id="numberPost" value="10" placeholder="Enter number of random stores" />
    <button id="btnGetRandomStores">Get Random Stores</button>
</div>

<?php wp_footer(); ?>