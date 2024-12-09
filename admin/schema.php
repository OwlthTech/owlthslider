<?php

/**
 * Get the enhanced slider schema with support for grouped fields.
 *
 * @return array The enhanced schema array.
 */
function os_get_slides_schema()
{
    $common_fields = array(
        'enabled' => array(
            'type' => 'boolean',
            'label' => __('#', 'owlthslider'),
            'default' => 1,
            'sanitize_callback' => 'rest_sanitize_boolean',
            'classes' => 'cb-column'
        ),
    );

    $schemas = array(
        'slider_data' => array(
            'type' => 'object',
            'description' => __('Data for slides', 'owlthslider'),
            'sanitize_callback' => 'os_sanitize_and_validate_data',
            'properties' => array(
                'default' => array(
                    'type' => 'object',
                    'description' => __('Default Slider Data', 'owlthslider'),
                    'properties' => array_merge($common_fields, array(
                        'heading' => array(
                            'type' => 'string',
                            'label' => __('Heading', 'owlthslider'),
                            'default' => '',
                            'sanitize_callback' => 'sanitize_text_field',
                            'classes' => 'heading-column'
                        ),
                        'caption' => array(
                            'type' => 'wp_editor',
                            'label' => __('Caption', 'owlthslider'),
                            'default' => '',
                            'sanitize_callback' => 'wp_kses_post',
                            'classes' => 'caption-column'
                        ),
                        'background_image' => array(
                            'type' => 'image',
                            'label' => __('Background Image', 'owlthslider'),
                            'default' => '',
                            'sanitize_callback' => 'esc_url_raw',
                            'classes' => 'image-column block'
                        ),
                        'cta_details' => array(
                            'type' => 'object',
                            'label' => __('CTA Details', 'owlthslider'),
                            'classes' => 'cta-column',
                            'properties' => array(
                                'cta_text' => array(
                                    'type' => 'string',
                                    'label' => __('CTA Text', 'owlthslider'),
                                    'default' => '',
                                    'sanitize_callback' => 'sanitize_text_field',
                                ),
                                'cta_link' => array(
                                    'type' => 'url',
                                    'label' => __('CTA URL', 'owlthslider'),
                                    'default' => '',
                                    'sanitize_callback' => 'esc_url_raw',
                                ),
                            ),
                        ),
                        // 'validity' => array(
                        //     'type' => 'datetime',
                        //     'label' => __('Valid till', 'owlthslider'),
                        //     'default' => '',
                        //     'sanitize_callback' => 'sanitize_text_field',
                        //     'classes' => 'cta-column'
                        // ),
                    )),
                ),
                'reviews' => array(
                    'type' => 'object',
                    'description' => __('Reviews Slider Data', 'owlthslider'),
                    'properties' => array_merge($common_fields, array(
                        'author_details' => array(
                            'type' => 'object',
                            'classes' => 'heading-column',
                            'label' => __('Author Details', 'owlthslider'),
                            'properties' => array(
                                'author_name' => array(
                                    'type' => 'string',
                                    'label' => __('Author Name', 'owlthslider'),
                                    'default' => '',
                                    'sanitize_callback' => 'sanitize_text_field',
                                ),
                                'author_avatar' => array(
                                    'type' => 'image',
                                    'label' => __('Author Avatar', 'owlthslider'),
                                    'default' => '',
                                    'sanitize_callback' => 'esc_url_raw',
                                ),
                            ),
                        ),
                        'rating' => array(
                            'type' => 'float',
                            'label' => __('Rating', 'owlthslider'),
                            'default' => 5.0,
                            'sanitize_callback' => 'floatval',
                            'classes' => 'rating-column'
                        ),
                        'review_body' => array(
                            'type' => 'wp_editor',
                            'label' => __('Review Body', 'owlthslider'),
                            'default' => '',
                            'sanitize_callback' => 'wp_kses_post',
                            'classes' => 'caption-column'
                        ),
                        'review_date' => array(
                            'type' => 'date',
                            'label' => __('Review Date', 'owlthslider'),
                            'default' => '',
                            'sanitize_callback' => 'sanitize_text_field',
                            'classes' => 'cta-column'
                        ),
                    )),
                ),
            ),
        ),
    );
    return apply_filters('os_slider_schema', $schemas);
}

/**
 * Get the schema for slider options.
 *
 * @return array The slider options schema.
 */
function os_get_slider_option_schema()
{
    $schemas = array(
        'slider_options' => array(
            'type' => 'object', // Options are stored as an object in the meta key
            'description' => __('Slider options', 'owlthslider'),
            'properties' => array(
                'os_slider_loop' => array(
                    'type' => 'boolean',
                    'label' => __('Enable Loop', 'owlthslider'),
                    'default' => false,
                    'sanitize_callback' => 'rest_sanitize_boolean',
                ),
                'os_slider_draggable' => array(
                    'type' => 'boolean',
                    'label' => __('Enable Draggable', 'owlthslider'),
                    'default' => true,
                    'sanitize_callback' => 'rest_sanitize_boolean',
                ),
                'os_slider_align' => array(
                    'type' => 'string',
                    'label' => __('Alignment', 'owlthslider'),
                    'default' => 'center',
                    'sanitize_callback' => 'sanitize_text_field',
                ),
                'os_slider_autoplay' => array(
                    'type' => 'boolean',
                    'label' => __('Enable Autoplay', 'owlthslider'),
                    'default' => false,
                    'sanitize_callback' => 'rest_sanitize_boolean',
                ),
                'os_slider_autoplay_delay' => array(
                    'type' => 'integer',
                    'label' => __('Autoplay Delay (ms)', 'owlthslider'),
                    'default' => 3000,
                    'sanitize_callback' => 'intval',
                ),
                'os_slider_autoscroll' => array(
                    'type' => 'boolean',
                    'label' => __('Enable Autoscroll', 'owlthslider'),
                    'default' => false,
                    'sanitize_callback' => 'rest_sanitize_boolean',
                ),
                'os_slider_autoscroll_speed' => array(
                    'type' => 'integer',
                    'label' => __('Autoscroll Speed', 'owlthslider'),
                    'default' => 5,
                    'sanitize_callback' => 'intval',
                ),
                'os_slider_fade' => array(
                    'type' => 'boolean',
                    'label' => __('Enable Fade', 'owlthslider'),
                    'default' => false,
                    'sanitize_callback' => 'rest_sanitize_boolean',
                ),
                'os_slider_scroll_progress' => array(
                    'type' => 'boolean',
                    'label' => __('Show Scroll Progress', 'owlthslider'),
                    'default' => false,
                    'sanitize_callback' => 'rest_sanitize_boolean',
                ),
                'os_slider_wheel_gesture' => array(
                    'type' => 'boolean',
                    'label' => __('Enable Wheel Gesture', 'owlthslider'),
                    'default' => false,
                    'sanitize_callback' => 'rest_sanitize_boolean',
                ),
            ),
        ),
    );

    return apply_filters('os_slider_options_schema', $schemas);
}

