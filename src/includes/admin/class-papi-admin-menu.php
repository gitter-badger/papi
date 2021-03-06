<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Papi Admin Menu class.
 *
 * @package Papi
 */
class Papi_Admin_Menu {

	/**
	 * The construct.
	 */
	public function __construct() {
		$this->setup_actions();
	}

	/**
	 * Fill labels on admin bar.
	 */
	public function admin_bar_menu() {
		if ( $page_type = $this->get_page_type() ) {
			$this->override_labels( $page_type );
		}
	}

	/**
	 * Get current page type.
	 *
	 * @return Papi_Page_Type
	 */
	private function get_page_type() {
		$page_type_id = papi_get_page_type_id();

		if ( ! is_string( $page_type_id ) ) {
			return;
		}

		if ( $page_type = papi_get_page_type_by_id( $page_type_id ) ) {
			return $page_type;
		}
	}

	/**
	 * Override labels with labels from the page type.
	 *
	 * @param Papi_Page_Type $page_type
	 */
	private function override_labels( Papi_Page_Type $page_type ) {
		global $wp_post_types;

		$post_type = papi_get_post_type();

		if ( empty( $post_type ) || ! isset( $wp_post_types[$post_type] ) ) {
			return;
		}

		foreach ( $page_type->get_labels() as $key => $value ) {
			if ( ! isset( $wp_post_types[$post_type]->labels->$key ) || empty( $value ) ) {
				continue;
			}

			$wp_post_types[$post_type]->labels->$key = $value;
		}
	}

	/**
	 * Page items menu.
	 *
	 * This function will register all page types
	 * that has a fake post type. Like option types.
	 */
	public function page_items_menu() {
		$page_types = papi_get_all_page_types( false, null, true );

		foreach ( $page_types as $page_type ) {
			$meta = call_user_func( [$page_type, $page_type->_meta_method] );

			if ( ! isset( $meta['menu'] ) || ! isset( $meta['name'] ) ) {
				continue;
			}

			$meta['slug'] = 'papi/' . $page_type->get_id();

			if ( ! isset( $meta['capability'] ) ) {
				$meta['capability'] = 'manage_options';
			}

			add_submenu_page(
				$meta['menu'],
				$meta['name'],
				$meta['name'],
				$meta['capability'],
				$meta['slug'],
				[ $page_type, 'render' ]
			);
		}
	}

	/**
	 * Setup menu items for real post types.
	 */
	public function post_types_menu() {
		global $submenu;

		$post_types = papi_get_post_types();

		foreach ( $post_types as $post_type ) {

			if ( ! post_type_exists( $post_type ) ) {
				continue;
			}

			if ( $post_type === 'post' ) {
				$edit_url = 'edit.php';
			} else {
				$edit_url = 'edit.php?post_type=' . $post_type;
			}

			if ( ! isset( $submenu[$edit_url] ) || ! isset( $submenu[$edit_url][10] ) || ! isset( $submenu[$edit_url][10][2] ) ) {
				continue;
			}

			$only_page_type = papi_filter_settings_only_page_type( $post_type );

			if ( ! empty( $only_page_type ) ) {
				$submenu[$edit_url][10][2] = papi_get_page_new_url( $only_page_type, false, $post_type, [ 'action', 'message', 'page_type', 'post', 'post_type' ] );
			} else {
				$page = 'papi-add-new-page,' . $post_type;

				if ( strpos( $edit_url, 'post_type' ) === false ) {
					$start = '?';
				} else {
					$start = '&';
				}

				$submenu[$edit_url][10][2] = $edit_url . $start . 'page=' . $page;

				// Hidden menu item.
				add_submenu_page( null, __( 'Add New', 'papi' ), __( 'Add New', 'papi' ), 'read', $page, [ $this, 'render_view' ] );
			}
		}
	}

	/**
	 * Menu callback that loads right view depending on what the `page` query string says.
	 */
	public function render_view() {
		if ( strpos( papi_get_qs( 'page' ), 'papi' ) !== false ) {
			$page = str_replace( 'papi-', '', papi_get_qs( 'page' ) );
			$res = preg_replace( '/\,.*/', '', $page );

			if ( is_string( $res ) ) {
				$page_view = $res;
			}
		}

		if ( ! isset( $page_view ) ) {
			$page_view = null;
		}

		if ( ! is_null( $page_view ) ) {
			$view = new Papi_Admin_View;
			$view->render( $page_view );
		} else {
			echo '<h2>Papi - 404</h2>';
		}
	}

	/**
	 * Setup actions.
	 */
	private function setup_actions() {
		if ( is_admin() ) {
			add_action( 'admin_init', [$this, 'admin_bar_menu'] );
			add_action( 'admin_menu', [$this, 'page_items_menu'] );
			add_action( 'admin_menu', [$this, 'post_types_menu'] );
		} else {
			add_action( 'admin_bar_menu', [$this, 'admin_bar_menu'] );
		}
	}

}

new Papi_Admin_Menu;
