<?php 
	if(isset($_REQUEST['action_req']) && $_REQUEST['action_req']=='bookings_delete' && $_REQUEST['booking_id'] !=='')
	add_action('init','bookings_delete');
    function bookings_delete(){		
		wp_delete_post($_REQUEST['booking_id']);
		return;
	}
?>