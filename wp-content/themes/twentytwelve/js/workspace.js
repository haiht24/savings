jQuery(document).ready(function($){
    // Load my store
    $.ajax({
            type: 'POST',
            url: tempDirUri + "/ajax/ajax_workspace.php",
            data: {
                action : 'getMyStores',
                currentUserId : currentUserId
            },
            dataType : 'json',
            success: function(rs) {
                $('#mine').empty();
                $('#number').html(' (' + rs.length + ')');
                if(rs.length > 0){
                    for(i = 0; i < rs.length; i++){
                        $('#mine').append(rs[i] + '<br/>');
                    }
                }
                console.log(rs);
            },
            timeout : 999999999,
            complete: function() {
            }
        });
    // Get random store
    $('#btnGetRandomStores').click(function(event){
        event.preventDefault();
        $(this).attr('disabled', 'disabled');
        $('#mess').text('Processing...');
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
                console.log(rs);
                alert(rs + ' stores added');
                $('#btnGetRandomStores').attr('disabled', false);
                location.reload();

            },
            timeout : 999999999,
            complete: function() {

            }
        });
    })
})