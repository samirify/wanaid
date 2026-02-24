<?php

namespace Modules\Client\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Core\Models\LanguageCode;
use Modules\Core\Models\LanguageTranslation;

/**
 * Seeds BLOG_* and CAUSE_* translation codes and their en/ar texts into language_code and language_translation.
 */
class BlogAndCausesTranslationsSeeder extends Seeder
{
    public function run(): void
    {
        $data = $this->getTranslationsData();
        if (empty($data)) {
            return;
        }

        $englishLocale = DB::table('locales')->where('locale', 'en')->first();
        $arabicLocale = DB::table('locales')->where('locale', 'ar')->first();
        if (!$englishLocale || !$arabicLocale) {
            $this->command?->warn('English or Arabic locale not found; skipping blog/causes translations.');
            return;
        }

        $enLang = DB::table('language')->where('locales_id', $englishLocale->id)->first();
        $arLang = DB::table('language')->where('locales_id', $arabicLocale->id)->first();
        if (!$enLang || !$arLang) {
            $this->command?->warn('English or Arabic language not found; skipping blog/causes translations.');
            return;
        }

        foreach ($data as $code => $texts) {
            $langCode = LanguageCode::firstOrCreate(
                ['code' => $code],
                ['is_html' => !empty($texts['is_html'])]
            );

            $enText = $texts['en'] ?? '';
            $arText = $texts['ar'] ?? '';
            if ($enText !== null && $enText !== '') {
                LanguageTranslation::updateOrCreate(
                    [
                        'language_id' => $enLang->id,
                        'language_code_id' => $langCode->id,
                    ],
                    ['text' => $enText]
                );
            }
            if ($arText !== null && $arText !== '') {
                LanguageTranslation::updateOrCreate(
                    [
                        'language_id' => $arLang->id,
                        'language_code_id' => $langCode->id,
                    ],
                    ['text' => $arText]
                );
            }
        }
    }

    /**
     * Prod data: BLOG_* and CAUSE_* translation codes (TITLE, SHORT_DESCRIPTION, BODY).
     *
     * @return array<string, array{en: string, ar: string, is_html?: bool}>
     */
    private function getTranslationsData(): array
    {
        return [
            'CAUSE_1_TITLE' => ['en' => 'Help us', 'ar' => 'ساعدنا', 'is_html' => false],
            'CAUSE_1_SHORT_DESCRIPTION' => ['en' => 'Help us', 'ar' => 'ساعدنا', 'is_html' => false],
            'CAUSE_1_BODY' => ['en' => '<p>We would like to thank you for being here.</p>

                        <p>Your desire to help children is greatly appreciated, and we will try to make this as easy as possible both for yourselves and our beloved children.</p>

                        <p>At WAN Aid, we are committed to lots of causes and we will allocate all your kind donations to them accordingly.</p>

                        <p>Once again, thank you for supporting us.</p>

                        <p class="text-primary">The WAN Aid Team</p>', 'ar' => '<p dir="rtl">نود أن نشكرك على وجودك هنا.</p>

                        <p dir="rtl">إن رغبتكم في مساعدة الأطفال موضع تقدير كبير ، وسنحاول أن نجعل ذلك سهلاً قدر الإمكان لكم ولأطفالنا الأحباء.</p>

                        <p dir="rtl">في وان ايد ، نحن ملتزمون بالعديد من مشاريع الدعم وسنخصص كل تبرعاتكم الكريمة لهم وفقًا لذلك.</p>

                        <p dir="rtl">مرة أخرى ، شكرًا لكم على دعمنا.</p>

                        <p class="text-primary" dir="rtl">فريق وان ايد</p>', 'is_html' => true],
            'CAUSE_2_TITLE' => ['en' => 'Clean drinking water', 'ar' => 'توفير مياه صالحة للشُرب', 'is_html' => false],
            'CAUSE_2_SHORT_DESCRIPTION' => ['en' => 'Be the solution to an urgent need in the developing areas in Sudan. access to safe water. Provide schools with clean', 'ar' => 'سقيا المدارس من اهداف منظمة وان توفير مياه الشرب الي المدارس في المناطق الفقيره في السودان كمبردات الطوب الحراري وخ', 'is_html' => false],
            'CAUSE_2_BODY' => ['en' => '', 'ar' => '', 'is_html' => true],
            'CAUSE_3_TITLE' => ['en' => 'School meals', 'ar' => 'توفير وجبات مدرسية', 'is_html' => false],
            'CAUSE_3_SHORT_DESCRIPTION' => ['en' => 'We believe that the best way to help to help school children living in poverty is to use food to encourage them to attend schools', 'ar' => 'اطعام الاطفال غير المستطيعين احضار وجبة اثناء اليوم الدراسي. و كمحفز لتقليل التغيب عن المدارس و مساعدة الطلاب لتحسين مستواهم الاكاديمي', 'is_html' => false],
            'CAUSE_3_BODY' => ['en' => '', 'ar' => '', 'is_html' => true],
            'CAUSE_4_TITLE' => ['en' => 'School uniform', 'ar' => 'توفير الزي المدرسي', 'is_html' => false],
            'CAUSE_4_SHORT_DESCRIPTION' => ['en' => 'WAN Aid helps disadvantaged families in Sudan with the cost of School Program. Your donation toward this program will ensure students attend school with un', 'ar' => 'مبادرة الزي المدرسي تهدف لمساعدة الطلبة الدارسين من الأسر المحتاجة، بهدف المساهمة في تحمل جزء من تكاليف المعيشه بتوفير الزي المدرسي', 'is_html' => false],
            'CAUSE_4_BODY' => ['en' => '', 'ar' => '', 'is_html' => true],
            'CAUSE_5_TITLE' => ['en' => 'School books', 'ar' => 'توفير كتب مدرسية', 'is_html' => false],
            'CAUSE_5_SHORT_DESCRIPTION' => ['en' => 'WAN Aid helps disadvantaged families in Sudan with the cost of School Program. Your donation toward this program will ensure students attend school with un', 'ar' => 'مبادرة الزي المدرسي تهدف لمساعدة الطلبة الدارسين من الأسر المحتاجة، بهدف المساهمة في تحمل جزء من تكاليف المعيشه بتوفير الزي المدرسي', 'is_html' => false],
            'CAUSE_5_BODY' => ['en' => '', 'ar' => '', 'is_html' => true],
            'CAUSE_6_TITLE' => ['en' => 'Sanitary towels', 'ar' => 'توفير فوط صحية', 'is_html' => false],
            'CAUSE_6_SHORT_DESCRIPTION' => ['en' => 'WAN Aid helps disadvantaged families in Sudan with the cost of School Program. Your donation toward this program will ensure students attend school with un', 'ar' => 'مبادرة الزي المدرسي تهدف لمساعدة الطلبة الدارسين من الأسر المحتاجة، بهدف المساهمة في تحمل جزء من تكاليف المعيشه بتوفير الزي المدرسي', 'is_html' => false],
            'CAUSE_6_BODY' => ['en' => '', 'ar' => '', 'is_html' => true],
            'CAUSE_7_TITLE' => ['en' => 'Zakat Alfitr', 'ar' => 'زكاة الفطر', 'is_html' => false],
            'CAUSE_7_SHORT_DESCRIPTION' => ['en' => 'Since 2012, WAN AID has been gathering and distributing Zakat al-Fitr to its rightful beneficiaries in Sudan promptl', 'ar' => 'منذ عام 2012، يقوم وان إيد بجمع وتوزيع زكاة الفطر على مستحقيها في السودان في الوقت المناسب', 'is_html' => false],
            'CAUSE_7_BODY' => ['en' => '', 'ar' => '', 'is_html' => true],
            'BLOG_2_TITLE' => ['en' => 'WAN Aid Support in Al Fath 2', 'ar' => 'دعم وان ايد في منطقة الفتح 2', 'is_html' => false],
            'BLOG_2_SHORT_DESCRIPTION' => ['en' => 'Report on the distribution of financial aid from WAN Aid. The amount sent by WAN Aid was 1,450,000', 'ar' => 'تقرير حول توزيع المساعدات المالية من WAN Aid. نود أن نعلمكم بأن المبلغ المرسل وان ايد WAN Aid والبالغ 1,450,000', 'is_html' => false],
            'BLOG_2_BODY' => ['en' => '', 'ar' => '', 'is_html' => true],
            'BLOG_3_TITLE' => ['en' => 'Seminar: The Impact of War on Mental Health and the Partnership with the Sudan Doctors Union in the UK', 'ar' => 'ندوة: آثار الحرب على الصحة النفسية', 'is_html' => false],
            'BLOG_3_SHORT_DESCRIPTION' => ['en' => 'We are honored to have this project in partnership with the Sudan Doctors\' Union in the UK.', 'ar' => 'يشرفنا أن يكون هذا المشروع بالشراكة مع نقابة الأطباء السودانيين في بريطانيا، إيمانًا بأهمية الصحة النفسية', 'is_html' => false],
            'BLOG_3_BODY' => ['en' => '', 'ar' => '', 'is_html' => true],
            'BLOG_4_TITLE' => ['en' => 'A Training Course for Mental Health Professionals', 'ar' => 'كورس سيكولوجية الحرب وأثرها على الأزواج', 'is_html' => false],
            'BLOG_4_SHORT_DESCRIPTION' => ['en' => 'In partnership with WAN Aid and the Sudan Doctors\' Union in the UK.', 'ar' => 'بالشراكة بين منظمة وان إيد واتحاد الأطباء السودانيين بالمملكة المتحدة', 'is_html' => false],
            'BLOG_4_BODY' => ['en' => '', 'ar' => '', 'is_html' => true],
            'BLOG_5_TITLE' => ['en' => 'Thank you Mazin for walking for Sudan', 'ar' => 'شكرا مازن علي المشي من اجل دعم السودان', 'is_html' => false],
            'BLOG_5_SHORT_DESCRIPTION' => ['en' => 'The War in Sudan, needs our help', 'ar' => 'الحرب في السودان، يحتاج لمساعدتنا', 'is_html' => false],
            'BLOG_5_BODY' => ['en' => '', 'ar' => '', 'is_html' => true],
            'BLOG_6_TITLE' => ['en' => 'Your Zahat made other\'s life better', 'ar' => 'زكاتك غيرت حياة بعض الناس', 'is_html' => false],
            'BLOG_6_SHORT_DESCRIPTION' => ['en' => '\'\'Take from their wealth a charity by which you purify them and cause them increase\'\'', 'ar' => '\'\'خذ من أموالهم صدقة تطهرهم وتزكيهم بها\'\'', 'is_html' => false],
            'BLOG_6_BODY' => ['en' => '', 'ar' => '', 'is_html' => true],
            'BLOG_7_TITLE' => ['en' => 'Successful Leadership Workshop in Cardiff by WAN Aid and Waging Peace', 'ar' => 'ورشة قيادة ناجحة في كارديف بتنظيم وان إيد وواجينغ بيس', 'is_html' => false],
            'BLOG_7_SHORT_DESCRIPTION' => ['en' => 'A successful leadership workshop by WAN Aid and Waging Peace empowering Sudanese women in Cardiff.', 'ar' => 'ورشة تدريبية ناجحة من تنظيم وان إيد وواجينغ بيس لتعزيز مهارات القيادة للنساء السودانيات في كارديف.', 'is_html' => false],
            'BLOG_7_BODY' => ['en' => '', 'ar' => '', 'is_html' => true],
            'BLOG_8_TITLE' => ['en' => 'WAN Aid Winter Bazaar – A Celebration of Culture and Community', 'ar' => 'بازار وان إيد الشتوي – احتفال بالثقافة  السودانية', 'is_html' => false],
            'BLOG_8_SHORT_DESCRIPTION' => ['en' => 'Coming together for a cause: Cardiff\'s Sudanese community unites to support families in Sudan', 'ar' => 'تجمعنا من أجل هدف نبيل: الجالية السودانية في كارديف تتحد لدعم الأسر في السودان', 'is_html' => false],
            'BLOG_8_BODY' => ['en' => '', 'ar' => '', 'is_html' => true],
            'BLOG_9_TITLE' => ['en' => 'A distinctive humanitarian initiative aimed at promoting the values of social solidarity and supporting underprivileged families', 'ar' => 'مبادرة إنسانية مميزة تهدف إلى تعزيز قيم التكافل الاجتماعي ودعم الأسر المتعففة', 'is_html' => false],
            'BLOG_9_SHORT_DESCRIPTION' => ['en' => 'The distinguished role of WAN Aid and the Blue Sky Volunteer Organization', 'ar' => 'دور مميز لمنظمة وان إيد ومنظمة بلو سكاي الطوعية', 'is_html' => false],
            'BLOG_9_BODY' => ['en' => '', 'ar' => '', 'is_html' => true],
        ];
    }
}
