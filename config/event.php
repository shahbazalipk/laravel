<?php

return [
    'event_id' => env('EVENT_ID'),
    'org_id' => env('ORG_ID'),
    'org_portal_url' => env('ORG_PORTAL_URL', 'http://127.0.0.1:8000'),
    'sso_secret' => env('EVENT_SSO_SECRET'),
    'event_domain' => env('EVENT_DOMAIN', 'glimzo.ai'),
];
