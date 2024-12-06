<?php

/**
 * Register Custom Post Type for Sliders.
 */
function os_register_cpt($cpt_slug, $cpt_taxonomies)
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
        'rest_base' => 'os_slider',
        'query_var' => true,
        'rewrite' => array('slug' => 'slider', 'with_front' => true),
        'capability_type' => 'post',
        'has_archive' => false,
        'hierarchical' => false,
        'supports' => array('title', 'custom-fields'),
        'taxonomies' => $cpt_taxonomies,
    );
    

    register_post_type($cpt_slug, $args);

    os_register_taxonomy($cpt_slug, $cpt_taxonomies);
}


function os_register_taxonomy($cpt_slug, $cpt_taxonomies) {
    
    // Predefine categories for Slider Types
    $slider_types = array(
        'Default',
        'Carousel',
        'Reviews',
        'Products'
    );

    $labels = array(
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

    $args = array(
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'show_in_rest' => true,
        'show_in_menu' => false,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'slider-type'),
    );

    register_taxonomy($cpt_taxonomies[0], array($cpt_slug), $args);

    foreach ($slider_types as $type) {
        if (!term_exists($type, 'os_slider_type')) {
            wp_insert_term($type, 'os_slider_type');
        }
    }
}

