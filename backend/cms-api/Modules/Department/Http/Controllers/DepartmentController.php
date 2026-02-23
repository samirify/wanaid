<?php

namespace Modules\Department\Http\Controllers;

use App\Traits\AppHelperTrait;
use App\Traits\SAAApiResponse;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\Core\Models\LanguageCode;
use Modules\Core\Services\TranslationService;
use Modules\Department\Entities\Department;
use Modules\Team\Models\TeamMember;
use SoulDoit\DataTable\SSP;

class DepartmentController extends Controller
{
    use AppHelperTrait, SAAApiResponse;

    public function __construct(
        private readonly TranslationService $translationService
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
            ['label' => 'ID',         'db' => 'id'],
            ['label' => 'Name',     'db' => 'name'],
            ['label' => 'Sub Header',      'db' => 'sub_header'],
            ['label' => 'Order on Website',      'db' => 'order'],
            ['label' => 'Updated At',      'db' => 'updated_at'],
        ]);

        $ssp->setQuery(function ($selected_columns) use ($request) {
            $query = DB::table('v_departments')
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
        return $this->successResponse([]);
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
            'name'       => 'required',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 400);
        }

        DB::beginTransaction();

        try {
            $department = new Department();

            $department->name = $request->name;
            $department->sub_header = $request->sub_header;
            $department->order = $request->order ?? 1;
            $department->unique_title = $this->formatUniqueTitle($department->name);

            $department->save();

            $prefix = 'DEPARTMENT_' . $department->id . '_';
            $department->update([
                'name' => str_replace('TMPSET_', $prefix, $department->name),
                'sub_header' => str_replace('TMPSET_', $prefix, $department->sub_header),
            ]);

            $this->translationService->translateFields([
                'name',
                'sub_header'
            ], $request->all(), $prefix);

            DB::commit();
            return $this->successResponse([
                'msg' => 'Created successfully!'
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
        $department = Department::find($id);

        return $this->successResponse([
            'department' => $department,
            'translations' => getCodesTranslations([
                'DEPARTMENT_' . $department->id . '_name',
                'DEPARTMENT_' . $department->id . '_sub_header',
            ])
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
        $department = Department::find($id);

        $validator = Validator::make($request->all(), [
            'name'        => 'required',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 400);
        }

        DB::beginTransaction();

        try {
            $department->name = $request->name;
            $department->sub_header = $request->sub_header;
            $department->order = $request->order ?? 1;
            $department->unique_title = $this->formatUniqueTitle($department->name);

            $department->save();

            $prefix = 'DEPARTMENT_' . $department->id . '_';
            $this->translationService->translateFields([
                'name',
                'sub_header'
            ], $request->all(), $prefix);

            DB::commit();
            return $this->successResponse([
                'msg' => 'Updated successfully!'
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
            // Check related team members
            $relatedTeamMembers = TeamMember::where('departments_id', (int)$id)->get();

            if (count($relatedTeamMembers) > 0) {
                if (count($relatedTeamMembers) === 1) {
                    throw new Exception("We found team member {$relatedTeamMembers[0]->unique_title} assigned to this department! Please assign them to different departments then try again.");
                } else {
                    throw new Exception("There seems to be some team members assigned to this department! Please assign them to different departments then try again.");
                }
            }

            $department = Department::find($id);
            $departmentName = getLanguageTranslation($department->name);

            // Clean translations
            LanguageCode::whereIn('code', [
                $department->name,
                $department->sub_header,
            ])->delete();

            $department->delete();

            DB::commit();
            return $this->successResponse([
                'msg' => 'Department ' . $departmentName . ' was deleted successfully'
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->handleExceptionResponse($th);
        }
    }
}
