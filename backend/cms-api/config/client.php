<?php

use Modules\Core\Services\Constants;

return [

    /*
    |--------------------------------------------------------------------------
    | Client Config
    | Samir Ibrahim - 15/03/2020
    |--------------------------------------------------------------------------
    |
    | This file is for storing the config parameters for Client
    |
    */

    'app_version' => env('APP_VERSION'),
    'app_emails' => [
        'img_path' => [
            'logo_header' => 'https://samirify.com/assets/images/logos/png/samirify-color-logo.png',
            'logo_footer' => 'https://samirify.com/assets/images/logos/png/samirify-white-logo.png',
        ],
    ],
    'app_environment' => env('APP_ENV'),
    'sitemap_location' => env('SITEMAP_LOCATION'),
    'admin_root_folder_location' => env('ADMIN_ROOT_FOLDER_LOCATION'),
    'website_public_folder_location' => env('WEBSITE_PUBLIC_FOLDER_LOCATION'),
    'registration' => [
        'email' => env('CLIENT_REGISTRATION_EMAIL', 'soiswis@gmail.com'), // Use only for local development!
    ],
    'images' => [
        'allowed_mime_types' => 'jpeg,jpg,bmp,png,gif,svg',
        'max_upload_size' => 3000,
        'thumbnail_extensions' => ['png', 'jpg', 'jpeg', 'bmp', 'webp']
    ],
    'api' => [
        'root' => env('CLIENT_API_ROOT'),
        'key' => env('CLIENT_API_KEY'),
        'version' => env('CLIENT_API_VERSION', 'v1'),
        'private' => [
            'allowed_hosts' => env('CLIENT_API_PRIVATE_ALLOWED_HOSTS'),
        ]
    ],
    'site' => [
        'root' => env('CLIENT_SITE_ROOT'),
        'emails' => [
            'signup_target_emails' => null,
            'subscribe_target_bcc_emails' => null,
            'payment_target_bcc_emails' => null,
            'contact_feedback_emails' => null,
            'contact_join_us_emails' => null,
            'contact_website_issues_emails' => null,
            'payment_success_emails' => null,
            'payment_failure_emails' => null,
        ],
        'page' => [
            'headers' => [
                'main_header_top',
                'main_header_middle_big',
                'main_header_bottom',
            ],
            'general_translations' => [
                // Footer
                'WEBSITE_FOOTER_FOLLOW_US_ON_LABEL' => [
                    'name' => 'Website footer - Follow us on label',
                    'location' => 'Footer'
                ],
                'WEBSITE_FOOTER_NEWSLETTER_LABEL' => [
                    'name' => 'Website footer - Newsletter label',
                    'location' => 'Footer'
                ],
                'WEBSITE_FOOTER_NEWSLETTER_SUBSCRIBE_MSG' => [
                    'name' => 'Website footer - Subscribe message',
                    'location' => 'Footer'
                ],
                'WEBSITE_FOOTER_NEWSLETTER_TERMS_MSG' => [
                    'name' => 'Website footer - Terms message',
                    'location' => 'Footer'
                ],
                'WEBSITE_FOOTER_NEWSLETTER_JOIN_FIELD_PLACEHOLDER' => [
                    'name' => 'Website footer - Join field placeholder',
                    'location' => 'Footer'
                ],
                'WEBSITE_FOOTER_NEWSLETTER_JOIN_BTN_LABEL' => [
                    'name' => 'Website footer - Join button label',
                    'location' => 'Footer'
                ],
                'WEBSITE_FOOTER_NEWSLETTER_JOIN_IN_PROGRESS_BTN_LABEL' => [
                    'name' => 'Website footer - Join in progress button label',
                    'location' => 'Footer'
                ],
                'WEBSITE_FOOTER_COPYRIGHT_MESSAGE' => [
                    'name' => 'Website footer - Copyright message',
                    'location' => 'Footer'
                ],
                // General
                'WEBSITE_COOKIE_ALERT_MESSAGE' => [
                    'name' => 'Website footer - Cookie alert message',
                    'location' => 'Footer'
                ],
                'WEBSITE_COOKIE_ALERT_MESSAGE_BTN_LABEL' => [
                    'name' => 'Website footer - Cookie alert button label',
                    'location' => 'Footer'
                ],
                'WEBSITE_COOKIE_ALERT_MESSAGE_PRIVACY_POLICY_LABEL' => [
                    'name' => 'Website footer - Cookie alert privacy policy label',
                    'location' => 'Footer'
                ],
                'WEBSITE_ALERT_MESSAGE_BTN_LABEL' => [
                    'name' => 'Website - Alert button label',
                    'location' => 'All pages'
                ],
                'WEBSITE_BACK_TO_HOME_PAGE_LABEL' => [
                    'name' => 'Website - Back to home page label',
                    'location' => 'All pages'
                ],
                // Errors
                'WEBSITE_ERRORS_INITIALISATION_FAILED_MESSAGE' => [
                    'name' => 'Website errors - Initialisation failed message',
                    'location' => 'All pages'
                ],
                'WEBSITE_ERRORS_PAGE_NOT_FOUND_HEADER' => [
                    'name' => 'Website errors - Page not found header',
                    'location' => 'All pages'
                ],
                'WEBSITE_ERRORS_PAGE_NOT_FOUND_MESSAGE' => [
                    'name' => 'Website errors - Page not found message',
                    'location' => 'All pages'
                ],
                'WEBSITE_ERRORS_SERVER_ERROR_HEADER' => [
                    'name' => 'Website errors - Server error header',
                    'location' => 'All pages'
                ],
                'WEBSITE_ERRORS_SERVER_ERROR_MESSAGE' => [
                    'name' => 'Website errors - Server error message',
                    'location' => 'All pages'
                ],
                'WEBSITE_ERROR_LABEL' => [
                    'name' => 'Website messages - Error occurred label',
                    'location' => 'All pages'
                ],
            ]
        ],
        'standard_pages' => [
            Constants::PAGE_CODE_ABOUT,
            Constants::PAGE_CODE_MAIN,
            Constants::PAGE_CODE_DISCLAIMER,
            Constants::PAGE_CODE_TERMS_OF_USE,
            Constants::PAGE_CODE_PRIVACY_POLICY,
        ]
    ],
    'admin_panel' => [
        'clear_cache' => [
            'no_auth_code' => 'AB00981HJKI01119',
        ],
        'reset_db' => [
            'no_auth_code' => 'AB00981HJKI01119',
            'allowed_hosts' => [
                'localhost:8000',
                '127.0.0.1:8000',
                '192.168.1.165:8000',
            ]
        ]
    ],
    'payment' => [
        'paypal' => [
            'api_root_v1' => env('PAYPAL_API_ROOT_V1', null),
            'api_root_v2' => env('PAYPAL_API_ROOT_V2', null),
            'client_id' => env('PAYPAL_CLIENT_ID', null),
            'secret' => env('PAYPAL_SECRET', null)
        ]
    ],
];
