<?php
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'wtbAddons' ) ) {
/**
 * Class to handle the addons page for Restaurant Reservations
 *
 * @since 1.3
 */
class wtbAddons {

	/**
	 * API endpoint to retrieve addons list
	 */
	public $api_url;

	/**
	 * Plugin slug to retrieve addons for
	 */
	public $plugin;

	public function __construct( $args ) {

		$this->parse_args( $args );

		if ( $this->check_config() ) {

			// Add the admin menu
			add_action( 'admin_menu', array( $this, 'add_menu_page' ), 100 );

			// Send addon data to the javascript
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

			// Receive ajax calls to fetch addons
			add_action( 'wp_ajax_nopriv_wtb-addons' , array( $this , 'ajax_nopriv_get_addons' ) );
			add_action( 'wp_ajax_wtb-addons', array( $this, 'ajax_get_addons' ) );

			// Add a newsletter subscription prompt above the addons
			add_action( 'wtb_addons_pre', array( $this, 'add_subscribe_pompt' ) );

		}
	}

	/**
	 * Parse the arguments passed in the construction and assign them to
	 * internal variables.
	 */
	private function parse_args( $args ) {
		foreach ( $args as $key => $val ) {
			switch ( $key ) {

				case 'api_url' :
					$this->{$key} = esc_url( $val );

				case 'plugin' :
					$this->{$key} = esc_attr( $val );

				default :
					$this->{$key} = $val;

			}
		}

		do_action( $this->plugin . '_addons_parse_args' );
	}

	/**
	 * Check that we have everything we need to render the addons page
	 */
	public function check_config() {

		if ( !empty( $this->api_url ) && !empty( $this->plugin ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Send addon data to the javascript
	 */
	public function enqueue_admin_assets() {

		// Use the page reference in $admin_page_hooks because
		// it changes in SOME hooks when it is translated.
		// https://core.trac.wordpress.org/ticket/18857
		global $admin_page_hooks;

		$screen = get_current_screen();
		if ( empty( $screen ) || empty( $admin_page_hooks['wtb-bookings'] ) ) {
			return;
		}

		if ( $screen->base == $admin_page_hooks['wtb-bookings'] . '_page_wtb-addons' ) {
			wp_localize_script(
				'wtb-admin',
				'wtb_addons',
				array(
					'nonce'			=> wp_create_nonce( 'wtb-addons' ),
					'strings'		=> array(
						'loading'		=> __( 'Loading', 'wp-table-bookings' ),
						'error_unknown'	=> _x( 'An unknown error occured.', 'Error message when retrieving list of addons', 'wp-table-bookings' ),
						'installed'		=> _x( 'Already Installed', 'Label for an addon that is already installed and activated.', 'wp-table-bookings' ),
						'coming_soon'	=> _x( 'Coming Soon', 'Label for an addon that is not yet released.', 'wp-table-bookings' ),
						'free'			=> _x( 'Free', 'Label for an addon that is free.', 'wp-table-bookings' ),
						'learn_more'	=> _x( 'Get It', 'Label for an addon that is released.', 'wp-table-bookings' ),
					)
				)
			);
		}
	}

	/**
	 * Add the addons page to the admin menu
	 */
	public function add_menu_page() {

		add_submenu_page(
			'wtb-bookings',
			_x( 'Addons', 'Title of addons page', 'wp-table-bookings' ),
			_x( 'Addons', 'Title of addons page in the admin menu', 'wp-table-bookings' ),
			'manage_options',
			'wtb-addons',
			array( $this, 'show_admin_addons_page' )
		);

	}

	/**
	 * Display the addons page
	 */
	public function show_admin_addons_page() {

		// @todo check for transient and only call the api if its missing
		?>

		<div class="wrap">
			<h1><?php _e( 'Addons for Restaurant Reservations', 'wp-table-bookings' ); ?></h1>
			<?php do_action( 'wtb_addons_pre' ); ?>
			<div id="wtb-addons">
				<div class="wtb-loading">
					<div class="spinner"></div>
					Loading
				</div>
			</div>
			<?php do_action( 'wtb_addons_post' ); ?>
		</div>

		<?php
	}

	/**
	 * Handle ajax request for addons from logged out user
	 */
	public function ajax_nopriv_get_addons() {

		wp_send_json_error(
			array(
				'error' => 'loggedout',
				'msg' => __( 'You have been logged out. Please login again to retrieve the addons.', 'wp-table-bookings' ),
			)
		);
	}

	/**
	 * Handle ajax request for addons
	 */
	public function ajax_get_addons() {

		$url = $this->api_url . $this->plugin;

		if ( !check_ajax_referer( 'wtb-addons', 'nonce' ) ||  !current_user_can( 'manage_options' )) {
			wp_send_json_error(
				array(
					'error' => 'nopriv',
					'msg' => __( 'You do not have permission to access this page. Please login to an administrator account if you have one.', 'wp-table-bookings' ),
				)
			);
		}

		if ( function_exists( 'curl_init' ) && function_exists( 'curl_setop' ) ) {
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, $url );
			curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Type: application/json' ) );
			curl_setopt( $ch, CURLOPT_USERAGENT, 'PHP-MCAPI/2.0' );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_TIMEOUT, 10 );
			$result = curl_exec($ch);
			curl_close($ch);

		} elseif ( ini_get( 'allow_url_fopen' ) ) {
			$result = @file_get_contents( $url );
		} else {
			$result = @file_get_contents( WTB_PLUGIN_DIR . '/assets/addons-backup.json' );
		}

		if ( $result ) {
			// @todo set a transient with this data to reduce calls
			wp_send_json_success( json_decode( $result ) );
		} else {
			wp_send_json_error(
				array(
					'error' => 'apifailed',
					'msg' => __( 'The addons list could not be retrieved. Please <a href="">try again</a>. If the problem persists over time, please report it on the <a href="http://wordpress.org/support/plugin/wp-table-bookings" target="_blank">support forums</a>.', 'wp-table-bookings' ),
				)
			);
		}
	}

	/**
	 * Add a prompt for users to subscribe to the Theme of the Crop mailing list
	 * below the addons list.
	 *
	 * @since 0.1
	 */
	public function add_subscribe_pompt() {

		?>

		<p>
			<?php echo sprintf( esc_html_x( 'Find out when new addons are available by subscribing to the %smonthly newsletter%s or following %sTheme of the Crop%s on Twitter.', 'wp-table-bookings' ), '<a href="http://themeofthecrop.com/about/mailing-list/?utm_source=Plugin&utm_medium=Addon%20List&utm_campaign=Restaurant%20Reservations">', '</a>', '<a href="http://twitter.com/themeofthecrop">', '</a>' ); ?>
		</p>

		<?php
	}

}
} // endif;
