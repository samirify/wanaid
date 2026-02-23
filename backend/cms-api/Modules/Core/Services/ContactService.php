<?php

namespace Modules\Core\Services;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Modules\Core\Models\Address;
use Modules\Core\Models\Email;
use Modules\Core\Models\Phone;

class ContactService
{
    /**
     * Fetches contacts autocomplete 
     * @param string|array $where
     * 
     * @return array $result
     */
    public function getContactsAutocomplete($where = [])
    {
        $result = [
            'success' => false,
            'message' => '',
            'contacts' => []
        ];

        try {
            $query = DB::table('contacts AS c')
                ->leftJoin('persons AS p', 'p.contact_id', '=', 'c.id')
                ->select(
                    'c.id AS id',
                    'p.first_name AS first_name',
                    'p.middle_names AS middle_names',
                    'p.last_name AS last_name'
                );

            if (count($where) > 0) {
                $query->where($where);
            }

            $contacts = $query->get()->toArray();

            foreach ($contacts as $contact) {
                array_push($result['contacts'], [
                    'id' => $contact->id,
                    'text' => $contact->first_name . ' ' . ($contact->middle_names ? $contact->middle_names . ' ' : '') . $contact->last_name,
                ]);
            }

            $result['success'] = true;
            $result['message'] = 'Results fetched successfully!';
        } catch (Exception $ex) {
            $result['error'] = [
                'code' => $ex->getCode(),
                'message' => $ex->getMessage()
            ];
        }

        return $result;
    }

    /**
     * Fetches non-users contacts autocomplete 
     * @param string|array $where
     * 
     * @return array $result
     */
    public function getNonUsersContactsAutocomplete($where = [])
    {
        $result = [
            'success' => false,
            'message' => '',
            'contacts' => []
        ];

        try {
            $query = DB::table('contacts AS c')
                ->leftJoin('persons AS p', 'p.contact_id', '=', 'c.id')
                ->select(
                    'c.id AS id',
                    'p.first_name AS first_name',
                    'p.middle_names AS middle_names',
                    'p.last_name AS last_name'
                )
                ->whereNotExists(function ($q) {
                    $q->select(DB::raw(1))
                        ->from('users AS u')
                        ->whereRaw('u.contact_id = c.id');
                });

            if (count($where) > 0) {
                $query->where($where);
            }

            $contacts = $query->get()->toArray();

            foreach ($contacts as $contact) {
                array_push($result['contacts'], [
                    'id' => $contact->id,
                    'text' => $contact->first_name . ' ' . ($contact->middle_names ? $contact->middle_names . ' ' : '') . $contact->last_name,
                ]);
            }

            $result['success'] = true;
            $result['message'] = 'Results fetched successfully!';
        } catch (Exception $ex) {
            $result['error'] = [
                'code' => $ex->getCode(),
                'message' => $ex->getMessage()
            ];
        }

        return $result;
    }

    public function sendContactEmail($data = [])
    {
        $result = [
            'success' => false,
            'msg' => '',
        ];

        try {
            $language = DB::table('language AS l')
                ->leftJoin('locales AS lo', 'lo.id', '=', 'l.locales_id')
                ->select('l.id AS id', 'l.direction AS direction')
                ->where([
                    'lo.locale' => $data['lang']
                ])
                ->first();

            $data['lang_id'] = $language->id;
            $data['lang_direction'] = $language->direction;

            $emails = [];
            $emailsConfig = config('client.site.emails');

            $query = DB::table('settings AS s')
                ->select('s.name', 's.value')
                ->whereIn('s.name', array_keys($emailsConfig));

            $emailsSettingsList = $query->get()->toArray();

            foreach ($emailsSettingsList as $emailTarget) {
                if ($emailTarget->value) {
                    $emails[$emailTarget->name] = explode(';', trim($emailTarget->value));
                }
            }

            $folderName = null;

            switch ($data['subject']) {
                case 'general_feedback':
                    if (array_key_exists('contact_feedback_emails', $emails) && is_array($emails['contact_feedback_emails']) && count($emails['contact_feedback_emails']) > 0) {
                        $data['target_emails'] = $emails['contact_feedback_emails'];
                        $folderName = 'general-feedback';
                        $data['admin_email_subject'] = getLanguageTranslation('SITE_EMAIL_ADMIN_GENERAL_FEEDBACK_SUBJECT', $data['lang_id']);
                        $data['admin_email_message'] = str_replace(
                            ['[[full_name]]', '[[user_message]]'],
                            [$data['full_name'], $data['message']],
                            getLanguageTranslation('SITE_EMAIL_ADMIN_GENERAL_FEEDBACK_CONTENT', $data['lang_id'])
                        );
                        $data['user_email_subject'] = getLanguageTranslation('SITE_EMAIL_USER_GENERAL_FEEDBACK_SUBJECT', $data['lang_id']);
                        $data['user_email_message'] = str_replace(
                            ['[[full_name]]', '[[user_message]]'],
                            [$data['full_name'], $data['message']],
                            getLanguageTranslation('SITE_EMAIL_USER_GENERAL_FEEDBACK_CONTENT', $data['lang_id'])
                        );
                        $data['signature'] = getLanguageTranslation('SITE_EMAIL_FEEDBACK_USER_SIGNATURE', $data['lang_id']);
                    }
                    break;
                case 'join_us_request':
                    if (array_key_exists('contact_join_us_emails', $emails) && is_array($emails['contact_join_us_emails']) && count($emails['contact_join_us_emails']) > 0) {
                        $data['target_emails'] = $emails['contact_join_us_emails'];
                        $folderName = 'join-us-request';
                        $data['admin_email_subject'] = getLanguageTranslation('SITE_EMAIL_ADMIN_JOIN_US_SUBJECT', $data['lang_id']);
                        $data['admin_email_message'] = str_replace(
                            ['[[full_name]]', '[[user_message]]'],
                            [$data['full_name'], $data['message']],
                            getLanguageTranslation('SITE_EMAIL_ADMIN_JOIN_US_CONTENT', $data['lang_id'])
                        );
                        $data['user_email_subject'] = getLanguageTranslation('SITE_EMAIL_USER_JOIN_US_SUBJECT', $data['lang_id']);
                        $data['user_email_message'] = str_replace(
                            ['[[full_name]]', '[[user_message]]'],
                            [$data['full_name'], $data['message']],
                            getLanguageTranslation('SITE_EMAIL_USER_JOIN_US_CONTENT', $data['lang_id'])
                        );
                        $data['signature'] = getLanguageTranslation('SITE_EMAIL_JOIN_US_USER_SIGNATURE', $data['lang_id']);
                    }
                    break;
                case 'technical_issues':
                    if (array_key_exists('contact_website_issues_emails', $emails) && is_array($emails['contact_website_issues_emails']) && count($emails['contact_website_issues_emails']) > 0) {
                        $data['target_emails'] = $emails['contact_website_issues_emails'];
                        $folderName = 'technical-issues';
                        $data['admin_email_subject'] = getLanguageTranslation('SITE_EMAIL_ADMIN_TECHNICAL_ISSUES_SUBJECT', $data['lang_id']);
                        $data['admin_email_message'] = str_replace(
                            ['[[full_name]]', '[[user_message]]'],
                            [$data['full_name'], $data['message']],
                            getLanguageTranslation('SITE_EMAIL_ADMIN_TECHNICAL_ISSUES_CONTENT', $data['lang_id'])
                        );
                        $data['user_email_subject'] = getLanguageTranslation('SITE_EMAIL_USER_TECHNICAL_ISSUES_SUBJECT', $data['lang_id']);
                        $data['user_email_message'] = str_replace(
                            ['[[full_name]]', '[[user_message]]'],
                            [$data['full_name'], $data['message']],
                            getLanguageTranslation('SITE_EMAIL_USER_TECHNICAL_ISSUES_CONTENT', $data['lang_id'])
                        );
                        $data['signature'] = getLanguageTranslation('SITE_EMAIL_TECH_ISSUE_USER_SIGNATURE', $data['lang_id']);
                    }
                    break;
                default:
                    throw new Exception('Invalid email type!');
                    break;
            }

            // throw new Exception(json_encode($data), 500);

            if (array_key_exists('target_emails', $data) && is_array($data['target_emails']) && count($data['target_emails']) > 0) {
                // Send email to company
                $data['user_message'] = $data['admin_email_message'];
                Mail::send('core::emails/contact/' . $folderName . '/contact-email', $data, function ($message) use ($data) {
                    $message
                        ->from($data['email'], $data['full_name'])
                        ->to($data['target_emails'], env('APP_NAME'))
                        ->subject($data['admin_email_subject']);
                });
                // Send confirmation email to user
                $data['user_message'] = $data['user_email_message'];
                Mail::send('core::emails/contact/' . $folderName . '/contact-no-reply-email', $data, function ($message) use ($data) {
                    $message
                        ->from('no-reply@samirify.com', 'Thank you from Samirify LTD')
                        ->to($data['email'], $data['full_name'])
                        ->subject($data['user_email_subject']);
                });
            } else {
                throw new Exception('Opps! something went wront at our end, Sorry! Please try again or contact us with ref: Error 101', 500);
            }

            $result['success'] = true;
            $result['msg'] = 'Email sent successfully!';
        } catch (Exception $ex) {
            // file_put_contents('err.txt', $ex->getMessage());
            $result['error'] = [
                'code' => $ex->getCode(),
                'msg' => $ex->getMessage()
            ];
        }

        return $result;
    }

    public function getCountriesList($lang)
    {
        $result = [
            'success' => false,
            'countries' => [],
            'msg' => '',
        ];

        try {
            $language = DB::table('language AS l')
                ->leftJoin('locales AS lo', 'lo.id', '=', 'l.locales_id')
                ->select('l.id AS id', 'l.direction AS direction')
                ->where([
                    'lo.locale' => $lang
                ])
                ->first();
            $countries = DB::table('countries AS c')
                ->leftJoin('language_code AS lc', 'lc.code', '=', 'c.formatted_name')
                ->leftJoin('language_translation AS lt', 'lc.id', '=', 'lt.language_code_id')
                ->leftJoin('language AS l', 'l.id', '=', 'lt.language_id')
                ->leftJoin('locales AS lo', 'lo.id', '=', 'l.locales_id')
                ->select('c.id AS id', 'lt.text AS name')
                ->where([
                    'lo.locale' => $lang
                ])
                ->orderBy('lt.text', 'asc')
                ->get()
                ->toArray();

            $result['countries'] = $countries;
            $result['countries'] = Arr::prepend($result['countries'], [
                'id' => '',
                'name' => getLanguageTranslation('WEBSITE_FORM_DROPDOWN_PLEASE_SELECT_TXT', $language->id)
            ]);
            $result['success'] = true;
            $result['msg'] = 'Success!';
        } catch (Exception $ex) {
            // file_put_contents('err.txt', $ex->getMessage());
            $result['error'] = [
                'code' => $ex->getCode(),
                'msg' => $ex->getMessage()
            ];
        }

        return $result;
    }

    public function getMainContactsByContactId(int $contactId)
    {
        return [
            'emails' => DB::table('email AS e')
                ->leftJoin('application_code AS ac', 'ac.id', '=', 'e.type_id')
                ->leftJoin('application_code_type AS act', 'act.id', '=', 'ac.application_code_type_id')
                ->select("e.id AS id", 'e.email_address AS value')
                ->where([
                    'e.contact_id' => $contactId,
                    'e.is_primary' => 1,
                    'act.code' => Constants::ACT_EMAIL_TYPES,
                ])
                ->get(),
            'addresses' => DB::table('address AS a')
                ->leftJoin('application_code AS ac', 'ac.id', '=', 'a.type_id')
                ->leftJoin('application_code_type AS act', 'act.id', '=', 'ac.application_code_type_id')
                ->select("a.id AS id", 'a.full_address AS value')
                ->where([
                    'a.contact_id' => $contactId,
                    'a.is_primary' => 1,
                    'act.code' => Constants::ACT_ADDRESS_TYPES,
                ])
                ->get(),
            'phones' => DB::table('phone AS p')
                ->leftJoin('application_code AS ac', 'ac.id', '=', 'p.type_id')
                ->leftJoin('application_code_type AS act', 'act.id', '=', 'ac.application_code_type_id')
                ->select("p.id AS id", 'p.phone_number AS value')
                ->where([
                    'p.contact_id' => $contactId,
                    'p.is_primary' => 1,
                    'act.code' => Constants::ACT_PHONE_TYPES,
                ])
                ->get()
        ];
    }

    public function updateMainContacts(int $contactId, Request $request): void
    {
        $requestParams = $request->all();

        foreach ($requestParams as $k => $value) {
            $keyParts = explode('-', $k);

            if (count($keyParts) > 0) {
                $type = strtolower($keyParts[0]);
                unset($keyParts[0]);
                $id = implode('-', $keyParts);

                switch ($type) {
                    case 'emails':
                        $this->updateMainEmail($id, $contactId, $value, $request);
                        break;
                    case 'addresses':
                        $this->updateMainAddress($id, $contactId, $value, $request);
                        break;
                    case 'phones':
                        $this->updateMainPhone($id, $contactId, $value, $request);
                        break;
                }
            }
        }
    }

    private function updateMainEmail($id, $contactId, $emailAddress, Request $request)
    {
        $isNew = str_contains($id, 'new');

        if (empty($emailAddress) && !$isNew) {
            Email::where([
                'contact_id' => $contactId,
                'id' => $id,
                'is_primary' => 1,
            ])->delete();
        } else {
            $obj = Email::where([
                'contact_id' => $contactId,
                'id' => $id,
                'is_primary' => 1
            ])->first();

            if ($obj) {
                $obj->email_address = $emailAddress;
                $obj->save();
            } else {
                $value = $request->get('emails-' . $id);

                if (!empty($value)) {
                    $emailType = DB::table('application_code_type')->where('code', Constants::ACT_EMAIL_TYPES)->first();

                    Email::create([
                        'contact_id' => $contactId,
                        'id' => $id,
                        'is_primary' => 1,
                        'type_id' => DB::table('application_code')
                            ->where('application_code_type_id', $emailType->id)
                            ->where('code', Constants::AC_EMAIL_TYPE_WORK)
                            ->first()->id,
                        'email_address' => $value,
                    ]);
                }
            }
        }
    }

    private function updateMainAddress($id, $contactId, $address, Request $request)
    {
        $isNew = str_contains($id, 'new');

        if (empty($address) && !$isNew) {
            Address::where([
                'contact_id' => $contactId,
                'id' => $id,
                'is_primary' => 1,
            ])->delete();
        } else {
            $obj = Address::where([
                'contact_id' => $contactId,
                'id' => $id,
                'is_primary' => 1
            ])->first();

            if ($obj) {
                $obj->full_address = $address;
                $obj->save();
            } else {
                $value = $request->get('addresses-' . $id);

                if (!empty($value)) {
                    $addressType = DB::table('application_code_type')->where('code', Constants::ACT_ADDRESS_TYPES)->first();

                    Address::create([
                        'contact_id' => $contactId,
                        'id' => $id,
                        'is_primary' => 1,
                        'type_id' => DB::table('application_code')
                            ->where('application_code_type_id', $addressType->id)
                            ->where('code', Constants::AC_ADDRESS_TYPE_WORK)
                            ->first()->id,
                        'full_address' => $value,
                    ]);
                }
            }
        }
    }

    private function updateMainPhone($id, $contactId, $phoneNumber, Request $request)
    {
        $isNew = str_contains($id, 'new');

        if (empty($phoneNumber) && !$isNew) {
            Phone::where([
                'contact_id' => $contactId,
                'id' => $id,
                'is_primary' => 1,
            ])->delete();
        } else {
            $obj = Phone::where([
                'contact_id' => $contactId,
                'id' => $id,
                'is_primary' => 1
            ])->first();

            if ($obj) {
                $obj->phone_number = $phoneNumber;
                $obj->save();
            } else {
                $value = $request->get('phones-' . $id);

                if (!empty($value)) {
                    $emailType = DB::table('application_code_type')->where('code', Constants::ACT_PHONE_TYPES)->first();

                    Phone::create([
                        'contact_id' => $contactId,
                        'id' => $id,
                        'is_primary' => 1,
                        'type_id' => DB::table('application_code')
                            ->where('application_code_type_id', $emailType->id)
                            ->where('code', Constants::AC_PHONE_TYPE_WORK)
                            ->first()->id,
                        'phone_number' => $value,
                    ]);
                }
            }
        }
    }
}
