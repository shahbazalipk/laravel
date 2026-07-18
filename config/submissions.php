<?php

return [
    'enabled' => env('SUBMISSIONS_MODULE_ENABLED', true),
    'portal_token_ttl_minutes' => 20,
    'max_upload_kilobytes' => 20 * 1024,
    'allowed_upload_mimes' => [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'image/jpeg',
        'image/png',
    ],
    'reminder_days' => [7, 3, 1],
];
