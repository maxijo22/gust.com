<?php

// use App\Http\Middlewares\Guest;

return [
    'guest' => App\Http\Middlewares\Guest::class,
    'auth' => App\Http\Middlewares\Auth::class,
    'crsf_token' => App\Http\Middlewares\VerifyCsrfToken::class
];