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
            'reference_no' => $this->formatCode('wanaid')
        ]);

        $contactId = $contact->id;

        Organisation::create([
            'contact_id' => $contactId,
            'name' => 'WAN Aid',
            'is_main' => 1
        ]);

        Email::create([
            'contact_id' => $contactId,
            'type_id' => DB::table('application_code')
                ->where('application_code_type_id', $emailType->id)
                ->where('code', Constants::AC_EMAIL_TYPE_WORK)
                ->first()->id,
            'email_address' => 'info@wanaid.org',
            'is_primary' => true,
        ]);

        Phone::create([
            'contact_id' => $contactId,
            'type_id' => DB::table('application_code')
                ->where('application_code_type_id', $phoneType->id)
                ->where('code', Constants::AC_PHONE_TYPE_WORK)
                ->first()->id,
            'phone_number' => '+44 7877 089072',
            'is_primary' => true,
        ]);

        Address::create([
            'contact_id' => $contactId,
            'type_id' => DB::table('application_code')
                ->where('application_code_type_id', $addressType->id)
                ->where('code', Constants::AC_ADDRESS_TYPE_WORK)
                ->first()->id,
            'full_address' => 'Louisa Place, Cardiff, South Glamorgan, United Kingdom, CF10 5BY',
            'is_primary' => true,
        ]);

        SocialMedia::create([
            'contact_id' => $contactId,
            'brand_id' => DB::table('application_code')
                ->where('application_code_type_id', $socialMediaBrand->id)
                ->where('code', Constants::AC_SOCIAL_MEDIA_FACEBOOK)
                ->first()->id,
            'url' => 'https://www.facebook.com/Womenaccessnetwork/',
            'is_primary' => true,
        ]);

        SocialMedia::create([
            'contact_id' => $contactId,
            'brand_id' => DB::table('application_code')
                ->where('application_code_type_id', $socialMediaBrand->id)
                ->where('code', Constants::AC_SOCIAL_MEDIA_INSTAGRAM)
                ->first()->id,
            'url' => 'https://www.instagram.com/women_access_network/',
            'is_primary' => true,
        ]);


        $englishLocale = DB::table('locales')->where('locale', 'en')->first();
        $arabicLocale = DB::table('locales')->where('locale', 'ar')->first();

        // English Language
        $enLangCode = DB::table('language')->where('locales_id', $englishLocale->id)->first();
        // Arabic Language
        $arLangCode = DB::table('language')->where('locales_id', $arabicLocale->id)->first();

        // Translations from Wanaid (source of truth). Keys aligned with Wanaid MainPagesSeeder.
        $generalTranslations = [
            'TOP_NAV_HOME_LABEL' => [$enLangCode->id => 'Home', $arLangCode->id => 'الرئيسية'],
            'TOP_NAV_ABOUT_LABEL' => [$enLangCode->id => 'About', $arLangCode->id => 'حول الموقع'],
            'TOP_NAV_CAUSES_LABEL' => [$enLangCode->id => 'Causes', $arLangCode->id => 'مشاريع الدعم'],
            'TOP_NAV_BLOG_LABEL' => [$enLangCode->id => 'Blog', $arLangCode->id => 'المدونات'],
            'TOP_NAV_CONTACT_LABEL' => [$enLangCode->id => 'Contact', $arLangCode->id => 'اتصل بنا'],
            'OPEN_CAUSES_HEADER_LABEL' => [$enLangCode->id => 'Open Causes', $arLangCode->id => 'مشاريع الدعم الحالية'],
            'OPEN_CAUSES_VIEW_ALL_LABEL' => [$enLangCode->id => 'View All Causes', $arLangCode->id => 'عرض جميع المشاريع'],
            'OPEN_CAUSES_TARGET_LABEL' => [$enLangCode->id => 'Target', $arLangCode->id => 'الهدف'],
            'OPEN_CAUSES_CONTRIBUTERS_LABEL' => [$enLangCode->id => 'Contributers', $arLangCode->id => 'المساهمون'],
            'OPEN_CAUSES_RAISED_LABEL' => [$enLangCode->id => 'Raised', $arLangCode->id => 'التبرعات'],
            'OPEN_CAUSES_DONATE_BTN_LABEL' => [$enLangCode->id => 'Donate', $arLangCode->id => 'تبرع'],
            'OPEN_CAUSES_DONATE_NOW_LABEL' => [$enLangCode->id => 'Donate Now', $arLangCode->id => 'تبرع الآن'],
            'LANDING_PAGE_BLOG_HEADER_LABEL' => [$enLangCode->id => 'Our Blog', $arLangCode->id => 'مدونتنا'],
            'LANDING_PAGE_BLOG_LATEST_FROM_US_LABEL' => [$enLangCode->id => 'The latest from us!', $arLangCode->id => 'آخر أخبارنا!'],
            'LANDING_PAGE_BLOG_NO_BLOGS_MESSAGE' => [$enLangCode->id => 'No blogs at present! Please come back later', $arLangCode->id => 'لا توجد مدونات في الوقت الحاضر! من فضلك عد لاحقا'],
            'LANDING_PAGE_BLOG_MORE_BTN_LABEL' => [$enLangCode->id => 'Read more...', $arLangCode->id => 'قراءة المزيد...'],
            'ABOUT_PAGE_TEAM_HEADER' => [$enLangCode->id => 'Our Team', $arLangCode->id => 'فريق العمل'],
            'ABOUT_PAGE_TEAM_SUB_HEADER' => [$enLangCode->id => 'The team who made it happen!', $arLangCode->id => 'الفريق الذي حقق ذلك!'],
            'WEBSITE_CONTACT_HEADER_MAIN_TOP' => [$enLangCode->id => 'Contact us', $arLangCode->id => 'اتصل بنا'],
            'WEBSITE_CONTACT_HEADER_LABEL' => [$enLangCode->id => "Let's Get In Touch!", $arLangCode->id => 'ابقى على تواصل!'],
            'WEBSITE_CONTACT_SUB_HEADER_MESSAGE' => [$enLangCode->id => 'Get in touch with us? Give us a call or send us an email and we will get back to you as soon as possible!', $arLangCode->id => 'ابق على تواصل معنا؟ اتصل بنا أو أرسل لنا بريدًا إلكترونيًا وسنعاود الاتصال بك في أقرب وقت ممكن!'],
            'WEBSITE_CONTACT_WRITE_TO_US_HEADER' => [$enLangCode->id => 'Write to us online', $arLangCode->id => 'اكتب إلينا عبر الإنترنت'],
            'WEBSITE_CONTACT_WRITE_TO_US_SUB_HEADER' => [$enLangCode->id => 'Submit the form below and we will get back to you as soon as possible!', $arLangCode->id => 'أرسل الرسالة أدناه وسنعاود الاتصال بك في أقرب وقت ممكن!'],
            'WEBSITE_CONTACT_WRITE_TO_FORM_NAME_LABEL' => [$enLangCode->id => 'Name', $arLangCode->id => 'الاسم'],
            'WEBSITE_CONTACT_WRITE_TO_FORM_NAME_PLACEHOLDER' => [$enLangCode->id => 'Your full name please', $arLangCode->id => 'الاسم الكامل من فضلك'],
            'WEBSITE_CONTACT_WRITE_TO_FORM_EMAIL_LABEL' => [$enLangCode->id => 'Email', $arLangCode->id => 'البريد الإلكتروني'],
            'WEBSITE_CONTACT_WRITE_TO_FORM_EMAIL_PLACEHOLDER' => [$enLangCode->id => 'Your email address please', $arLangCode->id => 'عنوان بريدك الإلكتروني من فضلك'],
            'WEBSITE_CONTACT_WRITE_TO_FORM_SUBJECT_LABEL' => [$enLangCode->id => 'Subject', $arLangCode->id => 'الموضوع'],
            'WEBSITE_CONTACT_WRITE_TO_FORM_SUBJECT_SELECT_OPTION_LABEL' => [$enLangCode->id => 'Select', $arLangCode->id => 'اختيار'],
            'WEBSITE_CONTACT_WRITE_TO_FORM_SUBJECT_GENERAL_FEEDBACK_OPTION_LABEL' => [$enLangCode->id => 'General Feedback', $arLangCode->id => 'ملاحظات عامة'],
            'WEBSITE_CONTACT_WRITE_TO_FORM_SUBJECT_TECH_ISSUES_OPTION_LABEL' => [$enLangCode->id => 'Website technical issue', $arLangCode->id => 'مشكلة فنية في الموقع'],
            'WEBSITE_CONTACT_WRITE_TO_FORM_SUBJECT_JOIN_US_OPTION_LABEL' => [$enLangCode->id => 'Join us request', $arLangCode->id => 'طلب الانضمام إلينا'],
            'WEBSITE_CONTACT_WRITE_TO_FORM_MESSAGE_LABEL' => [$enLangCode->id => 'Message', $arLangCode->id => 'الرسالة'],
            'WEBSITE_CONTACT_WRITE_TO_FORM_MESSAGE_PLACEHOLDER' => [$enLangCode->id => 'Your message...', $arLangCode->id => 'رسالتك...'],
            'WEBSITE_CONTACT_WRITE_TO_FORM_SUBMIT_BTN_LABEL' => [$enLangCode->id => 'Submit', $arLangCode->id => 'إرسال'],
            'WEBSITE_CONTACT_WRITE_TO_FORM_ERROR_NO_NAME' => [$enLangCode->id => 'The full name is required.', $arLangCode->id => 'الاسم الكامل مطلوب.'],
            'WEBSITE_CONTACT_WRITE_TO_FORM_ERROR_NO_EMAIL' => [$enLangCode->id => 'The email address is required.', $arLangCode->id => 'عنوان البريد الإلكتروني مطلوب.'],
            'WEBSITE_CONTACT_WRITE_TO_FORM_ERROR_INVALID_EMAIL' => [$enLangCode->id => 'The email must be a valid email address.', $arLangCode->id => 'يجب أن يكون البريد الإلكتروني عنوان بريد إلكتروني صالحًا.'],
            'WEBSITE_CONTACT_WRITE_TO_FORM_ERROR_NO_SUBJECT' => [$enLangCode->id => 'The subject is required.', $arLangCode->id => 'الموضوع مطلوب.'],
            'WEBSITE_CONTACT_WRITE_TO_FORM_ERROR_NO_MESSAGE' => [$enLangCode->id => 'The message is required.', $arLangCode->id => 'الرسالة مطلوبة.'],
            'WEBSITE_FORM_ERROR_RECAPTCHA' => [$enLangCode->id => 'Please show us you are a human by ticking the "I\'m not a robot" box!', $arLangCode->id => 'يرجى توضيح أنك إنسان من خلال وضع علامة على مربع "أنا لست روبوتًا"!'],
            'WEBSITE_CONTACT_WRITE_TO_FORM_THANK_YOU_MESSAGE' => [$enLangCode->id => 'Thank you for your message! We have sent you a confirmation email.', $arLangCode->id => 'شكرا لرسالتك! لقد ارسلنا اليك ايميل تاكيد.'],
            'WEBSITE_CONTACT_WRITE_TO_FORM_THANK_YOU_LABEL' => [$enLangCode->id => 'Thank you!', $arLangCode->id => 'شكرا لك!'],
            'WEBSITE_FOOTER_PAGES_LABEL' => [$enLangCode->id => 'Pages', $arLangCode->id => 'الصفحات'],
            'WEBSITE_FOOTER_PAGES_ABOUT_LABEL' => [$enLangCode->id => 'About', $arLangCode->id => 'حول الموقع'],
            'WEBSITE_FOOTER_PAGES_BLOG_LABEL' => [$enLangCode->id => 'Blog', $arLangCode->id => 'المدونات'],
            'WEBSITE_FOOTER_PAGES_CONTACT_LABEL' => [$enLangCode->id => 'Contact', $arLangCode->id => 'اتصل بنا'],
            'WEBSITE_FOOTER_USEFUL_LINKS_LABEL' => [$enLangCode->id => 'Useful Links', $arLangCode->id => 'روابط مفيدة'],
            'WEBSITE_FOOTER_USEFUL_LINKS_OPEN_CAUSES_LABEL' => [$enLangCode->id => 'Open Causes', $arLangCode->id => 'مشاريع الدعم الحالية'],
            'WEBSITE_FOOTER_FOLLOW_US_ON_LABEL' => [$enLangCode->id => 'Follow us on', $arLangCode->id => 'تابعنا على وسائل التواصل الإجتماعي'],
            'WEBSITE_FOOTER_NEWSLETTER_LABEL' => [$enLangCode->id => 'Newsletter', $arLangCode->id => 'نشرتنا الإخبارية'],
            'WEBSITE_FOOTER_NEWSLETTER_SUBSCRIBE_MSG' => [$enLangCode->id => 'Subscribe to our FREE newsletter and stay tuned!', $arLangCode->id => 'اشترك في النشرة الإخبارية المجانية لدينا وترقبوا!'],
            'WEBSITE_FOOTER_NEWSLETTER_TERMS_MSG' => [$enLangCode->id => 'I have read and understood the <a target="_blank" href="/terms-of-use">Terms &amp; Conditions</a>.', $arLangCode->id => 'لقد قرأت وفهمت <a target="_blank" href="/terms-of-use">الشروط والأحكام</a>.', 'is_html' => true],
            'WEBSITE_FOOTER_NEWSLETTER_JOIN_FIELD_PLACEHOLDER' => [$enLangCode->id => 'Enter your mail', $arLangCode->id => 'أدخل البريد الخاص بك'],
            'WEBSITE_FOOTER_NEWSLETTER_JOIN_BTN_LABEL' => [$enLangCode->id => 'Join Now!', $arLangCode->id => 'انضم الآن!'],
            'WEBSITE_FOOTER_NEWSLETTER_JOIN_IN_PROGRESS_BTN_LABEL' => [$enLangCode->id => 'Joining...', $arLangCode->id => 'جاري الإنضمام...'],
            'WEBSITE_FOOTER_COPYRIGHT_MESSAGE' => [$enLangCode->id => '© Copyright [YEAR] WAN Aid. All Rights Reserved', $arLangCode->id => '© حقوق الطبع والنشر [YEAR] وان ايد. كل الحقوق محفوظة'],
            'WEBSITE_DISCLAIMER_LABEL' => [$enLangCode->id => 'Disclaimer', $arLangCode->id => 'إخلاء المسؤولية'],
            'WEBSITE_TERMS_OF_USE_LABEL' => [$enLangCode->id => 'Terms Of Use', $arLangCode->id => 'تعليمات الاستخدام'],
            'WEBSITE_PRIVACY_POLICY_LABEL' => [$enLangCode->id => 'Privacy Policy', $arLangCode->id => 'سياسة الخصوصية'],
            'WEBSITE_COOKIE_ALERT_MESSAGE' => [$enLangCode->id => 'We use cookies to enhance your experience.', $arLangCode->id => 'نحن نستخدم ملفات تعريف الارتباط لتحسين تجربتك.'],
            'WEBSITE_COOKIE_ALERT_MESSAGE_BTN_LABEL' => [$enLangCode->id => 'I agree', $arLangCode->id => 'أنا موافق'],
            'WEBSITE_COOKIE_ALERT_MESSAGE_PRIVACY_POLICY_LABEL' => [$enLangCode->id => 'Click here to read our full Privacy Policy', $arLangCode->id => 'انقر هنا لقراءة سياسة الخصوصية الكاملة الخاصة بنا'],
            'WEBSITE_ALERT_MESSAGE_BTN_LABEL' => [$enLangCode->id => 'I agree', $arLangCode->id => 'أنا موافق'],
            'WEBSITE_BACK_TO_HOME_PAGE_LABEL' => [$enLangCode->id => 'Back to home page', $arLangCode->id => 'العودة إلى الصفحة الرئيسية'],
            'WEBSITE_ERRORS_INITIALISATION_FAILED_MESSAGE' => [$enLangCode->id => 'Opps!! The website failed to initialise properly! Please check your internet connection and refresh the page', $arLangCode->id => 'عفوا !! فشل الموقع في التهيئة بشكل صحيح! يرجى التحقق من الإنترنت الخاص بك وتحديث الصفحة'],
            'WEBSITE_ERRORS_PAGE_NOT_FOUND_HEADER' => [$enLangCode->id => 'Page not found!', $arLangCode->id => 'الصفحة غير موجودة!'],
            'WEBSITE_ERRORS_PAGE_NOT_FOUND_MESSAGE' => [$enLangCode->id => 'The page you\'re looking for doesn\'t exists or has been removed', $arLangCode->id => 'الصفحة التي تبحث عنها غير موجودة أو تمت إزالتها'],
            'WEBSITE_ERRORS_SERVER_ERROR_HEADER' => [$enLangCode->id => 'Opps! Something went wrong at our end!', $arLangCode->id => 'عفوا! حدث خطأ من جانبنا!'],
            'WEBSITE_ERRORS_SERVER_ERROR_MESSAGE' => [$enLangCode->id => 'We\'ll fix it soon.. apologies', $arLangCode->id => 'سنقوم بإصلاحه قريبا .. نأسف'],
            'WEBSITE_ERROR_LABEL' => [$enLangCode->id => 'Opps! An error occurred', $arLangCode->id => 'عفوا! حدث خطأ'],
            'LANDING_MAIN_HEADER_TOP' => [$enLangCode->id => 'Welcome to WAN Aid', $arLangCode->id => 'مرحبًا بكم في وان ايد'],
            'LANDING_MAIN_HEADER_MIDDLE_BIG' => [$enLangCode->id => 'We need your support', $arLangCode->id => 'نحن بحاجة إلى دعمكم'],
            'LANDING_MAIN_HEADER_BOTTOM' => [$enLangCode->id => 'Any contribution makes a huge difference! Thanks for your generosity', $arLangCode->id => 'أي مساهمة تحدث فرقا كبيرا! شكرا على كرمكم'],
            'ABOUT_MAIN_HEADER_TOP' => [$enLangCode->id => 'Welcome to WAN Aid', $arLangCode->id => 'مرحبًا بكم في وان ايد'],
            'ABOUT_MAIN_HEADER_MIDDLE_BIG' => [$enLangCode->id => 'Who we are', $arLangCode->id => 'من نحن'],
            'ABOUT_MAIN_HEADER_BOTTOM' => [$enLangCode->id => '', $arLangCode->id => ''],
            'WEBSITE_PROCESSING_PAYMENT_MESSAGE' => [$enLangCode->id => 'Your payment is being processed. Please wait..', $arLangCode->id => 'جاري معالجة الدفع. أرجو الإنتظار..'],
            'WEBSITE_PAYMENT_PROCESSED_SUCCESS_MESSAGE' => [$enLangCode->id => 'Your payment has been processed successfully. We have sent you a confirmation email.', $arLangCode->id => 'تم الدفع بنجاح. لقد أرسلنا لك بريد إلكتروني للتأكيد.'],
            'WEBSITE_PAYMENT_PROCESSED_SUCCESS_WITH_INTERNAL_ERROR_MESSAGE' => [$enLangCode->id => 'Your payment has been processed successfully.', $arLangCode->id => 'تم الدفع بنجاح.'],
            'CLICK_TO_GET_YOUR_COPY_LABEL' => [$enLangCode->id => 'Click here to get your copy', $arLangCode->id => 'إضغط هنا للحصول على نسختك'],
            'WHO_ARE_WE_BUTTON_LABEL' => [$enLangCode->id => 'Who are we?', $arLangCode->id => 'من نحن؟'],
            'TOP_NAV_STATIC_BUTTON_GET_STARTED_LABEL' => [$enLangCode->id => 'Donate Now', $arLangCode->id => 'تبرع الآن'],
            'DR_MAGDI_CLINIC_TOP_HEADER' => [$enLangCode->id => 'Marital Therapy Clinic', $arLangCode->id => 'عيادة الإستشارات الزوجية'],
            'DR_MAGDI_CLINIC_FORM_TITLE_TXT' => [$enLangCode->id => 'About the Clinic', $arLangCode->id => 'نبذة عن العيادة'],
            'DR_MAGDI_CLINIC_FORM_SUB_TITLE_TXT' => [$enLangCode->id => 'The clinic is based on the main principle of marriage which states that its normal to have difference within any relationship the way we deal with these differences dictate the outcome either strengthening the relationship or leading to a spirals of misery and failures. Hence our main aim and obective is to help couples to identify their differences accepting them and learn  health techniques and ways to deal with them.', $arLangCode->id => 'إن فكرة العياده تستند على حقيقة علميه وهي أن الخلاف هو جزء طبيعي وأصيل في أي علاقه. إن معظم أسباب المشاكل والأزمات تنبع من الفشل في التعامل مع هذه الإختلافات. إن الإرشاد النفسي هو محاولة لتقديم التفسير العلمي لجذور الإختلاف مع إقتراح أساليب وطرق موضوعيه للتعامل مع هذه الإختلافات بطريقة تحترم إحتياجات الطرفين ومستوى إستيعابهم لجذور الإختلاف وإنعكاساته.'],
            'DR_MAGDI_CLINIC_FORM_WAN_DISCLAIMER_TXT' => [$enLangCode->id => '<strong>Disclaimer:</strong> WAN Aid will convey your contact information to the clinic and will not save or use your information for any other puroses. All communication thereafter must be with the clinic directly', $arLangCode->id => '<strong> إخلاء المسؤولية: </strong> ستنقل وان ايد معلومات الاتصال الخاصة بك إلى العيادة ولن تحفظ معلوماتك أو تستخدمها لأي أغراض أخرى. يجب أن تكون جميع الاتصالات بعد ذلك مع العيادة مباشرة', 'is_html' => true],
            'DR_MAGDI_CLINIC_FORM_Q1_LABEL' => [$enLangCode->id => 'Nickname', $arLangCode->id => 'اسمك المستعار'],
            'DR_MAGDI_CLINIC_FORM_Q1_PLACEHOLDER' => [$enLangCode->id => 'Your Nickname', $arLangCode->id => 'اسمك المستعار'],
            'DR_MAGDI_CLINIC_FORM_Q2_LABEL' => [$enLangCode->id => 'Full Name (Optional)', $arLangCode->id => 'الاسم الكامل (اختياري)'],
            'DR_MAGDI_CLINIC_FORM_Q2_PLACEHOLDER' => [$enLangCode->id => 'Your full name', $arLangCode->id => 'الاسم الكامل'],
            'DR_MAGDI_CLINIC_FORM_Q3_LABEL' => [$enLangCode->id => 'Telephone / Mobile (Please include the full area code)', $arLangCode->id => 'الهاتف / الهاتف المحمول (يرجى تضمين رمز المنطقة بالكامل)'],
            'DR_MAGDI_CLINIC_FORM_Q3_PLACEHOLDER' => [$enLangCode->id => 'Telephone / Mobile (Please include the full area code)', $arLangCode->id => 'الهاتف / الهاتف المحمول (يرجى تضمين رمز المنطقة بالكامل)'],
            'DR_MAGDI_CLINIC_FORM_Q4_LABEL' => [$enLangCode->id => 'Country', $arLangCode->id => 'البلد'],
            'DR_MAGDI_CLINIC_FORM_Q4_PLACEHOLDER' => [$enLangCode->id => 'Country', $arLangCode->id => 'البلد'],
            'DR_MAGDI_CLINIC_FORM_Q5_LABEL' => [$enLangCode->id => 'Email Address', $arLangCode->id => 'البريد الالكترونى'],
            'DR_MAGDI_CLINIC_FORM_Q5_PLACEHOLDER' => [$enLangCode->id => 'Your email address', $arLangCode->id => 'البريد الالكترونى'],
            'DR_MAGDI_CLINIC_FORM_Q6_LABEL' => [$enLangCode->id => 'Notes', $arLangCode->id => 'معلومات أخرى'],
            'DR_MAGDI_CLINIC_FORM_Q6_PLACEHOLDER' => [$enLangCode->id => 'Please add any notes that you believe it can be useful here...', $arLangCode->id => 'الرجاء إضافة أي معلومات أخرى تعتقد أنها قد تكون مفيدة هنا ...'],
            'DR_MAGDI_CLINIC_FORM_ERROR_NO_NICKNAME' => [$enLangCode->id => 'The nickname is required', $arLangCode->id => 'اسمك المستعار مطلوب'],
            'DR_MAGDI_CLINIC_FORM_ERROR_NO_TEL_MOBILE' => [$enLangCode->id => 'Your telephone or mobile number is required', $arLangCode->id => 'رقم هاتفك أو هاتفك المحمول مطلوب'],
            'DR_MAGDI_CLINIC_FORM_ERROR_NO_COUNTRY' => [$enLangCode->id => 'The country field is required', $arLangCode->id => 'حقل البلد مطلوب'],
            'DR_MAGDI_CLINIC_FORM_ERROR_NO_EMAIL' => [$enLangCode->id => 'The email address is required.', $arLangCode->id => 'عنوان البريد الإلكتروني مطلوب.'],
            'DR_MAGDI_CLINIC_FORM_ERROR_INVALID_EMAIL' => [$enLangCode->id => 'The email must be a valid email address.', $arLangCode->id => 'يجب أن يكون البريد الإلكتروني عنوان بريد إلكتروني صالحًا.'],
            'DR_MAGDI_CLINIC_FORM_THANK_YOU_MESSAGE' => [$enLangCode->id => 'Thank you for your message! We have sent you a confirmation email.', $arLangCode->id => 'شكرا لرسالتك! لقد ارسلنا اليك ايميل تاكيد.'],
            'DR_MAGDI_CLINIC_FORM_THANK_YOU_LABEL' => [$enLangCode->id => 'Thank you!', $arLangCode->id => 'شكرا لك!'],
            'DR_MAGDI_CLINIC_FORM_SUBMIT_BTN_LABEL' => [$enLangCode->id => 'Submit', $arLangCode->id => 'إرسال'],
            'GALLERY_TOP_HEADER' => [$enLangCode->id => 'Gallery', $arLangCode->id => 'صور'],
            'GALLERY_TITLE_TXT' => [$enLangCode->id => 'Our Gallery', $arLangCode->id => 'معرضنا'],
            'GALLERY_SUB_TITLE_TXT' => [$enLangCode->id => 'Check what we\'re up to with our photo gallery!', $arLangCode->id => 'تحقق مما نحن بصدده من خلال معرض الصور الخاص بنا!'],
            'WEBSITE_BADGE_NEW' => [$enLangCode->id => 'New', $arLangCode->id => 'جديد'],
            'WEBSITE_PAYMENT_SELECT_OR_TYPE_AMOUNT_TXT' => [$enLangCode->id => 'Or type your amount', $arLangCode->id => 'أو اكتب المبلغ'],
            'WEBSITE_FORM_DROPDOWN_PLEASE_SELECT_TXT' => [$enLangCode->id => '-- Please Select --', $arLangCode->id => '- الرجاء التحديد -'],
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
            'label' => 'OPEN_CAUSES_HEADER_LABEL',
            'url' => 'open-causes',
            'url_type' => 'internal',
            'style' => 'dark',
            'order' => 1,
            'pages_id' => $mainPage->id,
            'active' => 1,
        ]);

        HeaderCta::create([
            'name' => 'header_cta2',
            'label' => 'WHO_ARE_WE_BUTTON_LABEL',
            'url' => Constants::PAGE_CODE_ABOUT,
            'url_type' => 'internal',
            'style' => 'light',
            'order' => 2,
            'pages_id' => $mainPage->id,
            'active' => 1,
        ]);

        Setting::updateOrCreate(
            ['name' => 'google_maps_iframe_url'],
            ['value' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2485.667977505269!2d-3.1698518842316057!3d51.464252179628055!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x486e03482bad6a89%3A0x22f4fc5c56459d9e!2sLouisa%20Pl%2C%20Cardiff%20CF10%205BY!5e0!3m2!1sen!2suk!4v1609634404697!5m2!1sen!2suk']
        );
    }
}
