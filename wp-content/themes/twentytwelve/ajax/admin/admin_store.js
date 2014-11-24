jQuery(document).ready(function($)
{

    if(typenow == 'store'){
        // Display all coupons of current store
        // check if in store edit page
        if($('#store_url_metadata').length > 0){
            $.ajax({
               type: 'POST',
               url: cpx_theme_uri + '/ajax/admin/ajax_store.php',
               dataType : 'json',
               data: {
                   storeId: $('#post_ID').val()
               },
               success: function (rs) {
                    //console.log(rs);
                    if(rs.length > 0){
                        $('#box_couponOfStore .inside').append(rs.length + ' coupons<br/>');
                        for(i = 0; i < rs.length; i++){
                            $('#box_couponOfStore .inside').append(rs[i] + '<br>');
                        }
                    }
               }
           });
           // Display link go to store at Savings.com
           storeUrl = $('#store_url_metadata').val();
           if(storeUrl){
               storeLink = '<a target="_blank" href="' + storeUrl + '" title = "View store at Savings.com" >Go to store at Savings.com</a>';
               $('#store_url_metadata').parent().append(storeLink);
           }
           // Display link go to store home page
           storeHomePage = $('#store_homepage_metadata').val();
           if(storeHomePage){
                storeHomePageLink = '<a target="_blank" href="' + storeHomePage + '" title = "View store home page" >Go to store home page</a>';
                $('#store_homepage_metadata').parent().append(storeHomePageLink);
           }
        }
    }

})