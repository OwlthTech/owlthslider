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
        // Register Custom Post Type
		if (!post_type_exists('os_slider')) {
        	self::register_custom_post_type();
		}

        // Register Taxonomies
        self::register_custom_taxonomies();

        // Insert default terms for taxonomies
        $default_slider_types = array('Default', 'Carousel', 'Reviews', 'Products');
        $default_slider_templates = array('Default', 'Carousel-1', 'Reviews-1', 'Products-1');

        foreach ($default_slider_types as $type) {
            if (!term_exists($type, 'os_slider_type')) {
                wp_insert_term($type, 'os_slider_type');
				error_log("Inserting slider_type: " . print_r($type, true));
            }
        }

        foreach ($default_slider_templates as $template) {
            if (!term_exists($template, 'os_slider_template')) {
                wp_insert_term($template, 'os_slider_template');
				error_log("Inserting slider_type: " . print_r($template, true));
            }
        }
    }

    private static function register_custom_post_type() {
        register_post_type('os_slider', array(
            'labels' => array(
                'name' => __('Sliders', 'owlthslider'),
                'singular_name' => __('Slider', 'owlthslider'),
            ),
            'public' => true,
            'has_archive' => true,
            'rewrite' => array('slug' => 'sliders'),
            'supports' => array('title', 'editor', 'thumbnail'),
        ));
    }

    private static function register_custom_taxonomies() {
        register_taxonomy('os_slider_type', 'os_slider', array(
            'labels' => array(
                'name' => __('Slider Types', 'owlthslider'),
                'singular_name' => __('Slider Type', 'owlthslider'),
            ),
            'hierarchical' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'rewrite' => array('slug' => 'slider-type'),
        ));

        register_taxonomy('os_slider_template', 'os_slider', array(
            'labels' => array(
                'name' => __('Slider Templates', 'owlthslider'),
                'singular_name' => __('Slider Template', 'owlthslider'),
            ),
            'hierarchical' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'rewrite' => array('slug' => 'slider-template'),
        ));
    }
}
