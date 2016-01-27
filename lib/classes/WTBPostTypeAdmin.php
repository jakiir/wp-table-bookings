<?php 
	if(!class_exists('WTBPostTypeAdmin')):
		class WTBPostTypeAdmin
		{
			function __construct()
			{
				add_filter( 'manage_edit-wtb-booking_columns', array($this, 'arrange_wtb_booking_columns'));
				add_action( 'manage_wtb-booking_posts_custom_column', array($this,'manage_wtb_booking_columns'), 10, 2);
				add_filter( "manage_edit-wtb-booking_sortable_columns", array($this,'wtb_booking_column_sort'));				
			}			
			
			public function arrange_wtb_booking_columns( $columns ) {
				$column_date = array( 'booking_date' => __( 'Date', WTB_SLUG ) );
				$column_time = array( 'time' => __( 'Time', WTB_SLUG ) );
				$column_name = array( 'name' => __( 'Name', WTB_SLUG ) );
				$column_phone = array( 'phone' => __( 'Phone', WTB_SLUG ) );
				$column_delete = array( 'delete' => __( 'Delete', WTB_SLUG ) );
				return  $column_date + $column_time + $column_name + $column_phone + $column_delete;
			}
			
			public function manage_wtb_booking_columns( $column ) {
			 global $wtbInit;
			 
				switch ( $column ) {
					case 'booking_date':
						echo 'fsdad';
						break;
					case 'time':
						echo 'dfasdf';
						break;
					case 'name':
						echo get_post_meta( get_the_ID() , 'email' , true );
						break;
					case 'phone':
						echo get_post_meta( get_the_ID() , 'phone' , true );
						break;
					case 'delete':
						echo 'X';
						break;
					default:
						break;
				}
			}
			
			/**
			 * column sortable
			 *
			 * @since 1.0
			 */
			function wtb_booking_column_sort($columns){
				$custom = array(
					'booking_date'     => 'date',
					'time'         => 'time'					
				);
				return wp_parse_args($custom, $columns);
			}
			
		}
	endif;
?>