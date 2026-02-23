<?php

namespace Modules\Team\Http\Controllers;

use App\Traits\AppHelperTrait;
use App\Traits\SAAApiResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Modules\Core\Models\MediaStore;
use Modules\Core\Services\TranslationService;
use Modules\Department\Services\DepartmentService;
use Modules\Team\Services\TeamService;
use SoulDoit\DataTable\SSP;

class TeamController extends Controller
{
    use SAAApiResponse, AppHelperTrait;

    public function __construct(
        private readonly TeamService $teamService,
        private readonly TranslationService $translationService,
        private readonly DepartmentService $departmentService,
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
            ['label' => 'Image ID',     'db' => 'media_store_id'],
            ['label' => 'Full Name',     'db' => 'full_name'],
            ['label' => 'Position',      'db' => 'position'],
            ['label' => 'Department',      'db' => 'department_name'],
            ['label' => 'On Website',      'db' => 'show_on_web'],
            ['label' => 'Order on Website',      'db' => 'order'],
        ]);

        $ssp->setQuery(function ($selected_columns) use ($request) {
            $query = DB::table('v_team')
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
            'departments' => $this->departmentService->getDepartments()['departments'] ?? [],
            'titles' => getTitles(),
            'available_social_media' => getAvailableSocialMedia(),
            'highest_order' => DB::table('team')->max('order')
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
            'first_name'        => 'required|max:255',
            'last_name'         => 'required|max:255',
            'position'          => 'required|max:255',
            'short_description' => 'required',
            // 'team_member_image' => 'max:' . config('client.images.max_upload_size') . '|mimes:' . config('client.images.allowed_mime_types'),
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 400);
        }

        return $this->teamService->createTeamMember($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $teamMember = $this->teamService->getTeamMember([
                't.id' => $id
            ]);

            $teamMemberImg = MediaStore::where([
                'entity_name' => 'TeamMemberImage',
                'entity_id' => $teamMember['id']
            ])->first();

            $teamMember['show_on_web'] = $teamMember['show_on_web'] === 1;
            $teamMember['media_store_id'] = $teamMemberImg ? $teamMemberImg->id : null;

            return $this->successResponse([
                'teamMember' => $teamMember,
                'departments' => $this->departmentService->getDepartments()['departments'] ?? [],
                'titles' => getTitles(),
                'available_social_media' => getAvailableSocialMedia(),
                'translations' => getCodesTranslations([
                    'TEAM_MEMBER_' . $teamMember['id'] . '_FIRST_NAME',
                    'TEAM_MEMBER_' . $teamMember['id'] . '_MIDDLE_NAMES',
                    'TEAM_MEMBER_' . $teamMember['id'] . '_LAST_NAME',
                    'TEAM_MEMBER_' . $teamMember['id'] . '_POSITION',
                    'TEAM_MEMBER_' . $teamMember['id'] . '_SHORT_DESCRIPTION',
                    'TEAM_MEMBER_' . $teamMember['id'] . '_DESCRIPTION',
                ])
            ]);
        } catch (\Throwable $th) {
            return $this->handleExceptionResponse($th);
        }
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
        $validator = Validator::make($request->all(), [
            'first_name'        => 'required|max:255',
            'last_name'         => 'required|max:255',
            'position'          => 'required|max:255',
            'short_description' => 'required',
            // 'team_member_image' => 'max:' . config('client.images.max_upload_size') . '|mimes:' . config('client.images.allowed_mime_types'),
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), 400);
        }

        return $this->teamService->updateTeamMember($id, $request);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        return $this->teamService->deleteTeamMember($id);
    }

    /**
     * Display one team member by unique_title.
     * @param string $unique_title
     * @return Response
     */
    public function teamMemberWebView($unique_title, Request $request)
    {
        try {
            $requestedLang = getLanguageByLocale($request->get('locale', null));

            $teamMember = $this->teamService->getTeamMember([
                't.unique_title' => $unique_title,
                't.show_on_web' => 1
            ], $requestedLang ? $requestedLang->id : null);

            $teamMember['show_on_web'] = $teamMember['show_on_web'] === 1;

            return $this->successResponse([
                'teamMember' => $teamMember,
            ]);
        } catch (\Throwable $th) {
            return $this->handleExceptionResponse($th);
        }
    }

    public function teamMembersWebView(Request $request)
    {
        try {
            $requestedLang = getLanguageByLocale($request->get('locale', null));

            $teamMembers = $this->teamService->getTeamMembersForWeb($requestedLang ? $requestedLang->id : null);

            return $this->successResponse([
                'teams' => $teamMembers,
            ]);
        } catch (\Throwable $th) {
            return $this->handleExceptionResponse($th);
        }
    }
}
