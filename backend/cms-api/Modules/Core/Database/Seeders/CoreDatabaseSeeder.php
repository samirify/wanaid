<?php

namespace Modules\Core\Database\Seeders;

use App\Traits\AppHelperTrait;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Core\Models\ApplicationCode;
use Modules\Core\Models\ApplicationCodeType;
use Modules\Core\Models\Contact;
use Modules\Core\Models\Email;
use Modules\Core\Models\LanguageCode;
use Modules\Core\Models\LanguageTranslation;
use Modules\Core\Models\MediaStore;
use Modules\Core\Models\Organisation;
use Modules\Core\Models\Person;
use Modules\Core\Models\User;
use Modules\Core\Services\Constants;
use Modules\Department\Database\Seeders\DepartmentDatabaseSeeder;
use Modules\Team\Database\Seeders\TeamDatabaseSeeder;

class CoreDatabaseSeeder extends Seeder
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

        $this->call(ContactsDatabaseSeeder::class);
        $this->call(CountriesTableSeeder::class);
        $this->call(LocalesTableSeeder::class);
        $this->call(LanguageSeeder::class);
        $this->call(CurrenciesTableSeeder::class);

        ApplicationCodeType::create([
            'code' => 'ACC_STATUS',
            'name' => 'Account Status',
        ]);

        ApplicationCodeType::create([
            'code' => 'ST_LEVEL',
            'name' => 'Support Ticket Level',
        ]);

        ApplicationCodeType::create([
            'code' => 'ST_STATUS',
            'name' => 'Support Ticket Status',
        ]);

        ApplicationCodeType::create([
            'code' => 'TITLES',
            'name' => 'Titles',
        ]);

        ApplicationCodeType::create([
            'code' => 'SYS_MSG_TYPES',
            'name' => 'System message types',
        ]);

        $systemMessageType = DB::table('application_code_type')->where('code', 'SYS_MSG_TYPES')->first();

        $systemMessageTypes = [
            'A' => 'Alert',
            'N' => 'Notification',
        ];

        foreach ($systemMessageTypes as $code => $value) {
            ApplicationCode::create([
                'application_code_type_id' => $systemMessageType->id,
                'code' => $code,
                'name' => $value,
            ]);
        }


        ApplicationCodeType::create([
            'code' => 'SYS_MSG_SEVERITY',
            'name' => 'System message severity',
        ]);

        $systemMessageSeverity = DB::table('application_code_type')->where('code', 'SYS_MSG_SEVERITY')->first();

        $systemMessageSeverities = [
            'L' => 'Low',
            'M' => 'Medium',
            'H' => 'High',
        ];

        foreach ($systemMessageSeverities as $code => $value) {
            ApplicationCode::create([
                'application_code_type_id' => $systemMessageSeverity->id,
                'code' => $code,
                'name' => $value,
            ]);
        }


        ApplicationCodeType::create([
            'code' => 'SYS_MSG_STATUS',
            'name' => 'System message status',
        ]);

        $systemMessageStatus = DB::table('application_code_type')->where('code', 'SYS_MSG_STATUS')->first();

        $systemMessageStatuses = [
            'R' => 'Read',
            'U' => 'Un-read',
            'N' => 'New',
        ];

        foreach ($systemMessageStatuses as $code => $value) {
            ApplicationCode::create([
                'application_code_type_id' => $systemMessageStatus->id,
                'code' => $code,
                'name' => $value,
            ]);
        }


        ApplicationCodeType::create([
            'code' => 'PROJECT_STATUS',
            'name' => 'Project Status',
        ]);

        ApplicationCodeType::create([
            'code' => 'PROJECT_MILESTONE_STATUS',
            'name' => 'Project Milestone Status',
        ]);

        ApplicationCodeType::create([
            'code' => 'PROJECT_MILESTONE_TASK_STATUS',
            'name' => 'Project Milestone Task Status',
        ]);

        $projectStatusType = DB::table('application_code_type')->where('code', 'PROJECT_STATUS')->first();

        $projectStatusTypes = [
            'NS' => 'Not Started',
            'IP' => 'In Progress',
            'CO' => 'Completed'
        ];

        foreach ($projectStatusTypes as $code => $value) {
            ApplicationCode::create([
                'application_code_type_id' => $projectStatusType->id,
                'code' => $code,
                'name' => $value,
            ]);
        }

        $projectMilestonesStatusType = DB::table('application_code_type')->where('code', 'PROJECT_MILESTONE_STATUS')->first();

        $projectMilestonesStatusTypes = [
            'NS' => 'Not Started',
            'IP' => 'In Progress',
            'CO' => 'Completed'
        ];

        foreach ($projectMilestonesStatusTypes as $code => $value) {
            ApplicationCode::create([
                'application_code_type_id' => $projectMilestonesStatusType->id,
                'code' => $code,
                'name' => $value,
            ]);
        }

        $milestoneTaskStatusType = DB::table('application_code_type')->where('code', 'PROJECT_MILESTONE_TASK_STATUS')->first();

        $milestoneTaskStatusTypes = [
            'NS' => 'Not Started',
            'IP' => 'In Progress',
            'CO' => 'Completed'
        ];

        foreach ($milestoneTaskStatusTypes as $code => $value) {
            ApplicationCode::create([
                'application_code_type_id' => $milestoneTaskStatusType->id,
                'code' => $code,
                'name' => $value,
            ]);
        }

        $titles = [
            'MR' => 'Mr',
            'MISS' => 'Miss',
            'MRS' => 'Mrs',
            'DR' => 'Dr'
        ];

        $titleType = DB::table('application_code_type')->where('code', 'TITLES')->first();

        foreach ($titles as $code => $value) {
            ApplicationCode::create([
                'application_code_type_id' => $titleType->id,
                'code' => $code,
                'name' => $value,
            ]);
        }

        $accountStatus = DB::table('application_code_type')->where('code', 'ACC_STATUS')->first();

        ApplicationCode::create([
            'application_code_type_id' => $accountStatus->id,
            'code' => 'AC',
            'name' => 'Active',
        ]);

        ApplicationCode::create([
            'application_code_type_id' => $accountStatus->id,
            'code' => 'IA',
            'name' => 'In-Active',
        ]);

        $supportTicketLevel = DB::table('application_code_type')->where('code', 'ST_LEVEL')->first();

        ApplicationCode::create([
            'application_code_type_id' => $supportTicketLevel->id,
            'code' => 'H',
            'name' => 'High',
        ]);

        $supportTicketStatus = DB::table('application_code_type')->where('code', 'ST_STATUS')->first();

        ApplicationCode::create([
            'application_code_type_id' => $supportTicketStatus->id,
            'code' => 'O',
            'name' => 'Open',
        ]);

        $this->call(RoleAndPermissionSeeder::class);

        $englishLocale = DB::table('locales')->where('locale', 'en')->first();
        $arabicLocale = DB::table('locales')->where('locale', 'ar')->first();

        // English Language
        $enLangCode = DB::table('language')->where('locales_id', $englishLocale->id)->first();
        // Arabic Language
        $arLangCode = DB::table('language')->where('locales_id', $arabicLocale->id)->first();

        $registrationValues = config('client.registration');

        $users = [
            [
                'type' => 'PERSON',
                'reference_no' => $this->formatCode('admin' . microtime()),
                'username' => Constants::APP_INITIAL_ADMIN_USERNAME,
                'password' => md5('samir2020'),
                'title' => DB::table('application_code')
                    ->where('application_code_type_id', $titleType->id)
                    ->where('code', 'MR')
                    ->first()->id,
                'first_name' => 'USER_{USER_ID}_FIRST_NAME',
                'middle_names' => 'USER_{USER_ID}_MIDDLE_NAMES',
                'last_name' => 'USER_{USER_ID}_LAST_NAME',
                'email' => $registrationValues['email'],
                // 'verified' => 1,
                'role' => Constants::USER_ROLE_OWNER,
                'translations' => [
                    'USER_{USER_ID}_FIRST_NAME' => [
                        $enLangCode->id => 'Administrator',
                        $arLangCode->id => 'المسؤول',
                    ],
                    'USER_{USER_ID}_MIDDLE_NAMES' => [
                        $enLangCode->id => '',
                        $arLangCode->id => '',
                    ],
                    'USER_{USER_ID}_LAST_NAME' => [
                        $enLangCode->id => '.',
                        $arLangCode->id => '.',
                    ],
                ],
            ]
        ];


        $contactType = DB::table('application_code_type')->where('code', Constants::ACT_CONTACT_TYPES)->first();
        $emailType = DB::table('application_code_type')->where('code', Constants::ACT_EMAIL_TYPES)->first();

        foreach ($users as $user) {
            $contactId = null;
            $translations = $user['translations'];

            unset($user['translations']);

            $person = null;
            $organisation = null;

            switch ($user['type']) {
                case 'PERSON':
                    $contact = Contact::create([
                        'contact_type_id' => DB::table('application_code')
                            ->where('application_code_type_id', $contactType->id)
                            ->where('code', Constants::AC_CONTACT_TYPE_PERSON)
                            ->first()->id,
                        'reference_no' => isset($user['reference_no']) && !empty($user['reference_no']) ? $user['reference_no'] : md5($user['email'])
                    ]);

                    $contactId = $contact->id;

                    $person = Person::create([
                        'contact_id' => $contactId,
                        'title_id' => $user['title'],
                        'first_name' => $user['first_name'],
                        'middle_names' => $user['middle_names'],
                        'last_name' => $user['last_name'],
                    ]);

                    Email::create([
                        'contact_id' => $contactId,
                        'type_id' => DB::table('application_code')
                            ->where('application_code_type_id', $emailType->id)
                            ->where('code', Constants::AC_EMAIL_TYPE_PERSONAL)
                            ->first()->id,
                        'email_address' => $user['email'],
                        'is_primary' => true,
                    ]);

                    break;
                case 'ORGANISATION':
                    $contact = Contact::create([
                        'contact_type_id' => DB::table('application_code')
                            ->where('application_code_type_id', $contactType->id)
                            ->where('code', Constants::AC_CONTACT_TYPE_ORGANISATION)
                            ->first()->id,
                        'reference_no' => isset($user['reference_no']) && !empty($user['reference_no']) ? $user['reference_no'] : time()
                    ]);

                    $contactId = $contact->id;

                    $organisation = Organisation::create([
                        'contact_id' => $contactId,
                        'name' => $user['name'],
                    ]);

                    Email::create([
                        'contact_id' => $contactId,
                        'type_id' => DB::table('application_code')
                            ->where('application_code_type_id', $emailType->id)
                            ->where('code', Constants::AC_EMAIL_TYPE_WORK)
                            ->first()->id,
                        'email_address' => $user['email'],
                        'is_primary' => true,
                    ]);

                    break;
            }

            if (!is_null($contactId)) {
                $newUser = User::create([
                    'username' => $user['username'],
                    'contact_id' => $contactId,
                    'verified' => $user['verified'] ?? false,
                    'password' => bcrypt($user['password'])
                ]);

                $newUser->assignRole($user['role']);

                if (isset($user['avatar']) && isset($user['avatar']['content']) && isset($user['avatar']['media_type'])) {
                    MediaStore::create([
                        'mime_type' => $user['avatar']['media_type']['mime_type'],
                        'file_name' => $user['avatar']['file_name'],
                        'file_size' => strlen($user['avatar']['content']),
                        'file_extension' => $user['avatar']['file_extension'],
                        'entity_name' => 'UserImage',
                        'entity_id' => (string)$newUser->id,
                        'content' => $user['avatar']['content'],
                    ]);
                }

                switch ($user['type']) {
                    case 'PERSON':
                        $person->update([
                            'first_name' => str_replace('{USER_ID}', $newUser->id, $user['first_name']),
                            'middle_names' => str_replace('{USER_ID}', $newUser->id, $user['middle_names']),
                            'last_name' => str_replace('{USER_ID}', $newUser->id, $user['last_name']),
                        ]);

                        break;
                    case 'ORGANISATION':
                        $organisation->update([
                            'name' => str_replace('{USER_ID}', $newUser->id, $user['name']),
                        ]);
                        break;
                }

                foreach ($translations as $code => $translation) {
                    $code = str_replace('{USER_ID}', $newUser->id, $code);
                    $langCode = LanguageCode::create([
                        'code' => $code,
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

        $this->call(SocialMediaDatabaseSeeder::class);
        $this->call(DepartmentDatabaseSeeder::class);
        $this->call(TeamDatabaseSeeder::class);
        $this->call(PaymentsSeeder::class);
        $this->call(AuthViewsSeeder::class);
    }
}
