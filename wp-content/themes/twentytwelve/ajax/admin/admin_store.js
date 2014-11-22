jQuery(document).ready(function($)
{
    // Display all coupons of current store
    if(typenow == 'store'){
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
                        for(i = 0; i < rs.length; i++){
                            $('#box_couponOfStore .inside').append(rs[i] + '<br>');
                        }
                    }
               }
           });
        }
    }
})