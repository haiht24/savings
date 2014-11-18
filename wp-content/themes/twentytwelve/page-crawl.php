<?php wp_head(); ?>
<style type="text/css">
div {margin: 10px;}
input {width:500px;margin: 10px;}
</style>
<script>
    jQuery(document).ready(function($){

        $('#submit').click(function(){
            $.ajax({
                type: 'POST',
                url: '<?php echo get_template_directory_uri() ?>' + "/ajax/ajax_crawl.php",
                data: {url : $('#url').val(), find : $('#find').val()},
                //dataType : 'json',
                success: function(rs) {
                    $('#rs').append(rs);
                    //alert(rs);
                },
                complete: function() {

                }
            });
        })

    })
</script>
<div>
    <label for="url">Website</label>
    <input id="url" value="http://www.myvouchercodes.co.uk/categories" />
    <br />
    <label for="find">Find</label><input id="find" value="f:div[class=AllCategories]:0 => e:arcticle[class=AllCategories-cat]" />
    <button id="submit">Submit</button>
</div>
<div id="rs"></div>