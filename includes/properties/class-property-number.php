<?php

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Page Type Builder - Property Number
 */

class PropertyNumber extends PTB_Property {

  /**
   * Get the html for output.
   *
   * @since 1.0
   *
   * @return string
   */

  public function html () {
    if (isset($this->get_options()->custom->css_class)) {
      $css_class = $this->get_options()->custom->css_class;
    } else {
      $css_class = '';
    }
    
    return PTB_Html::input('number', array(
      'name' => $this->get_options()->name,
      'id' => $this->get_options()->name,
      'value' => $this->get_options()->value,
      'class' => $this->css_classes()
    ));
  }
  
  /**
   * Convert the value of the property before we output it to the application.
   *
   * @param mixed $value
   * @since 1.0
   *
   * @return int|float
   */
  
  public function convert ($value) {
    if (floatval($value) && intval($value) != floatval($value)) {
      return floatval($value);
    } else {
      return intval($value);
    }
  }
}