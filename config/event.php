<?php

return [
    'event_id' => env('EVENT_ID'),
    'org_id' => env('ORG_ID'),
    'org_portal_url' => env('ORG_PORTAL_URL'),
    'org_portal_scheme' => env('ORG_PORTAL_SCHEME', 'https'),
    'sso_secret' => env('EVENT_SSO_SECRET'),
    'event_domain' => env('EVENT_DOMAIN', 'glimzo.ai'),
];
