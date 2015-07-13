<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Papi Conditional Rules class.
 *
 * @package Papi
 */

class Papi_Conditional_Rules {

	/**
	 * The constructor.
	 */

	public function __construct() {
		$this->setup_filters();
	}

	/**
	 * Convert string bool to bool.
	 *
	 * @param mixed $str
	 *
	 * @return mixed
	 */

	private function convert_bool( $str ) {
		if ( ! is_string( $str ) ) {
			return $str;
		}

		switch ( $str ) {
			case 'false':
				return false;
			case 'true':
				return true;
			default:
				return $str;
		}
	}

	/**
	 * Convert string number to int or float.
	 *
	 * @param string $str
	 *
	 * @return float|int
	 */

	private function convert_number( $str ) {
		if ( is_numeric( $str ) && ! is_string( $str ) || ! is_numeric( $str ) ) {
			return $str;
		}

		if ( $str == (int) $str ) {
			return (int) $str;
		} else {
			return (float) $str;
		}
	}

	/**
	 * Get converted value.
	 *
	 * @param Papi_Core_Conditional_Rule $rule
	 *
	 * @return array
	 */

	private function get_converted_value( Papi_Core_Conditional_Rule $rule ) {
		$value = $this->get_value( $rule );

		// Convert numeric values.
		if ( is_numeric( $value ) && is_numeric( $rule->value ) ) {
			return [
				$this->convert_number( $value ),
				$this->convert_number( $rule->value )
			];
		}

		// Convert bool value.
		if ( $rule->value === true || $rule->value === false ) {
			return [
				$this->convert_bool( $value ),
				$rule->value
			];
		}

		return [$value, $rule->value];
	}

	/**
	 * Get deep value.
	 *
	 * @param string $slug
	 * @param mixed $value
	 *
	 * @return mixed
	 */

	private function get_deep_value( $slug, $value ) {
		$slugs = explode( '.', $slug );
		array_shift( $slugs );
		return papi_field_value( $slugs, $value, $value );
	}

	/**
	 * Get property value.
	 *
	 * @param Papi_Core_Conditional_Rule $slug
	 *
	 * @return mixed
	 */

	private function get_value( Papi_Core_Conditional_Rule $rule ) {
		if ( defined( 'DOING_PAPI_AJAX' ) && DOING_PAPI_AJAX ) {
			$result    = papi_get_qs( [ 'page_type', 'slug', 'value'], true );
			$page_type = papi_get_page_type_by_post_id();

			if ( ! empty( $result ) && $page_type instanceof Papi_Page_Type !== false ) {
				$prop_slug  = $result['slug'];
				$prop_value = $result['value'];

				if ( $property = $page_type->get_property( $prop_slug ) ) {
					$post_id = papi_get_post_id();
					$prop_value = $property->format_value( $prop_value, $prop_slug, $post_id );
					$value = papi_filter_format_value( $property->type, $prop_value, $prop_slug, $post_id );
					return $this->get_deep_value( $rule->slug, $value );
				}
			}
		}

		if ( papi_is_option_page() ) {
			$value = papi_get_option( $rule->slug );
		} else {
		 	$value = papi_get_field( $rule->slug );
		}

		return $this->get_deep_value( $rule->slug, $value );
	}

	/**
	 * Equal conditional rule.
	 *
	 * @param Papi_Core_Conditional_Rule $rule
	 *
	 * @return bool
	 */

	public function rule_equal( Papi_Core_Conditional_Rule $rule ) {
		list( $value, $rule_value ) = $this->get_converted_value( $rule );
		return $value === $rule_value;
	}

	/**
	 * Not equal conditional rule.
	 *
	 * @param Papi_Core_Conditional_Rule $rule
	 *
	 * @return bool
	 */

	public function rule_not_equal( Papi_Core_Conditional_Rule $rule ) {
		list( $value, $rule_value ) = $this->get_converted_value( $rule );
		return $value !== $rule_value;
	}

	/**
	 * Greater then conditional rule.
	 *
	 * @param Papi_Core_Conditional_Rule $rule
	 *
	 * @return bool
	 */

	public function rule_greater_then( Papi_Core_Conditional_Rule $rule ) {
		$value = $this->get_value( $rule );

		if ( ! is_numeric( $value ) || ! is_numeric( $rule->value ) ) {
			return false;
		}

		return $this->convert_number( $value ) > $this->convert_number( $rule->value );
	}

	/**
	 * Greater then or equal conditional rule.
	 *
	 * @param Papi_Core_Conditional_Rule $rule
	 *
	 * @return bool
	 */

	public function rule_greater_then_or_equal( Papi_Core_Conditional_Rule $rule ) {
		$value = $this->get_value( $rule );

		if ( ! is_numeric( $value ) || ! is_numeric( $rule->value ) ) {
			return false;
		}

		return $this->convert_number( $value ) >= $this->convert_number( $rule->value );
	}

	/**
	 * Less then conditional rule.
	 *
	 * @param Papi_Core_Conditional_Rule $rule
	 *
	 * @return bool
	 */

	public function rule_less_then( Papi_Core_Conditional_Rule $rule ) {
		$value = $this->get_value( $rule );

		if ( ! is_numeric( $value ) || ! is_numeric( $rule->value ) ) {
			return false;
		}

		return $this->convert_number( $value ) < $this->convert_number( $rule->value );
	}

	/**
	 * Less then or equal conditional rule.
	 *
	 * @param Papi_Core_Conditional_Rule $rule
	 *
	 * @return bool
	 */

	public function rule_less_then_or_equal( Papi_Core_Conditional_Rule $rule ) {
		$value = $this->get_value( $rule );

		if ( ! is_numeric( $value ) || ! is_numeric( $rule->value ) ) {
			return false;
		}

		return $this->convert_number( $value ) <= $this->convert_number( $rule->value );
	}

	/**
	 * In array conditional rule.
	 *
	 * @param Papi_Core_Conditional_Rule $rule
	 *
	 * @return bool
	 */

	public function rule_in( Papi_Core_Conditional_Rule $rule ) {
		list( $value, $rule_value ) = $this->get_converted_value( $rule );

		if ( ! is_array( $rule_value ) ) {
			return false;
		}

		return in_array( $value, $rule_value );
	}

	/**
	 * Not in array conditional rule.
	 *
	 * @param Papi_Core_Conditional_Rule $rule
	 *
	 * @return bool
	 */

	public function rule_not_in( Papi_Core_Conditional_Rule $rule ) {
		list( $value, $rule_value ) = $this->get_converted_value( $rule );

		if ( ! is_array( $rule_value ) ) {
			return false;
		}

		return ! in_array( $value, $rule_value );
	}

	/**
	 * Like conditional rule.
	 *
	 * @param Papi_Core_Conditional_Rule $rule
	 *
	 * @return bool
	 */

	public function rule_like( Papi_Core_Conditional_Rule $rule ) {
		$value = $this->get_value( $rule );

		if ( ! is_string( $value ) ) {
			$value = papi_convert_to_string( $value );
		}

		if ( papi_is_empty ( $value ) ) {
			return false;
		}

		return strpos( strtolower( $value ), strtolower( $rule->value ) ) !== false;
	}

	/**
	 * Between conditional rule.
	 *
	 * @param Papi_Core_Conditional_Rule $rule
	 *
	 * @return bool
	 */

	public function rule_between( Papi_Core_Conditional_Rule $rule ) {
		$value = $this->get_value( $rule );

		if ( ! is_array( $rule->value ) ) {
			return false;
		}

		foreach ( $rule->value as $index => $v ) {
			$v = $this->convert_number( $v );

			if ( is_numeric( $v ) ) {
				$rule->value[$index] = $v;
			} else {
				unset( $rule->value[$index] );
			}
		}

		if ( ! is_numeric( $value ) || count( $rule->value ) !== 2 ) {
			return false;
		}

		$value = $this->convert_number( $value );

		return $rule->value[0] <= $value && $value <= $rule->value[1];
	}

	/**
	 * Not exists conditional rule.
	 *
	 * @param Papi_Core_Conditional_Rule $rule
	 *
	 * @return bool
	 */

	public function rule_not_exists( Papi_Core_Conditional_Rule $rule ) {
		return $this->get_value( $rule ) === null;
	}

	/**
	 * Setup filters.
	 */

	public function setup_filters() {
		add_filter( 'papi/conditional/rule/=', [$this, 'rule_equal'] );
		add_filter( 'papi/conditional/rule/!=', [$this, 'rule_not_equal'] );
		add_filter( 'papi/conditional/rule/>', [$this, 'rule_greater_then'] );
		add_filter( 'papi/conditional/rule/>=', [$this, 'rule_greater_then_or_equal'] );
		add_filter( 'papi/conditional/rule/<', [$this, 'rule_less_then'] );
		add_filter( 'papi/conditional/rule/<=', [$this, 'rule_less_then_or_equal'] );
		add_filter( 'papi/conditional/rule/IN', [$this, 'rule_in'] );
		add_filter( 'papi/conditional/rule/NOT IN', [$this, 'rule_not_in'] );
		add_filter( 'papi/conditional/rule/LIKE', [$this, 'rule_like'] );
		add_filter( 'papi/conditional/rule/BETWEEN', [$this, 'rule_between'] );
		add_filter( 'papi/conditional/rule/NOT EXISTS', [$this, 'rule_not_exists'] );
	}
}

new Papi_Conditional_Rules();