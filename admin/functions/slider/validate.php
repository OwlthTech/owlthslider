<?php

/**
 * Custom validation functions
 */

function os_validate_numeric( $param, $request, $key ) {
    return is_numeric( $param );
}


function os_validate_boolean($value) {
    return is_bool($value);
}

function os_validate_datetime($value) {
    $date = DateTime::createFromFormat('Y-m-d H:i:s', $value);
    return $date !== false;
}
function os_validate_date($value) {
    $date = DateTime::createFromFormat('Y-m-d', $value);
    return $date !== false;
}

function os_validate_non_empty($value) {
    return !empty($value);
}

function os_validate_url($value) {
    return filter_var($value, FILTER_VALIDATE_URL) !== false;
}

function os_validate_rating($value) {
    return is_numeric($value) && $value >= 0 && $value <= 5;
}