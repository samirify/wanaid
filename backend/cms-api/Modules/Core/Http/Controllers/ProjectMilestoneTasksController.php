<?php

namespace Modules\Core\Http\Controllers;

use App\Traits\AppHelperTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Modules\Core\Models\ProjectMilestoneTask;
use Modules\Core\Services\ProjectMilestoneTaskService;

class ProjectMilestoneTasksController extends Controller
{
    use AppHelperTrait;

    /**
     * constructor
     */
    public function __construct(
        private readonly ProjectMilestoneTaskService $projectMilestoneTaskService,
    ) {
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $projectMilestoneId = $request->get('pmsid');

        $projectMilestoneTasks = $this->projectMilestoneTaskService->getProjectMilestoneTasks([
            'proj_ms.id' => $projectMilestoneId
        ]);

        if (!$projectMilestoneTasks['success']) {
            Session::flash('error', $projectMilestoneTasks['error']);
            return back();
        }

        // var_dump($projectMilestoneTasks['project_milestone_tasks']);die;

        return view('core::project-milestone-tasks.index', [
            'project_milestones_id' => $projectMilestoneId,
            'projectMilestoneTasks' => $projectMilestoneTasks['success'] ? $projectMilestoneTasks['project_milestone_tasks'] : [],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        return view('core::project-milestone-tasks.create-new', [
            'project_milestones_id' => $request->get('pmsid'),
            'statuses' => DB::table('application_code AS ac')
                ->select('ac.id AS id', 'ac.name AS name')
                ->leftJoin('application_code_type AS act', 'act.id', '=', 'ac.application_code_type_id')
                ->where([
                    'act.code' => 'PROJECT_MILESTONE_TASK_STATUS',
                ])->get()->pluck('name', 'id')->toArray(),
            'contacts' => DB::table('contacts AS c')
                ->select('c.id AS id', DB::raw('CONCAT(p.first_name," ", p.last_name) AS name'))
                ->leftJoin('persons AS p', 'c.id', '=', 'p.contact_id')
                ->get()->pluck('name', 'id')->toArray()
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
            $projectMilestoneTask = new ProjectMilestoneTask();

            $projectMilestoneTask->project_milestones_id = $request->project_milestones_id;
            $projectMilestoneTask->title = $request->title;
            $projectMilestoneTask->code = $this->formatCode($projectMilestoneTask->title . $projectMilestoneTask->project_milestones_id);
            $projectMilestoneTask->unique_title = $this->formatUniqueTitle($projectMilestoneTask->title);
            $projectMilestoneTask->description = $request->description;
            $projectMilestoneTask->status_id = $request->status_id;
            $projectMilestoneTask->due_date = $request->due_date;
            $projectMilestoneTask->contact_id = $request->contact_id;

            $projectMilestoneTask->active = isset($request->active) ? true : false;

            $projectMilestoneTask->save();

            DB::commit();
            Session::flash('success', 'ProjectMilestoneTask <strong>' . $projectMilestoneTask->title . '</strong> created successfully!');

            return redirect()->route('admin.projects.milestones.tasks.list', [
                'pmsid' => $projectMilestoneTask->project_milestones_id
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
        return view('core::project-milestone-tasks.show', [
            'projectMilestoneTask' => ProjectMilestoneTask::find($id),
            'statuses' => DB::table('application_code AS ac')
                ->select('ac.id AS id', 'ac.name AS name')
                ->leftJoin('application_code_type AS act', 'act.id', '=', 'ac.application_code_type_id')
                ->where([
                    'act.code' => 'PROJECT_MILESTONE_TASK_STATUS',
                ])->get()->pluck('name', 'id')->toArray(),
            'contacts' => DB::table('contacts AS c')
                ->select('c.id AS id', DB::raw('CONCAT(p.first_name," ", p.last_name) AS name'))
                ->leftJoin('persons AS p', 'c.id', '=', 'p.contact_id')
                ->get()->pluck('name', 'id')->toArray()
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
        $projectMilestoneTask = ProjectMilestoneTask::find($id);

        $validator = Validator::make($request->all(), [
            'title'       => 'required',
            'due_date'       => 'required',
        ]);

        if ($validator->fails()) {
            return back()->withInput()->withErrors($validator);
        }

        try {
            $projectMilestoneTask->title = $request->title;
            $projectMilestoneTask->code = $this->formatCode($projectMilestoneTask->title . $projectMilestoneTask->project_milestones_id);
            $projectMilestoneTask->unique_title = $this->formatUniqueTitle($projectMilestoneTask->title);
            $projectMilestoneTask->description = $request->description;
            $projectMilestoneTask->status_id = $request->status_id;
            $projectMilestoneTask->due_date = $request->due_date;
            $projectMilestoneTask->contact_id = $request->contact_id;

            $projectMilestoneTask->active = isset($request->active) ? true : false;

            $projectMilestoneTask->save();

            // set flash data with success message
            Session::flash('success', 'Updated successfully!');

            // redirect with flash data to google.analytics.events.categories.list
            return redirect()->route('admin.projects.milestones.tasks.list', [
                'pmsid' => $projectMilestoneTask->project_milestones_id
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
            $projectMilestoneTask = ProjectMilestoneTask::find($id);
            $projectMilestoneTaskTitle = $projectMilestoneTask->title;
            $projectMilestoneId = $projectMilestoneTask->project_milestones_id;
            $projectMilestoneTask->delete();

            DB::commit();
            Session::flash('success', '<strong>' . $projectMilestoneTaskTitle . '</strong> project milestone task was successfully deleted');
            return redirect()->route('admin.projects.milestones.tasks.list', [
                'pmsid' => $projectMilestoneId
            ]);
        } catch (Exception $ex) {
            DB::rollBack();
            $error = $ex->getMessage();
            return back()->withInput()->withErrors($error);
        }
    }
}
