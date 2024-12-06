<?php


/**
 * Automatically sanitize slider data before updating post meta.
 *
 * @param mixed  $meta_value The value of the meta key.
 * @param int    $object_id  The object ID.
 * @param string $meta_key   The meta key.
 * @return mixed The sanitized meta value.
 */
function os_pre_update_slider_meta( $meta_value, $object_id, $meta_key ) {
    $errors = array();
    $slider_data_type = isset( $_POST['os_slider_type'] ) ? sanitize_text_field( $_POST['os_slider_type'] ) : 'default';

    if ( 'os_slider_data' === $meta_key ) {
        return os_sanitize_and_validate_meta($slider_data_type, $errors);
    }

    if ( 'os_slider_options' === $meta_key ) {
        return os_sanitize_and_validate_options_meta( $object_id, false, $errors );
    }

    return $meta_value;
}
add_filter( 'pre_update_post_meta', 'os_pre_update_slider_meta', 10, 3 );


/**
 * Sanitize and validate meta data based on the provided schema.
 *
 * @param int    $post_id   The ID of the post.
 * @param bool   $is_ajax   Whether the request is an AJAX request.
 * @param string $type      The type of schema ('default', 'reviews', etc.).
 * @param array  $errors    Array to store errors if any.
 * @return array The sanitized data.
 */
function os_sanitize_and_validate_meta( $type, &$errors ) {
    $slides_data = isset( $_POST['slides'] ) ? $_POST['slides'] : array();
    $sanitized_data = array();

    foreach ( $slides_data as $slide ) {
        $sanitized_data[] = os_slider_sanitize_slide( $slide, $type );
    }

    return $sanitized_data;
}

/**
 * Sanitize a single slide based on the schema type.
 *
 * @param array  $slide The slide data to sanitize.
 * @param string $type  The type of schema ('default', 'reviews', etc.).
 * @return array The sanitized slide data.
 */
function os_slider_sanitize_slide( $slide, $type ) {
    $type = (string) $type;
    $sanitized_slide = array();

    switch ( $type ) {
        case 'reviews':
            $sanitized_slide = array(
                'enabled'        => isset( $slide['enabled'] ) ? 1 : 0,
                'author_details' => array(
                    'author_name'  => sanitize_text_field( $slide['author_name'] ),
                    'author_avatar' => esc_url_raw( $slide['author_avatar'] ),
                ),
                'rating'        => floatval( $slide['rating'] ),
                'review_body'   => wp_kses_post( $slide['review_body'] ),
                'review_date'   => sanitize_text_field( $slide['review_date'] ),
            );
            break;
        default:
            $sanitized_slide = array(
                'enabled'          => isset( $slide['enabled'] ) ? 1 : 0,
                'heading'          => sanitize_text_field( $slide['heading'] ),
                'caption'          => wp_kses_post( $slide['caption'] ),
                'background_image' => esc_url_raw( $slide['background_image'] ),
                'cta_details'      => array(
                    'cta_text'         => sanitize_text_field( $slide['cta_text'] ),
                    'cta_link'         => esc_url_raw( $slide['cta_link'] ),
                ),
                'validity'         => sanitize_text_field( $slide['validity'] ),
            );
            break;
    }

    return $sanitized_slide;
}


/**
 * Sanitize and validate slider options meta data based on the provided schema.
 *
 * @param int    $post_id   The ID of the post.
 * @param bool   $is_ajax   Whether the request is an AJAX request.
 * @param array  $errors    Array to store errors if any.
 * @return array The sanitized data.
 */
function os_sanitize_and_validate_options_meta( $post_id, $is_ajax, &$errors ) {
    $options_data = isset( $_POST['os_slider_options'] ) ? $_POST['os_slider_options'] : array();
    $sanitized_options = array();

    $schema = os_get_slider_option_schema();
    $properties = $schema['slider_options']['properties'];

    foreach ( $properties as $key => $field_schema ) {
        if ( isset( $options_data[ $key ] ) ) {
            $value = $options_data[ $key ];

            // Sanitize the value based on the provided sanitize_callback
            if ( isset( $field_schema['sanitize_callback'] ) && is_callable( $field_schema['sanitize_callback'] ) ) {
                $sanitized_value = call_user_func( $field_schema['sanitize_callback'], $value );
                $sanitized_options[ $key ] = $sanitized_value;
            } else {
                $sanitized_options[ $key ] = $value;
            }
        } else {
            // Set default value if field is not set
            $sanitized_options[ $key ] = isset( $field_schema['default'] ) ? $field_schema['default'] : null;
        }
    }

    return $sanitized_options;
}


/**
 * Validate a field value based on its type.
 *
 * @param string $type  The field type.
 * @param mixed  $value The field value.
 * @return bool True if the value is valid, false otherwise.
 */
function os_validate_field_type( $type, $value ) {
    switch ( $type ) {
        case 'boolean':
            return is_bool( $value ) || in_array( $value, array( '1', '0', 1, 0 ), true );
        case 'string':
            return is_string( $value );
        case 'wp_editor':
            return is_string( $value );
        case 'image':
        case 'url':
            return filter_var( $value, FILTER_VALIDATE_URL ) !== false;
        case 'float':
            return is_float( $value ) || is_numeric( $value );
        case 'datetime':
        case 'date':
            return strtotime( $value ) !== false;
        case 'object':
            return is_array( $value );
        default:
            return false;
    }
}
