<?php
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'wtbLicenses' ) ) {
/**
 * Class to handle license keys when an addon
 * is enabled.
 *
 * This will add a tab to the settings page as long as at least
 * one addon has enabled it by setting its $enabled parameter
 * to `true`. It will also perform the license check and plugin
 * update procedures.
 *
 * If no addons are enabled, it does not phone home or perform
 * any additional actions.
 *
 * @since 1.4.1
 */
class wtbLicenses {

	/**
	 * Array of licensed products to manage
	 *
	 * @since 1.4.1
	 */
	public $licensed_products = array();

	/**
	 * Path to load license setting class file
	 *
	 * @since 1.4.1
	 */
	public $sap_extension_path;

	/**
	 * Filename of the setting class to handle a
	 * license key input field.
	 *
	 * @since 1.4.1
	 */
	public $sap_setting_file;

	/**
	 * Class name of the setting file to load
	 * when handling a license key input field.
	 *
	 * Should contain the class referenced in
	 * $sap_setting_class.
	 *
	 * @since 1.4.1
	 */
	public $sap_setting_class;

	/**
	 * Initialize the license handling
	 *
	 * @since 1.4.1
	 */
	public function __construct() {

		$this->sap_extension_path = WTB_PLUGIN_DIR . '/includes/';
		$this->sap_setting_file = 'AdminPageSettingLicenseKey.class.php';
		$this->sap_setting_class = 'wtbAdminPageSettingLicenseKey';

		// Check and process updates
		add_action( 'admin_init', array( $this, 'load_plugin_updater' ), 20 );

		// Add a licenses tab as the last tab in the settings page
		add_filter( 'wtb_settings_page', array( $this, 'add_licenses_tab' ), 100 );

		// Enqueue assets on licenses page
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );

		// Show a success/failed message on license activation/deactivation
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
	}

	/**
	 * Check if a licensing system should be enabled
	 *
	 * @since 1.4.1
	 */
	public function is_enabled() {

		if ( count( $this->licensed_products ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Add a licensed product to manage
	 *
	 * This should be called in admin_init (before priority 20) so that
	 * the updater knows what products to check.
	 *
	 * @since 1.4.1
	 */
	public function add_licensed_product( $product ) {
		$this->licensed_products[ $product['id'] ] = $product;
	}

	/**
	 * Add a licenses tab as the last tab in the settings page
	 *
	 * @since 1.4.1
	 */
	public function add_licenses_tab( $sap ) {

		if ( !$this->is_enabled() ) {
			return $sap;
		}

		$sap->add_section(
			'wtb-settings',
			array(
				'id'            => 'wtb-licenses',
				'title'         => __( 'Licenses', 'wp-table-bookings' ),
				'description'	=> sprintf(
					__( 'Activate license keys for any commercial addons you have purchased. %sView all addons%s.', 'wp-table-bookings' ),
					'<a href="' . admin_url( 'admin.php?page=wtb-addons' ) . '">',
					'</a>'
				),
				'is_tab'		=> true,
			)
		);

		$sap->lib_extension_path = $this->sap_extension_path;

		global $wtb_controller;
		foreach( $this->licensed_products as $product ) {
			$sap->add_setting(
				'wtb-settings',
				'wtb-licenses',
				array(
					'id'			=> 'wtb-license-key',
					'filename'		=> $wtb_controller->licenses->sap_setting_file,
					'class'			=> $wtb_controller->licenses->sap_setting_class,
				),
				$product
			);
		}

		do_action( 'wtb_settings_licenses', $sap );


		return $sap;
	}

	/**
	 * Check if we're on the licenses page
	 *
	 * @since 1.4.1
	 */
	public function is_license_page() {

		global $wtb_controller;

		// Use the page reference in $admin_page_hooks because
		// it changes in SOME hooks when it is translated.
		// https://core.trac.wordpress.org/ticket/18857
		global $admin_page_hooks;

		$screen = get_current_screen();
		if ( empty( $screen ) || empty( $admin_page_hooks['wtb-bookings'] ) ) {
			return false;
		}

		if ( $screen->base != $admin_page_hooks['wtb-bookings'] . '_page_wtb-settings' || empty( $_GET['tab'] ) || $_GET['tab'] !== 'wtb-licenses' ) {
			return false;
		}

		return true;
	}

	/**
	 * Enqueue JavaScript and CSS files on the licenses page
	 *
	 * @since 1.4.1
	 */
	public function enqueue_assets() {

		if  ( !$this->is_license_page() ) {
			return;
		}

		wp_enqueue_style( 'wtb-admin', WTB_PLUGIN_URL . '/assets/css/admin.css' );
	}

	/**
	 * Show admin notices on license activation/deactivation attempts
	 *
	 * @since 1.4.1
	 */
	public function admin_notices() {

		if  ( !$this->is_license_page() || !isset( $_GET['license_result'] ) || $_GET['license_result'] != 0 || empty( $_GET['action'] ) ) {
			return;
		}

		$error = empty( $_GET['result_error'] ) ? '' : $_GET['result_error'];

		if ( $_GET['action'] == 'deactivate' ) {
			$msg = __( 'Your attempt to deactivate a license key failed. Please try again later or contact support for help.', 'wp-table-bookings' );
		} else {

			if ( $error == 'no_activations_left' ) {
				$msg = sprintf( __( 'You have reached the activation limit for this license. If you have the license activated on other sites you will need to deactivate them or purchase more license keys from %sTheme of the Crop%s.', 'wp-table-bookings' ), '<a href="http://themeofthecrop.com/">', '</a>' );
			} else {
				$msg = __( 'Your attempt to activate a license key failed. Please check the license key and try again.', 'wp-table-bookings' );
			}
		}

		?>

		<div class="error">
			<p><?php echo $msg; ?></p>
		</div>

		<?php

	}

	/**
	 * Load plugin updater library for Easy Digital Downloads Software
	 * Licensing API
	 *
	 * @since 0.3
	 */
	public function load_plugin_updater() {

		if ( !$this->is_enabled() ) {
			return;
		}

		if ( !class_exists( 'WTB_EDD_SL_Plugin_Updater' ) ) {

			if ( !file_exists( WTB_PLUGIN_DIR . '/lib/EDD_SL_Plugin_Updater.class.php' ) ) {
				return;
			}

			require_once( WTB_PLUGIN_DIR . '/lib/EDD_SL_Plugin_Updater.class.php' );
		}

		global $wtb_controller;

		$this->updaters = array();
		foreach( $this->licensed_products as $product ) {

			$license = $wtb_controller->settings->get_setting( $product['id'] );

			if ( empty( $license ) || empty( $license['api_key']) ) {
				continue;
			}

			new WTB_EDD_SL_Plugin_Updater(
				$product['store_url'],
				$product['plugin_path'],
				array(
					'version' 			=> $product['version'],
					'license' 			=> $license['api_key'],
					'item_name' 		=> $product['product'],
					'author'			=> $product['author'],
				)
			);
		}
	}
}
} // endif
