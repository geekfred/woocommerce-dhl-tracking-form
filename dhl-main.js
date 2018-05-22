(function($) {

    $("#dhl-tracking-form-container button").on("click",function(){
       var trackingID = $("#dhl-tracking-form-container input#trackingid").val();
       var orderID = $("#dhl-tracking-form-container input#orderid").val();
        $("#dhl-tracking-response-container").html("<div class='loader'></div>");
        var data = {
            'action': 'get_dhl_tracking',
            'trackingID':trackingID,
            'orderID':orderID
        };
        $.get(
            woocommerce_params.ajax_url, // The AJAX URL
            data,
            function(response){
                $("#dhl-tracking-response-container").html(response);
            }
        );
    })
})( jQuery );