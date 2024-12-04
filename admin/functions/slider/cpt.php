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


function os_register_meta() {
    // Merge the schemas for both slider data and slider options
    $schemas = array_merge(new_os_get_slider_schema(), new_os_get_slider_options_schema());
    $post_type = 'os_slider'; // Your custom post type

    foreach ($schemas as $key => $field) {
        register_post_meta(
            $post_type,
            $field['meta_key'],
            array(
                'type'              => $field['type'],
                'description'       => isset($field['description']) ? $field['description'] : '',
                'single'            => true,
                // 'sanitize_callback' => $field['sanitize_callback'],
                // 'validate_callback' => $field['validate_callback'],
                'show_in_rest'      => $field['show_in_rest'],
            )
        );
    }
}
add_action('init', 'os_register_meta');

function os_sanitize_slider_data($value, $request, $param) {
    if (!is_array($value)) {
        return array();
    }

    foreach ($value as &$slide) {
        if (!is_array($slide)) {
            $slide = array();
            continue;
        }

        // Common sanitization
        $slide['type'] = isset($slide['type']) ? sanitize_key($slide['type']) : '';

        // Sanitize based on slide type
        switch ($slide['type']) {
            case 'carousel':
                $slide['enabled'] = isset($slide['enabled']) ? rest_sanitize_boolean($slide['enabled']) : false;
                $slide['heading'] = isset($slide['heading']) ? sanitize_text_field($slide['heading']) : '';
                $slide['caption'] = isset($slide['caption']) ? wp_kses_post($slide['caption']) : '';
                // Additional carousel properties
                break;
            case 'reviews':
                $slide['enabled'] = isset($slide['enabled']) ? rest_sanitize_boolean($slide['enabled']) : false;
                $slide['author_name'] = isset($slide['author_name']) ? sanitize_text_field($slide['author_name']) : '';
                $slide['author_avatar'] = isset($slide['author_avatar']) ? esc_url_raw($slide['author_avatar']) : '';
                $slide['rating'] = isset($slide['rating']) ? floatval($slide['rating']) : 0;
                $slide['review_body'] = isset($slide['review_body']) ? wp_kses_post($slide['review_body']) : '';
                // Additional review properties
                break;
            case 'products':
                $slide['enabled'] = isset($slide['enabled']) ? rest_sanitize_boolean($slide['enabled']) : false;
                $slide['product_id'] = isset($slide['product_id']) ? intval($slide['product_id']) : 0;
                $slide['product_name'] = isset($slide['product_name']) ? sanitize_text_field($slide['product_name']) : '';
                $slide['product_price'] = isset($slide['product_price']) ? floatval($slide['product_price']) : 0.0;
                // Additional product properties
                break;
            // Handle additional slide types
            default:
                // Unknown slide type; you might want to unset or handle accordingly
                $slide = array();
                break;
        }
    }

    return $value;
}


function os_validate_slider_data($value, $request, $param) {
    if (!is_array($value)) {
        return new WP_Error('rest_invalid_type', __('Slider data must be an array.', 'owlthslider'), array('status' => 400));
    }

    foreach ($value as $slide) {
        if (!is_array($slide)) {
            return new WP_Error('rest_invalid_type', __('Each slide must be an array.', 'owlthslider'), array('status' => 400));
        }

        if (!isset($slide['type'])) {
            return new WP_Error('rest_missing_type', __('Slide type is required.', 'owlthslider'), array('status' => 400));
        }

        // Validate based on slide type
        switch ($slide['type']) {
            case 'carousel':
                // Validate carousel properties
                if (!isset($slide['heading']) || !is_string($slide['heading'])) {
                    return new WP_Error('rest_invalid_param', __('Carousel slide "heading" must be a string.', 'owlthslider'), array('status' => 400));
                }
                // Additional validations
                break;
            case 'reviews':
                // Validate review properties
                if (!isset($slide['author_name']) || !is_string($slide['author_name'])) {
                    return new WP_Error('rest_invalid_param', __('Review slide "author_name" must be a string.', 'owlthslider'), array('status' => 400));
                }
                // Additional validations
                break;
            case 'products':
                // Validate product properties
                if (!isset($slide['product_id']) || !is_int($slide['product_id'])) {
                    return new WP_Error('rest_invalid_param', __('Product slide "product_id" must be an integer.', 'owlthslider'), array('status' => 400));
                }
                // Additional validations
                break;
            default:
                return new WP_Error('rest_invalid_type', __('Invalid slide type.', 'owlthslider'), array('status' => 400));
        }
    }

    return true;
}

/**
 * Update os_slider_data and os_slider_options meta fields for a specific post.
 */
function new_update_it() {
    $post_id = 361; // Replace with your actual post ID

    // Update os_slider_data
    $slider_data = array(
        array(
            'type'     => 'carousel',
            'enabled'  => true,
            'heading'  => 'First Slide',
            'caption'  => '<p>This is the first slide.</p>',
            // Additional carousel-specific properties can be added here
        ),
        array(
            'type'     => 'reviews',
            'enabled'  => true,
            'author_details' => array(
                'author_name'   => 'John Doe',
                'author_avatar' => 'http://example.com/avatar.jpg',
            ),
            'rating'      => 4.5,
            'review_body' => '<p>Great product!</p>',
            // Additional review-specific properties can be added here
        ),
        // Add more slides as needed...
    );

    // Update os_slider_options
    $slider_options = array(
        'loop'              => false,    // Corresponds to 'os_slider_loop'
        'draggable'         => true,     // Corresponds to 'os_slider_draggable'
        'align'             => 'center', // Corresponds to 'os_slider_align'
        'autoplay'          => false,    // Corresponds to 'os_slider_autoplay'
        'autoplay_delay'    => 3000,     // Corresponds to 'os_slider_autoplay_delay' (in milliseconds)
        'autoscroll'        => false,    // Corresponds to 'os_slider_autoscroll'
        'autoscroll_speed'  => 5,        // Corresponds to 'os_slider_autoscroll_speed'
        'fade'              => false,    // Corresponds to 'os_slider_fade'
        'scroll_progress'   => false,    // Corresponds to 'os_slider_scroll_progress'
        'wheel_gesture'     => false,    // Corresponds to 'os_slider_wheel_gesture'
    );

    // Update the os_slider_data meta field
    update_post_meta($post_id, 'os_slider_data', $slider_data);

    // Update the os_slider_options meta field
    update_post_meta($post_id, 'os_slider_options', $slider_options);
}

// Hook the function to an appropriate action or call it directly
// For example, to run it once, you might attach it to an admin_init hook temporarily
// add_action('admin_init', 'new_update_it');


/**
 * Function to update os_slider_data and os_slider_options via REST API.
 */
function new_update_slider_via_rest_api() {
    $post_id = 361; // Replace with your actual post ID
    $username = 'GauMartAdmin'; // Replace with your WordPress username
    $application_password = '123456789'; // Replace with your Application Password

    // Prepare the credentials for Basic Authentication
    $credentials = base64_encode($username . ':' . $application_password);

    // REST API Endpoint
    $url = site_url("/wp-json/wp/v2/os_slider/{$post_id}");

    // Data to update
    $data = array(
        'meta' => array(
            'os_slider_data' => array(
                array(
                    'type'     => 'carousel',
                    'enabled'  => true,
                    'heading'  => 'First Slide',
                    'caption'  => '<p>This is the first slide.</p>',
                ),
                array(
                    'type'     => 'reviews',
                    'enabled'  => true,
                    'author_details' => array(
                        'author_name'   => 'John Doe',
                        'author_avatar' => 'http://example.com/avatar.jpg',
                    ),
                    'rating'      => 4.5,
                    'review_body' => '<p>Great product!</p>',
                ),
            ),
            'os_slider_options' => array(
                'loop'              => false,
                'draggable'         => true,
                'align'             => 'center',
                'autoplay'          => false,
                'autoplay_delay'    => 3000,
                'autoscroll'        => false,
                'autoscroll_speed'  => 5,
                'fade'              => false,
                'scroll_progress'   => false,
                'wheel_gesture'     => false,
            ),
        ),
    );

    // Set up the arguments for the request
    $args = array(
        'method'    => 'POST',
        'headers'   => array(
            'Authorization' => 'Basic ' . $credentials,
            'Content-Type'  => 'application/json',
        ),
        'body'      => json_encode($data),
    );

    // Perform the request
    $response = wp_remote_post($url, $args);

    // Handle the response
    if (is_wp_error($response)) {
        // Log the error
        error_log('REST API Update Error: ' . $response->get_error_message());
    } else {
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);

        if ($response_code >= 200 && $response_code < 300) {
            // Success
            error_log('Slider updated successfully via REST API.');
        } else {
            // Log the response body for debugging
            error_log('REST API Update Failed: ' . $response_body);
        }
    }
}

// Example of triggering the update function
add_action('admin_init', 'new_update_slider_via_rest_api');

