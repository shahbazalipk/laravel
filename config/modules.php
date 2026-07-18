<?php

return [
    'finance' => [
        'enabled' => env('FINANCE_MODULE_ENABLED', true),
    ],

    'projects' => [
        'enabled' => env('PROJECTS_MODULE_ENABLED', true),
    ],

    'attachments' => [
        'disk' => env('MODULE_ATTACHMENTS_DISK', 'local'),
        'max_kilobytes' => env('MODULE_ATTACHMENT_MAX_KILOBYTES', 10 * 1024),
        'allowed_mimes' => [
            'image/jpeg',
            'image/png',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ],
    ],
];
