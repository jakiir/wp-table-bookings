<?php
/**
 * Plugin Name: WP Table Booking
 * Plugin URI: 
 * Description: Accept table booking and reservation online.
 * Version: 1.0
 * Author: Jakir Hossain
 * Author URI: http://jakirhossain.com
 * License:     GNU General Public License v2.0 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Text Domain: wp-table-bookings
 * Domain Path: /languages/
 *
 * You should have received a copy of the GNU General Public License along with this program;
 */
 if ( ! defined( 'ABSPATH' ) )
	exit;

define( 'WTB_VERSION', '1.0' );
define( 'WTB_TITLE', 'WP Table Bookings');
define( 'WTB_SLUG', 'wtb-booking');
define( 'WTB_PLUGIN_PATH', dirname( __FILE__ ));
define( 'WTB_PLUGIN_URL', plugins_url( '' , __FILE__ ));
define( 'WTB_LENGUAGE_PATH', dirname( plugin_basename( __FILE__ ) ) . '/languages');
require('lib/init.php');

?>