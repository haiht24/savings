jQuery(document).ready(function($)
{
    var st_link = "<a target='_blank' id='current_store'></a>";
    $('#store_id_metadata').parent().append(st_link);
    if($('#store_id_metadata').val() != '')
    {
        $.ajax({
               type: 'POST',
               url: cpx_theme_uri + '/ajax/admin/ajax_get_current_store_name.php',
               data: {
                   stid: $('#store_id_metadata').val()
               },
               success: function (rs) {
                    if(rs != 0)
                    {
                        $('#current_store').text(rs);
                        $('#current_store').attr('href',cpx_site_url + '/wp-admin/post.php?post=' + $('#store_id_metadata').val() + '&action=edit');
                    }
               }
       });
    }
})