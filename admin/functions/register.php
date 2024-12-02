<?php

/**
 * Register Custom Post Type for Sliders.
 */
function os_register_slider_cpt()
{
    $labels = array(
        'name' => __('Sliders', 'owlthslider'),
        'singular_name' => __('Slider', 'owlthslider'),
        'menu_name' => __('Owlth Sliders', 'owlthslider'),
        'add_new' => __('Add New Slider', 'owlthslider'),
        'add_new_item' => __('Add New Slider', 'owlthslider'),
        'edit_item' => __('Edit Slider', 'owlthslider'),
        'new_item' => __('New Slider', 'owlthslider'),
        'view_item' => __('View Slider', 'owlthslider'),
        'view_items' => __('View Sliders', 'owlthslider'),
        'all_items' => __('All Sliders', 'owlthslider'),
        'search_items' => __('Search Sliders', 'owlthslider'),
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_rest' => true,
        'query_var' => false,
        'rewrite' => false,
        'capability_type' => 'post',
        'has_archive' => false,
        'hierarchical' => false,
        'supports' => array('title'),
        'taxonomies' => array('os_slider_type', 'os_slider_effect')
    );

    register_post_type('os_slider', $args);

    /**
     * Slider Types
     * @var mixed
     */
    $slider_type_taxonomy_labels = array(
        'name' => __('Slider Types', 'owlthslider'),
        'singular_name' => __('Slider Type', 'owlthslider'),
        'search_items' => __('Search Slider Types', 'owlthslider'),
        'all_items' => __('All Slider Types', 'owlthslider'),
        'parent_item' => __('Parent Slider Type', 'owlthslider'),
        'parent_item_colon' => __('Parent Slider Type:', 'owlthslider'),
        'edit_item' => __('Edit Slider Type', 'owlthslider'),
        'update_item' => __('Update Slider Type', 'owlthslider'),
        'add_new_item' => __('Add New Slider Type', 'owlthslider'),
        'new_item_name' => __('New Slider Type Name', 'owlthslider'),
        'menu_name' => __('Slider Types', 'owlthslider'),
    );

    $slider_type_taxonomy_args = array(
        'hierarchical' => true,
        'labels' => $slider_type_taxonomy_labels,
        'show_ui' => true,
        'show_in_rest' => true,
        'show_in_menu' => false,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'slider-type'),
    );

    register_taxonomy('os_slider_type', array('os_slider'), $slider_type_taxonomy_args);

    // Predefine categories for Slider Types
    $predefined_slider_types = array(
        'Default',
        'Carousel',
        'Reviews',
        'Products'
    );

    foreach ($predefined_slider_types as $type) {
        if (!term_exists($type, 'os_slider_type')) {
            wp_insert_term($type, 'os_slider_type');
        }
    }

}
add_action('init', 'os_register_slider_cpt');


function os_register_slider_meta_for_rest_api()
{
    // Register '_os_slider_data' meta field
    register_post_meta('os_slider', '_os_slider_data', array(
        'show_in_rest' => true,
        'type' => 'array',
        'single' => true,
        'sanitize_callback' => 'sanitize_slider_data'
    ));

    // Register '_os_slider_type' meta field
    register_post_meta('os_slider', '_os_slider_type', array(
        'show_in_rest' => true,
        'type' => 'string',
        'single' => true,
        'sanitize_callback' => 'sanitize_text_field'
    ));

    // Register '_os_slider_autoplay_duration' meta field
    register_post_meta('os_slider', '_os_slider_autoplay_duration', array(
        'show_in_rest' => true,
        'type' => 'integer',
        'single' => true,
        'sanitize_callback' => 'absint'
    ));

    // Register '_os_slider_autoplay_delay' meta field
    register_post_meta('os_slider', '_os_slider_autoplay_delay', array(
        'show_in_rest' => true,
        'type' => 'integer',
        'single' => true,
        'sanitize_callback' => 'absint'
    ));

    // Register '_os_slider_activeslide_effect' meta field
    register_post_meta('os_slider', '_os_slider_activeslide_effect', array(
        'show_in_rest' => true,
        'type' => 'string',
        'single' => true,
        'sanitize_callback' => 'sanitize_text_field'
    ));
}
// add_action('init', 'os_register_slider_meta_for_rest_api');



