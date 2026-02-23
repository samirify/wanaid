<?php

namespace Modules\Core\Http\Controllers;

use App\Traits\AppHelperTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Modules\Core\Models\ProjectMilestone;
use Modules\Core\Services\ProjectMilestoneService;

class ProjectMilestonesController extends Controller
{
    use AppHelperTrait;

    /**
     * constructor
     */
    public function __construct(
        private readonly ProjectMilestoneService $projectMilestoneService,
    ) {
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $projectId = $request->get('pid');

        $projectMilestones = $this->projectMilestoneService->getProjectMilestones([
            'proj.id' => $projectId
        ]);

        if (!$projectMilestones['success']) {
            Session::flash('error', $projectMilestones['error']);
            return back();
        }

        // var_dump($projectMilestones['project_milestones']);die;

        return view('core::project-milestones.index', [
            'projects_id' => $projectId,
            'projectMilestones' => $projectMilestones['success'] ? $projectMilestones['project_milestones'] : [],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        return view('core::project-milestones.create-new', [
            'projects_id' => $request->get('pid'),
            'statuses' => DB::table('application_code AS ac')
                ->select('ac.id AS id', 'ac.name AS name')
                ->leftJoin('application_code_type AS act', 'act.id', '=', 'ac.application_code_type_id')
                ->where([
                    'act.code' => 'PROJECT_MILESTONE_STATUS',
                ])->get()->pluck('name', 'id')->toArray()
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
            'title'       => 'required',
            'due_date'       => 'required',
        ]);

        if ($validator->fails()) {
            return back()->withInput()->withErrors($validator);
        }

        DB::beginTransaction();

        try {
            $projectMilestone = new ProjectMilestone();

            $projectMilestone->projects_id = $request->projects_id;
            $projectMilestone->title = $request->title;
            $projectMilestone->code = $this->formatCode($projectMilestone->title . $projectMilestone->projects_id);
            $projectMilestone->unique_title = $this->formatUniqueTitle($projectMilestone->title);
            $projectMilestone->description = $request->description;
            $projectMilestone->status_id = $request->status_id;
            $projectMilestone->due_date = $request->due_date;

            $projectMilestone->active = isset($request->active) ? true : false;

            $projectMilestone->save();

            DB::commit();
            Session::flash('success', 'ProjectMilestone <strong>' . $projectMilestone->title . '</strong> created successfully!');

            return redirect()->route('admin.projects.milestones.list', [
                'pid' => $projectMilestone->projects_id
            ]);
        } catch (Exception $th) {
            DB::rollBack();
            $error = $th->getMessage();
            // switch ($th->getCode()) {
            //     case 23000:
            //         $error = 'Duplicate project milestone!';
            //         break;
            // }
            return back()->withInput()->withErrors($error);
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
        return view('core::project-milestones.show', [
            'projectMilestone' => ProjectMilestone::find($id),
            'statuses' => DB::table('application_code AS ac')
                ->select('ac.id AS id', 'ac.name AS name')
                ->leftJoin('application_code_type AS act', 'act.id', '=', 'ac.application_code_type_id')
                ->where([
                    'act.code' => 'PROJECT_MILESTONE_STATUS',
                ])->get()->pluck('name', 'id')->toArray()
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
        $projectMilestone = ProjectMilestone::find($id);

        $validator = Validator::make($request->all(), [
            'title'       => 'required',
            'due_date'       => 'required',
        ]);

        if ($validator->fails()) {
            return back()->withInput()->withErrors($validator);
        }

        try {
            $projectMilestone->title = $request->title;
            $projectMilestone->code = $this->formatCode($projectMilestone->title . $projectMilestone->projects_id);
            $projectMilestone->unique_title = $this->formatUniqueTitle($projectMilestone->title);
            $projectMilestone->description = $request->description;
            $projectMilestone->status_id = $request->status_id;
            $projectMilestone->due_date = $request->due_date;

            $projectMilestone->active = isset($request->active) ? true : false;

            $projectMilestone->save();

            // set flash data with success message
            Session::flash('success', 'Updated successfully!');

            // redirect with flash data to google.analytics.events.categories.list
            return redirect()->route('admin.projects.milestones.list', [
                'pid' => $projectMilestone->projects_id
            ]);
        } catch (\Exception $th) {
            $error = $th->getMessage();
            // switch ($th->getCode()) {
            //     case 23000:
            //         $error = 'Duplicate project milestone!';
            //         break;
            // }
            return back()->withInput()->withErrors($error);
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
            $projectMilestone = ProjectMilestone::find($id);
            $projectMilestoneTitle = $projectMilestone->title;
            $projectId = $projectMilestone->projects_id;
            $projectMilestone->delete();

            DB::commit();
            Session::flash('success', '<strong>' . $projectMilestoneTitle . '</strong> project milestone was successfully deleted');
            return redirect()->route('admin.projects.milestones.list', [
                'pid' => $projectId
            ]);
        } catch (Exception $ex) {
            DB::rollBack();
            $error = $ex->getMessage();
            return back()->withInput()->withErrors($error);
        }
    }
}
