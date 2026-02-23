<?php

namespace Modules\Client\Database\Seeders;

use App\Traits\AppHelperTrait;
use Exception;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Client\Models\ClientIdentity;
use Modules\Client\Models\ClientIdentityTheme;
use Modules\Core\Models\Colour;
use Modules\Core\Models\LanguageCode;
use Modules\Core\Models\LanguageTranslation;
use Modules\Core\Models\MediaStore;

class ClientIdentitySeeder extends Seeder
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

        DB::statement("DROP VIEW IF EXISTS v_client_identities");
        DB::statement("
            CREATE OR REPLACE VIEW v_client_identities AS 
                SELECT 
                    `c`.`id` as `id`, 
                    `c`.`name` as `name`, 
                    (
                        SELECT lt.text FROM language_translation lt
                            LEFT JOIN language_code lc ON lc.id = lt.language_code_id
                            LEFT JOIN language l ON l.id = lt.language_id
                            WHERE l.default = 1
                            AND lc.code = c.business_name
                    ) as `business_name`,
                    (
                        SELECT lt.text FROM language_translation lt
                            LEFT JOIN language_code lc ON lc.id = lt.language_code_id
                            LEFT JOIN language l ON l.id = lt.language_id
                            WHERE l.default = 1
                            AND lc.code = c.business_slogan
                    ) as `business_slogan`,
                    (
                        SELECT lt.text FROM language_translation lt
                            LEFT JOIN language_code lc ON lc.id = lt.language_code_id
                            LEFT JOIN language l ON l.id = lt.language_id
                            WHERE l.default = 1
                            AND lc.code = c.business_short_description
                    ) AS `business_short_description`, 
                    `c`.`active` as `active`, 
                    `c`.`default` as `default`, 
                    `c`.`created_at` as `created_at`, 
                    `c`.`updated_at` as `updated_at`
                FROM `client_identity` as `c`
        ");

        $primaryColour = Colour::create([
            'name' => 'Blue',
            'red' => '10',
            'green' => '99',
            'blue' => '234',
            'hex' => '#0A63EA',
        ]);

        $secondaryColour = Colour::create([
            'name' => 'Black',
            'red' => '0',
            'green' => '0',
            'blue' => '0',
            'hex' => '#000000',
        ]);

        $logoColouredLightImagePath = public_path('/img/seed-images/logo_coloured_light.svg');
        if (!file_exists($logoColouredLightImagePath)) {
            throw new Exception('Logo coloured image is missing!');
        }

        $content = file_get_contents($logoColouredLightImagePath);
        $logoColouredLightImage = MediaStore::create([
            'entity_name' => 'ClientLogoColouredLightImage',
            'entity_id' => '.',
            'mime_type' => 'image/svg+xml',
            'file_name' => 'logo_coloured_light.svg',
            'file_size' => strlen($content),
            'file_extension' => 'svg',
            'content' => $content
        ]);

        $logoDarkLightImage = null;
        $logoDarkLightImagePath = public_path('/img/seed-images/logo_coloured_dark.svg');
        if (file_exists($logoColouredLightImagePath)) {
            $content = file_get_contents($logoDarkLightImagePath);
            $logoDarkLightImage = MediaStore::create([
                'entity_name' => 'ClientLogoDarkLightImage',
                'entity_id' => '.',
                'mime_type' => 'image/svg+xml',
                'file_name' => 'logo_coloured_dark.svg',
                'file_size' => strlen($content),
                'file_extension' => 'svg',
                'content' => $content
            ]);
        }

        $clientIdentity = ClientIdentity::create([
            'name' => 'Default',
            'business_name' => '.',
            'business_slogan' => '.',
            'business_short_description' => '.',
            'active' => true,
            'default' => true,
        ]);

        $clientIdentity->update([
            'business_name' => 'CLIENT_IDENTITY_' . $clientIdentity->id . '_BUSINESS_NAME',
            'business_slogan' => 'CLIENT_IDENTITY_' . $clientIdentity->id . '_BUSINESS_SLOGAN',
            'business_short_description' => 'CLIENT_IDENTITY_' . $clientIdentity->id . '_BUSINESS_SHORT_DESCRIPTION',
        ]);

        $defaultClientIdentityTheme = ClientIdentityTheme::create([
            'client_identity_id' => $clientIdentity->id,
            'code' => 'default',
            'name' => 'Default',
            'primary_colour_id' => $primaryColour->id,
            'secondary_colour_id' => $secondaryColour->id,
            'logo_coloured_light_id' => $logoColouredLightImage->id,
            'active' => true,
            'default' => true,
        ]);

        $logoColouredLightImage->update([
            'entity_id' => (string)$defaultClientIdentityTheme->id
        ]);

        if (!empty($logoDarkLightImage)) {
            $defaultClientIdentityTheme->update([
                'logo_coloured_dark_id' => $logoDarkLightImage->id
            ]);
            $logoDarkLightImage->update([
                'entity_id' => (string)$defaultClientIdentityTheme->id
            ]);
        }

        $englishLocale = DB::table('locales')->where('locale', 'en')->first();
        $arabicLocale = DB::table('locales')->where('locale', 'ar')->first();

        // English Language
        $enLangCode = DB::table('language')->where('locales_id', $englishLocale->id)->first();
        // Arabic Language
        $arLangCode = DB::table('language')->where('locales_id', $arabicLocale->id)->first();

        $generalTranslations = [
            // About us page
            'CLIENT_IDENTITY_' . $clientIdentity->id . '_BUSINESS_NAME' => [
                $enLangCode->id => 'Samirify CMS',
                $arLangCode->id => 'Samirify CMS',
            ],
            'CLIENT_IDENTITY_' . $clientIdentity->id . '_BUSINESS_SLOGAN' => [
                $enLangCode->id => "Let's make our life easier!",
                $arLangCode->id => 'دعونا نجعل حياتنا أسهل!',
            ],
            'CLIENT_IDENTITY_' . $clientIdentity->id . '_BUSINESS_SHORT_DESCRIPTION' => [
                $enLangCode->id => "Your business' short description...",
                $arLangCode->id => 'الوصف المختصر لشركتك...',
                'is_html' => true
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
    }
}
