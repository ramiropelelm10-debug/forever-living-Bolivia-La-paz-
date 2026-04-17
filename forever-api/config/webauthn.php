<?php

use Cose\Algorithms;
use LaravelWebauthn\Models\WebauthnKey;

return [
    'enable' => true,

    'guard' => 'sanctum',

    'username' => 'email',

    'user_verification' => 'discouraged', 
    'attachment_mode' => 'platform',

    'prefix' => env('WEBAUTHN_ROUTES_PREFIX', null),
    'domain' => env('WEBAUTHN_ID', 'localhost'),

    'middleware' => ['api'],

    'model' => WebauthnKey::class,

    'limiters' => [
        'login' => null,
    ],

    'redirects' => [
        'login' => null,
        'register' => null,
        'key-confirmation' => null,
    ],

    'views' => [
        'authenticate' => 'webauthn::authenticate',
        'register' => 'webauthn::register',
    ],

    'log' => null,
    'session_name' => 'webauthn_auth',
    'challenge_length' => 32,
    'timeout' => 60000, 
    'extensions' => [],
    'icon' => env('WEBAUTHN_ICON'),

    'attestation_conveyance' => 'none',

    'public_key_credential_parameters' => [
        Algorithms::COSE_ALGORITHM_ES256,
        Algorithms::COSE_ALGORITHM_RS256,
    ],

    'resident_key' => 'preferred',

    'userless' => (bool) env('WEBAUTHN_USERLESS', false),
];