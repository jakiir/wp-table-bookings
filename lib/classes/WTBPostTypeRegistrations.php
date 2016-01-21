<?php 
	if(!class_exists('WTBPostTypeRegistrations')):

	class WTBPostTypeRegistrations {
		public function __construct() {
			// Add the table booking post type and taxonomies
			add_action( 'init', array( $this, 'register' ) );
		}
		
		/**
		 * Initiate registrations of post type and taxonomies.
		 *
		 * @uses Portfolio_Post_Type_Registrations::register_post_type()
		 */
		public function register() {
			$this->register_post_type();			
		}
		/**
		 * Register the custom post type.
		 *
		 * @link http://codex.wordpress.org/Function_Reference/register_post_type
		 */
		protected function register_post_type() {
			$team_labels = array(
				'name'                => _x( 'WP Table Bookings', WTB_SLUG ),
				'singular_name'       => _x( 'wp-table-bookings', WTB_SLUG ),
				'menu_name'           => __( 'WP Table Bookings', WTB_SLUG ),
				'name_admin_bar'      => __( 'Table Bookings', WTB_SLUG ),
				'parent_item_colon'   => __( 'Parent Booking:', WTB_SLUG ),
				'all_items'           => __( 'All Bookings', WTB_SLUG ),
				'add_new_item'        => __( 'Add New Booking', WTB_SLUG ),
				'add_new'             => __( 'Add Booking', WTB_SLUG ),
				'new_item'            => __( 'New Booking', WTB_SLUG ),
				'edit_item'           => __( 'Edit Bookings', WTB_SLUG ),
				'update_item'         => __( 'Update Bookings', WTB_SLUG ),
				'view_item'           => __( 'View Bookings', WTB_SLUG ),
				'search_items'        => __( 'Search Bookings', WTB_SLUG ),
				'not_found'           => __( 'Not found', WTB_SLUG ),
				'not_found_in_trash'  => __( 'Not found in Trash', WTB_SLUG ),
			);
			$team_args = array(
				'label'               => __( 'WP Table Bookings', WTB_SLUG ),
				'description'         => __( 'WP Table Bookings', WTB_SLUG ),
				'labels'              => $team_labels,
				'supports'            => array( 'title', 'editor','thumbnail', 'page-attributes' ),
				'taxonomies'          => array(),
				'hierarchical'        => false,
				'public'              => true,
				'rewrite'				=> true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'menu_position'       => 20,
				'menu_icon'			=> 'dashicons-calendar-alt',
				'show_in_admin_bar'   => true,
				'show_in_nav_menus'   => true,
				'can_export'          => true,
				'has_archive'         => false,
				'exclude_from_search' => false,
				'publicly_queryable'  => true,
				'capability_type'     => 'page',
			);
			global $wtbInit;
			register_post_type( $wtbInit->post_type, $team_args );
			flush_rewrite_rules();
		}
		
	}
endif;
?>