<?php

class Owlthslider_Metaboxes
{
    public function __construct()
    {
        add_action('add_meta_boxes', array($this, 'os_remove_meta_box'));
        add_action('add_meta_boxes', array($this, 'os_add_meta_box'));

        add_action('wp_ajax_process_reviews_json', array($this, 'os_process_reviews_json'));
        // add_action( 'wp_ajax_process_reviews_csv', 'os_process_reviews_csv' );
    }

    function os_remove_meta_box()
    {
        remove_meta_box('os_slider_typediv', 'os_slider', 'side');
        remove_meta_box('os_slider_templatediv', 'os_slider', 'side');
        remove_meta_box('os_slider_effectdiv', 'os_slider', 'side');
    }

    function os_add_meta_box()
    {

        // Options
        add_meta_box(
            'os_slider_settings',
            __('Slider Settings', 'owlthslider'),
            array($this, 'os_slider_render_options'),
            'os_slider',
            'side',
            'default'
        );

        // Types
        add_meta_box(
            'os_slider_types_meta_box',
            __('Slider Type', 'owlthslider'),
            array($this, 'os_slider_render_types'),
            'os_slider',
            'side',
            'default'
        );

        // Templates
        add_meta_box(
            'os_slider_templates_meta_box',
            __('Slider Type', 'owlthslider'),
            array($this, 'os_slider_render_templates'),
            'os_slider',
            'side',
            'default'
        );

        // Get the current slider type to conditionally add metaboxes
        add_meta_box(
            'os_slider_details',
            __('Slider Details', 'owlthslider'),
            'os_render_slider_data_table',
            'os_slider',
            'normal',
            'default'
        );

        global $post;
        // Check if the post is of type `os_slider`
        if ($post && $post->post_type === 'os_slider') {
            $terms = wp_get_post_terms($post->ID, 'os_slider_type');
            foreach ($terms as $term) {
                if ($term->slug === 'reviews') {
                    add_meta_box(
                        'os_reviews_meta_box',
                        __('Upload Reviews CSV', 'owlthslider'),
                        array($this, 'os_render_reviews_meta_box'),
                        'os_slider',
                        'normal',
                        'default'
                    );
                }
            }
        }

    }


    /**
     * Render the reviews meta box.
     */
    function os_render_reviews_meta_box($post)
    {
        $nonce = wp_create_nonce('os_reviews_json_upload_action');
        ?>
        <p><?php _e('Upload a JSON file to generate reviews based on the schema.', 'owlthslider'); ?></p>
        <input type="file" name="os_reviews_json" id="os_reviews_json" accept="application/json">
        <p class="description"><?php _e('Ensure the JSON file has the correct structure.', 'owlthslider'); ?></p>
        <button type="button" id="process_json_button" class="button button-primary">
            <?php _e('Process JSON and Update Reviews', 'owlthslider'); ?>
        </button>

        <script>
            document.getElementById('process_json_button').addEventListener('click', function () {
                const fileInput = document.getElementById('os_reviews_json');
                if (!fileInput.files.length) {
                    alert('<?php _e('Please select a JSON file first.', 'owlthslider'); ?>');
                    return;
                }

                const formData = new FormData();
                formData.append('action', 'process_reviews_json');
                formData.append('post_id', '<?php echo $post->ID; ?>');
                formData.append('os_reviews_json', fileInput.files[0]);
                formData.append('_ajax_nonce', '<?php echo $nonce; ?>');

                fetch(ajaxurl, {
                    method: 'POST',
                    body: formData,
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('<?php _e('Reviews updated successfully.', 'owlthslider'); ?>');
                        } else {
                            alert('<?php _e('Error processing JSON: ', 'owlthslider'); ?>' + data.data);
                        }
                    })
                    .catch(error => {
                        alert('<?php _e('An unexpected error occurred.', 'owlthslider'); ?>');
                        console.error(error);
                    });
            });
        </script>
        <?php
    }

    /**
     * AJAX handler to process the uploaded JSON and update reviews.
     */
    function os_process_reviews_json()
    {
        // Verify nonce
        if (!check_ajax_referer('os_reviews_json_upload_action', '_ajax_nonce', false)) {
            wp_send_json_error(__('Nonce verification failed.', 'owlthslider'));
        }

        // Check user permissions
        if (!current_user_can('edit_post', $_POST['post_id'])) {
            wp_send_json_error(__('Unauthorized.', 'owlthslider'));
        }

        // Check if a file is uploaded
        if (isset($_FILES['os_reviews_json']) && !empty($_FILES['os_reviews_json']['tmp_name'])) {
            $json_file = $_FILES['os_reviews_json']['tmp_name'];
            $json_content = file_get_contents($json_file);
            $reviews_data = json_decode($json_content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                wp_send_json_error(__('Invalid JSON file.', 'owlthslider'));
            }

            // Save the uploaded JSON file
            $upload_dir = wp_upload_dir();
            $json_dir = trailingslashit($upload_dir['basedir']) . 'owlthslider/json/';
            if (!file_exists($json_dir)) {
                wp_mkdir_p($json_dir);
            }

            $json_filename = 'reviews_' . intval($_POST['post_id']) . '.json';
            $json_file_path = $json_dir . $json_filename;
            file_put_contents($json_file_path, $json_content);
            $json_url = trailingslashit($upload_dir['baseurl']) . 'owlthslider/json/' . $json_filename;

            // Process reviews as before
            $reviews = array();
            $valid_keys = [
                'name',
                'reviewerPhotoUrl',
                'stars',
                'text',
                'publishedAtDate',
                'reviewUrl',
                'reviewerNumberOfReviews',
                'reviewerId'
            ];
            $author_dir = trailingslashit($upload_dir['basedir']) . 'owlthslider/authors/';
            if (!file_exists($author_dir)) {
                wp_mkdir_p($author_dir);
            }

            foreach ($reviews_data as $entry) {
                $filtered_entry = array_intersect_key($entry, array_flip($valid_keys));

                $author_avatar_url = '';
                if (!empty($filtered_entry['reviewerPhotoUrl'])) {
                    $image_data = file_get_contents($filtered_entry['reviewerPhotoUrl']);
                    if ($image_data) {
                        $filename = sanitize_file_name($filtered_entry['reviewerId'] . '.jpg');
                        $file_path = $author_dir . $filename;
                        if (!file_exists($file_path)) {
                            file_put_contents($file_path, $image_data);
                        }
                        $author_avatar_url = trailingslashit($upload_dir['baseurl']) . 'owlthslider/authors/' . $filename;
                    }
                }

                $reviews[] = array(
                    'enabled' => true,
                    'author_details' => array(
                        'author_name' => sanitize_text_field($filtered_entry['name'] ?? ''),
                        'author_avatar' => esc_url_raw($author_avatar_url),
                    ),
                    'rating' => isset($filtered_entry['stars']) ? floatval($filtered_entry['stars']) : 0,
                    'review_body' => wp_kses_post($filtered_entry['text'] ?? ''),
                    'review_date' => date('d-m-Y', strtotime($filtered_entry['publishedAtDate'] ?? '')),
                );
            }

            // Save reviews into post meta
            update_post_meta(intval($_POST['post_id']), 'os_slider_data', $reviews);

            // Set transient to cache the reviews
            set_transient('os_reviews_json_' . intval($_POST['post_id']), $reviews, 0);

            wp_send_json_success(__('Reviews updated successfully.', 'owlthslider'));
        }

        wp_send_json_error(__('Failed to process JSON.', 'owlthslider'));
    }



    /**
     * Render Slider Settings Metabox
     *
     * @param WP_Post $post The post object.
     */
    function os_slider_render_options($post)
    {
        // Add nonce field for security.
        wp_nonce_field('os_save_slider_universal_nonce_action', 'os_slider_universal_nonce');

        // Retrieve existing options from post meta.
        $options = get_post_meta($post->ID, 'os_slider_options', true);
        $options = is_array($options) ? $options : array();

        // Set default values if not set.
        $defaults = array(
            'os_slider_loop' => 'no',
            'os_slider_speed' => 10,
            'os_slider_draggable' => 'yes',
            'os_slider_align' => 'center',
            'os_slider_skip_snaps' => 'no',
            'os_slider_autoplay' => 'no',
            'os_slider_autoplay_delay' => 3000,
            'os_slider_autoscroll' => 'no',
            'os_slider_autoscroll_speed' => 5,
            'os_slider_classnames' => 'no',
            'os_slider_fade' => 'no',
            'os_slider_scroll_progress' => 'no',
            'os_slider_thumbs' => 'no',
            'os_slider_wheel_gesture' => 'no',
        );

        $options = wp_parse_args($options, $defaults);
        ?>
        <div class="os-slider-settings">
            <h4><?php _e('General Settings', 'owlthslider'); ?></h4>

            <p>
                <label>
                    <input type="checkbox" name="os_slider_options[os_slider_loop]" value="yes" <?php checked($options['os_slider_loop'], 'yes'); ?> />
                    <?php _e('Enable Loop', 'owlthslider'); ?>
                </label>
            </p>

            <p>
                <label for="os_slider_speed"><?php _e('Transition Speed', 'owlthslider'); ?></label><br />
                <input type="number" id="os_slider_speed" name="os_slider_options[os_slider_speed]"
                    value="<?php echo esc_attr($options['os_slider_speed']); ?>" min="1" />
            </p>

            <p>
                <label>
                    <input type="checkbox" name="os_slider_options[os_slider_draggable]" value="yes" <?php checked($options['os_slider_draggable'], 'yes'); ?> />
                    <?php _e('Enable Draggable', 'owlthslider'); ?>
                </label>
            </p>

            <p>
                <label for="os_slider_align"><?php _e('Alignment', 'owlthslider'); ?></label><br />
                <select id="os_slider_align" name="os_slider_options[os_slider_align]">
                    <option value="start" <?php selected($options['os_slider_align'], 'start'); ?>>
                        <?php _e('Start', 'owlthslider'); ?>
                    </option>
                    <option value="center" <?php selected($options['os_slider_align'], 'center'); ?>>
                        <?php _e('Center', 'owlthslider'); ?>
                    </option>
                    <option value="end" <?php selected($options['os_slider_align'], 'end'); ?>>
                        <?php _e('End', 'owlthslider'); ?>
                    </option>
                </select>
            </p>

            <p>
                <label>
                    <input type="checkbox" name="os_slider_options[os_slider_skip_snaps]" value="yes" <?php checked($options['os_slider_skip_snaps'], 'yes'); ?> />
                    <?php _e('Enable Skip Snaps', 'owlthslider'); ?>
                </label>
            </p>

            <hr />

            <h4><?php _e('Plugins', 'owlthslider'); ?></h4>

            <p>
                <label>
                    <input type="checkbox" id="os_slider_autoplay" name="os_slider_options[os_slider_autoplay]" value="yes"
                        <?php checked($options['os_slider_autoplay'], 'yes'); ?> />
                    <?php _e('Enable Autoplay', 'owlthslider'); ?>
                </label>
            </p>

            <div id="autoplay_options" style="<?php echo ($options['os_slider_autoplay'] === 'yes') ? '' : 'display:none;'; ?>">
                <p>
                    <label for="os_slider_autoplay_delay"><?php _e('Autoplay Delay (ms)', 'owlthslider'); ?></label><br />
                    <input type="number" id="os_slider_autoplay_delay" name="os_slider_options[os_slider_autoplay_delay]"
                        value="<?php echo esc_attr($options['os_slider_autoplay_delay']); ?>" min="0" />
                </p>
            </div>

            <p>
                <label>
                    <input type="checkbox" id="os_slider_autoscroll" name="os_slider_options[os_slider_autoscroll]" value="yes"
                        <?php checked($options['os_slider_autoscroll'], 'yes'); ?> />
                    <?php _e('Enable Autoscroll', 'owlthslider'); ?>
                </label>
            </p>

            <div id="autoscroll_options"
                style="<?php echo ($options['os_slider_autoscroll'] === 'yes') ? '' : 'display:none;'; ?>">
                <p>
                    <label for="os_slider_autoscroll_speed"><?php _e('Autoscroll Speed', 'owlthslider'); ?></label><br />
                    <input type="number" id="os_slider_autoscroll_speed" name="os_slider_options[os_slider_autoscroll_speed]"
                        value="<?php echo esc_attr($options['os_slider_autoscroll_speed']); ?>" min="1" />
                </p>
            </div>

            <!-- Additional plugin options -->
            <p>
                <label>
                    <input type="checkbox" name="os_slider_options[os_slider_classnames]" value="yes" <?php checked($options['os_slider_classnames'], 'yes'); ?> />
                    <?php _e('Enable Custom Class Names', 'owlthslider'); ?>
                </label>
            </p>

            <p>
                <label>
                    <input type="checkbox" name="os_slider_options[os_slider_fade]" value="yes" <?php checked($options['os_slider_fade'], 'yes'); ?> />
                    <?php _e('Enable Fade Effect', 'owlthslider'); ?>
                </label>
            </p>

            <p>
                <label>
                    <input type="checkbox" name="os_slider_options[os_slider_scroll_progress]" value="yes" <?php checked($options['os_slider_scroll_progress'], 'yes'); ?> />
                    <?php _e('Enable Scroll Progress Indicator', 'owlthslider'); ?>
                </label>
            </p>

            <p>
                <label>
                    <input type="checkbox" name="os_slider_options[os_slider_thumbs]" value="yes" <?php checked($options['os_slider_thumbs'], 'yes'); ?> />
                    <?php _e('Enable Thumbnails Navigation', 'owlthslider'); ?>
                </label>
            </p>

            <p>
                <label>
                    <input type="checkbox" name="os_slider_options[os_slider_wheel_gesture]" value="yes" <?php checked($options['os_slider_wheel_gesture'], 'yes'); ?> />
                    <?php _e('Enable Wheel Gesture Support', 'owlthslider'); ?>
                </label>
            </p>

            <script>
                jQuery(document).ready(function ($) {
                    function handleDependencies() {
                        if ($('#os_slider_autoplay').is(':checked')) {
                            $('#autoplay_options').show();
                            $('#os_slider_autoscroll').prop('checked', false);
                            $('#autoscroll_options').hide();
                        } else {
                            $('#autoplay_options').hide();
                        }

                        if ($('#os_slider_autoscroll').is(':checked')) {
                            $('#autoscroll_options').show();
                            $('#os_slider_autoplay').prop('checked', false);
                            $('#autoplay_options').hide();
                        } else {
                            $('#autoscroll_options').hide();
                        }
                    }

                    $('#os_slider_autoplay, #os_slider_autoscroll').change(function () {
                        handleDependencies();
                    });

                    // Initial call to set the correct display on page load
                    handleDependencies();
                });
            </script>
        </div>
        <?php
    }


    // Types
    function os_slider_render_types($post)
    {
        $slider_types = get_terms(array(
            'taxonomy' => 'os_slider_type',
            'hide_empty' => false,
        ));

        $selected_type = wp_get_post_terms($post->ID, 'os_slider_type', array('fields' => 'slugs'));
        if (empty($selected_type)) {
            $default_category = get_term_by('slug', 'default', 'os_slider_type');
            $selected_type = $default_category ? array($default_category->slug) : array();
        }
        var_dump($selected_type);
        wp_nonce_field('os_slider_nonce_action', 'os_slider_nonce');

        echo '<ul>';
        foreach ($slider_types as $type) {
            echo '<li>';
            echo '<label>';
            echo '<input type="radio" name="os_slider_type" value="' . esc_attr($type->slug) . '" ' . checked(!empty($selected_type) && in_array($type->slug, $selected_type), true, false) . ' />';
            echo esc_html($type->name);
            echo '</label>';
            echo '</li>';
        }
        echo '</ul>';
    }


    // Templates
    function os_slider_render_templates($post)
    {
        $slider_templates = get_terms(array(
            'taxonomy' => 'os_slider_template',
            'hide_empty' => false,
        ));

        $selected_template = wp_get_post_terms($post->ID, 'os_slider_template', array('fields' => 'slugs'));
        if (empty($selected_template)) {
            $default_template = get_term_by('slug', 'default', 'os_slider_template');
            $selected_template = $default_template ? array($default_template->slug) : array();
        }
        var_dump($selected_template);
        wp_nonce_field('os_slider_nonce_action', 'os_slider_nonce');

        echo '<ul>';
        foreach ($slider_templates as $template) {
            echo '<li>';
            echo '<label>';
            echo '<input type="radio" name="os_slider_template" value="' . esc_attr($template->slug) . '" ' . checked(!empty($selected_template) && in_array($template->slug, $selected_template), true, false) . ' />';
            echo esc_html($template->name);
            echo '</label>';
            echo '</li>';
        }
        echo '</ul>';
    }

}

add_action('save_post', 'os_save_slider_taxonomies');

function os_save_slider_taxonomies($post_id)
{
    // Check if the post type is `os_slider`.
    if (get_post_type($post_id) !== 'os_slider') {
        return;
    }

    // Verify the nonce to ensure the request is valid.
    if (!isset($_POST['os_slider_nonce']) || !wp_verify_nonce($_POST['os_slider_nonce'], 'os_slider_nonce_action')) {
        return;
    }

    // Check if the user has permission to edit the post.
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    error_log(print_r($_POST, true));
    // Check if the taxonomies are set in the POST data and assign terms.
    if (isset($_POST['os_slider_type'])) {
        $slider_type = sanitize_text_field($_POST['os_slider_type']);
        wp_set_post_terms($post_id, array($slider_type), 'os_slider_type', false);
    }

    if (isset($_POST['os_slider_template'])) {
        $slider_template = sanitize_text_field($_POST['os_slider_template']);
        wp_set_post_terms($post_id, array($slider_template), 'os_slider_template', false);
    }
}


/**
 * Render the slider data table.
 *
 * @param WP_Post $post The post object.
 */
function os_render_slider_data_table($post)
{
    // Get the slider type
    $slider_types = wp_get_post_terms($post->ID, 'os_slider_type', array('fields' => 'slugs'));
    $slider_type = isset($slider_types[0]) ? $slider_types[0] : 'default';

    // Get the schema for the slider type
    $schema = os_get_slides_schema()['slider_data']['properties'][isset($slider_type) ? $slider_type : 'default'];
    // var_dump($schema);

    // Get existing slider data or set default
    if (isset($slider_type) && $slider_type === 'default') {
        $slider_data = get_post_meta($post->ID, 'os_slider_data', true);
    }
    if (isset($slider_type) && $slider_type === 'reviews') {
        $slider_data = get_transient('os_reviews_json_' . intval($post->ID));
        if (!isset($slider_data) || empty($slider_data)) {
            $slider_data = get_post_meta($post->ID, 'os_slider_data', true);
        }
    }

    $slider_data = is_array($slider_data) ? $slider_data : array();

    // If no slides, add an empty one
    if (empty($slider_data)) {
        $slider_data[] = array();
    }

    wp_nonce_field('os_save_slider_universal_nonce_action', 'os_slider_universal_nonce');
    ?>
    <table class="os-slider-data-table widefat fixed" id="os-slider-table" cellspacing="0">
        <thead>
            <tr>
                <?php
                // Render table headings based on schema
                foreach ($schema['properties'] as $field_key => $field) {
                    if ($field['type'] === 'object' && isset($field['label'])) {
                        echo '<th class="' . esc_html(isset($field['classes']) ? $field['classes'] : '') . '">' . esc_html($field['label']) . '</th>';
                    } else {
                        echo '<th class="' . esc_html(isset($field['classes']) ? $field['classes'] : '') . '">' . esc_html($field['label']) . '</th>';
                    }
                }
                echo '<th>' . __('', 'owlthslider') . '</th>';
                ?>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($slider_data as $index => $slide) {
                echo render_table_row($index, $slide, $schema);
            }
            ?>
        </tbody>
    </table>
    <p>
        <button type="button" class="button os-add-slide"
            id="add-slider-row"><?php _e('Add Slide', 'owlthslider'); ?></button>
    </p>

    <script type="text/template"
        id="table-row-template"><?php render_table_row('index_count', $slider_data, $schema); ?></script>

    <?php
}


function render_table_row($index, $slide, $schema)
{

    echo '<tr>';
    foreach ($schema['properties'] as $field_key => $field) {
        echo '<td class="' . esc_html(isset($field['classes']) ? $field['classes'] : '') . '">';
        $value = (isset($slide[$field_key]) && !empty($slide[$field_key])) ? ($slide[$field_key]) : (isset($field['default']) ? $field['default'] : '');
        os_render_fieldset($field_key, $field, $value, $index);
        echo '</td>';
    }
    ?>
    <td class="action-column">
        <button type="button" class="button-icon remove-row" title="Remove Slide">
            <span class="dashicons dashicons-trash"></span>
        </button>
        <button type="button" class="button-icon duplicate-row" title="Duplicate Slide">
            <span class="dashicons dashicons-admin-page"></span>
        </button>
    </td>
    <?php
    echo '</tr>';

}


/**
 * Render a field or group based on its schema.
 *
 * @param string $field_key The field key.
 * @param array  $field     The field schema.
 * @param mixed  $value     The current value of the field.
 * @param int    $index     The slide index.
 * @return void
 */
function os_render_fieldset($field_key, $field, $value, $index)
{
    $label = isset($field['label']) ? $field['label'] : '';
    $type = isset($field['type']) ? $field['type'] : 'string';

    if ($type === 'object' && isset($field['properties'])) {
        // Render Group Label
        echo '<fieldset class="os-field-group">';

        // Iterate through group fields
        foreach ($field['properties'] as $sub_field_key => $sub_field) {
            $sub_value = isset($value[$sub_field_key]) ? $value[$sub_field_key] : $sub_field['default'];
            os_render_field("slides[{$index}][{$field_key}][{$sub_field_key}]", $sub_field, $sub_value);
        }

        echo '</fieldset>';
    } else {
        // Render Individual Field
        os_render_field("slides[{$index}][{$field_key}]", $field, $value);
    }
}

/**
 * Render an individual field based on its schema.
 *
 * @param string $name  The input name attribute.
 * @param array  $field The field schema.
 * @param mixed  $value The current value of the field.
 */
function os_render_field($name, $field, $value)
{
    $label = isset($field['label']) ? $field['label'] : '';
    $type = isset($field['type']) ? $field['type'] : 'string';

    echo '<div class="os-field os-field-' . esc_attr($type) . '">';

    switch ($type) {
        case 'boolean':
            ?>
            <label>
                <input type="checkbox" name="<?php echo esc_attr($name); ?>" value="1" <?php checked($value, 1); ?> />
                <span><?php echo esc_html($label); ?></span>
            </label>
            <?php
            break;
        case 'string':
            ?>
            <label>
                <input type="text" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>"
                    placeholder="<?php echo esc_html($label); ?>" />
            </label>
            <?php
            break;
        case 'wp_editor':
            $settings = array(
                'textarea_name' => $name,
                'textarea_rows' => 3,
                'teeny' => false, // Set to false to allow extended editor controls.
                'media_buttons' => false,
            );
            wp_editor($value, sanitize_title($name), $settings);
            break;
        case 'image':
            ?>
            <input hidden type="text" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_url($value); ?>" />
            <!-- preview -->
            <?php if (!empty($value)): ?>
                <div class="slider-image-thumbnail">
                    <img src="<?php echo esc_url($value); ?>" style="<?php echo ($value != '') ? 'aspect-ratio:16:9' : ''; ?>" />
                    <button type="button" class="button slider-remove-image">&times;</button>
                </div>
            <?php endif; ?>
            <button type="button"
                class="<?php echo !empty($value) ? 'hidden' : ''; ?> upload-button button-add-media button-add-site-icon slider-select-image"
                data-target="<?php echo esc_attr($name); ?>"
                style="aspect-ratio:16:9;<?php echo !empty($image) ? 'display:none' : ''; ?>"><?php _e('Select Image', 'owlthslider'); ?></button>
            <?php
            break;
        case 'url':
            ?>
            <label>
                <input type="url" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_url($value); ?>"
                    placeholder="<?php echo esc_html($label); ?>" />
            </label>
            <?php
            break;
        case 'datetime':
            ?>
            <label>
                <input type="datetime-local" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>" />
            </label>
            <?php
            break;
        case 'date':
            ?>
            <label>
                <input type="date" name="<?php echo esc_attr($name); ?>" value="<?php echo date('Y-m-d', strtotime($value)); ?>" />
            </label>
            <?php
            break;
        case 'float':
            ?>
            <label>
                <input type="number" step="0.1" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>"
                    placeholder="<?php echo esc_html($label); ?>" />
            </label>
            <?php
            break;
        default:
            do_action('os_render_custom_field', $name, $field, $value);
            break;
    }

    echo '</div>';
}


/**
 * Generate HTML output for displaying individual review rating.
 *
 * @param int $rating Rating value from 1 to 5.
 *
 * @return string HTML output of the star rating.
 */
function owlth_display_review_rating($rating)
{
    $output = '<div class="owlth__rating">';

    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $rating) {
            $output .= '<span class="star full">★</span>'; // Full star
        } else {
            $output .= '<span class="star empty">☆</span>'; // Empty star
        }
    }

    $output .= '</div>';

    return $output;
}