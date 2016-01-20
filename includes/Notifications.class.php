<?php
if ( !defined( 'ABSPATH' ) ) exit;

if ( !class_exists( 'wtbNotifications' ) ) {
/**
 * Class to process notifications for Table Bookings
 *
 * This class contains the registered notifications and sends them when the
 * event is triggered.
 *
 * @since 0.0.1
 */
class wtbNotifications {

	/**
	 * Booking object (class wtbBooking)
	 *
	 * @var object
	 * @since 0.0.1
	 */
	public $booking;

	/**
	 * Array of wtbNotification objects
	 *
	 * @var array
	 * @since 0.0.1
	 */
	public $notifications;

	/**
	 * Register notifications hook early so that other early hooks can
	 * be used by the notification system.
	 * @since 0.0.1
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'register_notifications' ) );
	}

	/**
	 * Register notifications
	 * @since 0.0.1
	 */
	public function register_notifications() {

		// Hook into all events that require notifications
		$hooks = array(
			'wtb_insert_booking'	=> array( $this, 'new_submission' ), 		// Booking submitted
			'pending_to_confirmed'	=> array( $this, 'pending_to_confirmed' ), 	// Booking confirmed
			'pending_to_closed'		=> array( $this, 'pending_to_closed' ), 	// Booking can not be made
		);

		$hooks = apply_filters( 'wtb_notification_transition_callbacks', $hooks );

		foreach ( $hooks as $hook => $callback ) {
			add_action( $hook, $callback );
		}

		// Register notifications
		require_once( WTB_PLUGIN_DIR . '/includes/Notification.class.php' );
		require_once( WTB_PLUGIN_DIR . '/includes/Notification.Email.class.php' );

		$this->notifications = array(
			new wtbNotificationEmail( 'new_submission', 'user' ),
			new wtbNotificationEmail( 'pending_to_confirmed', 'user' ),
			new wtbNotificationEmail( 'pending_to_closed', 'user' ),
		);

		global $wtb_controller;
		$admin_email_option = $wtb_controller->settings->get_setting( 'admin-email-option' );
		if ( $admin_email_option ) {
			$this->notifications[] = new wtbNotificationEmail( 'new_submission', 'admin' );
		}

		$this->notifications = apply_filters( 'wtb_notifications', $this->notifications );
	}

	/**
	 * Set booking data
	 * @since 0.0.1
	 */
	public function set_booking( $booking_post ) {
		require_once( WTB_PLUGIN_DIR . '/includes/Booking.class.php' );
		$this->booking = new wtbBooking();
		$this->booking->load_wp_post( $booking_post );
	}

	/**
	 * New booking submissions
	 *
	 * @var object $booking
	 * @since 0.0.1
	 */
	public function new_submission( $booking ) {

		// Bail early if $booking is not a wtbBooking object
		if ( get_class( $booking ) != 'wtbBooking' ) {
			return;
		}

		// If the post status is not pending, trigger a post status
		// transition as though it's gone from pending_to_{status}
		if ( $booking->post_status != 'pending' ) {
			do_action( 'pending_to_' . $booking->post_status, get_post( $booking->ID ) );

		// Otherwise proceed with the new_submission event
		} else {
			$this->booking = $booking;
			$this->event( 'new_submission' );
		}
	}

	/**
	 * Booking confirmed
	 * @since 0.0.1
	 */
	public function pending_to_confirmed( $booking_post ) {

		if ( $booking_post->post_type != WTB_BOOKING_POST_TYPE ) {
			return;
		}

		$this->set_booking( $booking_post );

		$this->event( 'pending_to_confirmed' );

	}

	/**
	 * Booking can not be made
	 * @since 0.0.1
	 */
	public function pending_to_closed( $booking_post ) {

		if ( $booking_post->post_type != WTB_BOOKING_POST_TYPE ) {
			return;
		}

		$this->set_booking( $booking_post );

		$this->event( 'pending_to_closed' );

	}

	/**
	 * Booking was confirmed and is now completed. Send out an optional
	 * follow-up email.
	 *
	 * @since 0.0.1
	 */
	public function confirmed_to_closed( $booking_post ) {

		if ( $booking_post->post_type != WTB_BOOKING_POST_TYPE ) {
			return;
		}

		$this->set_booking( $booking_post );

		$this->event( 'confirmed_to_closed' );

	}

	/**
	 * Process notifications for an event
	 * @since 0.0.1
	 */
	public function event( $event ) {

		foreach( $this->notifications as $notification ) {

			if ( $event == $notification->event ) {
				$notification->set_booking( $this->booking );
				if ( $notification->prepare_notification() ) {
					$notification->send_notification();
				}
			}
		}

	}

}
} // endif;
