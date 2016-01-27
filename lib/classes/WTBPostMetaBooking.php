<?php

if (!class_exists('WTBPostMetaBooking')):
    /**
     * Wp Booking Post Meta
	 * since 1.0
     */
    class WTBPostMetaBooking
    {
		function __construct() {
            add_action('add_meta_boxes', array($this, 'wtb_booking_meta_boxes'));
            //add_action('save_post', array($this, 'save_team_sc_meta_data'), 10, 3);
            add_action('admin_print_scripts-post-new.php', array($this, 'wtb_script'), 11);
            //add_action('admin_print_scripts-post.php', array($this, 'wtb_script'), 11);
        }
		
		
		function wtb_booking_meta_boxes() {
				global $wtbInit;
				add_meta_box(
					'wtb_booking_settings_meta',
					__('Booking form', WTB_SLUG ),
					array($this,'wtb_booking_settings_selection'),
					$wtbInit->post_type,
					'normal',
					'high');				
			}
			
			function wtb_booking_settings_selection($post){
				global $wtbInit;
				wp_nonce_field( 'wtb_bookings_nonce', 'wtb_nonce' );
				$html = null;				
		?>

		
			<div class="rtb-booking-form rtb-container">
				<form method="POST">
					<input type="hidden" name="action" value="admin_booking_request">
					<input type="hidden" name="ID" value="">					
					<div id="rtb-booking-form-fields">							
						<?php echo $this->print_wp_booking_form_fields(); ?>
					</div>

					<button type="submit" class="button button-primary">
						<?php _e( 'Add Booking', WTB_SLUG ); ?>
					</button>
					<a href="#" class="button" id="rtb-cancel-booking-modal">
						<?php _e( 'Cancel', WTB_SLUG ); ?>
					</a>					
				</form>
			</div>
				<?php
			}
			
			
			
		/**		 
		 * @since 1.0
		 */
		public function print_wp_booking_form_fields() {

			global $wtbInit;		

			// Retrieve the form fields
			$fields = $wtbInit->get_wp_booking_form_fields( $wtbInit->request );

			ob_start();			
			
			?>

				<?php foreach( $fields as $fieldset => $contents ) : ?>
				<fieldset class="<?php echo $fieldset; ?>">
					<?php
						foreach( $contents['fields'] as $slug => $field ) {

							$args = empty( $field['callback_args'] ) ? null : $field['callback_args'];

							@call_user_func( $field['callback'], $slug, $field['title'], $field['request_input'], $args );
						}
					?>
				</fieldset>
				<?php endforeach;
			return ob_get_clean();
		}
		
		function wtb_script() {
            global $post_type,$wtbInit;
            if($post_type == $wtbInit->post_type){
                $wtbInit->wtb_settings_style();
                $wtbInit->wtb_settings_script();
            }
        }
		
	}
endif;
?>