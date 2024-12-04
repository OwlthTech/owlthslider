<?php

/**
 * Get the enhanced slider schema with support for grouped fields.
 *
 * @return array The enhanced schema array.
 */


function new_os_get_slider_schema()
{
    $schemas = array(
        'slider_data' => array(
            'meta_key' => 'os_slider_data',
            'type' => 'array',
            'description' => __('Data for slides', 'owlthslider'),
            // 'sanitize_callback' => 'os_sanitize_slider_data',
            // 'validate_callback' => 'os_validate_slider_data',
            'show_in_rest' => array(
                'schema' => array(
                    'type' => 'object',
                    'items' => array(
                        'type' => 'array',
                        'oneOf' => array(
                            // Schema for carousel slides
                            array(
                                'type' => 'array',
                                'properties' => array(
                                    'type' => array(
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
                                    // Additional properties for carousel slides
                                ),
                                'required' => array('type', 'enabled'),
                            ),
                            // Schema for review slides
                            array(
                                'type' => 'array',
                                'properties' => array(
                                    'type' => array(
                                        'type' => 'string',
                                        'enum' => array('reviews'),
                                        'default' => 'reviews',
                                    ),
                                    'enabled' => array(
                                        'type' => 'boolean',
                                        'default' => true,
                                    ),
                                    'author_name' => array(
                                        'type' => 'string',
                                    ),
                                    'author_avatar' => array(
                                        'type' => 'string',
                                        'format' => 'uri',
                                    ),
                                    'rating' => array(
                                        'type' => 'number',
                                    ),
                                    'review_body' => array(
                                        'type' => 'string',
                                    ),
                                    // Additional properties for review slides
                                ),
                                'required' => array('type', 'enabled', 'author_name', 'rating'),
                            ),
                            // Schema for product slides
                            array(
                                'type' => 'object',
                                'properties' => array(
                                    'type' => array(
                                        'type' => 'string',
                                        'enum' => array('products'),
                                        'default' => 'products',
                                    ),
                                    'enabled' => array(
                                        'type' => 'boolean',
                                        'default' => true,
                                    ),
                                    'product_id' => array(
                                        'type' => 'integer',
                                    ),
                                    'product_name' => array(
                                        'type' => 'string',
                                    ),
                                    'product_price' => array(
                                        'type' => 'number',
                                    ),
                                    // Additional properties for product slides
                                ),
                                'required' => array('type', 'enabled', 'product_id'),
                            ),
                            // Add more schemas for other slide types if needed
                        ),
                    ),
                    'additionalProperties' => true,
                ),
            ),
        ),
    );

    return apply_filters('new_os_get_slider_schema', $schemas);
}


function os_get_slider_schema()
{
    $common_fields = array(
        'enabled' => array(
            'meta_key' => 'os_slider_enabled',
            'type' => 'boolean',
            'label' => __('Enabled', 'owlthslider'),
            'default' => true,
            'sanitize_callback' => 'rest_sanitize_boolean',
            'validate_callback' => 'rest_validate_request_arg',
        ),
    );

    $schemas = array(
        'slider_data' => array(
            'meta_key' => 'os_slider_data',
            'type' => 'array',
            'description' => __('Data for slides', 'owlthslider'),
            'sanitize_callback' => 'os_sanitize_slider_data',
            'validate_callback' => 'os_validate_slider_data',
            'properties' => array(
                'default' => array(
                    'type' => 'object',
                    'description' => __('Default Slider Data', 'owlthslider'),
                    'properties' => array_merge($common_fields, array(
                        'heading' => array(
                            'meta_key' => 'os_slider_heading',
                            'type' => 'string',
                            'label' => __('Heading', 'owlthslider'),
                            'default' => '',
                            'sanitize_callback' => 'sanitize_text_field',
                            'validate_callback' => 'rest_validate_request_arg',
                        ),
                        'caption' => array(
                            'meta_key' => 'os_slider_caption',
                            'type' => 'wp_editor',
                            'label' => __('Caption', 'owlthslider'),
                            'default' => '',
                            'sanitize_callback' => 'wp_kses_post',
                            'validate_callback' => 'rest_validate_request_arg',
                        ),
                        'background_image' => array(
                            'meta_key' => 'os_slider_background_image',
                            'type' => 'image',
                            'label' => __('Background Image', 'owlthslider'),
                            'default' => '',
                            'sanitize_callback' => 'esc_url_raw',
                            'validate_callback' => 'rest_validate_request_arg',
                        ),
                        'cta_details' => array(
                            'meta_key' => 'os_slider_cta_details',
                            'type' => 'object',
                            'label' => __('CTA Details', 'owlthslider'),
                            'properties' => array(
                                'cta_text' => array(
                                    'meta_key' => 'os_slider_cta_text',
                                    'type' => 'string',
                                    'label' => __('CTA Text', 'owlthslider'),
                                    'default' => '',
                                    'sanitize_callback' => 'sanitize_text_field',
                                    'validate_callback' => null,
                                ),
                                'cta_url' => array(
                                    'meta_key' => 'os_slider_cta_url',
                                    'type' => 'url',
                                    'label' => __('CTA URL', 'owlthslider'),
                                    'default' => '',
                                    'sanitize_callback' => 'esc_url_raw',
                                    'validate_callback' => 'rest_validate_request_arg',
                                ),
                            ),
                        ),
                        'validity' => array(
                            'meta_key' => 'os_slider_validity',
                            'type' => 'datetime',
                            'label' => __('Validity', 'owlthslider'),
                            'default' => '',
                            'sanitize_callback' => 'sanitize_text_field',
                            'validate_callback' => 'rest_validate_request_arg',
                        ),
                    )),
                ),
                'reviews' => array(
                    'type' => 'object',
                    'description' => __('Reviews Slider Data', 'owlthslider'),
                    'properties' => array_merge($common_fields, array(
                        'author_details' => array(
                            'meta_key' => 'os_slider_author_details',
                            'type' => 'object',
                            'label' => __('Author Details', 'owlthslider'),
                            'properties' => array(
                                'author_name' => array(
                                    'meta_key' => 'os_slider_author_name',
                                    'type' => 'string',
                                    'label' => __('Author Name', 'owlthslider'),
                                    'default' => '',
                                    'sanitize_callback' => 'sanitize_text_field',
                                    'validate_callback' => 'rest_validate_request_arg',
                                ),
                                'author_avatar' => array(
                                    'meta_key' => 'os_slider_author_avatar',
                                    'type' => 'image',
                                    'label' => __('Author Avatar', 'owlthslider'),
                                    'default' => '',
                                    'sanitize_callback' => 'esc_url_raw',
                                    'validate_callback' => 'rest_validate_request_arg',
                                ),
                            ),
                        ),
                        'rating' => array(
                            'meta_key' => 'os_slider_rating',
                            'type' => 'float',
                            'label' => __('Rating', 'owlthslider'),
                            'default' => 5.0,
                            'sanitize_callback' => 'floatval',
                            'validate_callback' => 'rest_validate_request_arg',
                        ),
                        'review_body' => array(
                            'meta_key' => 'os_slider_review_body',
                            'type' => 'wp_editor',
                            'label' => __('Review Body', 'owlthslider'),
                            'default' => '',
                            'sanitize_callback' => 'wp_kses_post',
                            'validate_callback' => 'rest_validate_request_arg',
                        ),
                        'review_date' => array(
                            'meta_key' => 'os_slider_review_date',
                            'type' => 'date',
                            'label' => __('Review Date', 'owlthslider'),
                            'default' => '',
                            'sanitize_callback' => 'sanitize_text_field',
                            'validate_callback' => 'rest_validate_request_arg',
                        ),
                    )),
                ),
            ),
        )
    );

    return apply_filters('os_slider_schema', $schemas);
}

/**
 * Get the schema for slider options.
 *
 * @return array The slider options schema.
 */
function os_get_slider_options_schema()
{
    $schemas = array(
        'slider_options' => array(
            'meta_key' => 'os_slider_options', // Group key for slider options
            'type' => 'object', // Options are stored as an object in the meta key
            'description' => __('Slider options', 'owlthslider'),
            'properties' => array(
                'os_slider_loop' => array(
                    'meta_key' => 'os_slider_loop',
                    'type' => 'boolean',
                    'label' => __('Enable Loop', 'owlthslider'),
                    'default' => false,
                    'sanitize_callback' => 'rest_sanitize_boolean',
                    'validate_callback' => 'rest_validate_request_arg',
                ),
                'os_slider_draggable' => array(
                    'meta_key' => 'os_slider_draggable',
                    'type' => 'boolean',
                    'label' => __('Enable Draggable', 'owlthslider'),
                    'default' => true,
                    'sanitize_callback' => 'rest_sanitize_boolean',
                    'validate_callback' => 'rest_validate_request_arg',
                ),
                'os_slider_align' => array(
                    'meta_key' => 'os_slider_align',
                    'type' => 'string',
                    'label' => __('Alignment', 'owlthslider'),
                    'default' => 'center',
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => 'rest_validate_request_arg',
                ),
                'os_slider_autoplay' => array(
                    'meta_key' => 'os_slider_autoplay',
                    'type' => 'boolean',
                    'label' => __('Enable Autoplay', 'owlthslider'),
                    'default' => false,
                    'sanitize_callback' => 'rest_sanitize_boolean',
                    'validate_callback' => 'rest_validate_request_arg',
                ),
                'os_slider_autoplay_delay' => array(
                    'meta_key' => 'os_slider_autoplay_delay',
                    'type' => 'integer',
                    'label' => __('Autoplay Delay (ms)', 'owlthslider'),
                    'default' => 3000,
                    'sanitize_callback' => 'intval',
                    'validate_callback' => 'rest_validate_request_arg',
                ),
                'os_slider_autoscroll' => array(
                    'meta_key' => 'os_slider_autoscroll',
                    'type' => 'boolean',
                    'label' => __('Enable Autoscroll', 'owlthslider'),
                    'default' => false,
                    'sanitize_callback' => 'rest_sanitize_boolean',
                    'validate_callback' => 'rest_validate_request_arg',
                ),
                'os_slider_autoscroll_speed' => array(
                    'meta_key' => 'os_slider_autoscroll_speed',
                    'type' => 'integer',
                    'label' => __('Autoscroll Speed', 'owlthslider'),
                    'default' => 5,
                    'sanitize_callback' => 'intval',
                    'validate_callback' => 'rest_validate_request_arg',
                ),
                'os_slider_fade' => array(
                    'meta_key' => 'os_slider_fade',
                    'type' => 'boolean',
                    'label' => __('Enable Fade', 'owlthslider'),
                    'default' => false,
                    'sanitize_callback' => 'rest_sanitize_boolean',
                    'validate_callback' => 'rest_validate_request_arg',
                ),
                'os_slider_scroll_progress' => array(
                    'meta_key' => 'os_slider_scroll_progress',
                    'type' => 'boolean',
                    'label' => __('Show Scroll Progress', 'owlthslider'),
                    'default' => false,
                    'sanitize_callback' => 'rest_sanitize_boolean',
                    'validate_callback' => 'rest_validate_request_arg',
                ),
                'os_slider_wheel_gesture' => array(
                    'meta_key' => 'os_slider_wheel_gesture',
                    'type' => 'boolean',
                    'label' => __('Enable Wheel Gesture', 'owlthslider'),
                    'default' => false,
                    'sanitize_callback' => 'rest_sanitize_boolean',
                    'validate_callback' => 'rest_validate_request_arg',
                ),
            ),
        ),
    );

    return apply_filters('os_slider_options_schema', $schemas);
}


function new_os_get_slider_options_schema()
{
    return array(
        'slider_options' => array(
            'meta_key' => 'os_slider_options',
            'type' => 'object',
            'description' => __('Slider options', 'owlthslider'),
            // 'sanitize_callback' => 'new_sanitize_slider_options',
            // 'validate_callback' => 'new_validate_slider_options',
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



/**
 * Get the REST API schema for sliders.
 *
 * @return array The REST API schema array.
 */
function os_get_rest_slider_schema()
{
    $schemas = os_get_slider_schema();
    $rest_schemas = array();

    foreach ($schemas as $slider_type => $schema) {
        $rest_schema = array(
            'type' => 'object',
            'properties' => array(),
        );

        foreach ($schema['properties'] as $field_key => $field) {
            if ($field['type'] === 'group' && isset($field['fields'])) {
                $group_properties = array();
                foreach ($field['fields'] as $sub_field_key => $sub_field) {
                    $group_properties[$sub_field_key] = array(
                        'type' => $sub_field['type'],
                        'description' => $sub_field['label'],
                        'default' => $sub_field['default'],
                    );
                }

                $rest_schema['properties'][$field_key] = array(
                    'type' => 'object',
                    'description' => $field['label'],
                    'properties' => $group_properties,
                );
            } else {
                $rest_schema['properties'][$field_key] = array(
                    'type' => $field['type'],
                    'description' => $field['label'],
                    'default' => $field['default'],
                );
            }
        }

        $rest_schemas[$slider_type] = $rest_schema;
    }

    return $rest_schemas;
}



