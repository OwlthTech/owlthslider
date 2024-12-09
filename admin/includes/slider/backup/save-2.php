<?php

/**
 * Sanitize and validate slider data before save.
 *
 * @param array $data The input data to sanitize and validate.
 * @return array|WP_Error Sanitized data or WP_Error on failure.
 */
function os_sanitize_validate_data_handler($data)
{
    $sanitized_data = array();

    // Sanitize Slider Data (Table)
    if (isset($data['slides']) && is_array($data['slides'])) {
        // error_log(print_r($data['slides'], true));
        $sanitized_data['slides'] = array_map('os_slider_sanitize_slide', $data['slides']);
    }

    // Sanitize slider options array
    if (isset($data['os_slider_options']) && is_array($data['os_slider_options'])) {
        $sanitized_data['os_slider_options'] = os_slider_sanitize_options($data['os_slider_options']);
    }

    // Apply a filter to allow further customization
    $sanitized_data = apply_filters('os_slider_sanitize_data', $sanitized_data, $data);

    // Validation can be added here if needed

    return $sanitized_data;
}


/**
 * Unified save function to save all slider data.
 * Using in POST & Ajax save requests handler functions 
 *
 * @param int   $post_id The post ID.
 * @param array $data    The data to save.
 * @return bool|WP_Error True on success, WP_Error on failure.
 */
function os_save_data_handler($post_id, $data)
{
    // Sanitize and validate data
    $sanitized_data = os_sanitize_validate_data_handler($data);
    error_log(print_r($sanitized_data, true));

    // Handle errors from sanitization
    if (is_wp_error($sanitized_data)) {
        return $sanitized_data;
    }

    // Save Slider Data (Table)
    if (isset($sanitized_data['slides'])) {
        update_post_meta($post_id, '_os_slider_data', $sanitized_data['slides']);
    }

    // Save Slider Type and Effect
    if (isset($sanitized_data['os_slider_type'])) {
        wp_set_post_terms($post_id, array($sanitized_data['os_slider_type']), 'os_slider_type', false);
    }

    if (isset($sanitized_data['os_slider_effect'])) {
        wp_set_post_terms($post_id, array($sanitized_data['os_slider_effect']), 'os_slider_effect', false);
    }

    // Save slider options array
    if (isset($sanitized_data['os_slider_options'])) {
        update_post_meta($post_id, '_os_slider_options', $sanitized_data['os_slider_options']);
    }

    // Allow developers to hook into the save process
    do_action('os_slider_after_save', $post_id, $sanitized_data);

    return true;
}


/**
 * AJAX Auto-Save Slider Data
 * @return void
 */
function os_save_data_ajax()
{
    error_log(print_r($_POST, true));
    // Verify the universal nonce
    if (!isset($_POST['os_slider_universal_nonce']) || !wp_verify_nonce($_POST['os_slider_universal_nonce'], 'os_save_slider_universal_nonce_action')) {
        wp_send_json_error('Unauthorized nonce verification failed', 403);
        return;
    }

    // Get and sanitize post ID
    $post_id = intval($_POST['post_id']);
    if (!$post_id || !current_user_can('edit_post', $post_id)) {
        wp_send_json_error('Unauthorized user or invalid post ID', 403);
        return;
    }

    // Optional: Check if the post exists and is of type 'os_slider'
    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'os_slider') {
        wp_send_json_error('Invalid post type', 400);
        return;
    }

    // Parse the slider data
    if (isset($_POST['slider_data'])) {
        parse_str($_POST['slider_data'], $slider_data);

        // Save slider data using the unified save function
        $result = os_save_data_handler($post_id, $slider_data);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message(), 400);
            return;
        }
    }

    wp_send_json_success('Slider data saved successfully');
}


/**
 * Save slider data
 * @param mixed $post_id
 * @return void
 */
function os_save_data($post_id) {
    // Verify the universal nonce
    if (!isset($_POST['os_slider_universal_nonce']) || !wp_verify_nonce($_POST['os_slider_universal_nonce'], 'os_save_slider_universal_nonce_action')) {
        return;
    }

    // Check if auto-saving, do nothing
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Check user permissions
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Collect data from $_POST
    $data = $_POST;
    
    // post_ID
    // os_slider_type (id)
    // os_slider_universal_nonce
    // os_slider_options
    
    // error_log(print_r($data, true));
    // Use the unified save function
    $result = os_save_data_handler($post_id, $data);

    if (is_wp_error($result)) {
        // Optionally, add an admin notice or log the error
        error_log('Slider Save Error: ' . $result->get_error_message());
        return;
    }
    error_log('Slider Saved successfully: ' . $result);
}


/**
 * Functions not in use
 * =================================================================================
 */



/**
 * Recursively sanitize and validate data using schema.
 *
 * @param array $data The raw data to be sanitized and validated.
 * @param array $schema The schema to use for sanitization and validation.
 * @return array Sanitized and validated data.
 */
function os_sanitize_validate_data_using_schema($data, $schema)
{
    $sanitized_data = [];

    foreach ($schema as $key => $field) {
        // Skip if the key doesn't exist in the input data
        if (!isset($data[$key])) {
            continue;
        }

        $value = $data[$key];

        // Check for nested fields (e.g., groups or objects)
        if (isset($field['properties']) && is_array($field['properties'])) {
            // Recursively sanitize and validate nested fields
            $value = os_sanitize_validate_data_using_schema($value, $field['fields']);
        } else {
            // Sanitize the data using the schema's sanitize_callback
            if (isset($field['sanitize_callback']) && is_callable($field['sanitize_callback'])) {
                $value = call_user_func($field['sanitize_callback'], $value);
            }

            // Validate the data using the schema's validate_callback
            if (isset($field['validate_callback']) && is_callable($field['validate_callback'])) {
                $is_valid = call_user_func($field['validate_callback'], $value, $field);

                if (!$is_valid) {
                    // Handle invalid data (e.g., log an error, skip the field, or use a default value)
                    continue; // Skip adding invalid data to the sanitized result
                }
            }
        }

        $sanitized_data[$key] = $value;
    }

    return $sanitized_data;
}

/**
 * Sanitize individual slide data using schema with nested fields.
 *
 * @param array $slide The slide data.
 * @return array Sanitized slide data.
 */
function os_slider_sanitize_slide_using_schema($slide)
{
    $schema = os_get_slider_schema()['default']['fields'];
    return os_sanitize_validate_data_using_schema($slide, $schema);
}

/**
 * Sanitize slider options using schema with nested fields.
 *
 * @param array $options The slider options array.
 * @return array Sanitized slider options.
 */
function os_slider_sanitize_options_using_schema($options)
{
    $schema = os_get_slider_options_schema()['slider_options']['fields'];
    return os_sanitize_validate_data_using_schema($options, $schema);
}
