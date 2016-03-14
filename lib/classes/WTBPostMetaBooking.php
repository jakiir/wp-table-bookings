<?php

if (!class_exists('WTBPostMetaBooking')):
    /**
     * Wp Booking Post Meta
	 * since 1.0
     */
    class WTBPostMetaBooking
    {
		function __construct() {
			add_action('get_booked_date', array($this, 'get_publish_booking_date'));	
            add_action('add_meta_boxes', array($this, 'wtb_booking_meta_boxes'));
            add_action('save_post', array($this, 'save_table_booking_meta_data'), 10, 3);
            add_action('admin_print_scripts-post-new.php', array($this, 'wtb_script'), 11);
            add_action('admin_print_scripts-post.php', array($this, 'wtb_script'), 11);				
        }
		
		/**
		 * This is booking date
		 * since 1.0
		*/
		function get_publish_booking_date(){
			global $wpdb;
			$get_booking_posts = $wpdb->get_results( "SELECT DISTINCT wposts.ID
			FROM $wpdb->posts wposts			
			WHERE wposts.post_status = 'publish'
			AND wposts.post_type = 'wtb-booking'			
			ORDER BY wposts.ID ASC" ); ?>
			<script type="text/javascript"> var arrDisabledDates = {};</script>
			<?php			
			foreach($get_booking_posts as $booking_posts){
				$wtb_date = get_post_meta( $booking_posts->ID, 'wtb-date', true );				
			?>
				<script type="text/javascript">					
					arrDisabledDates[new Date('<?php echo format_date($wtb_date,'m/d/Y'); ?>')] = new Date('<?php echo date('m/d/Y', strtotime($wtb_date)); ?>');					
				</script>
			<?php						
			}
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

				add_meta_box(
					'wtb_booking_shortcode_meta',
					__('Shortcode', WTB_SLUG ),
					array($this,'wtb_table_booking_sc_shortBox_meta'),
					$wtbInit->post_type,
					'side',
					'high');
					
			}
			
			 function wtb_table_booking_sc_shortBox_meta($post){
				echo "<div class='wtb-team-sc-text-meta-box'>";
					echo '<p>[wtb id="'.$post->ID.'" title="'.$post->post_title.'"]</p>';
				echo "</div>";
			}
			
			/**		
			 * Table booking meta-box
			 * @since 1.0
			 */
			function wtb_booking_settings_selection($post){
				global $wtbInit;				
				$html = null;	
				do_action('get_booked_date');
			?>
			<div class="table-booking-form rtb-container">
				<form method="POST">										
					<?php wp_nonce_field( $wtbInit->nonceText(), 'wtb_nonce' ); ?>					
					<div id="table-booking-form-fields">						
						<?php echo $this->print_wp_booking_form_fields($post); ?>
					</div>
				</form>
			</div>
				<?php
			}
		/**		 
		 * @since 1.0
		 */
		public function print_wp_booking_form_fields($post) {
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
							$meta_velue = get_post_meta( $post->ID, 'wtb-'.$slug, true );
							if($slug == 'date') $meta_velue = format_date($meta_velue);
							if($slug == 'time') $meta_velue = format_time($meta_velue);
							$meta_velue = (isset( $meta_velue ) ? sanitize_text_field($meta_velue) : '');							
							@call_user_func( $field['callback'], $slug, $field['title'], $meta_velue, $args );
						}
					?>
				</fieldset>
				<?php endforeach;
			return ob_get_clean();
		}
		
		function save_table_booking_meta_data($post_id, $post, $update) {
            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;

            global $wtbInit;
			if ( ! wp_verify_nonce( $_REQUEST['wtb_nonce'], $wtbInit->nonceText() ) ) return;                  

            if ( $wtbInit->post_type != $post->post_type ) return;			
            $meta['wtb-date'] = (isset( $_REQUEST['wtb-date'] ) ? sanitize_text_field(date('Y-m-d', strtotime($_REQUEST['wtb-date']))) : null);
			$meta['wtb-time'] = (isset( $_REQUEST['wtb-time'] ) ? sanitize_text_field(date("H:i:s", strtotime($_REQUEST['wtb-time']))) : null);
			$meta['wtb-party'] = (isset( $_REQUEST['wtb-party'] ) ? sanitize_text_field($_REQUEST['wtb-party']) : null);
			$meta['wtb-name'] = (isset( $_REQUEST['wtb-name'] ) ? sanitize_text_field($_REQUEST['wtb-name']) : null);
			$meta['wtb-email'] = (isset( $_REQUEST['wtb-email'] ) ? sanitize_text_field($_REQUEST['wtb-email']) : null);
			$meta['wtb-phone'] = (isset( $_REQUEST['wtb-phone'] ) ? sanitize_text_field($_REQUEST['wtb-phone']) : null);
			$meta['wtb-message'] = (isset( $_REQUEST['wtb-message'] ) ? sanitize_text_field($_REQUEST['wtb-message']) : null);
			$meta['wtb-status'] = (isset( $_REQUEST['wtb-status'] ) ? sanitize_text_field($_REQUEST['wtb-status']) : null);
			
            foreach($meta as $key => $data){
				if($data != null || $data != '')
                update_post_meta($post_id, $key, $data);
            }

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