<?php

namespace Modules\Core\Http\Controllers;

use App\Traits\AppHelperTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Modules\Core\Models\Project;
use Modules\Core\Services\ProjectService;

class ProjectsController extends Controller
{
    use AppHelperTrait;

    /**
     * constructor
     */
    public function __construct(
        private readonly ProjectService $projectService,
    ) {
    }

    /**
     * Fetch projects.
     *
     * @return \Illuminate\Http\Response
     */
    public function projects()
    {
        $projects = $this->projectService->getProjects([
            'active' => 1
        ]);

        return response()->json([
            'data' => [
                'projects' => $projects['projects']
            ]
        ]);
    }

    /**
     * Fetch project data.
     *
     * @return \Illuminate\Http\Response
     */
    public function projectData($unique_title)
    {
        $projectData = $this->projectService->getProjectData($unique_title);

        return response()->json([
            'data' => [
                'project' => $projectData['project']
            ]
        ]);
    }

    /**
     * Update milestone.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateMilestone($id, Request $request)
    {
        // validate the data
        $validator = Validator::make($request->all(), [
            'status_code' => 'required',
            'user_id'     => 'required',
            'password'    => 'required',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), Response::HTTP_BAD_REQUEST);
        }

        $updateData = $this->projectService->updateMilestone($id, $request->only(
            'status_code',
            'message',
            'user_id',
            'password'
        ));

        if ($updateData['success']) {
            return $this->successResponse($updateData['milestone']);
        } else {
            return $this->errorResponse($updateData['error']['message'], $updateData['error']['code']);
        }
    }

    /**
     * Update milestone task.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateTask($id, Request $request)
    {
        // validate the data
        $validator = Validator::make($request->all(), [
            'status_code' => 'required',
            'user_id'     => 'required',
            'password'    => 'required',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse($validator->errors(), Response::HTTP_BAD_REQUEST);
        }

        $updateData = $this->projectService->updateTask($id, $request->only(
            'status_code',
            'message',
            'user_id',
            'password'
        ));

        if ($updateData['success']) {
            return $this->successResponse($updateData);
        } else {
            return $this->errorResponse($updateData['error']['message'], $updateData['error']['code']);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $projects = $this->projectService->getProjects();

        if (!$projects['success']) {
            Session::flash('error', $projects['error']);
        }

        // var_dump($projects['projects']);die;

        return view('core::project.index', [
            'projects' => $projects['success'] ? $projects['projects'] : [],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('core::project.create-new', [
            'statuses' => DB::table('application_code AS ac')
                ->select('ac.id AS id', 'ac.name AS name')
                ->leftJoin('application_code_type AS act', 'act.id', '=', 'ac.application_code_type_id')
                ->where([
                    'act.code' => 'PROJECT_STATUS',
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
            $project = new Project();

            $project->title = $request->title;
            $project->code = $this->formatCode($project->title);
            $project->unique_title = $this->formatUniqueTitle($project->title);
            $project->description = $request->description;
            $project->status_id = $request->status_id;
            $project->due_date = $request->due_date;

            $project->active = isset($request->active) ? true : false;

            $project->save();

            DB::commit();
            Session::flash('success', 'Project <strong>' . $project->title . '</strong> created successfully!');

            return redirect()->route('admin.projects.list', $project->id);
        } catch (Exception $th) {
            DB::rollBack();
            $error = $th->getMessage();
            // switch ($th->getCode()) {
            //     case 23000:
            //         $error = 'Duplicate project!';
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
        return view('core::project.show', [
            'project' => Project::find($id),
            'statuses' => DB::table('application_code AS ac')
                ->select('ac.id AS id', 'ac.name AS name')
                ->leftJoin('application_code_type AS act', 'act.id', '=', 'ac.application_code_type_id')
                ->where([
                    'act.code' => 'PROJECT_STATUS',
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
        $project = Project::find($id);

        $validator = Validator::make($request->all(), [
            'title'       => 'required',
            'due_date'       => 'required',
        ]);

        if ($validator->fails()) {
            return back()->withInput()->withErrors($validator);
        }

        try {
            $project->title = $request->title;
            $project->code = $this->formatCode($project->title);
            $project->unique_title = $this->formatUniqueTitle($project->title);
            $project->description = $request->description;
            $project->status_id = $request->status_id;
            $project->due_date = $request->due_date;

            $project->active = isset($request->active) ? true : false;

            $project->save();

            // set flash data with success message
            Session::flash('success', 'Updated successfully!');

            // redirect with flash data to google.analytics.events.categories.list
            return redirect()->route('admin.projects.list');
        } catch (\Exception $th) {
            $error = $th->getMessage();
            // switch ($th->getCode()) {
            //     case 23000:
            //         $error = 'Duplicate project!';
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
            $project = Project::find($id);
            $projectName = $project->name;
            $project->delete();

            DB::commit();
            Session::flash('success', '<strong>' . $projectName . '</strong> project was successfully deleted');
            return redirect()->route('admin.projects.list');
        } catch (Exception $ex) {
            DB::rollBack();
            $error = $ex->getMessage();
            return back()->withInput()->withErrors($error);
        }
    }
}
