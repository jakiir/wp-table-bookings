<?php 
	if(!class_exists('WTBPostTypeAdmin')):
		class WTBPostTypeAdmin
		{
			function __construct()
			{
				add_filter( 'manage_edit-wtb-booking_columns', array($this, 'arrange_wtb_booking_columns'));
				add_action( 'manage_wtb-booking_posts_custom_column', array($this,'manage_wtb_booking_columns'), 10, 2);				
				//add_action( 'restrict_manage_posts', array( $this, 'add_taxonomy_filters' ) );
				//add_filter( "manage_edit-wtb-booking_sortable_columns", array($this,'wtb_booking_column_sort'));
			}
			
			public function arrange_wtb_booking_columns( $columns ) {
				$column_date = array( 'date' => __( 'Date', WTB_SLUG ) );
				$column_time = array( 'time' => __( 'Time', WTB_SLUG ) );
				$column_name = array( 'name' => __( 'Name', WTB_SLUG ) );
				$column_phone = array( 'phone' => __( 'Phone', WTB_SLUG ) );
				$column_delete = array( 'delete' => __( 'Delete', WTB_SLUG ) );
				return $column_date + $column_time + $column_name + $column_phone + $column_delete;
			}
			
			public function manage_wtb_booking_columns( $column ) {
			 global $wtbInit;
				switch ( $column ) {
					case 'date':
						echo $wtbInit->format_date( $booking->date );;
						break;
					case 'designation':
						echo get_post_meta( get_the_ID() , 'designation' , true );
						break;
					case 'email':
						echo get_post_meta( get_the_ID() , 'email' , true );
						break;
					case 'location':
						echo get_post_meta( get_the_ID() , 'location' , true );
						break;
					default:
						break;
				}
			}
			
		}
	endif;
?>