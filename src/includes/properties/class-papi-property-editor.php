<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Papi Property Editor class.
 *
 * @package Papi
 */
class Papi_Property_Editor extends Papi_Property {

	/**
	 * Format the value of the property before it's returned to the application.
	 *
	 * @param mixed $value
	 *
	 * @return array
	 */
	public function format_value( $value, $slug, $post_id ) {
		return apply_filters( 'the_content', $value );
	}

	/**
	 * Display property html.
	 */
	public function html() {
		$value = $this->get_value();
		$id    = str_replace( '[', '', str_replace( ']', '', $this->html_name() ) ) . '-' . uniqid();

		wp_editor( $value, $id, [
			'textarea_name' => $this->html_name(),
			'media_buttons' => true
		] );

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			add_filter( 'mce_external_plugins', '__return_empty_array' );
		}
	}

}
