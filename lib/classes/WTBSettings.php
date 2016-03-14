<?php
if (!class_exists('WTBSettings')):
    /**
     * Wp Booking Settings
	 * since 1.0
     */
    class WTBSettings
    {		
		function __construct() {
            add_action( 'admin_menu' , array($this, 'wtb_menu_register'));
			add_action(	'wp_ajax_wtbSettings', array($this, 'wtbSettings'));
        }
		
		/**
		 * WP Table bookings style
		 * @since 1.0
		 */
		function wtb_settings_style(){
			global $wtbInit;			
			wp_enqueue_style( 'wtb-core-ui-css', $wtbInit->assetsUrl . 'css/jquery-ui.min.css');
			wp_enqueue_style( 'wtb_css_timepicker', $wtbInit->assetsUrl . 'css/jquery-ui-timepicker-addon.css');
			wp_enqueue_style( 'wtb_css_settings', $wtbInit->assetsUrl . 'css/settings.css');
		}
		
		function wtb_menu_register() {
			$page_s = add_submenu_page( 'edit.php?post_type=wtb-booking', __('Settings',WTB_SLUG), __('Settings',WTB_SLUG), 'administrator', 'wtb_settings', array($this, 'wtb_settings') );			

			add_action('admin_print_styles-' . $page_s, array( $this,'wtb_style'));
			add_action('admin_print_scripts-'. $page_s, array( $this,'wtb_script'));
		}
		
		function wtb_style(){
			global $wtbInit;			
			wp_enqueue_style( 'wtb_css_settings', $wtbInit->assetsUrl . 'css/settings.css');
		}

		function wtb_script(){
			global $wtbInit;			
			$nonce = wp_create_nonce( $wtbInit->nonceText() );
			wp_enqueue_script('jquery');
			wp_enqueue_script( 'jquery-ui-js',  $wtbInit->assetsUrl. 'js/jquery-ui.min.js', array('jquery'), '', true );					
			wp_enqueue_script( 'wtb_js_settings',  $wtbInit->assetsUrl. 'js/wtbsettings.js', array('jquery'), '', true );
			wp_localize_script( 'wtb_js_settings', 'wtb_var',
				array(
					'wtb_nonce' => $nonce,
					'ajaxurl' => admin_url( 'admin-ajax.php' )
				) );
		}
		
		function wtb_settings(){
			global $wtbInit;
			$wtbInit->render('settings');
		}

		/**
		 * WP Table bookings script
		 * @since 1.0
		 */
		function wtb_settings_script(){
			global $wtbInit;
			wp_enqueue_script('jquery');
			wp_enqueue_script( 'jquery-ui-js',  $wtbInit->assetsUrl. 'js/jquery-ui.min.js', array('jquery'), '', true );
			wp_enqueue_script('jquery-ui-datepicker');	
			wp_enqueue_script( 'wtb_js_timepicker',  $wtbInit->assetsUrl. 'js/jquery-ui-timepicker-addon.js', array('jquery'), '', true );			
			wp_enqueue_script( 'wtb_js_settings',  $wtbInit->assetsUrl. 'js/settings.js', array('jquery'), '', true );
			$nonce = wp_create_nonce( $wtbInit->nonceText() );
			wp_localize_script( 'wtb_js_settings', 'wtb_var',
				array(
					'wtb_nonce' => $nonce,
					'ajaxurl' => admin_url( 'admin-ajax.php' )
				) );
		}
		
		/**
		 * wp table booking setting
		 * Get a setting's value or fallback to a default if one exists
		 * @since 1.0
		 */
		
		function wtbSettings(){
			global $wtbInit;
			$error = true;
			if($wtbInit->verifyNonce()){
				unset($_REQUEST['action']);
				update_option( $wtbInit->options['settings'], $_REQUEST);
				$response = array(
						'error'=> false,
						'msg' => 'Settings successsully updated'
					);
			}else{
				$response = array(
						'error'=> $error,
						'msg' => 'Security Error !!'
					);
			}
			echo json_encode( $response );
			die();
		}
		
		function verifyNonce( ){
            global $wtbInit;
            $nonce      = @$_REQUEST['wtb_nonce'];
            $nonceText  = $wtbInit->nonceText();
            if( !wp_verify_nonce( $nonce, $nonceText ) ) return false;
            return true;
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
					'message'		=> array(
						'title'			=> __( 'Message', WTB_SLUG ),
						'request_input'	=> empty( $request->message ) ? '' : $request->message,
						'callback'		=> 'wtb_print_form_textarea_field',
					),
				),
			),
			
			// Contact details fieldset
			'status'	=> array(
				'legend'	=> __( 'Booking Status', WTB_SLUG ),
				'fields'	=> array(
					'status'		=> array(
						'title'			=> __( 'Status', WTB_SLUG ),
						'request_input'	=> empty( $request->status ) ? '' : $request->status,
						'callback'		=> 'wtb_print_form_select_field',
						'required'		=> true,
						'callback_args'	=> array(
							'options'	=> $this->get_wp_form_status_options(),
						),
					)					
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
			global $wtbInit;
			$settings = get_option($wtbInit->options['settings']);
			$max_party_limit = (@$settings['general']['party']['max'] ? @$settings['general']['party']['max'] : 100);
			
			$party_size = (int) $this->get_setting( 'party-size' );

			$max = empty( $party_size ) ? apply_filters( 'wtb_party_size_upper_limit', $max_party_limit ) : (int) $this->get_setting( 'party-size' );

			for ( $i = 1; $i <= $max; $i++ ) {
				$options[$i] = $i;
			}

			return apply_filters( 'wtb_form_party_options', $options );
		}
		
		/**
		 * wp table booking
		 * Get options for the party select field in the booking status		 
		 * @since 1.0
		 */
		public function get_wp_form_status_options() {		
			$status_arr = array(
				'pending' => 'Pending',
				'confirmed' => 'Confirmed',
				'closed' => 'Closed'
			);
			foreach($status_arr as $key => $status){
				$options[$key] = $status;
			}
			return apply_filters( 'wtb_form_status_options', $options );
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