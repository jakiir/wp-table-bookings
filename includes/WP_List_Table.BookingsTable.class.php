<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( !class_exists( 'wtbBookingsTable' ) ) {
/**
 * Bookings Table Class
 *
 * Extends WP_List_Table to display the list of bookings in a format similar to
 * the default WordPress post tables.
 *
 * @h/t Easy Digital Downloads by Pippin: https://easydigitaldownloads.com/
 * @since 0.0.1
 */
class wtbBookingsTable extends WP_List_Table {

	/**
	 * Number of results to show per page
	 *
	 * @var string
	 * @since 0.0.1
	 */
	public $per_page = 30;

	/**
	 * URL of this page
	 *
	 * @var string
	 * @since 0.0.1
	 */
	public $base_url;

	/**
	 * Array of booking counts by total and status
	 *
	 * @var array
	 * @since 0.0.1
	 */
	public $booking_counts;

	/**
	 * Array of bookings
	 *
	 * @var array
	 * @since 0.0.1
	 */
	public $bookings;

	/**
	 * Current date filters
	 *
	 * @var string
	 * @since 0.0.1
	 */
	public $filter_start_date = null;
	public $filter_end_date = null;

	/**
	 * Current query string
	 *
	 * @var string
	 * @since 0.0.1
	 */
	public $query_string;

	/**
	 * Results of a bulk or quick action
	 *
	 * @var array
	 * @since 1.4.6
	 */
	public $action_result = array();

	/**
	 * Type of bulk or quick action last performed
	 *
	 * @var string
	 * @since 1.4.6
	 */
	public $last_action = '';

	/**
	 * Stored reference to visible columns
	 *
	 * @var string
	 * @since 1.5
	 */
	public $visible_columns = array();

	/**
	 * Initialize the table and perform any requested actions
	 *
	 * @since 0.0.1
	 */
	public function __construct() {

		global $status, $page;

		// Set parent defaults
		parent::__construct( array(
			'singular'  => __( 'Booking', 'wp-table-bookings' ),
			'plural'    => __( 'Bookings', 'wp-table-bookings' ),
			'ajax'      => false
		) );

		// Set the date filter
		$this->set_date_filter();

		// Strip unwanted query vars from the query string or ensure the correct
		// vars are used
		$this->query_string_maintenance();

		// Run any bulk action requests
		$this->process_bulk_action();

		// Run any quicklink requests
		$this->process_quicklink_action();

		// Retrieve a count of the number of bookings by status
		$this->get_booking_counts();

		// Retrieve bookings data for the table
		$this->bookings_data();

		$this->base_url = admin_url( 'admin.php?page=' . WTB_BOOKING_POST_TYPE );

		// Add default items to the details column if they've been hidden
		add_filter( 'wtb_bookings_table_column_details', array( $this, 'add_details_column_items' ), 10, 2 );
	}

	/**
	 * Set the correct date filter
	 *
	 * $_POST values should always overwrite $_GET values
	 *
	 * @since 0.0.1
	 */
	public function set_date_filter( $start_date = null, $end_date = null) {

		if ( !empty( $_GET['action'] ) && $_GET['action'] == 'clear_date_filters' ) {
			$this->filter_start_date = null;
			$this->filter_end_date = null;
		}

		$this->filter_start_date = $start_date;
		$this->filter_end_date = $end_date;

		if ( $start_date === null ) {
			$this->filter_start_date = !empty( $_GET['start_date'] ) ? sanitize_text_field( $_GET['start_date'] ) : null;
			$this->filter_start_date = !empty( $_POST['start_date'] ) ? sanitize_text_field( $_POST['start_date'] ) : $this->filter_start_date;
		}

		if ( $end_date === null ) {
			$this->filter_end_date = !empty( $_GET['end_date'] ) ? sanitize_text_field( $_GET['end_date'] ) : null;
			$this->filter_end_date = !empty( $_POST['end_date'] ) ? sanitize_text_field( $_POST['end_date'] ) : $this->filter_end_date;
		}
	}

	/**
	 * Get the current date range
	 *
	 * @since 1.3
	 */
	public function get_current_date_range() {

		$range = empty( $this->filter_start_date ) ? _x( '*', 'No date limit in a date range, eg 2014-* would mean any date from 2014 or after', 'wp-table-bookings' ) : $this->filter_start_date;
		$range .= empty( $this->filter_start_date ) || empty( $this->filter_end_date ) ? '' : _x( '&mdash;', 'Separator between two dates in a date range', 'wp-table-bookings' );
		$range .= empty( $this->filter_end_date ) ? _x( '*', 'No date limit in a date range, eg 2014-* would mean any date from 2014 or after', 'wp-table-bookings' ) : $this->filter_end_date;

		return $range;
	}

	/**
	 * Strip unwanted query vars from the query string or ensure the correct
	 * vars are passed around and those we don't want to preserve are discarded.
	 *
	 * @since 0.0.1
	 */
	public function query_string_maintenance() {

		$this->query_string = remove_query_arg( array( 'action', 'start_date', 'end_date' ) );

		if ( $this->filter_start_date !== null ) {
			$this->query_string = add_query_arg( array( 'start_date' => $this->filter_start_date ), $this->query_string );
		}

		if ( $this->filter_end_date !== null ) {
			$this->query_string = add_query_arg( array( 'end_date' => $this->filter_end_date ), $this->query_string );
		}

	}

	/**
	 * Show the time views, date filters and the search box
	 * @since 0.0.1
	 */
	public function advanced_filters() {

		// Show the date_range views (today, upcoming, all)
		if ( !empty( $_GET['date_range'] ) ) {
			$date_range = sanitize_text_field( $_GET['date_range'] );
		} else {
			$date_range = '';
		}

		// Use a custom date_range if a date range has been entered
		if ( $this->filter_start_date !== null || $this->filter_end_date !== null ) {
			$date_range = 'custom';
		}

		// Strip out existing date filters from the date_range view urls
		$date_range_query_string = remove_query_arg( array( 'date_range', 'start_date', 'end_date' ), $this->query_string );

		$views = array(
			'upcoming'	=> sprintf( '<a href="%s"%s>%s</a>', esc_url( add_query_arg( array( 'paged' => FALSE ), remove_query_arg( array( 'date_range' ), $date_range_query_string ) ) ), $date_range === '' ? ' class="current"' : '', __( 'Upcoming', 'wp-table-bookings' ) ),
			'today'	=> sprintf( '<a href="%s"%s>%s</a>', esc_url( add_query_arg( array( 'date_range' => 'today', 'paged' => FALSE ), $date_range_query_string ) ), $date_range === 'today' ? ' class="current"' : '', __( 'Today', 'wp-table-bookings' ) ),
			'all'		=> sprintf( '<a href="%s"%s>%s</a>', esc_url( add_query_arg( array( 'date_range' => 'all', 'paged' => FALSE ), $date_range_query_string ) ), $date_range == 'all' ? ' class="current"' : '', __( 'All', 'wp-table-bookings' ) ),
		);

		if ( $date_range == 'custom' ) {
			$views['custom'] = '<span class="current">' . $this->get_current_date_range() . '</span>';
		}

		$views = apply_filters( 'wtb_bookings_table_views_date_range', $views );
		?>

		<div id="wtb-filters" class="clearfix">
			<ul class="subsubsub wtb-views-date_range">
				<li><?php echo join( ' | </li><li>', $views ); ?></li>
			</ul>

			<div class="date-filters">
				<label for="start-date" class="screen-reader-text"><?php _e( 'Start Date:', 'wp-table-bookings' ); ?></label>
				<input type="text" id="start-date" name="start_date" class="datepicker" value="<?php echo esc_attr( $this->filter_start_date ); ?>" placeholder="<?php _e( 'Start Date', 'wp-table-bookings' ); ?>" />
				<label for="end-date" class="screen-reader-text"><?php _e( 'End Date:', 'wp-table-bookings' ); ?></label>
				<input type="text" id="end-date" name="end_date" class="datepicker" value="<?php echo esc_attr( $this->filter_end_date ); ?>" placeholder="<?php _e( 'End Date', 'wp-table-bookings' ); ?>" />
				<input type="submit" class="button-secondary" value="<?php _e( 'Apply', 'wp-table-bookings' ); ?>"/>
				<?php if( !empty( $start_date ) || !empty( $end_date ) ) : ?>
				<a href="<?php echo esc_url( add_query_arg( array( 'action' => 'clear_date_filters' ) ) ); ?>" class="button-secondary"><?php _e( 'Clear Filter', 'wp-table-bookings' ); ?></a>
				<?php endif; ?>
			</div>

			<?php if( !empty( $_GET['status'] ) ) : ?>
				<input type="hidden" name="status" value="<?php echo esc_attr( sanitize_text_field( $_GET['status'] ) ); ?>"/>
			<?php endif; ?>

			<?php
				// @todo Add support for the search box that uses more than just
				// 	the 's' argument in WP_Query. I need to search at least the
				// 	email post meta as well or this search box could be
				//	misleading for people who expect to search across all
				//	visible data
				// $this->search_box( __( 'Search', 'wp-table-bookings' ), 'wtb-bookings' );
			?>

			<?php
				// @todo use a datepicker. need to bring in styles for jquery ui or use pickadate
				// wp_enqueue_script('jquery-ui-datepicker');
			?>

		</div>

<?php
	}

	/**
	 * Retrieve the view types
	 * @since 0.0.1
	 */
	public function get_views() {

		$current = isset( $_GET['status'] ) ? $_GET['status'] : '';

		$views = array(
			'all'		=> sprintf( '<a href="%s"%s>%s</a>', esc_url( remove_query_arg( array( 'status', 'paged' ), $this->query_string ) ), $current === 'all' || $current == '' ? ' class="current"' : '', __( 'All', 'wp-table-bookings' ) . ' <span class="count">(' . $this->booking_counts['total'] . ')</span>' ),
			'pending'	=> sprintf( '<a href="%s"%s>%s</a>', esc_url( add_query_arg( array( 'status' => 'pending', 'paged' => FALSE ), $this->query_string ) ), $current === 'pending' ? ' class="current"' : '', __( 'Pending', 'wp-table-bookings' ) . ' <span class="count">(' . $this->booking_counts['pending'] . ')</span>' ),
			'confirmed'	=> sprintf( '<a href="%s"%s>%s</a>', esc_url( add_query_arg( array( 'status' => 'confirmed', 'paged' => FALSE ), $this->query_string ) ), $current === 'confirmed' ? ' class="current"' : '', __( 'Confirmed', 'wp-table-bookings' ) . ' <span class="count">(' . $this->booking_counts['confirmed'] . ')</span>' ),
			'closed'	=> sprintf( '<a href="%s"%s>%s</a>', esc_url( add_query_arg( array( 'status' => 'closed', 'paged' => FALSE ), $this->query_string ) ), $current === 'closed' ? ' class="current"' : '', __( 'Closed', 'wp-table-bookings' ) . ' <span class="count">(' . $this->booking_counts['closed'] . ')</span>' ),
			'trash' => sprintf( '<a href="%s"%s>%s</a>', esc_url( add_query_arg( array( 'status' => 'trash', 'paged' => FALSE ), $this->query_string ) ), $current === 'trash' ? ' class="current"' : '', __( 'Trash', 'wp-table-bookings' ) . ' <span class="count">(' . $this->booking_counts['trash'] . ')</span>' ),
		);

		return apply_filters( 'wtb_bookings_table_views_status', $views );
	}

	/**
	 * Extra controls to be displayed between bulk actions and pagination
	 *
	 * @param string pos Position of this tablenav: `top` or `btm`
	 * @since 1.4.1
	 */
	public function extra_tablenav( $pos ) {
		do_action( 'wtb_bookings_table_actions', $pos );
	}

	/**
	 * Generates content for a single row of the table
	 * @since 0.0.1
	 */
	public function single_row( $item ) {
		static $row_alternate_class = '';
		$row_alternate_class = ( $row_alternate_class == '' ? 'alternate' : '' );

		$row_classes = array( esc_attr( $item->post_status ) );

		if ( !empty( $row_alternate_class ) ) {
			$row_classes[] = $row_alternate_class;
		}

		$row_classes = apply_filters( 'wtb_admin_bookings_list_row_classes', $row_classes, $item );

		echo '<tr class="' . implode( ' ', $row_classes ) . '">';
		$this->single_row_columns( $item );
		echo '</tr>';
	}

	/**
	 * Retrieve the table columns
	 *
	 * @since 0.0.1
	 */
	public function get_columns() {

		// Prevent the lookup from running over and over again on a single
		// page load
		if ( !empty( $this->visible_columns ) ) {
			return $this->visible_columns;
		}

		$all_default_columns = $this->get_all_default_columns();

		global $wtb_controller;
		$visible_columns = $wtb_controller->settings->get_setting( 'bookings-table-columns' );
		if ( empty( $visible_columns ) ) {
			$columns = $all_default_columns;
		} else {
			$columns = array();
			$columns['cb'] = $all_default_columns['cb'];
			$columns['date'] = $all_default_columns['date'];

			foreach( $all_default_columns as $key => $column ) {
				if ( in_array( $key, $visible_columns ) ) {
					$columns[$key] = $all_default_columns[$key];
				}
			}
			$columns['details'] = $all_default_columns['details'];
		}

		$this->visible_columns = apply_filters( 'wtb_bookings_table_columns', $columns );

		return $this->visible_columns;
	}

	/**
	 * Retrieve all default columns
	 *
	 * @since 1.5
	 */
	public function get_all_default_columns() {
		return array(
			'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
			'date'     	=> __( 'Date', 'wp-table-bookings' ),
			'time'     	=> __( 'Time', 'wp-table-bookings' ),
			/*'party'  	=> __( 'Party', 'wp-table-bookings' ),*/
			'name'  	=> __( 'Name', 'wp-table-bookings' ),
			/*'email'  	=> __( 'Email', 'wp-table-bookings' ),*/
			'phone'  	=> __( 'Phone', 'wp-table-bookings' ),			
			'status'  	=> __( 'Status', 'wp-table-bookings' ),
			'details'  	=> __( 'Details', 'wp-table-bookings' ),
			'delete'  	=> __( 'Delete', 'wp-table-bookings' )
		);
	}

	/**
	 * Retrieve all available columns
	 *
	 * This is used to get all columns including those deactivated and filtered
	 * out via get_columns().
	 *
	 * @since 1.5
	 */
	public function get_all_columns() {
		$columns = $this->get_all_default_columns();
		return apply_filters( 'wtb_bookings_all_table_columns', $columns );
	}

	/**
	 * Retrieve the table's sortable columns
	 * @since 0.0.1
	 */
	public function get_sortable_columns() {
		$columns = array(
			'date' 		=> array( 'date', true ),
			'name' 		=> array( 'title', true ),
		);
		return apply_filters( 'wtb_bookings_table_sortable_columns', $columns );
	}

	/**
	 * This function renders most of the columns in the list table.
	 * @since 0.0.1
	 */
	public function column_default( $booking, $column_name ) {
		switch ( $column_name ) {
			case 'date' :
				$value = $booking->format_date( $booking->date );
				$value .= '<div class="status"><span class="spinner"></span> ' . __( 'Loading', 'wp-table-bookings' ) . '</div>';

				if ( $booking->post_status !== 'trash' ) {
					$value .= '<div class="actions">';
					$value .= '<a href="#" data-id="' . esc_attr( $booking->ID ) . '" data-action="edit">' . __( 'Edit', 'wp-table-bookings' ) . '</a>';
					$value .= ' | <a href="#" class="trash" data-id="' . esc_attr( $booking->ID ) . '" data-action="trash">' . __( 'Trash', 'wp-table-bookings' ) . '</a>';
					$value .= '</div>';
				}

				break;
			case 'time' :
				$value = $booking->format_time( $booking->date );
				break;
			case 'delete' :
				$value = '<div class="actions" style="opacity: 1;">';
				$value .= '<a href="#" class="trash" data-id="' . esc_attr( $booking->ID ) . '" data-action="trash">' . __( 'Delete', 'wp-table-bookings' ) . '</a>';
				$value .= '</div>';
			break;
			case 'party' :
				$value = $booking->party;
				break;
			case 'name' :
				$value = $booking->name;
				break;
			case 'email' :
				$value = $booking->email;
				$value .= '<div class="actions">';
				$value .= '<a href="#" data-id="' . esc_attr( $booking->ID ) . '" data-action="email" data-email="' . esc_attr( $booking->email ) . '" data-name="' . $booking->name . '">' . __( 'Send Email', 'wp-table-bookings' ) . '</a>';
				$value .= '</div>';
				break;
			case 'phone' :
				$value = $booking->phone;
				break;
			case 'status' :
				global $wtb_controller;
				if ( !empty( $wtb_controller->cpts->booking_statuses[$booking->post_status] ) ) {
					$value = $wtb_controller->cpts->booking_statuses[$booking->post_status]['label'];
				} elseif ( $booking->post_status == 'trash' ) {
					$value = _x( 'Trash', 'Status label for bookings put in the trash', 'wp-table-bookings' );
				} else {
					$value = $booking->post_status;
				}
				break;
			case 'details' :
				$value = '';

				$details = array();
				if ( trim( $booking->message ) ) {
					$details[] = array(
						'label' => __( 'Message', 'wp-table-bookings' ),
						'value' => $booking->message,
					);
				}

				$details = apply_filters( 'wtb_bookings_table_column_details', $details, $booking );

				if ( !empty( $details ) ) {
					$value = '<a href="#" class="wtb-show-details" data-id="details-' . esc_attr( $booking->ID ) . '"><span class="dashicons dashicons-testimonial"></span></a>';
					$value .= '<div class="wtb-details-data"><ul class="details">';
					foreach( $details as $detail ) {
						$value .= '<li><div class="label">' . $detail['label'] . '</div><div class="value">' . $detail['value'] . '</div></li>';
					}
					$value .= '</ul></div>';
				}
				break;
			default:
				$value = isset( $booking->$column_name ) ? $booking->$column_name : '';
				break;

		}

		return apply_filters( 'wtb_bookings_table_column', $value, $booking, $column_name );
	}

	/**
	 * Render the checkbox column
	 * @since 0.0.1
	 */
	public function column_cb( $booking ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			'bookings',
			$booking->ID
		);
	}

	/**
	 * Add hidden columns values to the details column
	 *
	 * This only handles the default columns. Custom data needs to hook in and
	 * add it's own items to the $details array.
	 *
	 * @since 1.5
	 */
	public function add_details_column_items( $details, $booking ) {
		global $wtb_controller;
		$visible_columns = $this->get_columns();
		$default_columns = $this->get_all_default_columns();

		// Columns which can't be hidden
		unset( $default_columns['cb'] );
		unset( $default_columns['details'] );
		unset( $default_columns['date'] );

		$detail_columns = array_diff( $default_columns, $visible_columns );

		if ( !empty( $detail_columns ) ) {
			foreach( $detail_columns as $key => $label ) {

				$value = $this->column_default( $booking, $key );
				if ( empty( $value ) ) {
					continue;
				}

				$details[] = array(
					'label' => $label,
					'value' => $value,
				);
			}
		}

		return $details;
	}

	/**
	 * Retrieve the bulk actions
	 * @since 0.0.1
	 */
	public function get_bulk_actions() {
		$actions = array(
			'delete'                 => __( 'Delete',                'wp-table-bookings' ),
			'set-status-confirmed'   => __( 'Set To Confirmed',      'wp-table-bookings' ),
			'set-status-pending'     => __( 'Set To Pending Review', 'wp-table-bookings' ),
			'set-status-closed'      => __( 'Set To Closed',         'wp-table-bookings' )
		);

		return apply_filters( 'wtb_bookings_table_bulk_actions', $actions );
	}

	/**
	 * Process the bulk actions
	 * @since 0.0.1
	 */
	public function process_bulk_action() {
		$ids    = isset( $_POST['bookings'] ) ? $_POST['bookings'] : false;
		$action = isset( $_POST['action'] ) ? $_POST['action'] : false;

		// Check bulk actions selector below the table
		$action = $action == '-1' && isset( $_POST['action2'] ) ? $_POST['action2'] : $action;

		if( empty( $action ) || $action == '-1' ) {
			return;
		}

		if ( !current_user_can( 'manage_bookings' ) ) {
			return;
		}

		if ( ! is_array( $ids ) ) {
			$ids = array( $ids );
		}

		global $wtb_controller;
		$results = array();
		foreach ( $ids as $id ) {
			if ( 'delete' === $action ) {
				$results[$id] = $wtb_controller->cpts->delete_booking( $id );
			}

			if ( 'set-status-confirmed' === $action ) {
				$results[$id] = $wtb_controller->cpts->update_booking_status( $id, 'confirmed' );
			}

			if ( 'set-status-pending' === $action ) {
				$results[$id] = $wtb_controller->cpts->update_booking_status( $id, 'pending' );
			}

			if ( 'set-status-closed' === $action ) {
				$results[$id] = $wtb_controller->cpts->update_booking_status( $id, 'closed' );
			}

			$results = apply_filters( 'wtb_bookings_table_bulk_action', $results, $id, $action );
		}

		if( count( $results ) ) {
			$this->action_result = $results;
			$this->last_action = $action;
			add_action( 'wtb_bookings_table_top', array( $this, 'admin_notice_bulk_actions' ) );
		}
	}

	/**
	 * Process quicklink actions sent out in notification emails
	 * @since 0.0.1
	 */
	public function process_quicklink_action() {

		if ( empty( $_REQUEST['wtb-quicklink'] ) ) {
			return;
		}

		if ( !current_user_can( 'manage_bookings' ) ) {
			return;
		}

		global $wtb_controller;

		$results = array();

		$id = !empty( $_REQUEST['booking'] ) ? $_REQUEST['booking'] : false;

		if ( $_REQUEST['wtb-quicklink'] == 'confirm' ) {
			$results[$id] = $wtb_controller->cpts->update_booking_status( $id, 'confirmed' );
			$this->last_action = 'set-status-confirmed';
		} elseif ( $_REQUEST['wtb-quicklink'] == 'close' ) {
			$results[$id] = $wtb_controller->cpts->update_booking_status( $id, 'closed' );
			$this->last_action = 'set-status-closed';
		}

		if( count( $results ) ) {
			$this->action_result = $results;
			add_action( 'wtb_bookings_table_top', array( $this, 'admin_notice_bulk_actions' ) );
		}
	}

	/**
	 * Display an admin notice when a bulk action is completed
	 * @since 0.0.1
	 */
	public function admin_notice_bulk_actions() {

		$success = 0;
		$failure = 0;
		foreach( $this->action_result as $id => $result ) {
			if ( $result === true || $result === null ) {
				$success++;
			} else {
				$failure++;
			}
		}

		if ( $success > 0 ) :
		?>

		<div id="wtb-admin-notice-bulk-<?php esc_attr( $this->last_action ); ?>" class="updated">

			<?php if ( $this->last_action == 'delete' ) : ?>
			<p><?php echo sprintf( _n( '%d booking deleted successfully.', '%d bookings deleted successfully.', $success, 'wp-table-bookings' ), $success ); ?></p>

			<?php elseif ( $this->last_action == 'set-status-confirmed' ) : ?>
			<p><?php echo sprintf( _n( '%d booking confirmed.', '%d bookings confirmed.', $success, 'wp-table-bookings' ), $success ); ?></p>

			<?php elseif ( $this->last_action == 'set-status-pending' ) : ?>
			<p><?php echo sprintf( _n( '%d booking set to pending.', '%d bookings set to pending.', $success, 'wp-table-bookings' ), $success ); ?></p>

			<?php elseif ( $this->last_action == 'set-status-closed' ) : ?>
			<p><?php echo sprintf( _n( '%d booking closed.', '%d bookings closed.', $success, 'wp-table-bookings' ), $success ); ?></p>

			<?php endif; ?>
		</div>

		<?php
		endif;

		if ( $failure > 0 ) :
		?>

		<div id="wtb-admin-notice-bulk-<?php esc_attr( $this->last_action ); ?>" class="error">
			<p><?php echo sprintf( _n( '%d booking had errors and could not be processed.', '%d bookings had errors and could not be processed.', $failure, 'wp-table-bookings' ), $failure ); ?></p>
		</div>

		<?php
		endif;
	}

	/**
	 * Retrieve the counts of bookings
	 * @since 0.0.1
	 */
	public function get_booking_counts() {

		global $wpdb;

		$where = "WHERE p.post_type = '" . WTB_BOOKING_POST_TYPE . "'";

		if ( $this->filter_start_date !== null || $this->filter_end_date !== null ) {

			if ( $this->filter_start_date !== null ) {
				$start_date = new DateTime( $this->filter_start_date );
				$where .= " AND p.post_date >= '" . $start_date->format( 'Y-m-d H:i:s' ) . "'";
			}

			if ( $this->filter_end_date !== null ) {
				$end_date = new DateTime( $this->filter_end_date );
				$where .= " AND p.post_date <= '" . $end_date->format( 'Y-m-d H:i:s' ) . "'";
			}

		} elseif ( !empty( $_GET['date_range'] ) ) {

			if ( $_GET['date_range'] ==  'today' ) {
				$where .= " AND p.post_date >= '" . date( 'Y-m-d', current_time( 'timestamp' ) ) . "' AND p.post_date < '" . date( 'Y-m-d', current_time( 'timestamp' ) + 86400 ) . "'";
			}

		// Default date setting is to show upcoming bookings
		} else {
			$where .= " AND p.post_date >= '" . date( 'Y-m-d H:i:s', current_time( 'timestamp' ) - 3600 ) . "'";
		}


		$query = "SELECT p.post_status,count( * ) AS num_posts
			FROM $wpdb->posts p
			$where
			GROUP BY p.post_status
		";

		$count = $wpdb->get_results( $query, ARRAY_A );

		$this->booking_counts = array();
		foreach ( get_post_stati() as $state ) {
			$this->booking_counts[$state] = 0;
		}

		$this->booking_counts['total'] = 0;
		foreach ( (array) $count as $row ) {
			$this->booking_counts[$row['post_status']] = $row['num_posts'];
			$this->booking_counts['total'] += $row['num_posts'];
		}

	}

	/**
	 * Retrieve all the data for all the bookings
	 * @since 0.0.1
	 */
	public function bookings_data() {

		$args = array(
			'posts_per_page'	=> $this->per_page,
		);

		if ( !empty( $this->filter_start_date ) ) {
			$args['start_date'] = $this->filter_start_date;
		}

		if ( !empty( $this->filter_end_date ) ) {
			$args['end_date'] = $this->filter_end_date;
		}

		$query = new wtbQuery( $args, 'bookings-table' );
		$query->parse_request_args();
		$query->prepare_args();
		$query->args = apply_filters( 'wtb_bookings_table_query_args', $query->args );

		$this->bookings = $query->get_bookings();
	}

	/**
	 * Setup the final data for the table
	 * @since 0.0.1
	 */
	public function prepare_items() {

		$columns  = $this->get_columns();
		$hidden   = array(); // No hidden columns
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->items = $this->bookings;

		$total_items   = empty( $_GET['status'] ) ? $this->booking_counts['total'] : $this->booking_counts[$_GET['status']];

		$this->set_pagination_args( array(
				'total_items' => $total_items,
				'per_page'    => $this->per_page,
				'total_pages' => ceil( $total_items / $this->per_page )
			)
		);
	}

	/**
	 * Add notifications above the table to indicate which bookings are
	 * being shown.
	 * @since 1.3
	 */
	public function display_rows_or_placeholder() {

		global $wtb_controller;

		$notifications = array();

		$status = '';
		if ( !empty( $_GET['status'] ) ) {
			$status = $_GET['status'];
			if ( $status == 'trash' ) {
				$notifications['status'] = __( "You're viewing bookings that have been moved to the trash.", 'wp-table-bookings' );
			} elseif ( !empty( $wtb_controller->cpts->booking_statuses[ $status ] ) ) {
				$notifications['status'] = sprintf( _x( "You're viewing bookings that have been marked as %s.", 'Indicates which booking status is currently being filtered in the list of bookings.', 'wp-table-bookings' ), $wtb_controller->cpts->booking_statuses[ $_GET['status'] ]['label'] );
			}
		}

		if ( !empty( $this->filter_start_date ) || !empty( $this->filter_end_date ) ) {
			$notifications['date'] = sprintf( _x( 'Only bookings from %s are being shown.', 'Notification of booking date range, eg - bookings from 2014-12-02-2014-12-05', 'wp-table-bookings' ), $this->get_current_date_range() );
		} elseif ( !empty( $_GET['date_range'] ) && $_GET['date_range'] == 'today' ) {
			$notifications['date'] = __( "Only today's bookings are being shown.", 'wp-table-bookings' );
		} elseif ( empty( $_GET['date_range'] ) ) {
			$notifications['date'] = __( 'Only upcoming bookings are being shown.', 'wp-table-bookings' );
		}

		$notifications = apply_filters( 'wtb_admin_bookings_table_filter_notifications', $notifications );

		if ( !empty( $notifications ) ) :
		?>

			<tr class="notice <?php echo esc_attr( $status ); ?>">
				<td colspan="<?php echo count( $this->get_columns() ); ?>">
					<?php echo join( ' ', $notifications ); ?>
				</td>
			</tr>

		<?php
		endif;

		parent::display_rows_or_placeholder();
	}
}
} // endif;
