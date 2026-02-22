<?php

/**
 * Environment-specific configuration (local, qa, production).
 *
 * APP_ENV must be one of: local, qa, production
 * These values are used to enforce safe defaults (e.g. debug off in non-local).
 */

$env = env('APP_ENV', 'production');

return [
    'current' => $env,

    'is_local' => $env === 'local',
    'is_qa' => $env === 'qa',
    'is_production' => $env === 'production',

    /*
    |--------------------------------------------------------------------------
    | Debug mode (overrides APP_DEBUG for safety)
    |--------------------------------------------------------------------------
    | In production and qa, debug is forced false. Only local can show debug.
    */
    'debug' => match ($env) {
        'local' => (bool) env('APP_DEBUG', true),
        'qa', 'production' => false,
        default => false,
    },

    /*
    |--------------------------------------------------------------------------
    | Log level
    |--------------------------------------------------------------------------
    */
    'log_level' => match ($env) {
        'local' => env('LOG_LEVEL', 'debug'),
        'qa' => env('LOG_LEVEL', 'info'),
        'production' => env('LOG_LEVEL', 'warning'),
        default => 'warning',
    },

    /*
    |--------------------------------------------------------------------------
    | Log stack (channels)
    |--------------------------------------------------------------------------
    | local: single; qa/production: daily for rotation.
    */
    'log_stack' => match ($env) {
        'local' => env('LOG_STACK', 'single'),
        'qa', 'production' => env('LOG_STACK', 'daily'),
        default => 'single',
    },

    /*
    |--------------------------------------------------------------------------
    | Daily log retention (days)
    |--------------------------------------------------------------------------
    */
    'log_daily_days' => match ($env) {
        'local' => (int) env('LOG_DAILY_DAYS', 3),
        'qa' => (int) env('LOG_DAILY_DAYS', 14),
        'production' => (int) env('LOG_DAILY_DAYS', 30),
        default => 14,
    },
];
