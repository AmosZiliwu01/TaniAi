<?php

return [

    'mailgun' => [
        'domain'   => env('MAILGUN_DOMAIN'),
        'secret'   => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme'   => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    // ── Groq / Llama AI ────────────────────────────────────────────────────────
    // Use meta-llama/llama-4-scout-17b-16e-instruct via Groq API (unlimited)
    'groq' => [
        'key'   => env('GROQ_API_KEY'),
        'url'   => 'https://api.groq.com/openai/v1/chat/completions',
        'model' => env('GROQ_MODEL', 'meta-llama/llama-4-scout-17b-16e-instruct'),
    ],

    // ── OpenWeatherMap ─────────────────────────────────────────────────────────
    'openweather' => [
        'key' => env('OPENWEATHER_API_KEY'),
    ],

];
