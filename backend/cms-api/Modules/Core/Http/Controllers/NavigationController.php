<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\AppHelperTrait;
use App\Traits\SAAApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\Core\Models\Navigation;
use Modules\Core\Services\NavigationService;
use Modules\PageComponents\Models\Page;
use SoulDoit\DataTable\SSP;

class NavigationController extends Controller
{
    use SAAApiResponse, AppHelperTrait;

    public function __construct(
        private readonly NavigationService $navigationService
    ) {}

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(SSP $ssp, Request $request)
    {
        $ssp->enableSearch();
        $ssp->setColumns([
            ['label' => 'ID',           'db' => 'id'],
            ['label' => 'Name',         'db' => 'name'],
            ['label' => 'Code',         'db' => 'code'],
            ['label' => 'Last updated', 'db' => 'updated_at'],
        ]);

        $ssp->setQuery(function ($selected_columns) use ($request) {
            $query = DB::table('navigation')
                ->select($selected_columns);

            $query = $this->filterDataTable($request, $query);

            return $query;
        });

        $result = $ssp->getData();

        return $this->successResponse([
            'records' => $result['items'],
            'totalRecords' => $result['total_item_count'],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return $this->successResponse([
            'available_pages' => Page::all(['id', 'name', 'code'])
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'               => 'required',
            'code'               => 'required',
        ], [
            'name.required' => 'Name is required',
            'code.required' => 'Code is required',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 400);
        }

        DB::beginTransaction();

        try {
            $navigation = new Navigation();

            $navigation->name = $request->name;
            $navigation->code = $this->formatUniqueTitle($request->code);
            $navigation->value = json_encode($this->navigationService->registerTranslations($request->value ?? []));

            $navigation->save();

            DB::commit();

            return $this->successResponse([
                'msg' => 'Created successfully!',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->handleExceptionResponse($th);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $navigation = Navigation::find($id);

        if (!$navigation) {
            throw new Exception('Navigation not found!', 404);
        }

        $navData = $this->navigationService->formatNavItems(json_decode($navigation->value ?? '', true) ?? [], true);

        $navigation->value = $navData['items'];

        return $this->successResponse([
            'navigation' => $navigation,
            'available_pages' => Page::all(['id', 'name', 'code']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Validate the data
        $navigation = Navigation::find($id);

        $originalDefault = determineBool($navigation->default);
        $newDefault = determineBool($request->default);

        if ($originalDefault && !$newDefault) {
            return $this->errorResponse('You cannot deactivate the default navigation. Please set another navigation as default and try again', 400);
        }

        $validator = Validator::make($request->all(), [
            'name'               => 'required',
            'code'               => 'required',
        ], [
            'name.required' => 'Name is required',
            'code.required' => 'Code is required',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 400);
        }

        DB::beginTransaction();

        try {
            $navigation->name = $request->name;
            $navigation->code = $this->formatUniqueTitle($request->code);
            $navigation->value = json_encode($this->navigationService->registerTranslations($request->value ?? []));

            $navigation->save();

            DB::commit();

            return $this->successResponse([
                'msg' => 'Updated successfully!',
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->handleExceptionResponse($th);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $navigation = Navigation::find($id);
            $navigationName = $navigation->name;
            $navigation->delete();

            DB::commit();
            return $this->successResponse([
                'msg' => $navigationName . ' navigation was successfully deleted'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->handleExceptionResponse($th);
        }
    }
}
