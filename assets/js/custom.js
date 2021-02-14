jQuery(document).ready( function() {
    jQuery("#get-stats").submit( function(e) {
        e.preventDefault();
        var name = jQuery("#type").val();
        jQuery.ajax({
            type : "post",
            url : ajaxurl,
            data : {action: "get_stats", type: name},
            beforeSend: function()
            {
                jQuery('#loader').show();
            },
            complete: function()
            {
                jQuery('#loader').hide();
            },
            success: function(response)
            {
                jQuery('.graph').html( response );
            }
        })

    })

})
