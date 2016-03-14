jQuery(function() {	

});

function wtbSettings(e){
    jQuery('#response').hide();
	arg=jQuery( e ).serialize();
	bindElement = jQuery('#wtbSaveButton');
	AjaxCall( bindElement, 'wtbSettings', arg, function(data){  		
        if(data.error == false){
            jQuery('#response').removeClass('error');
            jQuery('#response').show('slow').text(data.msg);
        }else{
            jQuery('#response').addClass('error');
            jQuery('#response').show('slow').text(data.msg);			
        }
    });

}

function AjaxCall( element, action, arg, handle){
    if(action) data = "action=" + action;
    if(arg)    data = arg + "&action=" + action;
    if(arg && !action) data = arg;
    data = data ;

    var n = data.search("wtb_nonce");
    if(n<0){
        data = data + "&wtb_nonce=" + wtb_var.wtb_nonce;
    }

	jQuery.ajax({
		type: "post",
		dataType: "json",
        url: ajaxurl,
        data: data,
		beforeSend: function() { jQuery("<span class='wtb_loading'></span>").insertAfter(element); },
		success: function( data ){			
            jQuery(".wtb_loading").remove();
            handle(data);
		}
	});
}