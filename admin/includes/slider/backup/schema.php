<?php

function get_schema_carousel()
{
    return array(
        'type' => 'object',
        'properties' => array(
            'slider_type' => array(
                'type' => 'string',
                'enum' => array('carousel'),
                'default' => 'carousel',
            ),
            'enabled' => array(
                'type' => 'boolean',
                'default' => true,
            ),
            'heading' => array(
                'type' => 'string',
            ),
            'caption' => array(
                'type' => 'string',
            ),
            // Additional properties for carousel slides...
        ),
        'required' => array('slider_type', 'enabled'),
        'additionalProperties' => false,
    );
}

function get_schema_reviews()
{
    return array(
        'type' => 'object',
        'properties' => array(
            'slider_type' => array(
                'type' => 'string',
                'name' => 's;oder_type',
                'enum' => array('reviews'),
                'default' => 'reviews',
            ),
            'enabled' => array(
                'type' => 'boolean',
                'name' => 'enabled',
                'default' => true,
            ),
            'author_details' => array( // Adjusted to match stored data
                'type' => 'object',
                'name' => 'author_details',
                'properties' => array(
                    'author_name' => array(
                        'type' => 'string',
                        'name' => 'author_name'
                    ),
                    'author_avatar' => array(
                        'type' => 'string',
                        'name' => 'author_avatar',
                        'format' => 'uri',
                    ),
                ),
            ),
            'rating' => array(
                'type' => 'number',
                'name' => 'rating'
            ),
            'review_body' => array(
                'type' => 'string',
                'name' => 'review_body'
            ),
        ),
        'required' => array('slider_type', 'enabled'),
        'additionalProperties' => false,
    );
}

function get_schema_products()
{
    return array(
        'type' => 'object',
        'properties' => array(
            'slider_type' => array(
                'type' => 'string',
                'name' => 'slider_type',
                'enum' => array('products'),
                'default' => 'products',
            ),
            'enabled' => array(
                'type' => 'boolean',
                'name' => 'enabled',
                'default' => true,
            ),
            'product_id' => array(
                'type' => 'integer',
                'name' => 'product_id'
            ),
            'product_name' => array(
                'type' => 'string',
                'name' => 'product_name'
            ),
            'product_price' => array(
                'type' => 'number',
                'name' => 'product_price'
            ),
            // Additional properties for product slides...
        ),
        'required' => array('slider_type', 'enabled'),
        'additionalProperties' => false,
    );
}

function get_full_slider_schema()
{
    $slide_schemas = array(
        get_schema_carousel(),
        get_schema_reviews(),
        get_schema_products(),
        // Add more slide schemas here if needed...
    );

    $schemas = array(
        'slider_data' => array(
            'meta_key' => 'os_slider_data',
            'type' => 'object',
            'name' => 'slider_data',
            'description' => __('Data for slides', 'owlthslider'),
            'sanitize_callback' => 'sanitize_slider_meta',
            'validate_callback' => 'validate_slider_meta',
            'show_in_rest' => array(
                'schema' => array(
                    'type' => 'object',
                    'properties' => array(
                        'slides' => array(
                            'type' => 'array',
                            'items' => array(
                                'type' => 'object', // Corrected to 'object'
                                'oneOf' => $slide_schemas, // Directly pass the slide schemas
                            ),
                        ),
                    ),
                    'additionalProperties' => true, // Enforce strict schema
                ),
            ),
        ),
    );

    return apply_filters('get_full_slider_schema', $schemas);
}


function get_schema_slider_options()
{
    return array(
        'slider_options' => array(
            'meta_key' => 'os_slider_options',
            'type' => 'object',
            'description' => __('Slider options', 'owlthslider'),
            'sanitize_callback' => 'sanitize_slider_options_meta',
            'validate_callback' => 'validate_slider_options_meta',
            'show_in_rest' => array(
                'schema' => array(
                    'type' => 'object',
                    'properties' => array(
                        'loop' => array(
                            'type' => 'boolean',
                            'description' => __('Enable Loop', 'owlthslider'),
                            'default' => false,
                        ),
                        'draggable' => array(
                            'type' => 'boolean',
                            'description' => __('Enable Draggable', 'owlthslider'),
                            'default' => true,
                        ),
                        'align' => array(
                            'type' => 'string',
                            'description' => __('Alignment', 'owlthslider'),
                            'default' => 'center',
                        ),
                        'autoplay' => array(
                            'type' => 'boolean',
                            'description' => __('Enable Autoplay', 'owlthslider'),
                            'default' => false,
                        ),
                        'autoplay_delay' => array(
                            'type' => 'integer',
                            'description' => __('Autoplay Delay (ms)', 'owlthslider'),
                            'default' => 3000,
                        ),
                        'autoscroll' => array(
                            'type' => 'boolean',
                            'description' => __('Enable Autoscroll', 'owlthslider'),
                            'default' => false,
                        ),
                        'autoscroll_speed' => array(
                            'type' => 'integer',
                            'description' => __('Autoscroll Speed', 'owlthslider'),
                            'default' => 5,
                        ),
                        'fade' => array(
                            'type' => 'boolean',
                            'description' => __('Enable Fade', 'owlthslider'),
                            'default' => false,
                        ),
                        'scroll_progress' => array(
                            'type' => 'boolean',
                            'description' => __('Show Scroll Progress', 'owlthslider'),
                            'default' => false,
                        ),
                        'wheel_gesture' => array(
                            'type' => 'boolean',
                            'description' => __('Enable Wheel Gesture', 'owlthslider'),
                            'default' => false,
                        ),
                    ),
                    'additionalProperties' => true, // Disallow additional properties
                ),
            ),
        ),
    );
}


function os_register_meta() {
    // Merge the schemas for both slider data and slider options
    $schemas = array_merge(get_full_slider_schema(), get_schema_slider_options());
    $custom_post_type = 'os_slider'; // Your custom post type

    foreach ($schemas as $key => $field) {
        register_meta(
            'post',
            $field['meta_key'],
            array(
                'type'              => $field['type'],
                'description'       => isset($field['description']) ? $field['description'] : '',
                'single'            => true,
                'sanitize_callback' => 'sanitize_slider_meta',
                'validate_callback' => 'validate_slider_meta',
                'object_subtype'    => $custom_post_type,
                'show_in_rest'      => array(
                    'schema' => $field['show_in_rest']['schema'],
                ),
            )
        );
    }
}
add_action('init', 'os_register_meta');




/**
 * Sanitization callback for custom meta fields.
 *
 * @param mixed  $meta_value The meta value to sanitize.
 * @param string $meta_key   The meta key.
 * @param string $object_type The type of object the meta is registered to.
 * @return mixed Sanitized meta value.
 */
function sanitize_slider_meta( $meta_value, $meta_key = '', $object_type = '' ) {

    if ( ! is_array( $meta_value ) ) {
        $meta_value = array();
    }

    $master_schema = get_full_slider_schema();
    if ( isset( $master_schema['slider_data'] ) ) {
        $sanitized_value = sanitize_field( $meta_value, $master_schema['slider_data'] );
        error_log( 'Sanitized meta value: ' . print_r( $sanitized_value, true ) );
        return $sanitized_value;
    }

    return $meta_value;
}

/**
 * Sanitizes a field based on its schema.
 *
 * @param mixed $value  The value to sanitize.
 * @param array $schema The schema for the field.
 * @return mixed Sanitized value.
 */
function sanitize_field($value, $schema)
{
    if (isset($schema['sanitize_callback']) && is_callable($schema['sanitize_callback'])) {
        $value = call_user_func($schema['sanitize_callback'], $value);
    }

    if ($schema['type'] === 'object' && isset($schema['properties']) && is_array($value)) {
        $sanitized_value = array();
        foreach ($schema['properties'] as $property_key => $property_schema) {
            if (isset($value[$property_key])) {
                $sanitized_value[$property_key] = sanitize_field($value[$property_key], $property_schema);
            } else {
                $sanitized_value[$property_key] = isset($property_schema['default']) ? $property_schema['default'] : null;
            }
        }
        return $sanitized_value;
    } elseif ($schema['type'] === 'array' && isset($schema['items']) && is_array($value)) {
        $sanitized_value = array();
        foreach ($value as $item) {
            $sanitized_value[] = sanitize_field($item, $schema['items']);
        }
        return $sanitized_value;
    }

    return $value;
}


/**
 * Validation callback for custom meta fields.
 *
 * @param mixed  $meta_value      The meta value to validate.
 * @param string $meta_key        The meta key.
 * @param string $object_type     The type of object the meta is registered to.
 * @param string $object_subtype  The subtype of the object the meta is registered to.
 * @return bool|WP_Error True if the value is valid, WP_Error otherwise.
 */
function validate_slider_meta( $meta_value, $meta_key = '', $object_type = '', $object_subtype = '' ) {
    error_log( 'Validating meta key: ' . $meta_key );
    error_log( 'Meta value: ' . print_r( $meta_value, true ) );

    $master_schema = get_full_slider_schema();
    if ( isset( $master_schema['slider_data'] ) ) {
        $valid = validate_field( $meta_value, $master_schema['slider_data'] );
        error_log( 'Validation result: ' . print_r( $valid, true ) );
        return $valid;
    }

    return true;
}


/**
 * Validates a field based on its schema.
 *
 * @param mixed $value  The value to validate.
 * @param array $schema The schema for the field.
 * @return bool|WP_Error True if valid, WP_Error otherwise.
 */
function validate_field($value, $schema)
{
    if (isset($schema['validate_callback']) && is_callable($schema['validate_callback'])) {
        $valid = call_user_func($schema['validate_callback'], $value, $schema);
        if (is_wp_error($valid)) {
            return $valid;
        } elseif (!$valid) {
            return new WP_Error('invalid_field', __('Invalid value.', 'textdomain'));
        }
    }

    if ($schema['type'] === 'object' && isset($schema['properties']) && is_array($value)) {
        foreach ($schema['properties'] as $property_key => $property_schema) {
            if (isset($value[$property_key])) {
                $valid = validate_field($value[$property_key], $property_schema);
                if (is_wp_error($valid)) {
                    return $valid;
                }
            } elseif (isset($property_schema['required']) && $property_schema['required']) {
                return new WP_Error('missing_field', sprintf(__('Missing required field %s.', 'textdomain'), $property_key));
            }
        }
        // Additional validation based on 'type'
        if (isset($value['type'])) {
            $slide_type = $value['type'];
            switch ($slide_type) {
                case 'carousel':
                    // Validate required fields for 'carousel'
                    if (empty($value['heading'])) {
                        return new WP_Error('missing_field', __('Missing required field heading for carousel slide.', 'textdomain'));
                    }
                    break;
                case 'reviews':
                    // Validate required fields for 'reviews'
                    if (empty($value['author_details']['author_name'])) {
                        return new WP_Error('missing_field', __('Missing required field author_name for review slide.', 'textdomain'));
                    }
                    if (empty($value['author_details']['author_avatar'])) {
                        return new WP_Error('missing_field', __('Missing required field author_avatar for review slide.', 'textdomain'));
                    }
                    if (!isset($value['rating'])) {
                        return new WP_Error('missing_field', __('Missing required field rating for review slide.', 'textdomain'));
                    }
                    break;
                case 'products':
                    // Validate required fields for 'products'
                    if (!isset($value['product_id'])) {
                        return new WP_Error('missing_field', __('Missing required field product_id for product slide.', 'textdomain'));
                    }
                    break;
                default:
                    return new WP_Error('invalid_value', __('Invalid slide type.', 'textdomain'));
            }
        }
    } elseif ($schema['type'] === 'array' && isset($schema['items']) && is_array($value)) {
        foreach ($value as $item) {
            $valid = validate_field($item, $schema['items']);
            if (is_wp_error($valid)) {
                return $valid;
            }
        }
    }

    return true;
}
