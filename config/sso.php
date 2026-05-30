<?php

return [
    'algo' => env('SSO_JWT_ALGO', 'HS256'),
    'secret' => env('SSO_JWT_SECRET', 'c322404f0d31668cb0e002cd42e3f0c2c8c66f4ba1a8de686961805ba283aa89'),
    'public_key' => env('SSO_JWT_PUBLIC_KEY'), // kalau pakai RS256

    'iss' => env('SSO_JWT_ISS', 'internal-system'),
    'aud' => env('SSO_JWT_AUD', 'lms-system'),

    'leeway' => (int) env('SSO_JWT_LEEWAY', 300),
    'max_age' => (int) env('SSO_JWT_MAX_AGE', 300),
];
