<?php

namespace Modules\Client\Database\Seeders;

use App\Traits\AppHelperTrait;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Core\Models\Contact;
use Modules\Core\Models\LanguageCode;
use Modules\Core\Models\LanguageTranslation;
use Modules\Core\Models\MediaStore;
use Modules\Core\Models\Person;
use Modules\Core\Services\Constants;
use Modules\Department\Entities\Department;
use Modules\Team\Models\TeamMember;

class DepartmentsAndTeamsSeeder extends Seeder
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

        DB::table('departments')->delete();

        $englishLocale = DB::table('locales')->where('locale', 'en')->first();
        $arabicLocale = DB::table('locales')->where('locale', 'ar')->first();

        // English Language
        $enLangCode = DB::table('language')->where('locales_id', $englishLocale->id)->first();
        // Arabic Language
        $arLangCode = DB::table('language')->where('locales_id', $arabicLocale->id)->first();

        $departments = [
            [
                'unique_title' => $this->formatUniqueTitle('Founding Team'),
                'name' => 'DEPARTMENT_{DEPARTMENT_ID}_NAME',
                'sub_header' => 'DEPARTMENT_{DEPARTMENT_ID}_SUB_HEADER',
                'order' => 1,
                'translations' => [
                    'DEPARTMENT_{DEPARTMENT_ID}_NAME' => [
                        $enLangCode->id => 'Founders & Co-Founders',
                        $arLangCode->id => 'المؤسسون والشركاء المؤسسون',
                    ],
                    'DEPARTMENT_{DEPARTMENT_ID}_SUB_HEADER' => [
                        $enLangCode->id => '',
                        $arLangCode->id => '',
                    ],
                ]
            ],
            [
                'unique_title' => $this->formatUniqueTitle('Tech Team'),
                'name' => 'DEPARTMENT_{DEPARTMENT_ID}_NAME',
                'sub_header' => 'DEPARTMENT_{DEPARTMENT_ID}_SUB_HEADER',
                'order' => 3,
                'translations' => [
                    'DEPARTMENT_{DEPARTMENT_ID}_NAME' => [
                        $enLangCode->id => 'Tech Team',
                        $arLangCode->id => 'فريق التكنولوجيا',
                    ],
                    'DEPARTMENT_{DEPARTMENT_ID}_SUB_HEADER' => [
                        $enLangCode->id => '',
                        $arLangCode->id => '',
                    ],
                ]
            ]
        ];

        foreach ($departments as $departmentData) {
            $translations = $departmentData['translations'];
            unset($departmentData['translations']);

            $department = Department::create($departmentData);
            $department->name = str_replace('{DEPARTMENT_ID}', $department->id, $departmentData['name']);
            $department->sub_header = str_replace('{DEPARTMENT_ID}', $department->id, $departmentData['sub_header']);
            $department->save();

            foreach ($translations as $code => $translation) {
                $code = str_replace('{DEPARTMENT_ID}', $department->id, $code);
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

        $departments = DB::table('departments')->pluck('id', 'unique_title')->toArray();

        $titleType = DB::table('application_code_type')->where('code', 'TITLES')->first();

        $team = [
            [
                'departments_id' => $departments[$this->formatUniqueTitle('Founding Team')],
                'title' => DB::table('application_code')
                    ->where('application_code_type_id', $titleType->id)
                    ->where('code', 'MR')
                    ->first()->id,
                'first_name' => 'TEAM_MEMBER_{TEAM_MEMBER_ID}_FIRST_NAME',
                'middle_names' => 'TEAM_MEMBER_{TEAM_MEMBER_ID}_MIDDLE_NAMES',
                'last_name' => 'TEAM_MEMBER_{TEAM_MEMBER_ID}_LAST_NAME',
                'unique_title' => $this->formatUniqueTitle('Administrator Account'),
                'position' => 'TEAM_MEMBER_{TEAM_MEMBER_ID}_POSITION',
                'short_description' => 'TEAM_MEMBER_{TEAM_MEMBER_ID}_SHORT_DESCRIPTION',
                'description' => 'TEAM_MEMBER_{TEAM_MEMBER_ID}_DESCRIPTION',
                'order' => 1,
                'show_on_web' => 1,
                'translations' => [
                    'TEAM_MEMBER_{TEAM_MEMBER_ID}_FIRST_NAME' => [
                        $enLangCode->id => 'Administrator',
                        $arLangCode->id => 'المسؤول',
                    ],
                    'TEAM_MEMBER_{TEAM_MEMBER_ID}_MIDDLE_NAMES' => [
                        $enLangCode->id => '',
                        $arLangCode->id => '',
                    ],
                    'TEAM_MEMBER_{TEAM_MEMBER_ID}_LAST_NAME' => [
                        $enLangCode->id => '.',
                        $arLangCode->id => '.',
                    ],
                    'TEAM_MEMBER_{TEAM_MEMBER_ID}_POSITION' => [
                        $enLangCode->id => 'Web Developer',
                        $arLangCode->id => 'مصمم الموقع',
                    ],
                    'TEAM_MEMBER_{TEAM_MEMBER_ID}_SHORT_DESCRIPTION' => [
                        $enLangCode->id => '',
                        $arLangCode->id => '',
                    ],
                    'TEAM_MEMBER_{TEAM_MEMBER_ID}_DESCRIPTION' => [
                        $enLangCode->id => '',
                        $arLangCode->id => '',
                    ],
                ],
            ],
        ];

        $contactType = DB::table('application_code_type')->where('code', Constants::ACT_CONTACT_TYPES)->first();

        foreach ($team as $teamMemberData) {
            $translations = $teamMemberData['translations'];
            $imagePath = $teamMemberData['imagePath'] ?? null;
            $titleId = $teamMemberData['title'];
            $firstName = $teamMemberData['first_name'];
            $middleNames = $teamMemberData['middle_names'];
            $lastName = $teamMemberData['last_name'];
            unset(
                $teamMemberData['translations'],
                $teamMemberData['imagePath'],
                $teamMemberData['title'],
                $teamMemberData['first_name'],
                $teamMemberData['middle_names'],
                $teamMemberData['last_name'],
            );

            $contact = Contact::create([
                'contact_type_id' => DB::table('application_code')
                    ->where('application_code_type_id', $contactType->id)
                    ->where('code', Constants::AC_CONTACT_TYPE_PERSON)
                    ->first()->id,
                'reference_no' => isset($user['reference_no']) && !empty($user['reference_no']) ? $user['reference_no'] : $this->formatCode(json_encode($teamMemberData))
            ]);

            $contactId = $contact->id;

            $teamMember = TeamMember::create(array_merge([
                'contact_id' => $contactId
            ], $teamMemberData));

            Person::create([
                'contact_id' => $contactId,
                'title_id' => $titleId,
                'first_name' => str_replace('{TEAM_MEMBER_ID}', $teamMember->id, $firstName),
                'middle_names' => str_replace('{TEAM_MEMBER_ID}', $teamMember->id, $middleNames),
                'last_name' => str_replace('{TEAM_MEMBER_ID}', $teamMember->id, $lastName),
            ]);

            $teamMember->position = str_replace('{TEAM_MEMBER_ID}', $teamMember->id, $teamMemberData['position']);
            $teamMember->short_description = str_replace('{TEAM_MEMBER_ID}', $teamMember->id, $teamMemberData['short_description']);
            $teamMember->description = str_replace('{TEAM_MEMBER_ID}', $teamMember->id, $teamMemberData['description']);
            $teamMember->save();

            if (isset($imagePath) && !empty($imagePath)) {
                $content = file_get_contents($imagePath);
                MediaStore::create([
                    'entity_name' => 'TeamMemberImage',
                    'entity_id' => (string)$teamMember->id,
                    'mime_type' => 'image/jpeg',
                    'file_name' => $teamMember['unique_title'] . '.jpg',
                    'file_size' => strlen($content),
                    'file_extension' => 'jpg',
                    'content' => $content
                ]);
            }

            foreach ($translations as $code => $translation) {
                $code = str_replace('{TEAM_MEMBER_ID}', $teamMember->id, $code);
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
}
