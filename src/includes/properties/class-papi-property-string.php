<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Papi Property String class.
 *
 * @package Papi
 */
class Papi_Property_String extends Papi_Property {

	/**
	 * The input type to use.
	 *
	 * @var string
	 */
	public $input_type = 'text';

	/**
	 * Format the value of the property before it's returned to the application.
	 *
	 * @param mixed $value
	 * @param string $slug
	 * @param int $post_id
	 *
	 * @return array
	 */
	public function format_value( $value, $slug, $post_id ) {
		if ( ! $this->get_setting( 'allow_html' ) && $this->input_type === 'text' ) {
			$value = sanitize_text_field( $value );
		}

		return $value;
	}

	/**
	 * Get default settings.
	 *
	 * @return array
	 */
	public function get_default_settings() {
		return [
			'allow_html' => false
		];
	}

	/**
	 * Get value from the database.
	 *
	 * @return string
	 */
	public function get_value() {
		$value = parent::get_value();
		return $this->format_value( $value, $this->get_slug(), papi_get_post_id() );
	}

	/**
	 * Display property html.
	 */
	public function html() {
		papi_render_html_tag( 'input', [
			'id'      => $this->html_id(),
			'name'    => $this->html_name(),
			'type'    => $this->input_type,
			'value'   => $this->get_value()
		] );
	}

}
