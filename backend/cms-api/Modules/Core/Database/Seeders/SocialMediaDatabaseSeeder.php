<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Models\ApplicationCodeType;
use Modules\Core\Services\Constants;

class SocialMediaDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        ApplicationCodeType::create([
            'code' => Constants::ACT_SOCIAL_MEDIA_BRANDS,
            'name' => 'Social Media Brands',
        ]);

        insertApplicationCodes(Constants::ACT_SOCIAL_MEDIA_BRANDS, [
            Constants::AC_SOCIAL_MEDIA_FACEBOOK => 'Facebook',
            Constants::AC_SOCIAL_MEDIA_X => 'X (Twitter)',
            Constants::AC_SOCIAL_MEDIA_LINKEDIN => 'LinkedIn',
            Constants::AC_SOCIAL_MEDIA_INSTAGRAM => 'Instagram',
            Constants::AC_SOCIAL_MEDIA_YOUTUBE => 'YouTube',
            Constants::AC_SOCIAL_MEDIA_TIKTOK => 'TikTok',
            Constants::AC_SOCIAL_MEDIA_GITHUB => 'GitHub',
        ]);
    }
}
