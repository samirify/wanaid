<?php

namespace Modules\Core\Services;

class Constants
{
    // Admin
    const APP_INITIAL_ADMIN_USERNAME = 'admin';

    // General Application variables
    const APP_IS_INITIATED = 'app_initiated';
    const APP_INITIATION_CODE = 'app_initiation_code';

    // User roles
    const USER_ROLE_OWNER = 'owner';
    const USER_ROLE_ADMIN = 'admin';
    const USER_ROLE_EDITOR = 'editor';
    const USER_ROLE_USER = 'user';
    const USER_ROLES_ARRAY = [
        self::USER_ROLE_OWNER => 'Project Owner',
        self::USER_ROLE_ADMIN => 'Administrator',
        self::USER_ROLE_EDITOR => 'Editor',
        self::USER_ROLE_USER => 'General User',
    ];

    // Pages
    const PAGE_CODE_ABOUT = 'about';
    const PAGE_CODE_MAIN = 'main';
    const PAGE_CODE_TERMS_OF_USE = 'terms-of-use';
    const PAGE_CODE_PRIVACY_POLICY = 'privacy-policy';
    const PAGE_CODE_DISCLAIMER = 'disclaimer';

    // Application code types
    const ACT_CONTACT_TYPES = 'CONTACT_TYPES';
    const ACT_EMAIL_TYPES = 'EMAIL_TYPES';
    const ACT_PHONE_TYPES = 'PHONE_TYPES';
    const ACT_ADDRESS_TYPES = 'ADDRESS_TYPES';
    const ACT_SOCIAL_MEDIA_BRANDS = 'SOCIAL_MEDIA_BRANDS';

    // Application codes
    const AC_CONTACT_TYPE_PERSON = 'P';
    const AC_CONTACT_TYPE_ORGANISATION = 'O';

    const AC_EMAIL_TYPE_PERSONAL = 'P';
    const AC_EMAIL_TYPE_WORK = 'W';
    const AC_EMAIL_TYPE_OTHER = 'O';

    const AC_PHONE_TYPE_PERSONAL = 'P';
    const AC_PHONE_TYPE_WORK = 'W';
    const AC_PHONE_TYPE_OTHER = 'O';

    const AC_ADDRESS_TYPE_PERSONAL = 'P';
    const AC_ADDRESS_TYPE_WORK = 'W';
    const AC_ADDRESS_TYPE_OTHER = 'O';

    const AC_SOCIAL_MEDIA_FACEBOOK = 'FACEBOOK';
    const AC_SOCIAL_MEDIA_X = 'X-TWITTER';
    const AC_SOCIAL_MEDIA_LINKEDIN = 'LINKEDIN';
    const AC_SOCIAL_MEDIA_INSTAGRAM = 'INSTAGRAM';
    const AC_SOCIAL_MEDIA_YOUTUBE = 'YOUTUBE';
    const AC_SOCIAL_MEDIA_TIKTOK = 'TIKTOK';
    const AC_SOCIAL_MEDIA_GITHUB = 'GITHUB';

    const AC_PAGE_SECTION_LIST_WIDGET = 'LIST';
    const AC_PAGE_SECTION_SEARCH_WIDGET = 'SEARCH';
    const AC_PAGE_SECTION_PRICING_WIDGET = 'PRICING';
    const AC_PAGE_SECTION_WIDGET_ARRAY = [
        self::AC_PAGE_SECTION_LIST_WIDGET => 'List or collection',
        self::AC_PAGE_SECTION_SEARCH_WIDGET => 'Search',
        self::AC_PAGE_SECTION_PRICING_WIDGET => 'Pricing',
    ];
}
