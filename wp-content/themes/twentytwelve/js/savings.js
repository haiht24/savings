jQuery(document).ready(function($){

        // Get categories
        $('#btnGetCategories').click(function(){
             $.ajax({
                type: 'POST',
                url: tempDirUri + "/ajax/ajax_savings.php",
                data: {action : 'get_categories', keyword : $('#txtKeywordAfterSlug').val()},
                //dataType : 'json',
                success: function(rs) {
                    getOtherCategories();
                },
                timeout : 999999999,
                complete: function() {
                    $('#btnGetCategories').text('Done');
                }
            });
        })
        // Rut gon de test cho nhanh
        $('#fastGetCat').click(function(){
            getOtherCategories();
        })
        // Get other categories
        function getOtherCategories(){
            $.ajax({
                type: 'POST',
                url: tempDirUri + "/ajax/ajax_savings.php",
                data: {action : 'printCatsNotCheck'},
                dataType : 'json',
                success: function(rs) {
                    if(rs.length > 0){
                        for(i = 0; i < rs.length; i++){
                            $('.cat_not_check_cat').append(rs[i]);
                        }
                        workerGetCat();
                    }
                },
                timeout : 999999999,
                complete: function() {
                }
            });
        }
        function workerGetCat(){
            var catID = $('.cat').attr('id');
            var cat_url = $('.cat').val();
            if(catID)
            {
                $.ajax({
                    type: 'POST',
                    url: tempDirUri + "/ajax/ajax_savings.php",
                    data: {action : 'get_other_cat', categoryID : catID, categoryURL : cat_url, keyword : $('#txtKeywordAfterSlug').val()},
                    dataType : 'json',
                    success: function(rs) {
                        if(rs && rs['new_cat'].length > 0 && parseInt(rs) > 0)
                        {
                            // Append new category to div
                            for (i = 0; i < rs['new_cat'].length; i++) {
                                $('.cat_not_check_cat').prepend(rs['new_cat'][i]);
                            }
                        }
                    },
                    timeout : 999999999,
                    complete: function() {
                        $('#' + catID + '.cat').remove();
                        setTimeout(workerGetCat, 0);
                    },
                    error: function(){
                        console.log(0);
                    }
                });
            }
            else
            {
                $('#btnGetOtherCat').val('Completed');
            }
        }
        // Get stores from category
        $('#btnGetStore').click(function(){
            $(this).val('Processing...');
            workerGetStoresFromCategory();
        })
        // Load categories not get store
        $('#btnLoadCatNotGetStores').click(function(){
            loadCategoryNotGetStores();
        })
        function workerGetStoresFromCategory(){

            if($('.catNotGetStore').length > 0){
                cat_id = $('.catNotGetStore').attr('id');
                cat_name = $('.catNotGetStore').val();

                $.ajax({
                    type: 'POST',
                    url: tempDirUri + "/ajax/ajax_savings.php",
                    data: {action : 'get_store', pageNum : $('#currentPage').val(), cat_id : cat_id, cat_name : cat_name},
                    dataType : 'json',
                    success: function(rs) {
                        if(parseInt(rs['isNext']) == 0){
                            console.log(rs['numAdded']);
                            nextPage = parseInt(rs['currentPageNumber']) + 1;
                            $('#currentPage').val(nextPage);
                            setTimeout(workerGetStoresFromCategory, 0);
                        }else // if is last page
                        {
                            $('#' + cat_id + '.catNotGetStore').remove();
                            $('#currentPage').val(1);
                            $('#btnLoadCatNotGetStores').val('Load Categories Not Get Store (remain: ' + $('.catNotGetStore').length + ')');
                            setTimeout(workerGetStoresFromCategory, 0);
                        }
                    },
                    timeout : 999999999,
                    complete: function() {
                        $('#btnGetStore').text('Done');
                    }
                });
            }

        }
        // Print category not get stores
        function loadCategoryNotGetStores(){
            $.ajax({
                type: 'POST',
                url: tempDirUri + "/ajax/ajax_savings.php",
                data: {action : 'loadCatNotGetStores'},
                dataType : 'json',
                success: function(rs) {
                    if(rs.length > 0){
                        for (i = 0; i < rs.length; i++) {
                            $('.cat_not_get_store').append(rs[i]);
                        }
                    }
                    $('#btnLoadCatNotGetStores').val($('#btnLoadCatNotGetStores').val() + ' (' + rs.length + ')');
                },
                timeout : 999999999,
                complete: function() {
                }
            });
        }
        // Load stores not get coupons
        $('#btnLoadStores').click(function(){
            $(this).val($(this).val() + ' (Loading...)');
            $('#storeNotGetCoupon').empty();
            $.ajax({
                type: 'POST',
                url: tempDirUri + "/ajax/ajax_savings.php",
                data: {action : 'loadStores'},
                dataType : 'json',
                success: function(rs) {
                    if(rs.length > 0){
                        for(i = 0; i < rs.length; i++){
                            var ip = "<input class='store_not_get_coupon' id='"
                            + rs[i]['id'] + "' value='"
                            + rs[i]['url'] +"'>";
                            $('#storeNotGetCoupon').append(ip);
                        }
                    }
                    $('#btnLoadStores').val('Load Stores Not Get Coupons (' + rs.length + ')');
                },
                timeout : 999999999,
                complete: function() {}
            });
        })
        // Get coupons
        $('#btnGetCoupons').click(function(){
            workerGetCoupons();
        })
        function workerGetCoupons()
        {
            var storeID = $('.store_not_get_coupon').attr('id');
            var storeURL = $('.store_not_get_coupon').val();
            if(storeID)
            {
                $.ajax({
                    type: 'POST',
                    url: tempDirUri + "/ajax/ajax_savings.php",
                    data: {action : 'getCoupons',storeID : storeID, storeURL : storeURL},
                    success: function(rs) {
                        console.log(rs + ' new active coupons added');
                    },
                    timeout : 999999999,
                    complete: function() {
                        $('#' + storeID + '.store_not_get_coupon').remove();
                        // Count remain
                        $('#messStoreNotGetCoupon').text('Stores remaining: ' + $('.store_not_get_coupon').length);
                        setTimeout(workerGetCoupons, 0);
                    }
                });
            }
            else
            {
                $('#btnGetCoupon').val('Completed');
            }
        }
        // RESET IS GET COUPON
        $('#btnResetIsGetCoupon').click(function(){
            var text = $(this).val();
            $(this).val(text + '(running...)');
            $.ajax({
                type: 'POST',
                url: tempDirUri + "/ajax/ajax_savings.php",
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
                url: tempDirUri + "/ajax/ajax_savings.php",
                data: {action : 'reset_lastnumbercoupon'},
                success: function(rs) {
                    $('#btnResetLastNumberCoupon').val(text + '(Completed)');
                }
            });
        })
        // Reset Check Get child categories
        $('#btnRSGetCategory').click(function(){
            var text = $(this).val();
            $(this).val(text + '(running...)');
            $.ajax({
                type: 'POST',
                url: tempDirUri + "/ajax/ajax_savings.php",
                data: {action : 'reset_check_cat'},
                success: function(rs) {
                    $('#btnRSGetCategory').val(text + '(Completed)');
                }
            });
        })
        // Test
        $('#btnTest').click(function(){
            loadCategoryNotGetStores();
        })
    })