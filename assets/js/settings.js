jQuery(function() {	

	jQuery('#wtb-date').datepicker({
		beforeShowDay: function (dt) {
		   var bDisable = arrDisabledDates[dt];
		   if (bDisable) 
			  return [false, '', ''];
		   else 
			  return [true, '', ''];
	   },
	   minDate: 0,
	   dateFormat: "MM d, yy"
	});
	
	jQuery('#wtb-time').timepicker({
		hourGrid: 4,
		minuteGrid: 10,
		timeFormat: 'hh:mm tt'
	});
});

function wtbSettings(e){
    jQuery('#response').hide();
	arg=jQuery( e ).serialize();
	bindElement = jQuery('#wtbSaveButton');
	AjaxCall( bindElement, 'wtbSettings', arg, function(data){
        console.log(data);
        if(data.error){
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
        url: ajaxurl,
        data: data,
		beforeSend: function() { jQuery("<span class='wtb_loading'></span>").insertAfter(element); },
		success: function( data ){
            jQuery(".wtb_loading").remove();
            handle(data);
		}
	});
}