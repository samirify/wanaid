<?php

namespace Modules\Core\Http\Controllers;

use App\Traits\AppHelperTrait;
use App\Traits\SAAApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Core\Services\AdministrationService;
use Throwable;

class AdministrationController extends Controller
{
    use SAAApiResponse, AppHelperTrait;

    /**
     * constructor
     */
    public function __construct(
        private readonly AdministrationService $administrationService,
    ) {
    }

    /**
     * @return Response|JsonResponse
     */
    public function index(): Response|JsonResponse
    {
        return $this->successResponse([
            'api_key' => config('client.api.key'),
        ]);
    }

    /**
     * @return Response|JsonResponse
     */
    public function refreshApiKey(): Response|JsonResponse
    {
        try {
            $newApiKey = Hash::make(time());

            $this->updateEnvValues([
                'CLIENT_API_KEY' => "'{$newApiKey}'",
            ], false);

            return $this->successResponse([
                'api_key' => $newApiKey,
            ]);
        } catch (\Throwable $th) {
            return $this->handleExceptionResponse($th);
        }
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function editPrivateAPIsProps(Request $request)
    {
        $privateAPIsProps = [
            'allowedHosts' => $this->administrationService->getClientAllowedHosts()
        ];

        if ($request->isMethod('post')) {
            DB::beginTransaction();
            try {
                $this->administrationService->updatePrivateAPIsProps($request);

                DB::commit();

                return $this->successResponse([
                    'msg' => 'Updated successfully!',
                    'req' => $request->all()
                ]);
            } catch (Throwable $th) {
                DB::rollBack();
                return $this->handleExceptionResponse($th);
            }
        } else {
            return $this->successResponse([
                'private_apis_props' => $privateAPIsProps,
            ]);
        }
    }
}
