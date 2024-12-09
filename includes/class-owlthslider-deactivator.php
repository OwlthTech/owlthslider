<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://owlth.tech
 * @since      1.0.0
 *
 * @package    Owlthslider
 * @subpackage Owlthslider/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Owlthslider
 * @subpackage Owlthslider/includes
 * @author     Owlth Tech <nil@owlth.tech>
 */
class Owlthslider_Deactivator {

	/**
	 * Handle plugin deactivation logic.
	 *
	 * Removes default terms for the `os_slider_type` and `os_slider_template` taxonomies if they exist.
	 *
	 * @since 1.0.0
	 */
	public static function deactivate() {
		// Default terms to remove
		$default_slider_types = array('Default', 'Carousel', 'Reviews', 'Products');
		$default_slider_templates = array(
			'Default', 'Carousel-1', 'Carousel-2', 'Carousel-3',
			'Reviews-1', 'Reviews-2', 'Reviews-3',
			'Products-1', 'Products-2', 'Products-3'
		);

		// Remove default slider types
		foreach ($default_slider_types as $type) {
			$term = get_term_by('name', $type, 'os_slider_type');
			if ($term && !is_wp_error($term)) {
				wp_delete_term($term->term_id, 'os_slider_type');
			}
		}

		// Remove default slider templates
		foreach ($default_slider_templates as $template) {
			$term = get_term_by('name', $template, 'os_slider_template');
			if ($term && !is_wp_error($term)) {
				wp_delete_term($term->term_id, 'os_slider_template');
			}
		}
	}
}
