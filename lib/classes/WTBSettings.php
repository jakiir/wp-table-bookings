<?php
if (!class_exists('WTBSettings')):
    /**
     * Wp Booking Settings
	 * since 1.0
     */
    class WTBSettings
    {		
		function __construct() {
            
        }
		
		/**
		 * WP Table bookings style
		 * @since 1.0
		 */
		function wtb_settings_style(){
			global $wtbInit;			
			wp_enqueue_style( 'wtb_css_settings', $wtbInit->assetsUrl . 'css/settings.css');
			wp_enqueue_style( 'tlp-team-core-ui-css', "https://code.jquery.com/ui/1.11.3/themes/smoothness/jquery-ui.css" );
			wp_enqueue_style( 'tpl-team-select2-css', $wtbInit->assetsUrl . 'vendor/select2/select2.min.css');	
		}

		/**
		 * WP Table bookings script
		 * @since 1.0
		 */
		function wtb_settings_script(){
			global $wtbInit;			
			wp_enqueue_script( 'tpl-team-select2-js',  $wtbInit->assetsUrl. 'vendor/select2/select2.min.js', array('jquery'), '', true );
			wp_enqueue_script( 'wtb_js_settings',  $wtbInit->assetsUrl. 'js/settings.js', array('jquery'), '', true );
			$nonce = wp_create_nonce( $wtbInit->nonceText() );
			wp_localize_script( 'wtb_js_settings', 'tlpteam_var',
				array(
					'wtb_nonce' => $nonce,
					'ajaxurl' => admin_url( 'admin-ajax.php' )
				) );
		}
		
		/**
		 * WP Table bookings form fields
		 * @since 1.0
		 */
		public function get_wp_booking_form_fields( $request = null ) {			
			$fields = array(

			// Table-booking details fieldset
			'table-booking'	=> array(
				'legend'	=> __( 'Book a table', WTB_SLUG ),
				'fields'	=> array(
					'date'		=> array(
						'title'			=> __( 'Date', WTB_SLUG ),
						'request_input'	=> empty( $request->request_date ) ? '' : $request->request_date,
						'callback'		=> 'wtb_print_form_text_field',
						'required'		=> true,
					),
					'time'		=> array(
						'title'			=> __( 'Time', WTB_SLUG ),
						'request_input'	=> empty( $request->request_time ) ? '' : $request->request_time,
						'callback'		=> 'wtb_print_form_text_field',
						'required'		=> true,
					),
					'party'		=> array(
						'title'			=> __( 'Party', WTB_SLUG ),
						'request_input'	=> empty( $request->party ) ? '' : $request->party,
						'callback'		=> 'wtb_print_form_select_field',
						'callback_args'	=> array(
							'options'	=> $this->get_wp_form_party_options(),
						),
						'required'		=> true,
					),
				),
			),

			// Contact details fieldset
			'contact'	=> array(
				'legend'	=> __( 'Contact Details', WTB_SLUG ),
				'fields'	=> array(
					'name'		=> array(
						'title'			=> __( 'Name', WTB_SLUG ),
						'request_input'	=> empty( $request->name ) ? '' : $request->name,
						'callback'		=> 'wtb_print_form_text_field',
						'required'		=> true,
					),
					'email'		=> array(
						'title'			=> __( 'Email', WTB_SLUG ),
						'request_input'	=> empty( $request->email ) ? '' : $request->email,
						'callback'		=> 'wtb_print_form_text_field',
						'callback_args'	=> array(
							'input_type'	=> 'email',
						),
						'required'		=> true,
					),
					'phone'		=> array(
						'title'			=> __( 'Phone', WTB_SLUG ),
						'request_input'	=> empty( $request->phone ) ? '' : $request->phone,
						'callback'		=> 'wtb_print_form_text_field',
						'callback_args'	=> array(
							'input_type'	=> 'tel',
						),
					),
					'add-message'	=> array(
						'title'		=> __( 'Add a Message', WTB_SLUG ),
						'request_input'	=> '',
						'callback'	=> 'wtb_print_form_message_link',
					),
					'message'		=> array(
						'title'			=> __( 'Message', WTB_SLUG ),
						'request_input'	=> empty( $request->message ) ? '' : $request->message,
						'callback'		=> 'wtb_print_form_textarea_field',
					),
				),
			),
		);

		return apply_filters( 'wtb_wp_booking_form_fields', $fields, $request );
			
			
		}
		
		/**
		 * wp table booking
		 * Get options for the party select field in the booking form		 
		 * @since 1.0
		 */
		public function get_wp_form_party_options() {

			$party_size = (int) $this->get_setting( 'party-size' );

			$max = empty( $party_size ) ? apply_filters( 'wtb_party_size_upper_limit', 100 ) : (int) $this->get_setting( 'party-size' );

			for ( $i = 1; $i <= $max; $i++ ) {
				$options[$i] = $i;
			}

			return apply_filters( 'wtb_form_party_options', $options );
		}
		
		/**
		 * wp table booking
		 * Get a setting's value or fallback to a default if one exists
		 * @since 1.0
		 */
		public function get_setting( $setting ) {

			if ( empty( $this->settings ) ) {
				$this->settings = get_option( 'wtb-settings' );
			}

			if ( !empty( $this->settings[ $setting ] ) ) {
				return apply_filters( 'wtb-setting-' . $setting, $this->settings[ $setting ] );
			}

			if ( !empty( $this->defaults[ $setting ] ) ) {
				return apply_filters( 'wtb-setting-' . $setting, $this->defaults[ $setting ] );
			}

			return apply_filters( 'wtb-setting-' . $setting, null );
		}
		
	}	
endif;
?>