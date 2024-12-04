<?php

/**
 * Retrieves Google reviews for a specified Place ID.
 *
 * @param string $google_place_id The Google Place ID.
 *
 * @return array An array of reviews or an empty array on failure.
 */
function os_fetch_google_reviews( $google_place_id, $refresh = false ) {
    if ( empty( $google_place_id ) ) {
        $google_place_id = ORGOTEL_PLACES_ID;
    }

    // Generate a unique transient key for caching the reviews.
    $transient_key   = 'owlth_google_reviews_' . md5( $google_place_id );
    // delete_transient($transient_key);
    $cached_reviews = get_transient( $transient_key );
    if ( false === $cached_reviews && ! is_wp_error( $cached_reviews ) ) {
        error_log( 'Failed to retrieve transient for Google reviews. Transient key: ' . $transient_key );
    }

    // If cached reviews exist, return them.
    if ( false !== $cached_reviews && $refresh === false ) {
        return $cached_reviews;
    }

    error_log('calling api');
    // Build the API request URL.
    $api_url = add_query_arg( array(
        'place_id' => $google_place_id,
        'key'      => OWLTH_GOOGLE_PLACES_API_KEY,
    ), 'https://maps.googleapis.com/maps/api/place/details/json' );

    // Make the API request with a timeout.
    $response = wp_remote_get( $api_url, array( 'timeout' => 10, 'headers' => array( 'User-Agent' => 'WordPress/' . get_bloginfo('version'), 'Referer' => site_url() ) ) );

    // Check for errors in the response.
    if ( is_wp_error( $response ) ) {
        error_log( 'API request failed. URL: ' . $api_url . ' Error: ' . $response->get_error_message() );
        return array();
    }

    // Retrieve and decode the response body.
    $body = wp_remote_retrieve_body( $response );

    if ( empty( $body ) ) {
        error_log( 'Empty response body for API request. URL: ' . $api_url );
        return array();
    }

    $data = json_decode( $body );

    // Validate the response data.
    if ( empty( $data ) || $data->status !== 'OK' || empty( $data->result->reviews ) ) {
        error_log( 'Invalid response data for API request. URL: ' . $api_url );
        return array();
    }

    $reviews = $data->result->reviews;

    // Cache the reviews for 12 hours to reduce API requests.
    if ( ! set_transient( $transient_key, $reviews, 12 * HOUR_IN_SECONDS ) ) {
        // Handle error if setting the transient fails.
        error_log( 'Failed to set transient for Google reviews. Transient key: ' . $transient_key );
    }

    // Cache profile photos
    $images_dir = OWLTHSLIDER_PLUGIN_DIR . '/review-images/';
    if ( ! file_exists( $images_dir ) ) {
        mkdir( $images_dir, 0755, true );
    }

    foreach ( $reviews as $review ) {
        if ( isset( $review->profile_photo_url ) && ! empty( $review->profile_photo_url ) ) {
            $cached_photo_path = $images_dir . md5( $review->profile_photo_url ) . '.jpg';
            if ( ! file_exists( $cached_photo_path ) ) {
                $photo_response = wp_remote_get( $review->profile_photo_url );
                if ( ! is_wp_error( $photo_response ) ) {
                    $photo_body = wp_remote_retrieve_body( $photo_response );
                    if ( ! empty( $photo_body ) ) {
                        file_put_contents( $cached_photo_path, $photo_body );
                    }
                }
            }
        }
    }

    chmod( $images_dir, 0644 );

    return $reviews;
}