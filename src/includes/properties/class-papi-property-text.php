<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Papi Property Text class.
 *
 * @package Papi
 */
class Papi_Property_Text extends Papi_Property {

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
		if ( ! $this->get_setting( 'allow_html' ) ) {
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
		papi_render_html_tag( 'textarea', [
			'class' => 'papi-property-text',
			'id'    => $this->html_id(),
			'name'  => $this->html_name(),
			$this->get_value()
		] );
	}
}
