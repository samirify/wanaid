<?php

namespace Modules\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Models\ApplicationCodeType;
use Modules\Core\Services\Constants;

class ContactsDatabaseSeeder extends Seeder
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
            'code' => Constants::ACT_CONTACT_TYPES,
            'name' => 'Contact Types',
        ]);

        insertApplicationCodes(Constants::ACT_CONTACT_TYPES, [
            Constants::AC_CONTACT_TYPE_PERSON => 'Person',
            Constants::AC_CONTACT_TYPE_ORGANISATION => 'Organisation'
        ]);

        ApplicationCodeType::create([
            'code' => Constants::ACT_EMAIL_TYPES,
            'name' => 'Email Types',
        ]);

        insertApplicationCodes(Constants::ACT_EMAIL_TYPES, [
            Constants::AC_EMAIL_TYPE_PERSONAL => 'Personal',
            Constants::AC_EMAIL_TYPE_WORK => 'Work',
            Constants::AC_EMAIL_TYPE_OTHER => 'Other',
        ]);

        ApplicationCodeType::create([
            'code' => Constants::ACT_PHONE_TYPES,
            'name' => 'Phone Types',
        ]);

        insertApplicationCodes(Constants::ACT_PHONE_TYPES, [
            Constants::AC_PHONE_TYPE_PERSONAL => 'Personal',
            Constants::AC_PHONE_TYPE_WORK => 'Work',
            Constants::AC_PHONE_TYPE_OTHER => 'Other',
        ]);

        ApplicationCodeType::create([
            'code' => Constants::ACT_ADDRESS_TYPES,
            'name' => 'Address Types',
        ]);

        insertApplicationCodes(Constants::ACT_ADDRESS_TYPES, [
            Constants::AC_ADDRESS_TYPE_PERSONAL => 'Personal',
            Constants::AC_ADDRESS_TYPE_WORK => 'Work',
            Constants::AC_ADDRESS_TYPE_OTHER => 'Other',
        ]);
    }
}
