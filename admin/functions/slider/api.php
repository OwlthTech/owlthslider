<?php


/**
 * Register REST API routes for sliders.
 */
function os_register_slider_rest_routes()
{
    register_rest_route('os-slider/v1', '/sliders/(?P<id>\d+)', array(
        'methods' => 'GET',
        'callback' => 'os_get_slider_data',
        'permission_callback' => 'os_slider_permission_check',
        'args' => array(
            'id' => array(
                'validate_callback' => 'os_validate_numeric',
            ),
        ),
    ));

    register_rest_route('os-slider/v1', '/sliders/(?P<id>\d+)', array(
        'methods' => 'POST',
        'callback' => 'os_update_slider_data',
        'permission_callback' => 'os_slider_permission_check',
        'args' => array(
            'id' => array(
                'validate_callback' => 'os_validate_numeric',
            ),
            'slides' => array(
                'type' => 'array',
                'description' => 'Array of slide data',
                'items' => array(
                    'type' => 'object',
                    'properties' => os_get_slider_schema()['slider_data']['properties'],
                ),
                'required' => true,
            ),
            'os_slider_options' => array(
                'type' => 'object',
                'description' => 'Slider setting options data',
                'properties' => os_get_slider_options_schema()['slider_options']['properties'],
                'required' => false,
            ),
        ),
    ));
}
add_action('rest_api_init', 'os_register_slider_rest_routes');

/**
 * Permission callback for REST API endpoints.
 *
 * @param WP_REST_Request $request The current request.
 * @return bool True if the user has permission, false otherwise.
 */
function os_slider_permission_check($request)
{
    // return current_user_can('edit_posts');
    return true;
}

/**
 * Get slider data via REST API.
 *
 * @param WP_REST_Request $request The REST request.
 * @return WP_REST_Response|WP_Error The slider data or error.
 */
function os_get_slider_data($request)
{
    $post_id = intval($request['id']);

    // Check if the post exists and is of type 'os_slider'
    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'os_slider') {
        return new WP_Error('invalid_post', __('Invalid slider ID.', 'owlthslider'), array('status' => 404));
    }

    // Get slider data
    $slider_data = get_post_meta($post_id, '_os_slider_data', true);
    $slider_data = is_array($slider_data) ? $slider_data : array();

    // Get slider options
    $slider_options = get_post_meta($post_id, '_os_slider_options', true);
    $slider_options = is_array($slider_options) ? $slider_options : array();

    return rest_ensure_response(array(
        'slides' => $slider_data,
        'options' => $slider_options,
    ));
}


/**
 * Update slider data via REST API.
 *
 * @param WP_REST_Request $request The REST request.
 * @return WP_REST_Response|WP_Error The response or error.
 */
function os_update_slider_data($request)
{
    $post_id = intval($request['id']);
    $slides = $request->get_param('slides');
    $slider_options = $request->get_param('os_slider_options');

    // Validate post
    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'os_slider') {
        return new WP_Error('invalid_post', __('Invalid slider ID.', 'owlthslider'), array('status' => 404));
    }

    // Prepare data to save
    $data_to_save = array(
        'slides' => $slides,
    );

    if (!empty($slider_options)) {
        $data_to_save['os_slider_options'] = $slider_options;
    }

    // Call the unified save function
    $result = os_save_all_slider_data($post_id, $data_to_save);

    if (is_wp_error($result)) {
        return $result;
    }

    return rest_ensure_response(array('success' => true, 'message' => __('Slider updated successfully.', 'owlthslider')));
}