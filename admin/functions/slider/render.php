<?php

/**
 * Render a field or group based on its schema.
 *
 * @param string $field_key The field key.
 * @param array  $field     The field schema.
 * @param mixed  $value     The current value of the field.
 * @param int    $index     The slide index.
 */
function os_render_field($field_key, $field, $value, $index) {
    $label = isset($field['label']) ? $field['label'] : '';
    $type  = isset($field['type']) ? $field['type'] : 'string';

    if ($type === 'object' && isset($field['properties'])) {
        // Render Group Label
        echo '<fieldset class="os-field-group">';
        // echo '<legend>' . esc_html($field['label']) . '</legend>';

        // Iterate through group fields
        foreach ($field['properties'] as $sub_field_key => $sub_field) {
            $sub_value = isset($value[$sub_field_key]) ? $value[$sub_field_key] : $sub_field['default'];
            os_render_individual_field("slides[{$index}][{$field_key}][{$sub_field_key}]", $sub_field, $sub_value);
        }

        echo '</fieldset>';
    } else {
        // Render Individual Field
        os_render_individual_field("slides[{$index}][{$field_key}]", $field, $value);
    }
}

/**
 * Render an individual field based on its schema.
 *
 * @param string $name  The input name attribute.
 * @param array  $field The field schema.
 * @param mixed  $value The current value of the field.
 */
function os_render_individual_field($name, $field, $value) {
    $label = isset($field['label']) ? $field['label'] : '';
    $type  = isset($field['type']) ? $field['type'] : 'string';

    echo '<div class="os-field os-field-' . esc_attr($type) . '">';

    switch ($type) {
        case 'boolean':
            ?>
            <label>
                <input type="checkbox" name="<?php echo esc_attr($name); ?>" value="1" <?php checked($value, true); ?> />
                <?php echo esc_html($label); ?>
            </label>
            <?php
            break;
        case 'string':
            ?>
            <label>
                <input type="text" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>" placeholder="<?php echo esc_html($label); ?>" />
            </label>
            <?php
            break;
        case 'wp_editor':
            ?>
            <?php
            $settings = array(
                'textarea_name' => $name,
                'textarea_rows' => 5,
            );
            wp_editor($value, sanitize_title($name), $settings);
            break;
        case 'image':
            ?>
            <input type="text" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_url($value); ?>" />
            <button type="button" class="button os-upload-image" data-target="<?php echo esc_attr($name); ?>"><?php _e('Upload Image', 'owlthslider'); ?></button>
            <?php
            break;
        case 'url':
            ?>
            <label>
                <input type="url" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_url($value); ?>" placeholder="<?php echo esc_html($label); ?>" />
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
                <input type="number" step="0.1" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>" placeholder="<?php echo esc_html($label); ?>"/>
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
 * Render the slider data table.
 *
 * @param WP_Post $post The post object.
 */
function os_render_slider_data_table($post) {
    // Get the slider type
    $slider_types = wp_get_post_terms($post->ID, 'os_slider_type', array('fields' => 'slugs'));
    $slider_type  = isset($slider_types[0]) ? $slider_types[0] : 'default';

    // Get the schema for the slider type
    $schemas = os_get_slider_schema()['slider_data']['properties'];
    $schema  = isset($schemas[$slider_type]) ? $schemas[$slider_type] : $schemas['default'];

    // Get existing slider data or set default
    $slider_data = get_post_meta($post->ID, '_os_slider_data', true);
    $slider_data = is_array($slider_data) ? $slider_data : array();

    // If no slides, add an empty one
    if (empty($slider_data)) {
        $slider_data[] = array();
    }

    ?>
    <table class="os-slider-data-table widefat fixed" cellspacing="0">
        <thead>
            <tr>
                <?php
                // Render table headings based on schema
                foreach ($schema['properties'] as $field_key => $field) {
                    if ($field['type'] === 'object' && isset($field['label'])) {
                        echo '<th>' . esc_html($field['label']) . '</th>';
                    } else {
                        echo '<th>' . esc_html($field['label']) . '</th>';
                    }
                }
                echo '<th>' . __('Actions', 'owlthslider') . '</th>';
                ?>
            </tr>
        </thead>
        <tbody>
            <?php
            // Render table rows
            foreach ($slider_data as $index => $slide) {
                echo '<tr>';
                foreach ($schema['properties'] as $field_key => $field) {
                    echo '<td>';
                    $value = isset($slide[$field_key]) ? $slide[$field_key] : isset($field['default']) ?? $field['default'];
                    os_render_field($field_key, $field, $value, $index);
                    echo '</td>';
                }
                // Actions
                echo '<td>';
                ?>
                <button type="button" class="button os-delete-slide"><?php _e('Delete', 'owlthslider'); ?></button>
                <button type="button" class="button os-duplicate-slide"><?php _e('Duplicate', 'owlthslider'); ?></button>
                <?php
                echo '</td>';
                echo '</tr>';
            }
            ?>
        </tbody>
    </table>
    <p>
        <button type="button" class="button os-add-slide"><?php _e('Add Slide', 'owlthslider'); ?></button>
    </p>
    <?php
}

