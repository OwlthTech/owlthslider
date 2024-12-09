<?php

/**
 * Admin: slider table mextabox callback
 * @param mixed $post
 * @return void
 */
function os_slider_render_table($post)
{
    // Add nonce field for security.
    wp_nonce_field('os_save_slider_universal_nonce_action', 'os_slider_universal_nonce');

    $slider_data = get_post_meta($post->ID, '_os_slider_data', true);
    $slider_data = is_array($slider_data) ? $slider_data : array();

    if (empty($slider_data)) {
        $slider_data[] = array(
            'enabled' => 'yes',
            'heading' => '',
            'caption' => '',
            'background_image' => '',
            'cta_text' => '',
            'cta_link' => ''
        );
    }
    ?>
    <table class="form-table" id="os-slider-table">
        <thead>
            <tr>
                <th class="cb-column"><?php _e('Enable', 'owlthslider'); ?></th>
                <th class="heading-column"><?php _e('Heading', 'owlthslider'); ?></th>
                <th class="caption-column"><?php _e('Caption Description', 'owlthslider'); ?></th>
                <th class="image-column"><?php _e('Background Image', 'owlthslider'); ?></th>
                <th class="cta-column"><?php _e('CTA Button Text', 'owlthslider'); ?></th>
                <th class="action-column"><?php _e('Actions', 'owlthslider'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($slider_data as $index => $data): ?>
                <?php echo render_table_rows($index, $data); ?>
            <?php endforeach; ?>
        </tbody>
    </table>
    <p>
        <button type="button" class="button button-primary" id="add-slider-row">Add New Row</button>
    </p>

    <script type="text/template" id="table-row-template"><?php echo render_table_row_template(); ?></script>

    <?php
}


function render_table_rows($index, $data = [])
{
    $enabled = isset($data['enabled']) ? esc_attr($data['enabled']) : 'no';
    $heading = isset($data['heading']) ? esc_attr($data['heading']) : '';
    $caption = isset($data['caption']) ? $data['caption'] : '';
    $image = isset($data['background_image']) ? esc_url($data['background_image']) : '';
    $cta_text = isset($data['cta_text']) ? esc_attr($data['cta_text']) : '';
    $cta_link = isset($data['cta_link']) ? esc_url($data['cta_link']) : '';
    ?>
    <tr>
        <td class=""><input type="checkbox" name="os_slider_data[<?php echo $index; ?>][enabled]" value="yes" <?php checked($enabled, 'yes'); ?> /></td>

        <td class="heading-column"><input type="text" name="os_slider_data[<?php echo $index; ?>][heading]"
                value="<?php echo $heading; ?>" size="20" /></td>

        <td class="caption-column">
            <?php
            wp_editor(
                $caption,
                'os_slider_caption_' . $index,
                array(
                    'textarea_name' => 'os_slider_data[' . $index . '][caption]', // Name attribute for the textarea
                    'textarea_rows' => 5,
                    'teeny' => true,
                    'media_buttons' => false
                )
            );
            ?>
        </td>

        <td class="image-column block">
            <input hidden type="text" name="os_slider_data[<?php echo $index; ?>][background_image]"
                value="<?php echo $image; ?>" size="20" placeholder="Image URL" />
            <?php if (!empty($image)): ?>
                <div class="slider-image-thumbnail">
                    <img src="<?php echo esc_url($image); ?>" alt=""
                        style="<?php echo ($image != '') ? 'aspect-ratio:16:9' : ''; ?>" />
                    <button type="button" class="button slider-remove-image">&times;</button>
                </div>
            <?php endif; ?>
            <button type="button" class="upload-button button-add-media button-add-site-icon slider-select-image"
                style="aspect-ratio:16:9;<?php echo !empty($image) ? 'display:none' : ''; ?>">Background Image</button>
        </td>

        <td class="cta-column"><input type="text" name="os_slider_data[<?php echo $index; ?>][cta_text]"
                value="<?php echo $cta_text; ?>" size="20" />
            <input type="text" name="os_slider_data[<?php echo $index; ?>][cta_link]" value="<?php echo $cta_link; ?>"
                size="20" />
        </td>

        <td class="action-column">
            <button type="button" class="button-icon remove-row" title="Remove Slide">
                <span class="dashicons dashicons-trash"></span>
            </button>
            <button type="button" class="button-icon duplicate-row" title="Duplicate Slide">
                <span class="dashicons dashicons-admin-page"></span>
            </button>
        </td>

    </tr>
    <?php
}

// Template row for adding new rows
if (!function_exists('render_table_row_template')):
    function render_table_row_template()
    {
        ob_start();
        render_table_rows('index_count');
        return ob_get_clean();
    }
endif;




/**
 * Render metabox for Reviews Slider Settings.
 *
 * @param WP_Post $post The post object.
 */
function os_slider_render_reviews_settings($post)
{
    // Add nonce field for security
    wp_nonce_field('os_save_slider_universal_nonce_action', 'os_slider_universal_nonce');

    // Retrieve existing options or set defaults
    $options = get_post_meta($post->ID, '_os_slider_options', true);
    $options = is_array($options) ? $options : array();

    // Set default values for reviews slider options
    $defaults = array(
        'os_slider_google_place_id' => ORGOTEL_PLACES_ID,
    );
    $options = wp_parse_args($options, $defaults);

    // Display the Google Place ID field
    ?>
    <p>
        <label for="os_slider_google_place_id"><?php _e('Google Place ID', 'owlthslider'); ?></label><br />
        <input type="text" id="os_slider_google_place_id" name="os_slider_options[os_slider_google_place_id]"
            value="<?php echo esc_attr($options['os_slider_google_place_id']); ?>" required />
    </p>
    <p>
        <button type="button" class="button" id="os_refresh_reviews"><?php _e('Refresh Reviews', 'owlthslider'); ?></button>
    </p>
    <hr />
    <h4><?php _e('Fetched Reviews', 'owlthslider'); ?></h4>
    <div id="os_reviews_table_container">
        <?php os_render_reviews_table($post->ID, $options['os_slider_google_place_id']); ?>
    </div>

    <?php
}

/**
 * Render the reviews table in the metabox.
 *
 * @param int    $post_id        The post ID.
 * @param string $google_place_id The Google Place ID.
 */
function os_render_reviews_table($post_id, $google_place_id, $refresh = false)
{

    $google_place_id = (isset($google_place_id) && !empty($google_place_id)) ? $google_place_id : ORGOTEL_PLACES_ID;

    // Generate a unique transient key for caching the reviews.
    $transient_key = 'owlth_google_reviews_' . md5($google_place_id);
    // delete_transient($transient_key);
    $reviews = os_fetch_google_reviews($google_place_id, $refresh);

    if (!isset($reviews) || empty($reviews)) {
        echo '<p>' . __('No reviews found or failed to fetch reviews.', 'owlthslider') . '</p>';
        return;
    }

    echo '<table class="widefat fixed" cellspacing="0">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>' . __('Reviewer', 'owlthslider') . '</th>';
    echo '<th>' . __('Rating', 'owlthslider') . '</th>';
    echo '<th>' . __('Review', 'owlthslider') . '</th>';
    echo '<th>' . __('Date', 'owlthslider') . '</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    foreach ($reviews as $review) {
        $reviewer = esc_html($review->author_name);
        $rating = intval($review->rating);
        $comment = esc_html($review->text);
        $date = esc_html(date_i18n(get_option('date_format'), strtotime($review->time)));
        echo '<tr>';
        echo '<td>' . $reviewer . '</td>';
        echo '<td>' . $rating . '</td>';
        echo '<td>' . $comment . '</td>';
        echo '<td>' . $date . '</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
}