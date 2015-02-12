<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Papi Page Type Base class.
 *
 * @package Papi
 * @since 1.0.0
 */

class Papi_Page_Type_Base {

	/**
	 * The meta method to call.
	 *
	 * @var string
	 * @since 1.2.0
	 */

	public $_meta_method = 'page_type';

	/**
	 * The page type class name.
	 *
	 * @var string
	 * @since 1.0.0
	 */

	public $_class_name = '';

	/**
	 * The file name of the page type file.
	 *
	 * @var string
	 * @since 1.0.0
	 */

	public $_file_name = '';

	/**
	 * The file path of the page type file.
	 *
	 * @var string
	 * @since 1.0.0
	 */

	public $_file_path = '';

	/**
	 * Constructor.
	 * Load a page type by the file.
	 *
	 * @param string $file_path
	 *
	 * @since 1.0.0
	 */

	public function __construct( $file_path ) {
		// Try to load the file if the file path is empty.
		if ( empty( $file_path ) ) {
			$page_type = papi_get_page_type_meta_value();
			$file_path = papi_get_file_path( $page_type );
		}

		if ( ! is_file( $file_path ) ) {
			return null;
		}

		$this->setup_file( $file_path );
		$this->setup_meta_data();
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @since 2.1
	 */

	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'papi' ), '1.0.0' );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 2.1
	 */

	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'papi' ), '1.0.0' );
	}

	/**
	 * Get the page type file name.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */

	public function get_filename() {
		return $this->_file_name;
	}

	/**
	 * Get the page type file pat.h
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */

	public function get_filepath() {
		return $this->_file_path;
	}

	/**
	 * Create a new instance of the page type file.
	 *
	 * @since 1.0.0
	 *
	 * @return object
	 */

	public function new_class() {
		if ( ! class_exists( $this->_class_name ) ) {
			require_once $this->_file_path;
		}

		return new $this->_class_name;
	}

	/**
	 * Load the file and setup page type meta data.
	 *
	 * @param string $file_path
	 *
	 * @since 1.0.0
	 * @access private
	 */

	private function setup_file( $file_path ) {
		$this->_file_path = $file_path;
		$this->_file_name = papi_get_page_type_base_path( $this->_file_path );

		// Get the class name of the file.
		$this->_class_name = papi_get_class_name( $this->_file_path );

		// Try to load the page type class.
		if ( ! class_exists( $this->_class_name ) ) {
			require_once $this->_file_path;
		}
	}

	/**
	 * Setup page type meta data.
	 *
	 * @since 1.0.0
	 * @access private
	 */

	private function setup_meta_data() {
		// Check so we have the page type meta array function.
		if ( ! method_exists( $this->_class_name, $this->_meta_method ) ) {
			return null;
		}

		foreach ( call_user_func( array( $this, $this->_meta_method ) ) as $key => $value ) {
			if ( substr( $key, 0, 1 ) === '_' ) {
				continue;
			}

			$this->$key = papi_esc_html( $value );
		}
	}
}
