<?php


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
 * Custom sanitization functions
 */

function os_sanitize_boolean($value) {
    return filter_var($value, FILTER_VALIDATE_BOOLEAN);
}

function os_sanitize_datetime($value) {
    // Assume the datetime is in 'Y-m-d H:i:s' format
    $date = DateTime::createFromFormat('Y-m-d H:i:s', $value);
    return $date ? $date->format('Y-m-d H:i:s') : '';
}

function os_sanitize_date($value) {
    // Assume the date is in 'Y-m-d' format
    $date = DateTime::createFromFormat('Y-m-d', $value);
    return $date ? $date->format('Y-m-d') : '';
}

