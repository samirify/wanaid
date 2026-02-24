<?php

namespace Modules\Client\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Core\Models\LanguageCode;
use Modules\Core\Models\LanguageTranslation;
use Modules\Core\Models\Setting;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds. Data source: Wanaid (source of truth).
     */
    public function run()
    {
        Model::unguard();

        // Main contact phone, address, email: seeded in MainPagesSeeder into phone, address, email tables (new app pattern). Not stored in settings.
        $settings = [
            ['name' => 'app_initiated', 'value' => '0', 'is_public' => false],
            ['name' => 'google_maps_iframe_url', 'value' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d4925.345516651633!2d0.8992641277809071!3d51.88518571879568!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47d904fbda7f0433%3A0xf46211285268afe5!2s11%20Mersea%20Rd%2C%20Colchester%20CO2%207EX!5e0!3m2!1sen!2suk!4v1591476246885!5m2!1sen!2suk', 'is_public' => true],
            ['name' => 'contact_feedback_emails', 'value' => 'sarahsalih2018@gmail.com;dolyship@gmail.com;soiswis@gmail.com;info@wanaid.org', 'is_public' => false],
            ['name' => 'contact_join_us_emails', 'value' => 'rihab_hasan@hotmail.com;Ibtisam65@gmail.com;dolyship@gmail.com;soiswis@gmail.com;info@wanaid.org', 'is_public' => false],
            ['name' => 'contact_website_issues_emails', 'value' => 'dolyship@gmail.com;soiswis@gmail.com;info@wanaid.org', 'is_public' => false],
            ['name' => 'clinic_emails', 'value' => 'hudaelnasri54@gmail.com', 'is_public' => false],
            ['name' => 'static_button_get_started_label', 'value' => 'TOP_NAV_STATIC_BUTTON_GET_STARTED_LABEL', 'is_public' => false],
            ['name' => 'static_button_get_started_url', 'value' => '/cause/help-us', 'is_public' => false],
            ['name' => 'static_button_get_started_url_type', 'value' => 'internal', 'is_public' => false],
            ['name' => 'static_button_get_started_ga_label', 'value' => 'Donate Now button - Top Nav', 'is_public' => false],
            ['name' => 'static_button_get_started_ga_action', 'value' => 'Donate Now', 'is_public' => false],
            ['name' => 'static_button_get_started_ga_category', 'value' => '1 - Donations', 'is_public' => false],
            ['name' => 'payment_success_emails', 'value' => 'Kontif@yahoo.com;kabashi20@hotmail.com;dolyship@gmail.com;soiswis@gmail.com;info@wanaid.org', 'is_public' => false],
            ['name' => 'payment_failure_emails', 'value' => 'Kontif@yahoo.com;kabashi20@hotmail.com;dolyship@gmail.com;soiswis@gmail.com;info@wanaid.org', 'is_public' => false],
        ];

        foreach ($settings as $s) {
            Setting::updateOrCreate(
                ['name' => $s['name']],
                ['value' => $s['value'], 'is_public' => $s['is_public'] ?? false]
            );
        }

        $englishLocale = DB::table('locales')->where('locale', 'en')->first();
        $arabicLocale = DB::table('locales')->where('locale', 'ar')->first();
        if (!$englishLocale || !$arabicLocale) {
            return;
        }
        $enLang = DB::table('language')->where('locales_id', $englishLocale->id)->first();
        $arLang = DB::table('language')->where('locales_id', $arabicLocale->id)->first();
        if (!$enLang || !$arLang) {
            return;
        }

        $pageCode = 'SITE_EMAIL_';
        $emailContentSettings = [
            ['name' => 'user_general_feedback_subject', 'value' => strtoupper($pageCode . 'user_general_feedback_subject'), 'en' => 'WAN Aid - Thank you for your feedback', 'ar' => 'وان ايد - شكرًا لكم على ملاحظاتكم'],
            ['name' => 'user_general_feedback_content', 'value' => strtoupper($pageCode . 'user_general_feedback_content'), 'en' => '<table border="0" cellpadding="0" cellspacing="0" class="container hero-subheader" dir="{{ $lang_direction }}" style="width: 100%;padding: 0 20px;" width="620"><tbody><tr><td align="left" class="hero-subheader__title" style="font-size: 26px; font-weight: bold; padding: 30px 0 15px 0;color: #c71c69;">Hi [[full_name]],</td></tr></tbody><tbody><tr><td align="left" class="hero-subheader__content" style="font-size: 16px; line-height: 27px; color: #222222; padding: 15px 0 0;"><p>We are always working to improve your experience and we really appreciate the time you took to help us improve.</p><p>We will review your feedback and will definitely learn from it. We at WAN Aid believe that we grow together.</p><h5>Your message:</h5><h5>[[user_message]]</h5><p>&nbsp;</p><p>Thanks</p></td></tr></tbody></table>', 'ar' => '<table border="0" cellpadding="0" cellspacing="0" class="container hero-subheader" dir="rtl" style="width: 100%;padding: 0 20px;" width="620"><tbody><tr><td align="right" class="hero-subheader__title" style="font-size: 26px; font-weight: bold; padding: 30px 0 15px 0;color: #c71c69;">مرحبا [[full_name]]،</td></tr></tr><tr><td align="right" class="hero-subheader__content" style="font-size: 16px; line-height: 27px; color: #222222; padding: 15px 0 0;"><p>نحن نعمل دائمًا على تحسين تجربتكم ونقدر حقًا الوقت الذي استغرقتموه لمساعدتنا على التحسين.</p><p>سنراجع ملاحظاتكم وسنتعلم منها بالتأكيد. نحن في وان ايد نعتقد أننا ننمو معًا.</p><h5>رسالتكم:</h5><h5>[[user_message]]</h5><p>&nbsp;</p><p>شكرا لكم</p></td></tr></tbody></table>'],
            ['name' => 'admin_general_feedback_subject', 'value' => strtoupper($pageCode . 'admin_general_feedback_subject'), 'en' => 'Feedback received from website', 'ar' => 'وردت ردود فعل جديدة من الموقع'],
            ['name' => 'admin_general_feedback_content', 'value' => strtoupper($pageCode . 'admin_general_feedback_content'), 'en' => '<table border="0" cellpadding="0" cellspacing="0" class="container hero-subheader" width="620" style="width: 100%;padding: 0 20px;"><tbody><tr><td align="left" class="hero-subheader__title" style="font-size: 26px; font-weight: bold; padding: 30px 0 15px 0;color: #c71c69;">Hi there,</td></tr></tbody><tbody><tr><td align="left" class="hero-subheader__content" style="font-size: 16px; line-height: 27px; color: #222222; padding: 15px 0 0;"><p>A new feedback was received from the website!</p><h5>Details:</h5><h5>Full Name: [[full_name]]</h5><h5>[[user_message]]</h5><p>&nbsp;</p><p>Thanks</p></td></tr></tbody></table>', 'ar' => '<table border="0" cellpadding="0" cellspacing="0" class="container hero-subheader" dir="rtl" width="620" style="width: 100%;padding: 0 20px;"><tbody><tr><td align="right" class="hero-subheader__title" style="font-size: 26px; font-weight: bold; padding: 30px 0 15px 0;color: #c71c69;">مرحبا،</td></tr><tr><td align="right" class="hero-subheader__content" style="font-size: 16px; line-height: 27px; color: #222222; padding: 15px 0 0;"><p>تم تلقي ردود فعل جديدة من الموقع!</p><h5>التفاصيل:</h5><h5>الاسم: [[full_name]]</h5><h5>الرسالة: [[user_message]]</h5><p>&nbsp;</p><p>شكرا لكم</p></td></tr></tbody></table>'],
            ['name' => 'user_join_us_subject', 'value' => strtoupper($pageCode . 'user_join_us_subject'), 'en' => 'WAN Aid - Thank you for your message', 'ar' => 'وان ايد - شكرًا على رسالتكم'],
            ['name' => 'user_join_us_content', 'value' => strtoupper($pageCode . 'user_join_us_content'), 'en' => '<table border="0" cellpadding="0" cellspacing="0" class="container hero-subheader" width="620" style="width: 100%;padding: 0 20px;"><tbody><tr><td align="left" class="hero-subheader__title" style="font-size: 26px; font-weight: bold; padding: 30px 0 15px 0;color: #c71c69;">Hi [[full_name]],</td></tr></tbody><tbody><tr><td align="left" class="hero-subheader__content" style="font-size: 16px; line-height: 27px; color: #222222; padding: 15px 0 0;"><p>Thank you for your interest to join WAN Aid. We\'re very excited and we will get in touch with you within the next 48 hours.</p><p>In the meantime if you have any questions or concerns you could emails us at <a href="mailto:queries@wanaid.org">queries@wanaid.org</a></p><p>&nbsp;</p><p>Thanks</p></td></tr></tbody></table>', 'ar' => '<table border="0" cellpadding="0" cellspacing="0" class="container hero-subheader" dir="rtl" width="620" style="width: 100%;padding: 0 20px;"><tbody><tr><td align="right" class="hero-subheader__title" style="font-size: 26px; font-weight: bold; padding: 30px 0 15px 0;color: #c71c69;">مرحبا [[full_name]]،</td></tr><tr><td align="right" class="hero-subheader__content" style="font-size: 16px; line-height: 27px; color: #222222; padding: 15px 0 0;"><p>شكرًا لكم على اهتمامكم بالانضمام إلى وان ايد. نحن متحمسون للغاية وسوف نتواصل معكم في غضون 48 ساعة القادمة.</p><p> في غضون ذلك ، إذا كان لديكم أي أسئلة أو مخاوف ، يمكنكم مراسلتنا عبر البريد الإلكتروني على <a href="mailto:queries@wanaid.org"> queries@wanaid.org </a></p><p>&nbsp;</p><p>شكرا لكم</p></td></tr></tbody></table>'],
            ['name' => 'admin_join_us_subject', 'value' => strtoupper($pageCode . 'admin_join_us_subject'), 'en' => 'Website new Join Request received', 'ar' => 'تم استلام طلب انضمام جديد إلى موقع الويب'],
            ['name' => 'admin_join_us_content', 'value' => strtoupper($pageCode . 'admin_join_us_content'), 'en' => 'Hi there, <p>A new join request received from the website!</p><h5>Details:</h5><h5>Full Name: [[full_name]]</h5><h5>Message: [[user_message]]</h5><p>Thanks</p>', 'ar' => 'مرحبا، <p>تم استلام طلب انضمام جديد من الموقع!</p><h5>التفاصيل:</h5><h5>الاسم: [[full_name]]</h5><h5>الرسالة: [[user_message]]</h5><p>شكرا لكم</p>'],
            ['name' => 'user_technical_issues_subject', 'value' => strtoupper($pageCode . 'user_technical_issues_subject'), 'en' => 'WAN Aid - Thank you for your message', 'ar' => 'وان ايد - شكرًا على رسالتكم'],
            ['name' => 'user_technical_issues_content', 'value' => strtoupper($pageCode . 'user_technical_issues_content'), 'en' => 'Hi [[full_name]], <p>Thank you for bringing this to our attention. It must have been frustrating! Please accept our apologies and we promise we will inform our technology team right away.</p><p>Thanks as always for using our website.</p><h5>Here\'s your message:</h5><h5>[[user_message]]</h5><p>Many thanks</p>', 'ar' => 'مرحبا [[full_name]]، <p>شكرا لكم على لفت انتباهنا إلى هذا. لا بد أنه كان محبطًا! يُرجى قبول اعتذارنا ونعدكم بإبلاغ فريق التكنولوجيا لدينا على الفور.</p><p>نشكركم دائمًا على استخدام موقعنا الإلكتروني.</p><h5>ها هي رسالتكم:</h5><h5>[[user_message]]</h5><p>شكرا لكم</p>'],
            ['name' => 'admin_technical_issues_subject', 'value' => strtoupper($pageCode . 'admin_technical_issues_subject'), 'en' => 'Website technical issue reported', 'ar' => 'تم الإبلاغ عن مشكلة فنية في موقع الويب'],
            ['name' => 'admin_technical_issues_content', 'value' => strtoupper($pageCode . 'admin_technical_issues_content'), 'en' => 'Hi there, <p>A user has reported a technical issue with the website!</p><p>Details:</p><h5>Full Name: [[full_name]]</h5><h5>Message: [[user_message]]</h5><p>Thanks</p>', 'ar' => 'مرحبا، <p>أبلغ أحد المستخدمين عن مشكلة فنية في الموقع!</p><p>التفاصيل:</p><h5>الاسم: [[full_name]]</h5><h5>الرسالة: [[user_message]]</h5><p>شكرا لكم</p>'],
            ['name' => 'user_paypal_subject', 'value' => strtoupper($pageCode . 'user_paypal_subject'), 'en' => 'WAN Aid - Thank you for your donation', 'ar' => 'وان ايد - شكرًا لكم على تبرعكم'],
            ['name' => 'user_paypal_content', 'value' => strtoupper($pageCode . 'user_paypal_content'), 'en' => 'Hi [[full_name]], <p>Congratulations! Thank you very much for your generosity.</p><p>We confirm that we\'ve received your payment of [[donation_amount]]</p><p>Thanks</p>', 'ar' => 'مرحبا [[full_name]]، <p>تهانينا! شكرا جزيلا على كرمكم.</p><p>نؤكد استلامنا دفعتكم وقدرها [[donation_amount]]</p><p>شكرا لكم</p>'],
            ['name' => 'admin_paypal_subject', 'value' => strtoupper($pageCode . 'admin_paypal_subject'), 'en' => 'New donation received via the website', 'ar' => 'تم استلام تبرع جديد عبر الموقع'],
            ['name' => 'admin_paypal_content', 'value' => strtoupper($pageCode . 'admin_paypal_content'), 'en' => 'Hi there, <p>A user has just paid us some donation!</p><h5>Details:</h5><h5>Name: [[full_name]]</h5><h5>Amount: [[donation_amount]]</h5><h5>Order Id: [[order_id]]</h5><p>Thanks</p>', 'ar' => 'مرحبا، <p>لقد دفع لنا مستخدم للتو بعض التبرعات!</p><h5>التفاصيل:</h5><h5>الاسم: [[full_name]]</h5><h5>المبلغ: [[donation_amount]]</h5><h5>معرّف الطلب: [[order_id]]</h5><p>شكرا لكم</p>'],
            ['name' => 'user_clinic_subject', 'value' => strtoupper($pageCode . 'user_clinic_subject'), 'en' => 'Thank you for your message - Marital Therapy Clinic - WAN Aid', 'ar' => 'شكرًا على رسالتك - عيادة الإستشارات الزوجية - وان ايد'],
            ['name' => 'user_clinic_content', 'value' => strtoupper($pageCode . 'user_clinic_content'), 'en' => 'Hi [[full_name]], <p>Thank you for your request.</p><p>We will forward it to the Marital Therapy Clinic and you will be contacted within the next 48 hours</p><h5>Your details:</h5><h5>Nickname: [[nickname]]</h5><h5>Full Name: [[full_name]]</h5><h5>Telephone / Mobile: [[tel_mobile]]</h5><h5>Country: [[country_name]]</h5><h5>Email: [[email]]</h5><h5>Notes: [[notes]]</h5><p>Thanks</p>', 'ar' => 'مرحبا [[full_name]]، <p>شكرا لطلبكم.</p><p>سنقوم بإرسال معلوماتكم إلى عيادة الإستشارات الزوجية وسيتم الاتصال بكم في غضون 48 ساعة القادمة</p><h5>التفاصيل:</h5><h5>الاسم المستعار: [[nickname]]</h5><h5>الاسم الكامل: [[full_name]]</h5><h5>الهاتف / الجوال: [[tel_mobile]]</h5><h5>البلد: [[country_name]]</h5><h5>البريد الإلكتروني: [[email]]</h5><h5>الملاحظات: [[notes]]</h5><p>شكرا لكم</p>'],
            ['name' => 'admin_clinic_subject', 'value' => strtoupper($pageCode . 'admin_clinic_subject'), 'en' => 'New request fro Marital Therapy Clinic received', 'ar' => 'تم استلام طلب جديد لعيادة العلاج الزوجي'],
            ['name' => 'admin_clinic_content', 'value' => strtoupper($pageCode . 'admin_clinic_content'), 'en' => 'Hi there, <p>A user has just requested an appointment with the Clinic</p><h5>Details:</h5><h5>Nickname: [[nickname]]</h5><h5>Full Name: [[full_name]]</h5><h5>Telephone / Mobile: [[tel_mobile]]</h5><h5>Country: [[country_name]]</h5><h5>Email: [[email]]</h5><h5>Notes: [[notes]]</h5><p>Thanks</p>', 'ar' => 'مرحبا، <p>طلب أحد المستخدمين للتو موعدًا مع العيادة</p><h5>التفاصيل:</h5><h5>الاسم المستعار: [[nickname]]</h5><h5>الاسم الكامل: [[full_name]]</h5><h5>الهاتف / الجوال: [[tel_mobile]]</h5><h5>البلد: [[country_name]]</h5><h5>البريد الإلكتروني: [[email]]</h5><h5>الملاحظات: [[notes]]</h5><p>شكرا لكم</p>'],
        ];

        foreach ($emailContentSettings as $row) {
            Setting::updateOrCreate(
                ['name' => $row['name']],
                ['value' => $row['value'], 'is_public' => false]
            );
            $langCode = LanguageCode::firstOrCreate(
                ['code' => $row['value']],
                ['is_html' => true]
            );
            LanguageTranslation::updateOrCreate(
                ['language_id' => $enLang->id, 'language_code_id' => $langCode->id],
                ['text' => $row['en']]
            );
            LanguageTranslation::updateOrCreate(
                ['language_id' => $arLang->id, 'language_code_id' => $langCode->id],
                ['text' => $row['ar']]
            );
        }
    }
}
