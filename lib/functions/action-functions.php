<?php 
	if(isset($_REQUEST['action_req']) && $_REQUEST['action_req']=='bookings_delete' && $_REQUEST['booking_id'] !=='')
	add_action('init','bookings_delete');
    function bookings_delete(){		
		wp_delete_post($_REQUEST['booking_id']);
		return;
	}
	
	/**
	 * Format date
	 * @since 1.0
	 */
	function format_date( $date, $format_date='' ) {
		$format_date = ($format_date !='' ? $format_date : get_option( 'date_format' ));
		$date = mysql2date( $format_date , $date);
		return apply_filters( 'get_the_date', $date );
	}
	
	/**
	 * Format time
	 * @since 1.0
	 */
	function format_time( $time ) {
		$time = mysql2date( get_option( 'time_format' ) , $time);
		return apply_filters( 'get_the_time', $time );
	}
	
	
?>