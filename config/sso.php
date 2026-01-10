<?php

return [
    'algo' => env('SSO_JWT_ALGO', 'HS256'),
    'secret' => env('SSO_JWT_SECRET'),
    'public_key' => env('SSO_JWT_PUBLIC_KEY'), // kalau pakai RS256

    'iss' => env('SSO_JWT_ISS', 'internal-system'),
    'aud' => env('SSO_JWT_AUD', 'lms-system'),

    'leeway' => (int) env('SSO_JWT_LEEWAY', 180),
    'max_age' => (int) env('SSO_JWT_MAX_AGE', 180),
];
