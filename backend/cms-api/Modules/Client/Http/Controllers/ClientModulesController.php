<?php

namespace Modules\Client\Http\Controllers;

use App\Traits\AppHelperTrait;
use App\Traits\SAAApiResponse;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\Client\Models\ClientModule;
use Modules\Client\Services\ClientModulesService;
use Modules\Core\Services\SettingsService;
use Modules\PageComponents\Services\PageService;

class ClientModulesController extends Controller
{
    use SAAApiResponse, AppHelperTrait;

    public function __construct(
        private readonly ClientModulesService $clientModulesService,
        private readonly SettingsService $settingsService,
        private readonly PageService $pageService,
    ) {}

    public function index()
    {
        return $this->successResponse(ClientModule::all()->toArray());
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return $this->successResponse([
            'available_categories' => $this->clientModulesService->getAvailableModuleCategories()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'required|max:255',
            'code'         => 'required|max:32|unique:client_modules',
            'category_id'  => 'required',
        ], [
            'name.required' => 'Module Name is required',
            'name.max' => 'Module Name must not be greater than 255 characters!',
            'code.required' => 'Code is required',
            'code.unique' => 'Code already exists!',
            'code.max' => 'Code must not be greater than 32 characters!',
            'category_id.required' => 'Module category is required',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 400);
        }

        try {
            $clientModule = ClientModule::create([
                'name' => $request->name,
                'code' => $this->formatModuleCode($request->code),
                'category_id' => $request->category_id
            ]);

            $this->clientModulesService->createModuleTable($clientModule);

            $this->clientModulesService->handleModuleMainPage($clientModule, $request);
            $this->clientModulesService->handleModuleRecordsTemplatePage($clientModule, $request);

            return $this->successResponse([
                'msg' => 'Module created successfully!',
                'available_modules' => $this->settingsService->getClientModules(),
            ]);
        } catch (\Throwable $th) {
            return $this->handleExceptionResponse($th);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  string  $code
     * @return \Illuminate\Http\Response
     */
    public function show(string $code)
    {
        $clientModules = ClientModule::where([
            'code' => $code,
        ])->first();

        if (!$clientModules) {
            return $this->errorResponse('Module was not found!', 404);
        }

        $clientModules->active = $clientModules->active === 1;

        return $this->successResponse([
            'module' => $clientModules,
            'available_categories' => $this->clientModulesService->getAvailableModuleCategories()
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $code
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, string $code)
    {
        $clientModule = ClientModule::where([
            'code' => $code,
        ])->first();

        if (!$clientModule) {
            return $this->errorResponse('Module was not found!', 404);
        }

        // Validate the data
        $validator = Validator::make($request->all(), [
            'name'       => 'required|max:255',
        ], [
            'name.required' => 'Module Name is required',
            'name.max' => 'Module Name must not be greater than 255 characters!',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 400);
        }

        DB::beginTransaction();
        try {
            $clientModule->name = $request->name;
            $clientModule->active = $request->active;

            $clientModule->save();

            $this->clientModulesService->handleModuleMainPage($clientModule, $request, true);
            $this->clientModulesService->handleModuleRecordsTemplatePage($clientModule, $request, true);

            DB::commit();
            return $this->successResponse([
                'msg' => 'Updated successfully!',
                'available_modules' => $this->settingsService->getClientModules(),
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->handleExceptionResponse($th);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  string $code
     * @return \Illuminate\Http\Response
     */
    public function destroy(string $code)
    {
        try {
            $clientModule = ClientModule::where([
                'code' => $code,
            ])->first();

            if (!$clientModule) {
                return $this->successResponse([
                    'msg' => 'Module has already been deleted',
                    'available_modules' => $this->settingsService->getClientModules(),
                ]);
            }

            $name = $clientModule->name;
            $mainPageId = $clientModule->pages_id;
            $recordsTemplatePageId = $clientModule->records_template_page_id;

            $this->clientModulesService->removeModuleTableAndView($clientModule);

            $clientModule->delete();

            // Clean up
            // Remove the module's main page
            if ($mainPageId) {
                $pageDeleted = $this->pageService->deletePage($mainPageId);

                if (!$pageDeleted['success']) {
                    throw new Exception($pageDeleted['error'], $pageDeleted['code']);
                }
            }
            if ($recordsTemplatePageId) {
                $recordsTemplatePageDeleted = $this->pageService->deletePage($recordsTemplatePageId);

                if (!$recordsTemplatePageDeleted['success']) {
                    throw new Exception($recordsTemplatePageDeleted['error'], $recordsTemplatePageDeleted['code']);
                }
            }

            return $this->successResponse([
                'msg' => 'Module ' . $name . ' was deleted successfully',
                'available_modules' => $this->settingsService->getClientModules(),
            ]);
        } catch (\Throwable $th) {
            return $this->handleExceptionResponse($th);
        }
    }
}
