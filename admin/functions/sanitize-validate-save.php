<?php

/**
 * Sanitize and validate slider data.
 *
 * @param array $data The input data to sanitize and validate.
 * @return array|WP_Error Sanitized data or WP_Error on failure.
 */
function os_slider_sanitize_and_validate_data($data) {
    $sanitized_data = array();

    // Sanitize Slider Data (Table)
    if (isset($data['os_slider_data']) && is_array($data['os_slider_data'])) {
        $sanitized_data['os_slider_data'] = array_map('os_slider_sanitize_slide', $data['os_slider_data']);
    }

    // Sanitize Slider Type and Effect
    if (isset($data['os_slider_type'])) {
        $sanitized_data['os_slider_type'] = intval($data['os_slider_type']);
    }

    if (isset($data['os_slider_effect'])) {
        $sanitized_data['os_slider_effect'] = intval($data['os_slider_effect']);
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
 * Sanitize individual slide data.
 *
 * @param array $slide The slide data.
 * @return array Sanitized slide data.
 */
function os_slider_sanitize_slide($slide) {
    return array(
        'enabled'          => isset($slide['enabled']) ? 'yes' : 'no',
        'heading'          => sanitize_text_field($slide['heading']),
        'caption'          => wp_kses_post($slide['caption']),
        'background_image' => esc_url_raw($slide['background_image']),
        'cta_text'         => sanitize_text_field($slide['cta_text']),
        'cta_link'         => esc_url_raw($slide['cta_link']),
    );
}

/**
 * Sanitize slider options.
 *
 * @param array $options The slider options array.
 * @return array Sanitized slider options.
 */
function os_slider_sanitize_options($options) {
    $sanitized_options = array();

    // Boolean fields
    $boolean_fields = array(
        'os_slider_loop',
        'os_slider_draggable',
        'os_slider_skip_snaps',
        'os_slider_autoplay',
        'os_slider_autoscroll',
        'os_slider_classnames',
        'os_slider_fade',
        'os_slider_scroll_progress',
        'os_slider_thumbs',
        'os_slider_wheel_gesture'
    );

    foreach ($boolean_fields as $field) {
        $sanitized_options[$field] = isset($options[$field]) && $options[$field] === 'yes' ? 'yes' : 'no';
    }

    // Numerical fields
    $numerical_fields = array(
        'os_slider_speed',
        'os_slider_autoplay_delay',
        'os_slider_autoscroll_speed',
    );

    foreach ($numerical_fields as $field) {
        if (isset($options[$field])) {
            $sanitized_options[$field] = intval($options[$field]);
        }
    }

    // 'align' option
    if (isset($options['os_slider_align'])) {
        $align_options = array('start', 'center', 'end');
        $sanitized_options['os_slider_align'] = in_array($options['os_slider_align'], $align_options) ? $options['os_slider_align'] : 'center';
    }

    return $sanitized_options;
}



/**
 * Unified save function to save all slider data.
 *
 * @param int   $post_id The post ID.
 * @param array $data    The data to save.
 * @return bool|WP_Error True on success, WP_Error on failure.
 */
function os_save_all_slider_data($post_id, $data) {
    // Sanitize and validate data
    $sanitized_data = os_slider_sanitize_and_validate_data($data);

    // Handle errors from sanitization
    if (is_wp_error($sanitized_data)) {
        return $sanitized_data;
    }

    // Save Slider Data (Table)
    if (isset($sanitized_data['os_slider_data'])) {
        update_post_meta($post_id, '_os_slider_data', $sanitized_data['os_slider_data']);
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


