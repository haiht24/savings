<?php
	if (!in_array(cpx_get_user_role_name(), array('administrator')))
	{
		die('You do not have permission to access this page!');
	}
    wp_head();
?>
<style type="text/css">
div {margin: 10px;}
</style>
<script>
    jQuery(document).ready(function($){

        $('#btnGetCat').click(function(){
            $(this).val('Processing...');
            $.ajax({
                type: 'POST',
                url: '<?php echo get_template_directory_uri() ?>' + "/ajax/ajax_bot.php",
                dataType : 'json',
                data: {action : 'get_cat'},
                success: function(rs) {
                    $('#result').append(rs + ' categories added' + '<br/>');
                    //worker_get_categories();
                    location.reload();
                }
            });
        })

        $('#btnParenting').click(function(){
            $(this).val('Processing...');
            worker_parenting_category();
        })
        function worker_parenting_category()
        {
            var cat_prt_id = $('.cat_parenting').attr('id');
            var cat_prt_url = $('.cat_parenting').val();
            var cat_prt_name = $('.cat_parenting').attr('cat_name');
            if(cat_prt_id)
            {
                $.ajax({
                    type: 'POST',
                    url: '<?php echo get_template_directory_uri() ?>' + "/ajax/ajax_bot.php",
                    data: {action : 'parenting' , cat_id : cat_prt_id , cat_url : cat_prt_url, cat_name : cat_prt_name},
                    success: function(rs) {
                        $('#result').append(rs);
                    },
                    complete: function() {
                        $('#' + cat_prt_id + '.cat_parenting').remove();
                        setTimeout(worker_parenting_category, 2000);
                    }
                });
            }
        }

        $('#btnGetStore').click(function(){
            $(this).val('Processing...');
            worker_get_stores();
        })

        $('#btnGetOtherCat').click(function(){
            worker_get_categories();
        })

        $('#btnGetCoupon').click(function(){
            worker_get_coupon();
        })

        // worker get categories
        function worker_get_categories()
        {
            var catID = $('.cat').attr('id');
            var cat_url = $('.cat').val();
            if(catID)
            {
                $.ajax({
                    type: 'POST',
                    url: '<?php echo get_template_directory_uri() ?>' + "/ajax/ajax_bot.php",
                    data: {action : 'get_other_cat', categoryID : catID, categoryURL : cat_url},
                    dataType : 'json',
                    success: function(rs) {
                        $('#result').append(rs['c'] + ' categories added' + '<br/>');
                        if(rs['new_cat'])
                        {
                            // Append new category to div
                            for (i = 0; i < rs['new_cat'].length; i++) {
                                $('.not_check_cat').prepend(rs['new_cat'][i]);
                            }
                        }
                    },
                    complete: function() {
                        $('#' + catID + '.cat').remove();
                        setTimeout(worker_get_categories, 2000);
                    }
                });
            }
            else
            {
                $('#btnGetOtherCat').val('Completed');
            }
        }
        // worker get stores per category
        function worker_get_stores()
        {
            var next_cat_id = $('.cat_not_get_store').attr('id');
            var next_cat_name = $('.cat_not_get_store').val();

            if(next_cat_id)
            {
                $.ajax({
                    type: 'POST',
                    url: '<?php echo get_template_directory_uri() ?>' + "/ajax/ajax_bot.php",
                    data: {action : 'get_store',cat_id : next_cat_id,cat_name : next_cat_name},
                    success: function(rs) {
                        $('#result').append(rs + ' stores added<br/>');
                    },
                    complete: function() {
                        $('#' + next_cat_id + '.cat_not_get_store').remove();
                        setTimeout(worker_get_stores, 2000);
                    }
                });
            }
            else
            {
                 $('#btnGetStore').val('Completed');
            }
        }
        // worker get coupon
        function worker_get_coupon()
        {
            var storeID = $('.store_not_get_coupon').attr('id');
            var storeURL = $('.store_not_get_coupon').val();

            if(storeID)
            {
                $.ajax({
                    type: 'POST',
                    url: '<?php echo get_template_directory_uri() ?>' + "/ajax/ajax_bot.php",
                    data: {action : 'get_coupon',store_id : storeID, store_url : storeURL},
                    success: function(rs) {
                        $('#result').append(rs + ' coupons added<br/>');
                    },
                    complete: function() {
                        $('#' + storeID + '.store_not_get_coupon').remove();
                        setTimeout(worker_get_coupon, 2000);
                    }
                });
            }
            else
            {
                $('#btnGetCoupon').val('Completed');
            }
        }
        /**
         * RESET
         */
        $('#btnRSGetStore').click(function(){
            var text = $(this).val();
            $(this).val(text + '(running...)');
            $.ajax({
                type: 'POST',
                url: '<?php echo get_template_directory_uri() ?>' + "/ajax/ajax_bot.php",
                data: {action : 'reset_check_get_store'},
                success: function(rs) {
                    $('#btnRSGetStore').val(text + '(Completed)');
                }
            });
        })
        // DELETE STORES
        $('#btnDeleteStore').click(function(){
            var text = $(this).val();
            $(this).val(text + '(running...)');
            $.ajax({
                type: 'POST',
                url: '<?php echo get_template_directory_uri() ?>' + "/ajax/ajax_bot.php",
                data: {action : 'del_stores'},
                success: function(rs) {
                    $('#btnDeleteStore').val(text + '(Deleted ' + rs + ' stores)');
                }
            });
        })
        // DELETE COUPON
        $('#btnDeleteCoupon').click(function(){
            var text = $(this).val();
            $(this).val(text + '(running...)');
            $.ajax({
                type: 'POST',
                url: '<?php echo get_template_directory_uri() ?>' + "/ajax/ajax_bot.php",
                data: {action : 'del_coupons'},
                success: function(rs) {
                    $('#btnDeleteCoupon').val(text + '(Deleted ' + rs + ' coupons)');
                }
            });
        })
        // RESET CATEGORY
        $('#btnRSGetCategory').click(function(){
            var text = $(this).val();
            $(this).val(text + '(running...)');
            $.ajax({
                type: 'POST',
                url: '<?php echo get_template_directory_uri() ?>' + "/ajax/ajax_bot.php",
                data: {action : 'reset_check_cat'},
                success: function(rs) {
                    $('#btnRSGetCategory').val(text + '(Completed)');
                }
            });
        })
        // RESET IS GET COUPON
        $('#btnResetIsGetCoupon').click(function(){
            var text = $(this).val();
            $(this).val(text + '(running...)');
            $.ajax({
                type: 'POST',
                url: '<?php echo get_template_directory_uri() ?>' + "/ajax/ajax_bot.php",
                data: {action : 'reset_check_isgetcoupon'},
                success: function(rs) {
                    $('#btnResetIsGetCoupon').val(text + '(Completed)');
                }
            });
        })
        // RESET LAST NUMBER COUPON
        $('#btnResetLastNumberCoupon').click(function(){
            var text = $(this).val();
            $(this).val(text + '(running...)');
            $.ajax({
                type: 'POST',
                url: '<?php echo get_template_directory_uri() ?>' + "/ajax/ajax_bot.php",
                data: {action : 'reset_lastnumbercoupon'},
                success: function(rs) {
                    $('#btnResetLastNumberCoupon').val(text + '(Completed)');
                }
            });
        })
    })
</script>
<div id="control">
    <input type="button" id="btnGetCat" value="Get Categories" />
    <input type="button" id="btnGetOtherCat" value="Get Other Categories" />

    <input type="button" id="btnParenting" value="Parenting Category" />

    <input type="button" id="btnGetStore" value="Get Stores" />
    <input type="button" id="btnGetCoupon" value="Get Coupons" />
    <hr />
    <input type="button" id="btnRSGetStore" value="(Cat)Reset check get stores" />
    <input type="button" id="btnRSGetCategory" value="(Cat)Reset check get categories" />

    <input type="button" id="btnResetIsGetCoupon" value="(Store)Reset is get coupon" />
    <input type="button" id="btnResetLastNumberCoupon" value="(Store)Reset value last number coupon" />

    <input type="button" id="btnDeleteStore" value="Delete All Stores" />
    <input type="button" id="btnDeleteCoupon" value="Delete All Coupons" />
</div>
<div id="result"></div>
<?php wp_footer(); ?>

<div class="not_check_cat" style="background-color: lightgreen;height: 25px;text-align: center;">
    <label style="padding: 10px;">Category not check</label>
    <?php print_category_not_check(); ?>
</div>

<div class="not_parenting" style="background-color: lightgreen;height: 25px;text-align: center;">
    <label style="padding: 10px;">Category not Parenting</label>
    <?php print_cat_not_parenting(); ?>
</div>

<div class="not_get_store" style="background-color: pink;height: 25px;text-align: center;">
    <label style="padding: 10px;">Category not get store</label>
    <?php print_cat_not_getted_stores(); ?>
</div>

<div class="not_get_coupon" style="background-color: red;height: 25px;text-align: center;">
    <label style="padding: 10px;">Stores not get coupon</label>
    <?php print_stores_not_get_coupon(); ?>
</div>