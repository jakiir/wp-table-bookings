/* Javascript for Restaurant Reservations admin */
jQuery(document).ready(function ($) {

	// Add date picker to date filter in admin
	$( '#start-date, #end-date' ).each( function() {
		var input = $(this);

		input.pickadate({
			format: wtb_pickadate.date_format,
			formatSubmit: 'yyyy/mm/dd',
			hiddenName: true,

			onStart: function() {
				if ( input.val()	!== '' ) {
					var date = new Date( input.val() );
					if ( Object.prototype.toString.call( date ) === "[object Date]" ) {
						this.set( 'select', date );
					}
				}
			}
		});
	});

	// Show or hide extra booking details in the bookings table
	$( '.wtb-show-details' ).click( function (e) {
		e.preventDefault();
		wtb_toggle_details_modal( true, $(this).siblings( '.wtb-details-data' ).html() );
	});

	// Register clicks on action links
	$( '#wtb-bookings-table tr .actions' ).click( function(e) {

		e.stopPropagation();

		var target = $(e.target);
		var action = target.data( 'action' );

		if ( !action ) {
			return;
		}

		var cell = target.parent().parent();

		if ( target.data( 'action' ) == 'edit' ) {
			wtb_booking_loading_spinner( true, cell );
			wtb_get_booking( target.data( 'id' ), cell );

		} else if ( target.data( 'action' ) == 'trash' ) {
			wtb_booking_loading_spinner( true, cell );
			wtb_trash_booking( target.data( 'id' ), cell );

		} else if ( target.data( 'action' ) == 'email') {
			wtb_toggle_email_modal( true, target.data( 'id'), target.data( 'email' ), target.data( 'name' ) );
		}

		e.preventDefault();
	});

	// Show booking form modal
	$( '.add-booking' ).click( function( e ) {
		e.preventDefault();
		wtb_toggle_booking_form_modal( true );
	});

	// Show column configuration modal
	$( '.wtb-columns-button' ).click( function( e ) {
		e.preventDefault();
		wtb_toggle_column_modal( true );
	});

	/**
	 * Show/hide loading spinner when edit/delete link clicked
	 */
	function wtb_booking_loading_spinner( loading, cell ) {
		if ( loading ) {
			cell.addClass( 'loading' );
		} else {
			cell.removeClass( 'loading' );
		}
	}

	/**
	 * Modals for the admin page
	 */
	var wtb_booking_modal = $( '#wtb-booking-modal' );
	var wtb_booking_modal_fields = wtb_booking_modal.find( '#wtb-booking-form-fields' );
	var wtb_booking_modal_submit = wtb_booking_modal.find( 'button' );
	var wtb_booking_modal_cancel = wtb_booking_modal.find( '#wtb-cancel-booking-modal' );
	var wtb_booking_modal_action_status = wtb_booking_modal.find( '.action-status' );
	var wtb_email_modal = $( '#wtb-email-modal' );
	var wtb_email_modal_submit = wtb_email_modal.find( 'button' );
	var wtb_email_modal_cancel = wtb_email_modal.find( '#wtb-cancel-email-modal' );
	var wtb_email_modal_action_status = wtb_email_modal.find( '.action-status' );
	var wtb_column_modal = $( '#wtb-column-modal' );
	var wtb_column_modal_submit = wtb_column_modal.find( 'button' );
	var wtb_column_modal_cancel = wtb_column_modal.find( '#wtb-cancel-column-modal' );
	var wtb_column_modal_action_status = wtb_column_modal.find( '.action-status' );
	var wtb_details_modal = $( '#wtb-details-modal' );
	var wtb_details_modal_close = wtb_details_modal.find( '#wtb-close-details-modal' );
	var wtb_details_modal_cancel = wtb_details_modal.find( '#wtb-cancel-details-modal' );
	var wtb_booking_modal_error = $( '#wtb-error-modal' );
	var wtb_booking_modal_error_msg = wtb_booking_modal_error.find( '.wtb-error-msg' );

	/**
	 * Show or hide the booking form modal
	 */
	function wtb_toggle_booking_form_modal( show, fields, booking ) {

		if ( show ) {
			wtb_booking_modal.scrollTop( 0 ).addClass( 'is-visible' );

			if ( typeof fields !== 'undefined' ) {
				wtb_booking_modal_fields.html( fields );
				wtb_init_booking_form_modal_fields();
			}

			if ( typeof booking == 'undefined' ) {
				wtb_booking_modal_fields.find( '#wtb-post-status' ).val( 'confirmed' );
				wtb_booking_modal_submit.html( wtb_admin.strings.add_booking );
			} else {
				wtb_booking_modal_submit.html( wtb_admin.strings.edit_booking );
				wtb_booking_modal.find( 'input[name=ID]' ).val( booking.ID );
			}

			$( 'body' ).addClass( 'wtb-hide-body-scroll' );

		} else {
			wtb_booking_modal.removeClass( 'is-visible' );
			wtb_booking_modal.find( '.wtb-error' ).remove();
			wtb_booking_modal.find( '.notifications-description' ).removeClass( 'is-visible' );
			wtb_booking_modal_action_status.removeClass( 'is-visible' );
			wtb_reset_booking_form_modal_fields();
			wtb_booking_modal_submit.removeData( 'id' );
			wtb_booking_modal_submit.prop( 'disabled', false );
			wtb_booking_modal_cancel.prop( 'disabled', false );
			wtb_booking_modal.find( 'input[name=ID]' ).val( '' );

			$( 'body' ).removeClass( 'wtb-hide-body-scroll' );
		}
	}

	/**
	 * Show or hide the booking form error modal
	 */
	function wtb_toggle_booking_form_error_modal( show, msg ) {

		if ( show ) {
			wtb_booking_modal_error_msg.html( msg );
			wtb_booking_modal_error.addClass( 'is-visible' );

		} else {
			wtb_booking_modal_error.removeClass( 'is-visible' );
		}
	}

	/**
	 * Show or hide the email form modal
	 */
	function wtb_toggle_email_modal( show, id, email, name ) {

		if ( show ) {
			wtb_email_modal.scrollTop( 0 ).addClass( 'is-visible' );
			wtb_email_modal.find( 'input[name=ID]' ).val( id );
			wtb_email_modal.find( 'input[name=email]' ).val( email );
			wtb_email_modal.find( 'input[name=name]' ).val( name );
			wtb_email_modal.find( '.wtb-email-to' ).html( name + ' &lt;' + email + '&gt;' );

			$( 'body' ).addClass( 'wtb-hide-body-scroll' );

		} else {
			wtb_email_modal.removeClass( 'is-visible' );
			wtb_email_modal.find( '.wtb-email-to' ).html( '' );
			wtb_email_modal.find( 'textarea, input[type="hidden"], input[type="text"]' ).val( '' );
			wtb_email_modal_submit.prop( 'disabled', false );
			wtb_email_modal_cancel.prop( 'disabled', false );

			$( 'body' ).removeClass( 'wtb-hide-body-scroll' );
		}
	}

	/**
	 * Show or hide the column configuration modal
	 */
	function wtb_toggle_column_modal( show ) {

		if ( show ) {
			wtb_column_modal.scrollTop( 0 ).addClass( 'is-visible' );
			$( 'body' ).addClass( 'wtb-hide-body-scroll' );

		} else {
			wtb_column_modal.removeClass( 'is-visible' );
			$( 'body' ).removeClass( 'wtb-hide-body-scroll' );
		}
	}

	/**
	 * Show or hide the booking details modal
	 */
	function wtb_toggle_details_modal( show, content ) {

		if ( show ) {
			wtb_details_modal.addClass( 'is-visible' ).scrollTop( 0 )
				.find( '.wtb-details-data' ).html( content );
			$( 'body' ).addClass( 'wtb-hide-body-scroll' );
			wtb_details_modal.find( '.actions' ).click( function(e) {
				var target = $( e.target );
				wtb_toggle_details_modal( false );
				wtb_toggle_email_modal( true, target.data( 'id'), target.data( 'email' ), target.data( 'name' ) );
			});

		} else {
			wtb_details_modal.removeClass( 'is-visible' );
			$( 'body' ).removeClass( 'wtb-hide-body-scroll' );
			setTimeout( function() {
				wtb_details_modal.find( '.wtb-details-data' ).empty();
			}, 300 );
		}
	}

	/**
	 * Initialize form field events
	 */
	function wtb_init_booking_form_modal_fields() {

		// Run init on the form
		wtb_booking_form.init();

		// Show full description for notifications toggle
		wtb_booking_modal_fields.find( '.wtb-description-prompt' ).click( function() {
			$(this).parent().siblings( '.wtb-description' ).addClass( 'is-visible' );
		});
	}

	/**
	 * Reset booking form fields
	 */
	function wtb_reset_booking_form_modal_fields() {
		wtb_booking_modal_fields.find( 'input,select, textarea' ).not( 'input[type="checkbox"],input[type="radio"]' ).val( '' );
		wtb_booking_modal_fields.find( 'input[name=wtb-notifications]' ).removeAttr( 'checked' );
	}

	/**
	 * Retrieve booking from the database
	 */
	function wtb_get_booking( id, cell ) {

		var params = {};

		params.action = 'wtb-admin-booking-modal';
		params.nonce = wtb_admin.nonce;
		params.booking = {
			'ID':	id
		};

		var data = $.param( params );

		var jqhxr = $.get( ajaxurl, data, function( r ) {

			if ( r.success ) {
				wtb_toggle_booking_form_modal( true, r.data.fields, r.data.booking );

			} else {

				if ( typeof r.data.error == 'undefined' ) {
					wtb_toggle_booking_form_error_modal( true, wtb_admin.strings.error_unspecified );
				} else {
					wtb_toggle_booking_form_error_modal( true, r.data.msg );
				}
			}

			wtb_booking_loading_spinner( false, cell );
		});
	}

	/**
	 * Trash booking
	 */
	function wtb_trash_booking( id, cell ) {

		var params = {};

		params.action = 'wtb-admin-trash-booking';
		params.nonce = wtb_admin.nonce;
		params.booking = id;

		var data = $.param( params );

		var jqhxr = $.post( ajaxurl, data, function( r ) {

			if ( r.success ) {

				cell.parent().fadeOut( 500, function() {
					$(this).remove();
				});

				var trash_count_el = $( '#wtb-bookings-table .subsubsub .trash .count' );
				var trash_count = parseInt( trash_count_el.html().match(/\d+/), 10 ) + 1;
				trash_count_el.html( '(' + trash_count + ')' );

			} else {

				if ( typeof r.data == 'undefined' || typeof r.data.error == 'undefined' ) {
					wtb_toggle_booking_form_error_modal( true, wtb_admin.strings.error_unspecified );
				} else {
					wtb_toggle_booking_form_error_modal( true, r.data.msg );
				}
			}

			wtb_booking_loading_spinner( false, cell );
		});

	}

	/**
	 * Show the appropriate result status icon
	 */
	function wtb_show_action_status( el, status ) {

		el.find( 'span' ).hide();

		if ( status === true ) {
			el.find( '.success' ).show();
		} else if ( status === false ) {
			el.find( '.error' ).show();
		} else {
			el.find( '.spinner' ).show();
		}
	}

	// Reset the forms on load
	// This fixes a strange bug in Firefox where disabled buttons would
	// persist after the page refreshed. I'm guessing its a cache issue
	// but this will just reset everything again
	wtb_toggle_booking_form_modal( false );
	wtb_toggle_email_modal( false );
	wtb_toggle_column_modal( false );

	// Close booking form modal when background or cancel button is clicked
	wtb_booking_modal.click( function(e) {
		if ( $(e.target).is( wtb_booking_modal ) ) {
			wtb_toggle_booking_form_modal( false );
		}

		if ( $(e.target).is( wtb_booking_modal_cancel ) && wtb_booking_modal_cancel.prop( 'disabled' ) === false ) {
			wtb_toggle_booking_form_modal( false );
		}
	});

	// Close email modal when background or cancel button is clicked
	wtb_email_modal.click( function(e) {
		if ( $(e.target).is( wtb_email_modal ) ) {
			wtb_toggle_email_modal( false );
		}

		if ( $(e.target).is( wtb_email_modal_cancel ) && wtb_email_modal_cancel.prop( 'disabled' ) === false ) {
			wtb_toggle_email_modal( false );
		}
	});

	// Close column modal when background or cancel button is clicked
	wtb_column_modal.click( function(e) {
		if ( $(e.target).is( wtb_column_modal ) ) {
			wtb_toggle_column_modal( false );
		}

		if ( $(e.target).is( wtb_column_modal_cancel ) && wtb_column_modal_cancel.prop( 'disabled' ) !== true ) {
			wtb_toggle_column_modal( false );
		}
	});

	// Close details modal when background or cancel button is clicked
	wtb_details_modal.click( function(e) {
		if ( $(e.target).is( wtb_details_modal ) ) {
			wtb_toggle_details_modal( false );
		}

		if ( $(e.target).is( wtb_details_modal_cancel ) ) {
			wtb_toggle_details_modal( false );
		}
	});

	// Close booking form error modal when background or cancel button is clicked
	wtb_booking_modal_error.click( function(e) {
		if ( $(e.target).is( wtb_booking_modal_error ) || $(e.target).is( wtb_booking_modal_error.find( 'a.button' ) ) ) {
			wtb_toggle_booking_form_error_modal( false );
		}
	});

	// Close modals when ESC is keyed
	$(document).keyup( function(e) {
		if ( e.which == '27' ) {
			wtb_toggle_booking_form_modal( false );
			wtb_toggle_email_modal( false );
			wtb_toggle_column_modal( false );
			wtb_toggle_details_modal( false );
			wtb_toggle_booking_form_error_modal( false );
		}
	});

	// Submit booking form modal
	wtb_booking_modal_submit.click( function(e) {

		e.preventDefault();
		e.stopPropagation();

		if ( $(this).prop( 'disabled' ) === true ) {
			return;
		}

		// Loading
		wtb_booking_modal_submit.prop( 'disabled', true );
		wtb_booking_modal_cancel.prop( 'disabled', true );
		wtb_booking_modal_action_status.addClass( 'is-visible' );
		wtb_show_action_status( wtb_booking_modal_action_status, 'loading' );

		var params = {};

		params.action = 'wtb-admin-booking-modal';
		params.nonce = wtb_admin.nonce;
		params.booking = wtb_booking_modal.find( 'form' ).serializeArray();

		var data = $.param( params );

		var jqhxr = $.post( ajaxurl, data, function( r ) {

			if ( r.success ) {

				// Refresh the page so that the new details are visible
				window.location.reload();

			} else {

				// Validation failed
				if ( r.data.error == 'invalid_booking_data' ) {

					// Replace form fields with HTML returned
					wtb_booking_modal_fields.html( r.data.fields );
					wtb_init_booking_form_modal_fields();

				// Logged out
				} else if ( r.data.error == 'loggedout' ) {
					wtb_booking_modal_fields.after( '<div class="wtb-error">' + r.data.msg + '</div>' );

				// Unspecified error
				} else {
					wtb_booking_modal_fields.after( '<div class="wtb-error">' + wtb_admin.strings.error_unspecified + '</div>' );
				}

				wtb_booking_modal_cancel.prop( 'disabled', false );
				wtb_booking_modal_submit.prop( 'disabled', false );
			}

			wtb_show_action_status( wtb_booking_modal_action_status, r.success );

			// Hide result status icon after a few seconds
			setTimeout( function() {
				wtb_booking_modal.find( '.action-status' ).removeClass( 'is-visible' );
			}, 4000 );
		});
	});

	// Submit email form modal
	wtb_email_modal_submit.click( function(e) {

		e.preventDefault();
		e.stopPropagation();

		if ( $(this).prop( 'disabled' ) === true ) {
			return;
		}

		// Loading
		wtb_email_modal_submit.prop( 'disabled', true );
		wtb_email_modal_cancel.prop( 'disabled', true );
		wtb_email_modal_action_status.addClass( 'is-visible' );
		wtb_show_action_status( wtb_email_modal_action_status, 'loading' );

		var params = {};

		params.action = 'wtb-admin-email-modal';
		params.nonce = wtb_admin.nonce;
		params.email = wtb_email_modal.find( 'form' ).serializeArray();

		var data = $.param( params );

		var jqhxr = $.post( ajaxurl, data, function( r ) {

			if ( r.success ) {

				wtb_show_action_status( wtb_email_modal_action_status, r.success );

				// Hide result status icon after a few seconds
				setTimeout( function() {
					wtb_email_modal.find( '.action-status' ).removeClass( 'is-visible' );
					wtb_toggle_email_modal( false );
				}, 1000 );

			} else {

				if ( typeof r.data == 'undefined' || typeof r.data.error == 'undefined' ) {
					wtb_toggle_booking_form_error_modal( true, wtb_admin.strings.error_unspecified );
				} else {
					wtb_toggle_booking_form_error_modal( true, r.data.msg );
				}

				wtb_email_modal_cancel.prop( 'disabled', false );
				wtb_email_modal_submit.prop( 'disabled', false );

				wtb_show_action_status( wtb_email_modal_action_status, false );

				// Hide result status icon after a few seconds
				setTimeout( function() {
					wtb_email_modal.find( '.action-status' ).removeClass( 'is-visible' );
				}, 4000 );
			}
		});
	});

	// Submit column configuration modal
	wtb_column_modal_submit.click( function(e) {

		e.preventDefault();
		e.stopPropagation();

		if ( $(this).prop( 'disabled' ) === true ) {
			return;
		}

		// Loading
		wtb_column_modal_submit.prop( 'disabled', true );
		wtb_column_modal_cancel.prop( 'disabled', true );
		wtb_column_modal_action_status.addClass( 'is-visible' );
		wtb_show_action_status( wtb_column_modal_action_status, 'loading' );

		var params = {};

		params.action = 'wtb-admin-column-modal';
		params.nonce = wtb_admin.nonce;

		params.columns = [];
		wtb_column_modal.find( 'input[name="wtb-columns-config"]:checked' ).each( function() {
			params.columns.push( $(this).val() );
		});

		var data = $.param( params );

		var jqhxr = $.post( ajaxurl, data, function( r ) {

			if ( r.success ) {

				// Refresh the page so that the new details are visible
				window.location.reload();

			} else {

				if ( typeof r.data == 'undefined' || typeof r.data.error == 'undefined' ) {
					wtb_toggle_booking_form_error_modal( true, wtb_admin.strings.error_unspecified );
				} else {
					wtb_toggle_booking_form_error_modal( true, r.data.msg );
				}

				wtb_column_modal_cancel.prop( 'disabled', false );
				wtb_column_modal_submit.prop( 'disabled', false );
			}

			wtb_show_action_status( wtb_column_modal_action_status, r.success );

			// Hide result status icon after a few seconds
			setTimeout( function() {
				wtb_column_modal.find( '.action-status' ).removeClass( 'is-visible' );
			}, 4000 );
		});
	});

	// Show the addons
	if ( $( '#wtb-addons' ).length ) {

		var wtbAddons = {

			el: $( '#wtb-addons' ),

			load: function() {

				var params = {
					action: 'wtb-addons',
					nonce: wtb_addons.nonce
				};

				var data = $.param( params );

				// Send Ajax request
				var jqxhr = $.post( ajaxurl, data, function( r ) {

					wtbAddons.el.find( '.wtb-loading' ).fadeOut( '250', function() {
						if ( r.success ) {
							wtbAddons.showAddons( r );
						} else {
							wtbAddons.showError( r );
						}
					});


				});
			},

			showAddons: function( r ) {

				if ( typeof r.data == 'undefined' || !r.data.length ) {
					wtbAddons.showError();
					return false;
				}

				for( var i in r.data ) {
					wtbAddons.el.append( wtbAddons.getAddonHTML( r.data[i] ) );
					wtbAddons.el.find( '.addon.' + r.data[i].id ).fadeIn();
				}
			},

			showError: function( r ) {

				if ( typeof r.data == 'undefined' || typeof r.data.msg == 'undefined' ) {
					wtbAddons.el.html( '<span class="error">' + wtb_addons.strings.error_unknown + '</span>' );
				} else {
					wtbAddons.el.html( '<span class="error">' + r.data.msg + '</span>' );
				}

			},

			getAddonHTML: function( addon ) {

				if ( typeof addon.id === 'undefined' && typeof addon.title === 'undefined' ) {
					return;
				}

				// Set campaign parameters for addons
				addon.url += '?utm_source=Plugin&utm_medium=Addon%20List&utm_campaign=Restaurant%20Reservations';

				var html = '<div class="addon ' + addon.id + '">';

				if ( typeof addon.url !== 'undefined' && typeof addon.img !== 'undefined' ) {
					html += '<a href="' + addon.url + '"><img src="' + addon.img + '"></a>';
				} else if ( typeof addon.img !== 'undefind' ) {
					html += '<img src="' + addon.img + '">';
				}

				html += '<h3>' + addon.title + '</h3>';

				html += '<div class="details">';

				if ( typeof addon.description !== 'undefined' ) {
					html += '<div class="description">' + addon.description + '</div>';
				}

				if ( typeof addon.status !== 'undefined' ) {

					html += '<div class="action">';

					if ( addon.status === 'released' && typeof addon.url !== 'undefined' ) {
						html += '<a href="' + addon.url + '" class="button button-primary" target="_blank">';

						if ( typeof addon.price !== 'undefined' && addon.price.length ) {
							html += wtb_addons.strings.learn_more;
						} else {
							html += wtb_addons.strings.free;
						}

						html += '</a>';

					} else if ( addon.status === 'installed' ) {
						html += '<span class="installed">' + wtb_addons.strings.installed + '</span>';

					} else {
						html += '<span class="soon">' + wtb_addons.strings.coming_soon + '</span>';
					}

					html += '</div>'; // .action
				}

				html += '</div>'; // .details

				html += '</div>'; // .addon

				return html;
			}
		};

		wtbAddons.load();
	}

});
