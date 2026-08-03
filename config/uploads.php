<?php

/**
 * Intended upload/post body limits for this application.
 *
 * PHP's post_max_size and upload_max_filesize are PHP_INI_PERDIR and cannot be
 * raised reliably with ini_set() from Laravel. On Apache they are configured in:
 * - public/.htaccess  (mod_php)
 * - public/.user.ini  (PHP-FPM / CGI)
 *
 * Keep those files aligned with the values below.
 */
return [
    'upload_max_filesize' => env('UPLOAD_MAX_FILESIZE', '16M'),
    'post_max_size' => env('POST_MAX_SIZE', '32M'),
    'max_input_vars' => (int) env('MAX_INPUT_VARS', 5000),

    // Soft limit for registration profile photos after client-side compression.
    'profile_picture_max_kb' => (int) env('PROFILE_PICTURE_MAX_KB', 2048),
];
