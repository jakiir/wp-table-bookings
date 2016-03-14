<?php 
if(!class_exists('WTBShortCode')):
	class WTBShortCode
	{
		function __construct()
		{
			add_shortcode( 'wtb', array( $this, 'wtb_shortcode' ) );
			add_shortcode( 'wtb-form', array( $this, 'wtb_booking_form_front_end' ) );	
			add_action('init', array($this, 'wtb_booking_form_front_end_save'), 10, 3);
		}
		
		function register_shortcode_scripts(){
			global $wtbInit;
			wp_enqueue_style('shortcode-style', $wtbInit->assetsUrl . 'css/shortcode.css' );
			wp_enqueue_script('tlpteam-js', $wtbInit->assetsUrl . 'js/shortcode.js', null, null, true);
		}
		
		function wtb_shortcode($atts){
			global $post_type,$wtbInit;			
			$rand = mt_rand();
			$layoutID = "wtb-container-".$rand;			
			$html = null;
			$arg= array();
			$layout = 'layout1';
			$atts = shortcode_atts( array(
				'id' => null
			), $atts, 'wtb' );
			$scID =  $atts['id'];
			if($scID && !is_null(get_post( $scID )) && $wtbInit->post_type == get_post( $scID )->post_type){				
				$arg['title'] = get_the_title($scID);
				$arg['pLink'] = get_permalink($scID);
				$arg['date'] = get_post_meta( $scID, 'wtb-date', true );
				$arg['time'] = get_post_meta( $scID, 'wtb-time', true );
				$arg['party'] = get_post_meta( $scID, 'wtb-party', true );
				$arg['name'] = get_post_meta( $scID, 'wtb-name', true );
				$arg['email'] = get_post_meta( $scID, 'wtb-email', true );
				$arg['phone'] = get_post_meta( $scID, 'wtb-phone', true );
				$arg['message'] = get_post_meta( $scID, 'wtb-message', true );
				$arg['status'] = get_post_meta( $scID, 'wtb-status', true );				
				$html .= '<div class="container-fluid">';
					$html .= '<div class="wtb-row wtb-team">';
						$html .= "<div class='{$layout}' id='{$layoutID}'>";
							$html .= '<ul class="wp-table-bookins">';
								foreach($arg as $key => $get_wtb){
									$html .= '<li class="wtb-title">';
										$html .= $key;
									$html .= '</li>';
									$html .= '<li>';
										$html .= $get_wtb;
									$html .= '</li>';									
								}
							$html .= '</ul>';
						$html .= '</div>';	
					$html .= '</div>';
				$html .= '</div>';
			} else{
				$html .="<p>No shortCode found</p>";
			}
			add_action( 'wp_footer', array($this, 'register_shortcode_scripts'));
			return $html;
		}
		
		/**		
		 * Table booking Shortcode
		 * @since 1.0
		 */
		function wtb_booking_form_front_end($post){
			global $wtbInit, $wpdb;				
			$html = null;	
			do_action('get_booked_date');
		?>
		<div class="table-booking-form rtb-container">
			<form method="POST">										
				<?php wp_nonce_field( $wtbInit->nonceText(), 'wtb_nonce' ); ?>					
				<div id="table-booking-form-fields">
					<div class="wtb-text title">
						<label for="wtb-title">
							Title
						</label>
						<input type="text" name="wtb-title" id="wtb-title" value="">
					</div>					
					<?php echo $this->print_wp_booking_form_fields_front_end($post); ?>
					<div class="wtb-submit submit">						
						<input type="submit" name="wtb-submit" id="wtb-submit" value="Submit">
					</div>
				</div>
			</form>
		</div>
			<?php			
			add_action( 'wp_footer', array($wtbInit, 'wtb_settings_style'));
			add_action( 'wp_footer', array($wtbInit, 'wtb_settings_script'));				
		}
		
		/**		 
		 * @since 1.0
		 */
		function print_wp_booking_form_fields_front_end($post) {
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
							if($slug !== 'status')
							@call_user_func( $field['callback'], $slug, $field['title'], '', $args );
						}
					?>
				</fieldset>
				<?php endforeach;
			return ob_get_clean();
		}
		
		/**		
		 * Table booking form save
		 * @since 1.0
		 */
		 
		function wtb_booking_form_front_end_save(){
            global $wtbInit;
			if ( ! wp_verify_nonce( $_REQUEST['wtb_nonce'], $wtbInit->nonceText() ) ) return;  
			if ( ! isset($_REQUEST['wtb-submit']) ) return;  
			if ( ! isset($_REQUEST['wtb-title']) ) return; 
			
			$wtb_booking_post = array(
				'post_title' => $_POST['wtb-title'],				
				'post_status' => 'pending',
				'post_type' => 'wtb-booking'				
			);
			$the_post_id = wp_insert_post( $wtb_booking_post );
			
			$meta['wtb-date'] = (isset( $_REQUEST['wtb-date'] ) ? sanitize_text_field(date('Y-m-d', strtotime($_REQUEST['wtb-date']))) : null);
			$meta['wtb-time'] = (isset( $_REQUEST['wtb-time'] ) ? sanitize_text_field(date("H:i:s", strtotime($_REQUEST['wtb-time']))) : null);
			$meta['wtb-party'] = (isset( $_REQUEST['wtb-party'] ) ? sanitize_text_field($_REQUEST['wtb-party']) : null);
			$meta['wtb-name'] = (isset( $_REQUEST['wtb-name'] ) ? sanitize_text_field($_REQUEST['wtb-name']) : null);
			$meta['wtb-email'] = (isset( $_REQUEST['wtb-email'] ) ? sanitize_text_field($_REQUEST['wtb-email']) : null);
			$meta['wtb-phone'] = (isset( $_REQUEST['wtb-phone'] ) ? sanitize_text_field($_REQUEST['wtb-phone']) : null);
			$meta['wtb-message'] = (isset( $_REQUEST['wtb-message'] ) ? sanitize_text_field($_REQUEST['wtb-message']) : null);
			$meta['wtb-status'] = ( isset( $_REQUEST['wtb-status'] ) ? sanitize_text_field($_REQUEST['wtb-message']) : 'pending');
			
            foreach($meta as $key => $data){
				if($data != null || $data != '')
                update_post_meta($the_post_id, $key, $data);
            }
			
			
		}
		
	}
endif;
?>