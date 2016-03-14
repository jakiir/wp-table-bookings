<?php 
	/**
	 * Print a text input form field
	 * @since 1.0
	 */
	 
if ( !function_exists( 'wtb_print_form_text_field' ) ) {
function wtb_print_form_text_field( $slug, $title, $value, $args = array() ) {

	$slug = esc_attr( $slug );
	$value = esc_attr( $value );
	$type = empty( $args['input_type'] ) ? 'text' : esc_attr( $args['input_type'] );
	$classes = isset( $args['classes'] ) ? $args['classes'] : array();
	$classes[] = 'wtb-text';

	?>

	<div <?php echo wtb_print_element_class( $slug, $classes ); ?>>
		<?php echo wtb_print_form_error( $slug ); ?>
		<label for="wtb-<?php echo $slug; ?>">
			<?php echo $title; ?>
		</label>
		<input type="<?php echo $type; ?>" name="wtb-<?php echo $slug; ?>" id="wtb-<?php echo $slug; ?>" value="<?php echo $value; ?>">
	</div>

	<?php

}
} // endif;

/**
 * Print a class attribute based on the optional classes and slug, provided with arguments
 * @since 1.0
 */
if ( !function_exists( 'wtb_print_element_class' ) ) {
function wtb_print_element_class( $slug, $additional_classes = array() ) {
	$classes = empty( $additional_classes ) ? array() : $additional_classes;

	if ( ! empty( $slug ) ) {
		array_push( $classes, $slug );
	}

	$class_attr = esc_attr( join( ' ', $classes ) );

	return empty( $class_attr ) ? '' : sprintf( 'class="%s"', $class_attr );

}
} // endif;

/**
 * Print a form validation error
 * @since 0.0.1
 */
if ( !function_exists( 'wtb_print_form_error' ) ) {
function wtb_print_form_error( $field ) {

	global $wtbInit;

	if ( !empty( $wtbInit->request ) && !empty( $wtbInit->request->validation_errors ) ) {
		foreach ( $wtbInit->request->validation_errors as $error ) {
			if ( $error['field'] == $field ) {
				echo '<div class="wtb-error">' . $error['message'] . '</div>';
			}
		}
	}
}
} // endif;

/**
 * Print a select form field
 * @since 1.0
 */
if ( !function_exists( 'wtb_print_form_select_field' ) ) {
function wtb_print_form_select_field( $slug, $title, $value, $args ) {

	$slug = esc_attr( $slug );
	$value = esc_attr( $value );
	$options = is_array( $args['options'] ) ? $args['options'] : array();
	$classes = isset( $args['classes'] ) ? $args['classes'] : array();
	$classes[] = 'wtb-select';

	?>

	<div <?php echo wtb_print_element_class( $slug, $classes ); ?>>
		<?php echo wtb_print_form_error( $slug ); ?>
		<label for="wtb-<?php echo $slug; ?>">
			<?php echo $title; ?>
		</label>
		<select name="wtb-<?php echo $slug; ?>" id="wtb-<?php echo $slug; ?>">
			<?php foreach ( $options as $opt_value => $opt_label ) : ?>
			<option value="<?php echo esc_attr( $opt_value ); ?>" <?php selected( $opt_value, $value ); ?>><?php echo esc_attr( $opt_label ); ?></option>
			<?php endforeach; ?>
		</select>
	</div>

	<?php

}
} // endif;

/**
 * Print a textarea form field
 * @since 1.0
 */
if ( !function_exists( 'wtb_print_form_textarea_field' ) ) {
function wtb_print_form_textarea_field( $slug, $title, $value, $args = array() ) {

	$slug = esc_attr( $slug );
	// Strip out <br> tags when placing in a textarea
	$value = preg_replace('/\<br(\s*)?\/?\>/i', '', $value);
	$classes = isset( $args['classes'] ) ? $args['classes'] : array();
	$classes[] = 'wtb-textarea';

	?>

	<div <?php echo wtb_print_element_class( $slug, $classes ); ?>>
		<?php echo wtb_print_form_error( $slug ); ?>
		<label for="wtb-<?php echo $slug; ?>">
			<?php echo $title; ?>
		</label>
		<textarea name="wtb-<?php echo $slug; ?>" id="wtb-<?php echo $slug; ?>"><?php echo $value; ?></textarea>
	</div>

	<?php

}
} // endif;

/**
 * Print the Add Message link to display the message field
 * @since 1.3
 */
if ( !function_exists( 'wtb_print_form_message_link' ) ) {
function wtb_print_form_message_link( $slug, $title, $value, $args = array() ) {

	$slug = esc_attr( $slug );
	$value = esc_attr( $value );
	$classes = isset( $args['classes'] ) ? $args['classes'] : array();

	?>

	<div <?php echo wtb_print_element_class( $slug, $classes ); ?>>
		<a href="#">
			<?php echo $title; ?>
		</a>
	</div>

	<?php

}
} // endif;

?>