<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Unit tests covering `Papi_Admin_Post_Handler` class.
 *
 * @package Papi
 */
class Papi_Admin_Post_Handler_Test extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();
		$this->handler = new Papi_Admin_Post_Handler;

		tests_add_filter( 'papi/settings/directories', function () {
			return [1,  PAPI_FIXTURE_DIR . '/page-types'];
		} );

		$this->post_id = $this->factory->post->create();

		$_GET = [];
		$_GET['post'] = $this->post_id;

		update_post_meta( $this->post_id, PAPI_PAGE_TYPE_KEY, 'properties-page-type' );

		$this->page_type = papi_get_page_type_by_id( 'properties-page-type' );
	}

	public function tearDown() {
		parent::tearDown();
		unset(
			$_GET,
			$_POST,
			$this->handler,
			$this->post_id,
			$this->page_type
		);
	}

	public function test_actions() {
		$this->assertGreaterThan( 0, has_action( 'save_post', [$this->handler, 'save_meta_boxes'] ) );
	}

	public function test_save_meta_boxes() {
		$property = $this->page_type->get_property( 'string_test' );

		$_POST = papi_test_create_property_post_data( [
			'slug'  => $property->slug,
			'type'  => $property,
			'value' => 'Hello, world!'
		], $_POST );

		$user_id = $this->factory->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user_id );

		$_POST['papi_meta_nonce'] = wp_create_nonce( 'papi_save_data' );
		$_POST['post_ID'] = $this->post_id;

		$this->handler->save_meta_boxes( $this->post_id, get_post( $this->post_id ) );
		wp_set_current_user( 0 );

		$this->assertEquals( 'Hello, world!', papi_get_field( $this->post_id, $property->slug ) );
	}

	public function test_save_meta_boxes_2() {
		$property = $this->page_type->get_property( 'bool_test' );

		$_POST = papi_test_create_property_post_data( [
			'slug'  => $property->slug,
			'type'  => $property,
			'value' => 'on'
		], $_POST );

		$user_id = $this->factory->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user_id );

		$_POST['papi_meta_nonce'] = wp_create_nonce( 'papi_save_data' );
		$_POST['post_ID'] = $this->post_id;

		$this->handler->save_meta_boxes( $this->post_id, get_post( $this->post_id ) );
		wp_set_current_user( 0 );

		$this->assertTrue( papi_get_field( $this->post_id, $property->slug ) );
	}

	public function test_save_meta_boxes_fail_1() {
		$property = $this->page_type->get_property( 'string_test' );

		$_POST = papi_test_create_property_post_data( [
			'slug'  => $property->slug,
			'type'  => $property,
			'value' => 'Hello, world!'
		], $_POST );

		$user_id = $this->factory->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user_id );

		$_POST['papi_meta_nonce'] = '';
		$_POST['post_ID'] = $this->post_id;

		$this->handler->save_meta_boxes( $this->post_id, get_post( $this->post_id ) );
		wp_set_current_user( 0 );

		// wrong nonce
		$this->assertNull( papi_get_field( $this->post_id, $property->slug ) );
	}

	public function test_save_meta_boxes_fail_2() {
		$property = $this->page_type->get_property( 'string_test' );

		$_POST = papi_test_create_property_post_data( [
			'slug'  => $property->slug,
			'type'  => $property,
			'value' => 'Hello, world!'
		], $_POST );

		$user_id = $this->factory->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user_id );

		$_POST['papi_meta_nonce'] = wp_create_nonce( 'papi_save_data' );

		$this->handler->save_meta_boxes( 0, null );
		wp_set_current_user( 0 );

		// wrong post id
		$this->assertNull( papi_get_field( $this->post_id, $property->slug ) );
	}

	public function test_save_meta_boxes_fail_3() {
		$property = $this->page_type->get_property( 'string_test' );

		$_POST = papi_test_create_property_post_data( [
			'slug'  => $property->slug,
			'type'  => $property,
			'value' => 'Hello, world!'
		], $_POST );

		$user_id = $this->factory->user->create( [ 'role' => 'read' ] );
		wp_set_current_user( $user_id );

		$_POST['papi_meta_nonce'] = wp_create_nonce( 'papi_save_data' );
		$_POST['post_ID'] = $this->post_id;

		$this->handler->save_meta_boxes( $this->post_id, get_post( $this->post_id ) );
		wp_set_current_user( 0 );

		// wrong capability
		$this->assertNull( papi_get_field( $this->post_id, $property->slug ) );
	}

	public function test_save_meta_boxes_fail_4() {
		$property = $this->page_type->get_property( 'string_test' );

		$_POST = papi_test_create_property_post_data( [
			'slug'  => $property->slug,
			'type'  => $property,
			'value' => 'Hello, world!'
		], $_POST );

		$user_id = $this->factory->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user_id );

		$_POST['papi_meta_nonce'] = wp_create_nonce( 'papi_save_data' );
		$_POST['post_ID'] = $this->post_id;

		define( 'DOING_AUTOSAVE', true );

		$this->handler->save_meta_boxes( $this->post_id, get_post( $this->post_id ) );
		wp_set_current_user( 0 );

		// doing autosave is true
		$this->assertNull( papi_get_field( $this->post_id, $property->slug ) );
	}

	public function test_save_meta_boxes_fail_5() {
		$property = $this->page_type->get_property( 'bool_test' );

		$_POST = papi_test_create_property_post_data( [
			'slug'  => $property->slug,
			'type'  => 'kvacker',
			'value' => 'on'
		], $_POST );

		$user_id = $this->factory->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user_id );

		$_POST['papi_meta_nonce'] = wp_create_nonce( 'papi_save_data' );
		$_POST['post_ID'] = $this->post_id;

		$this->handler->save_meta_boxes( $this->post_id, get_post( $this->post_id ) );
		wp_set_current_user( 0 );

		$this->assertNull( papi_get_field( $this->post_id, $property->slug ) );
	}

	public function test_save_property() {
		$property = $this->page_type->get_property( 'string_test' );
		$_POST = papi_test_create_property_post_data( [
			'slug'  => $property->slug,
			'type'  => $property,
			'value' => 'Hello, world!'
		], $_POST );

		$this->handler->save_property( $this->post_id );

		$value = papi_get_field( $this->post_id, $property->slug );

		$this->assertEquals( 'Hello, world!', $value );

		$property = $this->page_type->get_property( 'number_test' );
		$_POST = papi_test_create_property_post_data( [
			'slug'  => $property->slug,
			'type'  => $property,
			'value' => 42
		], $_POST );

		$this->handler->save_property( 0 );

		$value = papi_get_field( 0, $property->slug );

		$this->assertNull( $value );
	}

	public function test_pre_data() {
		$_POST = [
			'_papi_item'   => 'Item 42',
			'_papi_item_2' => ''
		];

		$this->handler->save_property( $this->post_id );

		$this->assertEquals( 'Item 42', get_post_meta( $this->post_id, '_papi_item', true ) );
		$this->assertEmpty( get_post_meta( $this->post_id, '_papi_item_2', true ) );
	}

}
