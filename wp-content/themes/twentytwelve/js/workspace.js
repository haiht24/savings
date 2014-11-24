jQuery(document).ready(function($){
    $('#btnGetRandomStores').click(function(){
        $.ajax({
            type: 'POST',
            url: tempDirUri + "/ajax/ajax_workspace.php",
            data: {
                action : 'getRandomStores',
                currentUserId : currentUserId,
                numberPost : $('#numberPost').val()
            },
            //dataType : 'json',
            success: function(rs) {
                console.log(rs)
            },
            timeout : 999999999,
            complete: function() {
            }
        });
    })
})