<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Papi Option Page.
 *
 * @package Papi
 */

class Papi_Option_Page extends Papi_Core_Page {

	/**
	 * Data type to describe which
	 * type of page data is it.
	 *
	 * @var string
	 */

	protected $type = 'option';

	/**
	 * Load property from page type.
	 *
	 * @param string $slug
	 * @param string $child_slug
	 *
	 * @return object
	 */

	public function get_property( $slug, $child_slug = '' ) {
		$page_type_id = str_replace( 'papi/', '', papi_get_qs( 'page' ) );

		if ( empty( $page_type_id ) ) {
			$page_types = papi_get_all_page_types( false, null, true );
			$property   = null;

			foreach ( $page_types as $index => $page_type ) {
				if ( $property = $page_type->get_property( $slug, $child_slug ) ) {
					break;
				}
			}

			if ( is_null( $property ) ) {
				return;
			}

			return $property;
		}

		$page_type = papi_get_page_type_by_id( $page_type_id );

		if ( ! is_object( $page_type ) || $page_type instanceof Papi_Option_Type === false ) {
			return;
		}

		return $page_type->get_property( $slug, $child_slug );
	}

	/**
	 * Check if it's a valid page.
	 *
	 * @return bool
	 */

	public function valid() {
		return $this->id === 0 && $this->valid_type();
	}

}