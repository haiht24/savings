<?php
	if (!in_array(cpx_get_user_role_name(), array('administrator')))
	{
		die('You do not have permission to access this page!');
	}
    wp_head();
    wp_enqueue_script('savings', get_template_directory_uri() . '/js/savings.js');
?>
<title>Crawler</title>
<style type="text/css">
body {margin: 10px;}
.reset{color:blue;}
</style>
<script>
    var tempDirUri = '<?php echo get_template_directory_uri() ?>';
</script>
<div id="control">
<!--
    <input type="button" value="test" id="btnTest" />
-->
    <input type="button" id="btnGetCategories" value="Get Categories" />
    <input type="text" id="txtKeywordAfterSlug" value="-coupon-codes" placeholder="eg: -coupon-codes" />
    <input type="button" id="fastGetCat" value="Fast check get cat" />
<hr />
    <input type="button" id="btnLoadCatNotGetStores" value="Load Categories Not Get Store" />
    <input type="button" id="btnGetStore" value="Get Store" />
    <label>Current Category Page: </label><input type="text" id="currentPage" value="1" style="width: 50px;"/>
<hr />
    <input type="button" id="btnLoadStores" value="Load Stores Not Get Coupons" />
    <input type="button" id="btnGetCoupons" value="Get Coupons" />
<hr />
    <input type="button" id="btnRSGetCategory" value="(Cat)Reset check get child categories" />
    <input type="button" id="btnResetIsGetCoupon" value="(Store)Reset is get coupon" />
    <input type="button" id="btnResetTurnGetCoupon" value="(Store)Reset turn get coupons" />
<?php
 if(strpos(home_url(), 'localhost') >= 0): ?>
<hr />
    <input type="button" id="btnDelStore" value="Delete All Stores" />
    <input type="button" id="btnDelCoupon" value="Delete All Coupons" />
<?php endif; ?>
</div>
<label id="messStoreNotGetCoupon"></label>
<div id="storeNotGetCoupon" style='height: 200px; overflow-y: scroll;'></div>
<div id="result" style='height: 200px; overflow-y: scroll;'></div>
<div class="cat_not_check_cat" style="background-color: lightgreen;height: 25px;text-align: center;display: none;">
    <label style="padding: 10px;">Category not check</label>
</div>
<div class="cat_not_get_store" style="background-color: lightgreen;height: 25px;text-align: center;display: none;">
    <label style="padding: 10px;">Category not get stores</label>
</div>
<?php wp_footer(); ?>