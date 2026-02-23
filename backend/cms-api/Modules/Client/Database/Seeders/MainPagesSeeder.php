<?php

namespace Modules\Client\Database\Seeders;

use App\Traits\AppHelperTrait;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Core\Models\Address;
use Modules\Core\Models\Contact;
use Modules\Core\Models\Email;
use Modules\Core\Models\LanguageCode;
use Modules\Core\Models\LanguageTranslation;
use Modules\Core\Models\MediaStore;
use Modules\Core\Models\Organisation;
use Modules\Core\Models\Phone;
use Modules\Core\Models\Setting;
use Modules\Core\Models\SocialMedia;
use Modules\Core\Services\Constants;
use Modules\PageComponents\Models\HeaderCta;

class MainPagesSeeder extends Seeder
{
    use AppHelperTrait;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        $contactType = DB::table('application_code_type')->where('code', Constants::ACT_CONTACT_TYPES)->first();
        $emailType = DB::table('application_code_type')->where('code', Constants::ACT_EMAIL_TYPES)->first();
        $addressType = DB::table('application_code_type')->where('code', Constants::ACT_ADDRESS_TYPES)->first();
        $phoneType = DB::table('application_code_type')->where('code', Constants::ACT_PHONE_TYPES)->first();
        $socialMediaBrand = DB::table('application_code_type')->where('code', Constants::ACT_SOCIAL_MEDIA_BRANDS)->first();

        $contact = Contact::create([
            'contact_type_id' => DB::table('application_code')
                ->where('application_code_type_id', $contactType->id)
                ->where('code', Constants::AC_CONTACT_TYPE_ORGANISATION)
                ->first()->id,
            'reference_no' => $this->formatCode('samirify-demo-site')
        ]);

        $contactId = $contact->id;

        Organisation::create([
            'contact_id' => $contactId,
            'name' => 'Samirify Org',
            'is_main' => 1
        ]);

        Email::create([
            'contact_id' => $contactId,
            'type_id' => DB::table('application_code')
                ->where('application_code_type_id', $emailType->id)
                ->where('code', Constants::AC_EMAIL_TYPE_WORK)
                ->first()->id,
            'email_address' => 'tech@samirify.com',
            'is_primary' => true,
        ]);

        Phone::create([
            'contact_id' => $contactId,
            'type_id' => DB::table('application_code')
                ->where('application_code_type_id', $phoneType->id)
                ->where('code', Constants::AC_PHONE_TYPE_WORK)
                ->first()->id,
            'phone_number' => '+44 7568 277011',
            'is_primary' => true,
        ]);

        Address::create([
            'contact_id' => $contactId,
            'type_id' => DB::table('application_code')
                ->where('application_code_type_id', $addressType->id)
                ->where('code', Constants::AC_ADDRESS_TYPE_WORK)
                ->first()->id,
            'full_address' => 'Flat 9, 11 Mersea Road, Colchester CO2 7EX, United Kingdom',
            'is_primary' => true,
        ]);

        SocialMedia::create([
            'contact_id' => $contactId,
            'brand_id' => DB::table('application_code')
                ->where('application_code_type_id', $socialMediaBrand->id)
                ->where('code', Constants::AC_SOCIAL_MEDIA_LINKEDIN)
                ->first()->id,
            'url' => 'https://www.linkedin.com/in/samir-s-ibrahim-393971101/',
            'is_primary' => true,
        ]);

        SocialMedia::create([
            'contact_id' => $contactId,
            'brand_id' => DB::table('application_code')
                ->where('application_code_type_id', $socialMediaBrand->id)
                ->where('code', Constants::AC_SOCIAL_MEDIA_X)
                ->first()->id,
            'url' => 'https://twitter.com/soiswis',
            'is_primary' => true,
        ]);


        $englishLocale = DB::table('locales')->where('locale', 'en')->first();
        $arabicLocale = DB::table('locales')->where('locale', 'ar')->first();

        // English Language
        $enLangCode = DB::table('language')->where('locales_id', $englishLocale->id)->first();
        // Arabic Language
        $arLangCode = DB::table('language')->where('locales_id', $arabicLocale->id)->first();

        $generalTranslations = [
            // Footer
            'WEBSITE_FOOTER_FOLLOW_US_ON_LABEL' => [
                $enLangCode->id => 'Follow us on',
                $arLangCode->id => 'تابعنا على وسائل التواصل الإجتماعي',
            ],
            'WEBSITE_FOOTER_NEWSLETTER_LABEL' => [
                $enLangCode->id => 'Newsletter',
                $arLangCode->id => 'نشرتنا الإخبارية',
            ],
            'WEBSITE_FOOTER_NEWSLETTER_SUBSCRIBE_MSG' => [
                $enLangCode->id => 'Subscribe to our FREE newsletter and stay tuned!',
                $arLangCode->id => 'اشترك في النشرة الإخبارية المجانية لدينا وترقبوا!',
            ],
            'WEBSITE_FOOTER_NEWSLETTER_TERMS_MSG' => [
                $enLangCode->id => 'I have read and understood the <a target="_blank" href="/terms-of-use">Terms &amp; Conditions</a>.',
                $arLangCode->id => 'لقد قرأت وفهمت <a target="_blank" href="/terms-of-use">الشروط والأحكام</a>.',
                'is_html' => true
            ],
            'WEBSITE_FOOTER_NEWSLETTER_JOIN_FIELD_PLACEHOLDER' => [
                $enLangCode->id => 'Enter your mail',
                $arLangCode->id => 'أدخل البريد الخاص بك',
            ],
            'WEBSITE_FOOTER_NEWSLETTER_JOIN_BTN_LABEL' => [
                $enLangCode->id => 'Join Now!',
                $arLangCode->id => 'انضم الآن!',
            ],
            'WEBSITE_FOOTER_NEWSLETTER_JOIN_IN_PROGRESS_BTN_LABEL' => [
                $enLangCode->id => 'Joining...',
                $arLangCode->id => 'جاري الإنضمام...',
            ],
            'WEBSITE_FOOTER_COPYRIGHT_MESSAGE' => [
                $enLangCode->id => '© Copyright [YEAR] Samirify LTD. All Rights Reserved',
                $arLangCode->id => '© حقوق الطبع والنشر [YEAR] Samirify LTD. كل الحقوق محفوظة',
            ],
            // General
            'WHO_ARE_WE_BUTTON_LABEL' => [
                $enLangCode->id => 'Who are we?',
                $arLangCode->id => 'من نحن؟',
            ],
            'TOP_NAV_STATIC_BUTTON_GET_STARTED_LABEL' => [
                $enLangCode->id => 'Get Started',
                $arLangCode->id => 'لنبدأ',
            ],
            'WEBSITE_COOKIE_ALERT_MESSAGE' => [
                $enLangCode->id => 'We use cookies to enhance your experience.',
                $arLangCode->id => 'نحن نستخدم ملفات تعريف الارتباط لتحسين تجربتك.',
            ],
            'WEBSITE_COOKIE_ALERT_MESSAGE_BTN_LABEL' => [
                $enLangCode->id => 'I agree',
                $arLangCode->id => 'أنا موافق',
            ],
            'WEBSITE_COOKIE_ALERT_MESSAGE_PRIVACY_POLICY_LABEL' => [
                $enLangCode->id => 'Click here to read our full Privacy Policy',
                $arLangCode->id => 'انقر هنا لقراءة سياسة الخصوصية الكاملة الخاصة بنا',
            ],
            'WEBSITE_ALERT_MESSAGE_BTN_LABEL' => [
                $enLangCode->id => 'I agree',
                $arLangCode->id => 'أنا موافق',
            ],
            'WEBSITE_BACK_TO_HOME_PAGE_LABEL' => [
                $enLangCode->id => 'Back to home page',
                $arLangCode->id => 'العودة إلى الصفحة الرئيسية',
            ],
            // Errors
            'WEBSITE_ERRORS_INITIALISATION_FAILED_MESSAGE' => [
                $enLangCode->id => 'Sorry! The website failed to initialise properly! Please check your internet connection and refresh the page',
                $arLangCode->id => 'عفوا ! فشل الموقع في التهيئة بشكل صحيح! يرجى التحقق من الإنترنت الخاص بك وتحديث الصفحة',
            ],
            'WEBSITE_ERRORS_PAGE_NOT_FOUND_HEADER' => [
                $enLangCode->id => 'Page not found!',
                $arLangCode->id => 'الصفحة غير موجودة!',
            ],
            'WEBSITE_ERRORS_PAGE_NOT_FOUND_MESSAGE' => [
                $enLangCode->id => 'The page you\'re looking for doesn\'t exists or has been removed',
                $arLangCode->id => 'الصفحة التي تبحث عنها غير موجودة أو تمت إزالتها',
            ],
            'WEBSITE_ERRORS_SERVER_ERROR_HEADER' => [
                $enLangCode->id => 'Opps! Something went wrong at our end!',
                $arLangCode->id => 'عفوا! حدث خطأ من جانبنا!',
            ],
            'WEBSITE_ERRORS_SERVER_ERROR_MESSAGE' => [
                $enLangCode->id => 'We\'ll fix it soon.. apologies',
                $arLangCode->id => 'سنقوم بإصلاحه قريبا .. نأسف',
            ],
            'WEBSITE_ERROR_LABEL' => [
                $enLangCode->id => 'Opps! An error occurred',
                $arLangCode->id => 'عفوا! حدث خطأ',
            ],
        ];

        foreach ($generalTranslations as $code => $translation) {
            $langCode = LanguageCode::create([
                'code' => $code,
                'is_html' => $translation['is_html'] ?? false
            ]);

            LanguageTranslation::create([
                'language_id' => $enLangCode->id,
                'language_code_id' => $langCode->id,
                'text' => $translation[$enLangCode->id]
            ]);

            LanguageTranslation::create([
                'language_id' => $arLangCode->id,
                'language_code_id' => $langCode->id,
                'text' => $translation[$arLangCode->id]
            ]);
        }

        $aboutPage = DB::table('pages')->where('code', Constants::PAGE_CODE_ABOUT)->first();

        $aboutHeaderImagePath = public_path('/img/test-images/about-header.jpg');
        if (file_exists($aboutHeaderImagePath)) {
            $content = file_get_contents($aboutHeaderImagePath);
            MediaStore::create([
                'entity_name' => 'PageHeaderImage',
                'entity_id' => (string)$aboutPage->id,
                'mime_type' => 'image/jpeg',
                'file_name' => 'about-header.jpg',
                'file_size' => strlen($content),
                'file_extension' => 'jpg',
                'content' => $content
            ]);
        }

        $aboutPagePillarSection = DB::table('page_sections')->where([
            'code' => 'PILLARS',
            'pages_id' => $aboutPage->id
        ])->first();

        $mainPage = DB::table('pages')->where('code', Constants::PAGE_CODE_MAIN)->first();

        $mainPagePillarSection = DB::table('page_sections')->where([
            'code' => 'PILLARS',
            'pages_id' => $mainPage->id
        ])->first();

        HeaderCta::create([
            'name' => 'header_cta1',
            'label' => 'TOP_NAV_STATIC_BUTTON_GET_STARTED_LABEL',
            'url' => $mainPagePillarSection->id,
            'url_type' => 'internal',
            'style' => 'light',
            'order' => 1,
            'pages_id' => $mainPage->id,
            'active' => 1,
        ]);

        HeaderCta::create([
            'name' => 'header_cta2',
            'label' => 'WHO_ARE_WE_BUTTON_LABEL',
            'url' => $aboutPagePillarSection->id,
            'url_type' => 'internal',
            'style' => 'dark',
            'order' => 2,
            'pages_id' => $mainPage->id,
            'active' => 1,
        ]);

        Setting::updateOrCreate([
            'name' => 'google_maps_iframe_url',
        ], [
            'value' => ''
        ]);
    }
}
