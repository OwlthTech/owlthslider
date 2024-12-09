<?php

/**
 * Fired during plugin activation
 *
 * @link       https://owlth.tech
 * @since      1.0.0
 *
 * @package    Owlthslider
 * @subpackage Owlthslider/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Owlthslider
 * @subpackage Owlthslider/includes
 * @author     Owlth Tech <nil@owlth.tech>
 */
class Owlthslider_Activator {
	public static function activate() {
	    // Insert default terms for taxonomies
	    $default_slider_types = array('Default', 'Carousel', 'Reviews', 'Products');
	    $default_slider_templates = array('Default', 'Carousel-1', 'Reviews-1', 'Products-1');
  
	    foreach ($default_slider_types as $type) {
		  if (!term_exists($type, 'os_slider_type')) {
			wp_insert_term($type, 'os_slider_type');
		  }
	    }
  
	    foreach ($default_slider_templates as $template) {
		  if (!term_exists($template, 'os_slider_template')) {
			wp_insert_term($template, 'os_slider_template');
		  }
	    }
	}
  }
  