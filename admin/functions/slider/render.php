<?php




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

    // Get existing slider data or set default
    error_log(print_r($post, true));
    $slider_data = get_post_meta($post->ID, 'os_slider_data', true);
    $slider_data = is_array($slider_data) ? $slider_data : array();
    error_log(print_r($slider_data, true));

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

// Template row for adding new rows
if (!function_exists('render_table_row_template')):
    function render_table_row_template()
    {
        ob_start();
        render_table_row('index_count');
        return ob_get_clean();
    }
endif;

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
                'textarea_rows' => 5,
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
                <button type="button" class="<?php echo !empty($value) ? 'hidden' : ''; ?> upload-button button-add-media button-add-site-icon slider-select-image"
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
                <input type="date" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>" />
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

