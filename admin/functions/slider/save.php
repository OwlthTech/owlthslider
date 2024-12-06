<?php
/**
 * Save Slider Data during post save.
 *
 * @param int $post_id The ID of the post being saved.
 */
function test_os_save_data( $post_id ) {
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    // Optional: Check if the post exists and is of type 'os_slider'
    $post = get_post( $post_id );
    if ( ! $post || $post->post_type !== 'os_slider' ) {
        return;
    }

    $is_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX;
    $errors  = array();

    // Get the slider type from $_POST
    $slider_data_type = isset( $_POST['os_slider_type'] ) ? sanitize_text_field( $_POST['os_slider_type'] ) : 'default';

    // Check if required fields are present
    if ( isset( $_POST['slides'] ) && isset( $_POST['os_slider_options'] ) ) {
        // Validate 'os_slider_options' structure
        if ( ! is_array( $_POST['os_slider_options'] ) ) {
            if ( $is_ajax ) {
                wp_send_json_error( 'Invalid slider options structure.', 400 );
            }
            return;
        }

        // Since sanitization is now handled by the 'pre_update_post_meta' filter, no need to manually call the sanitization function here.
        $sanitized_data = $_POST['slides'];

        $current_data = get_post_meta( $post_id, 'os_slider_data', true );
        if ( $current_data !== $sanitized_data ) {
            update_post_meta( $post_id, 'os_slider_data', $sanitized_data );
        }
    }
}

// add_action( 'save_post_os_slider', 'test_os_save_data' );

/**
 * AJAX Auto-Save Slider Data
 *
 * @return void
 */
function os_save_data_ajax() {
    // Verify the universal nonce
    if ( ! isset( $_POST['os_slider_universal_nonce'] ) || ! wp_verify_nonce( $_POST['os_slider_universal_nonce'], 'os_save_slider_universal_nonce_action' ) ) {
        wp_send_json_error( 'Unauthorized nonce verification failed', 403 );
        return;
    }

    $is_ajax = defined( 'DOING_AJAX' ) && DOING_AJAX;

    // Get and sanitize post ID
    $post_id = isset( $_POST['post_id'] ) ? intval( $_POST['post_id'] ) : 0;
    if ( ! $post_id || ! current_user_can( 'edit_post', $post_id ) ) {
        wp_send_json_error( 'Unauthorized user or invalid post ID', 403 );
        return;
    }

    // Optional: Check if the post exists and is of type 'os_slider'
    $post = get_post( $post_id );
    if ( ! $post || $post->post_type !== 'os_slider' ) {
        wp_send_json_error( 'Invalid post type', 400 );
        return;
    }

    // Parse the slider data from POST request
    $slider_data = isset( $_POST['slider_data'] ) ? $_POST['slider_data'] : '';
    parse_str( $slider_data, $parsed_data );

    if ( empty( $parsed_data ) ) {
        wp_send_json_error( 'Failed to parse slider data.', 400 );
        return;
    }

    // Get the slider type from parsed data
    $slider_data_type = isset( $parsed_data['os_slider_type'] ) ? sanitize_text_field( $parsed_data['os_slider_type'] ) : 'default';

    // Parse the slider data
    if ( isset( $parsed_data['slides'] ) && isset( $parsed_data['os_slider_options'] ) ) {
        // Validate 'os_slider_options' structure
        if ( ! is_array( $parsed_data['os_slider_options'] ) ) {
            wp_send_json_error( 'Invalid slider options structure.', 400 );
            return;
        }

        // Since sanitization is now handled by the 'pre_update_post_meta' filter, no need to manually call the sanitization function here.
        $sanitized_data = $parsed_data['slides'];
        $sanitized_options = $parsed_data['os_slider_options'];

        $current_data = get_post_meta( $post_id, 'os_slider_data', true );
        $current_options = get_post_meta( $post_id, 'os_slider_options', true );

        if ( $current_data !== $sanitized_data ) {
            update_post_meta( $post_id, 'os_slider_data', $sanitized_data );
        }
        
        if ( $current_options !== $sanitized_options ) {
            update_post_meta( $post_id, 'os_slider_options', $sanitized_options );
        }

        wp_send_json_success( 'Slider data saved successfully' );
    } else {
        wp_send_json_error( 'Missing required fields in slider data.', 400 );
    }
}
// add_action( 'wp_ajax_save_slider_meta', 'os_save_data_ajax' );
