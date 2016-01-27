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
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_style( 'wtb_css_settings', $wtbInit->assetsUrl . 'css/settings.css');
		}

		/**
		 * WP Table bookings script
		 * @since 1.0
		 */
		function wtb_settings_script(){
			global $wtbInit;
			wp_enqueue_script( 'wtb_js_settings',  $wtbInit->assetsUrl. 'js/settings.js', array('jquery','wp-color-picker'), '', true );
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

			// Reservation details fieldset
			'reservation'	=> array(
				'legend'	=> __( 'Book a table', 'restaurant-reservations' ),
				'fields'	=> array(
					'date'		=> array(
						'title'			=> __( 'Date', 'restaurant-reservations' ),
						'request_input'	=> empty( $request->request_date ) ? '' : $request->request_date,
						'callback'		=> 'rtb_print_form_text_field',
						'required'		=> true,
					),
					'time'		=> array(
						'title'			=> __( 'Time', 'restaurant-reservations' ),
						'request_input'	=> empty( $request->request_time ) ? '' : $request->request_time,
						'callback'		=> 'rtb_print_form_text_field',
						'required'		=> true,
					),
					'party'		=> array(
						'title'			=> __( 'Party', 'restaurant-reservations' ),
						'request_input'	=> empty( $request->party ) ? '' : $request->party,
						'callback'		=> 'rtb_print_form_select_field',
						'callback_args'	=> array(
							'options'	=> $this->get_wp_form_party_options(),
						),
						'required'		=> true,
					),
				),
			),

			// Contact details fieldset
			'contact'	=> array(
				'legend'	=> __( 'Contact Details', 'restaurant-reservations' ),
				'fields'	=> array(
					'name'		=> array(
						'title'			=> __( 'Name', 'restaurant-reservations' ),
						'request_input'	=> empty( $request->name ) ? '' : $request->name,
						'callback'		=> 'rtb_print_form_text_field',
						'required'		=> true,
					),
					'email'		=> array(
						'title'			=> __( 'Email', 'restaurant-reservations' ),
						'request_input'	=> empty( $request->email ) ? '' : $request->email,
						'callback'		=> 'rtb_print_form_text_field',
						'callback_args'	=> array(
							'input_type'	=> 'email',
						),
						'required'		=> true,
					),
					'phone'		=> array(
						'title'			=> __( 'Phone', 'restaurant-reservations' ),
						'request_input'	=> empty( $request->phone ) ? '' : $request->phone,
						'callback'		=> 'rtb_print_form_text_field',
						'callback_args'	=> array(
							'input_type'	=> 'tel',
						),
					),
					'add-message'	=> array(
						'title'		=> __( 'Add a Message', 'restaurant-reservations' ),
						'request_input'	=> '',
						'callback'	=> 'rtb_print_form_message_link',
					),
					'message'		=> array(
						'title'			=> __( 'Message', 'restaurant-reservations' ),
						'request_input'	=> empty( $request->message ) ? '' : $request->message,
						'callback'		=> 'rtb_print_form_textarea_field',
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

			$max = empty( $party_size ) ? apply_filters( 'rtb_party_size_upper_limit', 100 ) : (int) $this->get_setting( 'party-size' );

			for ( $i = 1; $i <= $max; $i++ ) {
				$options[$i] = $i;
			}

			return apply_filters( 'rtb_form_party_options', $options );
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