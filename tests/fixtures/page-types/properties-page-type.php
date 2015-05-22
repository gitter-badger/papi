<?php

class Properties_Page_Type extends Papi_Page_Type {

	/**
	 * Define our Page Type meta data.
	 *
	 * @return array
	 */

	public function page_type() {
		return [
			'name'        => 'Properties page type',
			'description' => 'This is a properties page',
			'template'    => 'pages/properties-page.php'
		];
	}

	/**
	 * Define our properties.
	 */

	public function register() {

		$this->box( 'Properties', [

			// Bool
			papi_property( [
				'type'  => 'bool',
				'title' => 'Bool test',
				'slug'  => 'bool_test'
			] ),

			// Checkbox
			papi_property( [
				'type'     => 'checkbox',
				'title'    => 'Checkbox test',
				'slug'     => 'checkbox_test',
				'settings' => [
					'items' => [
						'White' => '#ffffff',
						'Black' => '#000000'
					]
				]
			] ),

			// Color
			papi_property( [
				'type'  => 'color',
				'title' => 'Color test',
				'slug'  => 'color_test'
			] ),

			// Datetime
			papi_property( [
				'type'  => 'datetime',
				'title' => 'Datetime test',
				'slug'  => 'datetime_test'
			] ),

			// Divider
			papi_property( [
				'type'  => 'divider',
				'title' => 'Divider test',
				'slug'  => 'divider_test'
			] ),

			// Dropdown
			papi_property( [
				'type'     => 'dropdown',
				'title'    => 'Dropdown test',
				'slug'     => 'dropdown_test',
				'settings' => [
					'items' => [
						'White' => '#ffffff',
						'Black' => '#000000'
					]
				]
			] ),

			// Editor
			papi_property( [
				'type'  => 'editor',
				'title' => 'Editor test',
				'slug'  => 'editor_test'
			] ),

			// Email
			papi_property( [
				'type'  => 'email',
				'title' => 'Email test',
				'slug'  => 'email_test'
			] ),

			// Gallery
			papi_property( [
				'type'  => 'gallery',
				'title' => 'Gallery test',
				'slug'  => 'gallery_test'
			] ),

			// Hidden
			papi_property( [
				'type'  => 'hidden',
				'title' => 'Hidden test',
				'slug'  => 'hidden_test'
			] ),

			// Html
			papi_property( [
				'type'  => 'html',
				'title' => 'Html test',
				'slug'  => 'html_test',
				'settings' => [
					'html' => '<p>Hello, world!</p>'
				]
			] ),

			// Html 2
			papi_property( [
				'type'  => 'html',
				'title' => 'Html test 2',
				'slug'  => 'html_test_2',
				'settings' => [
					'html' => [$this, 'output_html']
				]
			] ),

			// Image
			papi_property( [
				'type'  => 'image',
				'title' => 'Image test',
				'slug'  => 'image_test'
			] )
		] );

		$this->box( 'Flexible', [
			papi_property( [
				'type'     => 'flexible',
				'title'    => 'Sections',
				'slug'     => 'sections',
				'settings' => [
					'items' => [
						'twitter' => [
							'title' => 'Twitter',
							'items' => [
								papi_property( [
									'type'  => 'string',
									'title' => 'Twitter name',
									'slug'  => 'twitter_name'
								] )
							]
						],
						'posts' => [
							'title' => 'Posts',
							'items' => [
								papi_property( [
									'type'  => 'post',
									'title' => 'Post one',
									'slug'  => 'post_one'
								] ),
								papi_property( [
									'type'  => 'post',
									'title' => 'Post two',
									'slug'  => 'post_two'
								] )
							]
						]
					]
				]
			] )
		] );

		$this->box( 'Repeater', [
			papi_property( [
				'type'     => 'repeater',
				'title'    => 'Books',
				'slug'     => 'books',
				'settings' => [
					'items' => [
						papi_property( [
							'type'  => 'string',
							'title' => 'Book name',
							'slug'  => 'book_name'
						] ),
						papi_property( [
							'type'  => 'bool',
							'title' => 'Is open?',
							'slug'  => 'is_open'
						] )
					]
				]
			] )
		] );

	}

	public function output_html() {
		?>
		<p>Hello, callable!</p>
		<?php
	}
}
