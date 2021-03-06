<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Papi Core Property class.
 *
 * @package Papi
 */
class Papi_Core_Property {

	/**
	 * The conditional class.
	 *
	 * @var Papi_Core_Conditional
	 */
	protected $conditional;

	/**
	 * The convert type.
	 *
	 * @var string
	 */
	public $convert_type = 'string';

	/**
	 * Default options.
	 *
	 * @var array
	 */
	protected $default_options = [
		'capabilities' => [],
		'description'  => '',
		'disabled'     => false,
		'lang'         => false,
		'raw'          => false,
		'required'     => false,
		'rules'        => [],
		'settings'     => [],
		'sidebar'      => true,
		'slug'         => '',
		'sort_order'   => -1,
		'title'        => '',
		'type'         => 'string',
		'value'        => ''
	];

	/**
	 * Default value.
	 *
	 * @var null
	 */
	public $default_value;

	/**
	 * Display the property in WordPress admin.
	 *
	 * @var bool
	 */
	protected $display = true;

	/**
	 * Current property options object.
	 *
	 * @var stdClass
	 */
	private $options;

	/**
	 * The page that the property exists on.
	 *
	 * @var Papi_Core_Page
	 */
	private $page;

	/**
	 * The constructor.
	 */
	public function __construct() {
		$this->setup_actions();
		$this->setup_conditional();
		$this->setup_filters();
	}

	/**
	 * Get option value dynamic.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function __get( $key ) {
		return $this->get_option( $key );
	}

	/**
	 * Check if options value exists or not.
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function __isset( $key ) {
		return $this->get_option( $key ) !== null;
	}

	/**
	 * Set options value dynamic.
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function __set( $key, $value ) {
		$this->set_option( $key, $value );
	}

	/**
	 * Check if the property is allowed
	 * to render by the conditional rules.
	 *
	 * @param array $rules
	 *
	 * @return bool
	 */
	public function render_is_allowed_by_rules( array $rules = [] ) {
		if ( empty( $rules ) ) {
			$rules = $this->get_rules();
		}

		return $this->conditional->display( $rules, $this );
	}

	/**
	 * Create a property from options.
	 *
	 * @param array|object $options
	 *
	 * @return Papi_Property
	 */
	public static function create( $options = [] ) {
		$property = new static;
		$property->set_options( $options );
		return $property;
	}

	/**
	 * Convert settings items to properties if they are a property.
	 *
	 * @param array $settings
	 *
	 * @return array
	 */
	private function convert_settings( $settings ) {
		foreach ( $settings as $key => $value ) {
			if ( ! is_array( $value ) ) {
				continue;
			}

			$settings[$key] = $this->convert_items_array( $value );
		}

		return $settings;
	}

	/**
	 * Convert all arrays that has a valid property type.
	 *
	 * @param array $items
	 *
	 * @return array
	 */
	private function convert_items_array( $items ) {
		foreach ( $items as $index => $item ) {
			if ( is_array( $item ) && ! isset( $item['type'] ) ) {
				foreach ( $item as $key => $value ) {
					if ( is_array( $value ) ) {
						$items[$index][$key] = $this->convert_items_array( $value );
						$items[$index][$key] = array_filter( $items[$index][$key] );
						$items[$index][$key] = array_values( $items[$index][$key] );
					}
				}

				continue;
			}

			if ( papi_is_property( $item ) ) {
				$child_items = $item->get_setting( 'items' );

				if ( is_array( $child_items ) ) {
					$items[$index]->set_setting( 'items', $this->convert_items_array( $child_items ) );
				}

				continue;
			}

			if ( ( is_array( $item ) && isset( $item['type'] ) ) || ( is_object( $item ) && isset( $item->type ) ) ) {
				$items[$index] = papi_get_property_type( $item );

				if ( is_null( $items[$index] ) ) {
					unset( $items[$index] );
					continue;
				}

				if ( is_object( $items[$index] ) ) {
					$child_items = $items[$index]->get_setting( 'items' );

					if ( is_array( $child_items ) ) {
						$items[$index]->set_setting( 'items', $this->convert_items_array( $child_items ) );
					}
				}
			}
		}

		return $items;
	}

	/**
	 * Delete value from the database.
	 *
	 * @param string $slug
	 * @param int $post_id
	 * @param string $type
	 *
	 * @return bool
	 */
	public function delete_value( $slug, $post_id, $type ) {
		if ( $type === Papi_Core_Page::TYPE_OPTION || $this->is_option_page() ) {
			return delete_option( $slug );
		}

		return delete_post_meta( $post_id, $slug );
	}

	/**
	 * Create a new instance of the given type.
	 *
	 * @param mixed $type
	 *
	 * @return object
	 */
	public static function factory( $type ) {
		if ( is_array( $type ) ) {
			$prop = self::create( $type );
			$type = $prop->get_options();
		}

		if ( ! is_string( $type ) && ! is_object( $type ) ) {
			return;
		}

		if ( is_subclass_of( $type, __CLASS__ ) ) {
			return $type;
		}

		$options = null;

		if ( is_object( $type ) ) {
			if ( ! isset( $type->type ) || ! is_string( $type->type ) ) {
				return;
			}

			$options = $type;
			$type = $type->type;
		}

		$type = preg_replace( '/^Property/', '', $type );

		if ( empty( $type ) ) {
			return;
		}

		$class_name = papi_get_property_class_name( $type );

		if ( ! class_exists( $class_name ) || ! is_subclass_of( $class_name, __CLASS__ ) ) {
			return;
		}

		if ( ! papi()->exists( $class_name ) ) {
			papi()->bind( $class_name, new $class_name() );
		}

		$class = papi()->make( $class_name );

		if ( ! is_object( $class ) || $class instanceof Papi_Core_Property === false ) {
			$class = new $class_name();
			papi()->bind( $class_name, $class );
		}

		$property = clone $class;

		if ( is_object( $options ) ) {
			$property->set_options( $options );
		}

		return $property;
	}

	/**
	 * Format the value of the property before we output it to the application.
	 *
	 * @param mixed $value
	 * @param string $slug
	 * @param int $post_id
	 *
	 * @return mixed
	 */
	public function format_value( $value, $slug, $post_id ) {
		return $value;
	}

	/**
	 * Get child properties from `items` in the settings array.
	 *
	 * @return array
	 */
	public function get_child_properties() {
		return $this->get_setting( 'items', [] );
	}

	/**
	 * Get default settings.
	 *
	 * @return array
	 */
	public function get_default_settings() {
		return [];
	}

	/**
	 * Get option value.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function get_option( $key ) {
		if ( isset( $this->options->$key ) ) {
			return $this->options->$key;
		}

		if ( isset( $this->default_options[$key] ) ) {
			$option = $this->default_options[$key];

			if ( $key === 'settings' ) {
				$option = (object) $option;
			}

			return $option;
		}
	}

	/**
	 * Get the current property options object.
	 *
	 * @return stdClass
	 */
	public function get_options() {
		return $this->options;
	}

	/**
	 * Get the page that the property is on.
	 *
	 * @return Papi_Core_Page
	 */
	public function get_page() {
		if ( $this->page instanceof Papi_Core_Page ) {
			return $this->page;
		}

		return papi_get_page( $this->get_post_id() );
	}

	/**
	 * Get post id.
	 *
	 * @return int
	 */
	public function get_post_id() {
		if ( $this->page instanceof Papi_Core_Page ) {
			return $this->page->id;
		}

		return papi_get_post_id();
	}

	/**
	 * Get conditional rules.
	 *
	 * @return array
	 */
	public function get_rules() {
		return $this->get_option( 'rules' );
	}

	/**
	 * Get setting value.
	 *
	 * @param string $key
	 * @param mixed $default
	 *
	 * @return stdClass
	 */
	public function get_setting( $key, $default = null ) {
		$settings = $this->get_settings();

		if ( isset( $settings->$key ) ) {
			return $settings->$key;
		}

		return $default;
	}

	/**
	 * Get custom property settings.
	 *
	 * @return stdClass
	 */
	public function get_settings() {
		$settings = wp_parse_args( $this->get_option( 'settings' ), $this->get_default_settings() );
		return (object) $this->convert_settings( $settings );
	}

	/**
	 * Get property slug.
	 *
	 * @param bool $remove_prefix
	 *
	 * @return string
	 */
	public function get_slug( $remove_prefix = false ) {
		if ( $remove_prefix ) {
			return papi_remove_papi( $this->get_option( 'slug' ) );
		}

		return $this->get_option( 'slug' );
	}

	/**
	 * Get value.
	 *
	 * @return mixed
	 */
	public function get_value() {
		$value = $this->get_option( 'value' );

		if ( papi_is_empty( $value ) ) {
			$slug = $this->get_slug( true );

			if ( $this->is_option_page() ) {
				$value = papi_get_option( $slug );
			} else {
				$value = papi_get_field( $this->get_post_id(), $slug );
			}
		}

		$value = $this->prepare_value( $value );

		return $value;
	}

	/**
	 * Match property slug with given slug value.
	 *
	 * @param string $slug
	 *
	 * @return bool
	 */
	public function match_slug( $slug ) {
		if ( ! is_string( $slug ) ) {
			$slug = '';
		}

		return $this->get_slug( ! preg_match( '/^papi\_/', $slug ) ) === $slug;
	}

	/**
	 * Prepare property value.
	 *
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	public function prepare_value( $value ) {
		if ( papi_is_empty( $value ) ) {
			return $this->default_value;
		}

		if ( $this->convert_type === 'string' ) {
			$value = papi_convert_to_string( $value );
		}

		return papi_santize_data( $value );
	}

	/**
	 * Check if it's a option page or not.
	 *
	 * @return bool
	 */
	public function is_option_page() {
		if ( $this->page === null ) {
			return papi_is_option_page();
		}

		return $this->page->is( Papi_Core_Page::TYPE_OPTION );
	}

	/**
	 * Change value after it's loaded from the database.
	 *
	 * @param mixed $value
	 * @param string $slug
	 * @param int $post_id
	 *
	 * @return mixed
	 */
	public function load_value( $value, $slug, $post_id ) {
		return $value;
	}

	/**
	 * Render AJAX request.
	 */
	public function render_ajax_request() {
		papi_render_property( $this );
	}

	/**
	 * Set the page that the property is on.
	 *
	 * @param Papi_Core_Page $page
	 */
	public function set_page( Papi_Core_Page $page ) {
		$this->page = $page;
	}

	/**
	 * Set the current property options object.
	 *
	 * @param array|object $options
	 */
	public function set_options( $options ) {
		$this->options = $this->setup_options( $options );
	}

	/**
	 * Set property option value.
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function set_option( $key, $value ) {
		if ( ! is_object( $this->options ) ) {
			$this->options = (object) $this->default_options;
		}

		if ( isset( $this->options->$key ) ) {
			$this->options->$key = $value;
		}
	}

	/**
	 * Set property setting value.
	 *
	 * @param string $key
	 * @param mixed $value
	 */
	public function set_setting( $key, $value ) {
		if ( isset( $this->options->settings ) && isset( $this->options->settings->$key ) ) {
			$this->options->settings->$key = $value;
		}
	}

	/**
	 * Setup actions.
	 */
	protected function setup_actions() {
	}

	/**
	 * Setup conditional class.
	 */
	protected function setup_conditional() {
		$this->conditional = new Papi_Core_Conditional();
	}

	/**
	 * Setup filters.
	 */
	protected function setup_filters() {
	}

	/**
	 * Setup options.
	 *
	 * @param array|object $options
	 *
	 * @return mixed
	 */
	private function setup_options( $options ) {
		if ( ! is_array( $options ) ) {
			return $options;
		}

		if ( empty( $options ) ) {
			return new stdClass;
		}

		$options = array_merge( $this->default_options, $options );
		$options = (object) $options;

		if ( $options->sort_order === -1 ) {
			$options->sort_order = papi_filter_settings_sort_order();
		}

		$options->capabilities = papi_to_array( $options->capabilities );

		// Generate slug from title or type.
		if ( empty( $options->slug ) ) {
			if ( empty( $options->title ) ) {
				$options->slug = papi_slugify( $options->type );
			} else {
				$options->slug = papi_slugify( $options->title );
			}
		}

		// Generate a vaild Papi meta name for slug.
		$options->slug = papi_html_name( $options->slug );

		$property_class = self::factory( $options->type );

		if ( papi_is_property( $property_class ) ) {
			$options->settings = array_merge( (array) $property_class->get_default_settings(), (array) $options->settings );
		}

		$options->settings = (object) $this->convert_settings( $options->settings );

		$options = papi_esc_html( $options, ['html'] );

		return $options;
	}

	/**
	 * Update value before it's saved to the database.
	 *
	 * @param mixed $value
	 * @param string $slug
	 * @param int $post_id
	 *
	 * @return mixed
	 */
	public function update_value( $value, $slug, $post_id ) {
		return $value;
	}

}
