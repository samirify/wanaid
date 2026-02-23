<?php

namespace Modules\Core\Http\Controllers;

use App\Traits\SAAApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\Core\Services\ContactService;
use Throwable;

class ContactController extends Controller
{
    use SAAApiResponse;

    /**
     * constructor
     */
    public function __construct(
        private readonly ContactService $contactService,
    ) {
    }

    /**
     * Contact us form handler
     * @param Request $request
     * @return Response
     */
    public function sendContactEmail(Request $request)
    {
        $result = [
            'success' => false,
            'msg' => null,
        ];

        $validator = Validator::make($request->all(), [
            'full_name'     => 'required',
            'email'    => 'required|email',
            'subject'  => 'required',
            'message'  => 'required',
            'g-recaptcha-response' => 'recaptcha',
        ], [
            'full_name.required'     => 'WEBSITE_CONTACT_WRITE_TO_FORM_ERROR_NO_NAME',
            'email.required'    => 'WEBSITE_CONTACT_WRITE_TO_FORM_ERROR_NO_EMAIL',
            'email.email'       => 'WEBSITE_CONTACT_WRITE_TO_FORM_ERROR_INVALID_EMAIL',
            'subject.required'  => 'WEBSITE_CONTACT_WRITE_TO_FORM_ERROR_NO_SUBJECT',
            'message.required'  => 'WEBSITE_CONTACT_WRITE_TO_FORM_ERROR_NO_MESSAGE',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), Response::HTTP_BAD_REQUEST);
        } else {
            try {
                $sendEmail = $this->contactService->sendContactEmail($request->only([
                    'full_name', 'email', 'subject', 'message', 'lang'
                ]));

                $result['success'] = true;
                $result['msg'] = 'WEBSITE_CONTACT_WRITE_TO_FORM_THANK_YOU_MESSAGE';
                if ($sendEmail['success']) {
                    return $this->successResponse($result);
                } else {
                    throw new Exception($sendEmail['error']['msg'], $sendEmail['error']['code']);
                }
            } catch (Throwable $th) {
                return $this->handleExceptionResponse($th);
            }
        }
    }

    /**
     * Get form countries list
     * @param Request $request
     * @return Response
     */
    public function getCountries($lang = 'en', Request $request)
    {
        try {
            $result = $this->contactService->getCountriesList($lang);

            if ($result['success']) {
                return $this->successResponse($result['countries']);
            } else {
                throw new Exception($result['error']['msg'], $result['error']['code']);
            }
        } catch (Throwable $th) {
            return $this->handleExceptionResponse($th);
        }
    }

    /**
     * Contact autocomplete.
     * @return Response
     */
    public function contactsAC(Request $request)
    {
        $contactsAC = $this->contactService->getContactsAutocomplete([]);
        return json_encode([
            'results' => $contactsAC['contacts']
        ]);
    }

    /**
     * Non-users contact autocomplete.
     * @return Response
     */
    public function nonUsersContactsAC(Request $request)
    {
        $contactsAC = $this->contactService->getNonUsersContactsAutocomplete([]);
        return json_encode([
            'results' => $contactsAC['contacts']
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function editMainContacts(Request $request)
    {
        $mainOrg = getMainOrganisation();

        $mainContacts = $this->contactService->getMainContactsByContactId($mainOrg->contact_id);

        if ($request->isMethod('post')) {
            DB::beginTransaction();
            try {
                $this->contactService->updateMainContacts($mainOrg->contact_id, $request);

                DB::commit();

                return $this->successResponse(['msg' => 'Updated successfully!']);
            } catch (Throwable $th) {
                DB::rollBack();
                return $this->handleExceptionResponse($th);
            }
        } else {
            return $this->successResponse([
                'main_contacts' => $mainContacts
            ]);
        }
    }
}
