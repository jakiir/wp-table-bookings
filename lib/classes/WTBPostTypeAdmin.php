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
				$shortcode = array( 'shortcode' => __( 'Shortcode', WTB_SLUG ) );
				return  array_slice( $columns, 0, 1, true ) + $column_date + $column_time + $column_name + $column_phone + $column_delete + $shortcode;
			}
			
			public function manage_wtb_booking_columns( $column ) {
				global $wtbInit, $post;
				$postId = get_the_ID();
				$table_delete = add_query_arg(array('action_req'=>'bookings_delete', 'booking_id'=>$postId));				
				$table_edit = admin_url("/post.php?post=$postId&action=edit");	
				switch ( $column ) {
					case 'booking_date':
						echo get_post_meta( $postId, 'wtb-date', true );
						if ( $post->post_status !== 'trash' ) {
							$value .= '<div class="row-actions">';
							$value .= '<span class="edit"><a href="'.$table_edit.'" title="Edit this item">Edit</a></span>';
							$value .= '</div>';
						}
						echo $value;						
						break;
					case 'time':
						echo get_post_meta( $postId , 'wtb-time' , true );
						break;
					case 'name':
						echo get_post_meta( $postId , 'wtb-email' , true );
						break;
					case 'phone':
						echo get_post_meta( $postId , 'wtb-phone' , true );
						break;
					case 'delete':
						echo "<a href='".wp_nonce_url($table_delete)."' class='delete-bookings' alt='delete-bookings' title='delete-bookings'>Delete</a>";
						break;
					case 'shortcode':
						echo '[wtb id="'.get_the_ID().'" title="'.get_the_title().'"]';
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