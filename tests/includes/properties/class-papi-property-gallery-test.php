<?php

/**
 * Unit tests covering property gallery.
 *
 * @package Papi
 */

class Papi_Property_Gallery_Test extends Papi_Property_Test_Case {

	public $slug = 'gallery_test';

	public function get_value() {
		return [23];
	}

	public function get_expected() {
		return [23];
	}

	public function test_property_options() {
		$this->assertEquals( 'gallery', $this->property->get_option( 'type' ) );
		$this->assertEquals( 'Gallery test', $this->property->get_option( 'title' ) );
		$this->assertEquals( 'papi_gallery_test', $this->property->get_option( 'slug' ) );
		$this->assertTrue( $this->property->get_setting( 'gallery' ) );
	}

}
