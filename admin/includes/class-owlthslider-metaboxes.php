<?php

class Class_Owlthslider_Metaboxes
{
    public function __construct()
    {
        add_action('add_meta_boxes', array($this, 'os_remove_meta_box'));
        add_action('add_meta_boxes', array($this, 'os_add_meta_box'));
    }

    function os_remove_meta_box()
    {
        remove_meta_box('os_slider_typediv', 'os_slider', 'side');
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


        // Get the current slider type to conditionally add metaboxes
        add_meta_box(
            'os_slider_details',
            __('Slider Details', 'owlthslider'),
            'os_render_slider_data_table',
            'os_slider',
            'normal',
            'default'
        );
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
        // Get all categories for the 'os_slider_type' taxonomy.
        $categories = get_terms(array(
            'taxonomy' => 'os_slider_type',
            'hide_empty' => false,
        ));

        
        // Get currently selected category.
        $selected_category = wp_get_post_terms($post->ID, 'os_slider_type', array('fields' => 'slugs'));
        // var_dump($selected_category);

        // If no category is selected, set the default to 'Default'.
        if (empty($selected_category)) {
            $default_category = get_term_by('slug', 'default', 'os_slider_type');
            $selected_category = $default_category ? array($default_category->slug) : array();
        }

        // Render categories as radio buttons.
        echo '<ul>';
        foreach ($categories as $category) {
            echo '<li>';
            echo '<label>';
            echo '<input type="radio" name="os_slider_type" value="' . esc_attr($category->slug) . '" ' . checked(!empty($selected_category) && in_array($category->slug, $selected_category), true, false) . ' />';
            echo esc_html($category->name);
            echo '</label>';
            echo '</li>';
        }
        echo '</ul>';
    }
}


