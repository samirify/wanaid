<?php

namespace Modules\Core\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Modules\Core\Models\ProjectMilestone;
use Modules\Core\Models\ProjectMilestoneTask;
use Modules\Core\Models\User;

class ProjectService
{

    public function __construct()
    {
    }

    /**
     * Fetches projects 
     * @param string|array $where
     * 
     * @return array $result
     */
    public function getProjects($where = [])
    {
        $result = [
            'success' => false,
            'message' => '',
            'projects' => []
        ];

        try {
            $query = DB::table('projects AS p')
                ->leftJoin('application_code AS ac', 'ac.id', '=', 'p.status_id')
                ->leftJoin('application_code_type AS act', 'act.id', '=', 'ac.application_code_type_id')
                ->leftJoin('users AS u', 'u.id', '=', 'p.created_by')
                ->leftJoin('contacts AS con', 'con.id', '=', 'u.contact_id')
                ->leftJoin('persons AS per', 'per.contact_id', '=', 'con.id')
                ->select(
                    'p.id AS id',
                    'p.unique_title AS unique_title',
                    'p.title AS title',
                    'p.description AS description',
                    'p.due_date AS due_date',
                    'p.status_id AS status_id',
                    'ac.name AS status',
                    'p.created_by AS created_by',
                    'p.active AS active',
                    'p.created_at AS created_at',
                    'p.updated_at AS updated_at',
                    DB::raw('CONCAT(per.first_name," ", per.last_name) AS creator')
                );

            if (count($where) > 0) {
                $query->where($where);
            }

            $result['projects'] = $query->get();
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

    public function getProjectData($unique_title)
    {
        $result = [
            'success' => false,
            'message' => '',
            'project' => []
        ];

        try {
            $projectQuery = DB::table('projects AS p')
                ->leftJoin('application_code AS ac', 'ac.id', '=', 'p.status_id')
                ->leftJoin('application_code_type AS act', 'act.id', '=', 'ac.application_code_type_id')
                ->leftJoin('users AS u', 'u.id', '=', 'p.created_by')
                ->leftJoin('contacts AS con', 'con.id', '=', 'u.contact_id')
                ->leftJoin('persons AS per', 'per.contact_id', '=', 'con.id')
                ->select(
                    'p.id AS id',
                    'p.unique_title AS unique_title',
                    'p.title AS title',
                    'p.description AS description',
                    'p.due_date AS due_date',
                    'p.status_id AS status_id',
                    'ac.code AS status_code',
                    'ac.name AS status',
                    'p.created_by AS created_by',
                    'p.active AS active',
                    'p.created_at AS created_at',
                    'p.updated_at AS updated_at',
                    DB::raw('CONCAT(per.first_name," ", per.last_name) AS creator')
                )
                ->where('p.unique_title', $unique_title);

            $projectDataRecords = $projectQuery->get()->transform(function ($i) {
                return (array)$i;
            })->toArray();

            $result['project'] = isset($projectDataRecords[0]) ? $projectDataRecords[0] : [];

            if (isset($result['project']['id']) && $result['project']['id']) {
                $projectMilestonesQuery = DB::table('project_milestones AS pms')
                    ->select(
                        'pms.id AS id',
                        'p.id AS project_id',
                        'pms.unique_title AS unique_title',
                        'pms.title AS title',
                        'pms.description AS description',
                        'pms.due_date AS due_date',
                        'pms.status_id AS status_id',
                        'ac.code AS status_code',
                        'ac.name AS status',
                        'pms.created_by AS created_by',
                        'pms.active AS active',
                        'pms.created_at AS created_at',
                        'pms.updated_at AS updated_at',
                        DB::raw('CONCAT(per.first_name," ", per.last_name) AS creator')
                    )
                    ->leftJoin('projects AS p', 'p.id', '=', 'pms.projects_id')
                    ->leftJoin('application_code AS ac', 'ac.id', '=', 'pms.status_id')
                    ->leftJoin('application_code_type AS act', 'act.id', '=', 'ac.application_code_type_id')
                    ->leftJoin('users AS u', 'u.id', '=', 'pms.created_by')
                    ->leftJoin('contacts AS con', 'con.id', '=', 'u.contact_id')
                    ->leftJoin('persons AS per', 'per.contact_id', '=', 'con.id')
                    ->where([
                        'pms.active' => 1,
                        'p.id' => $result['project']['id']
                    ])
                    ->get();

                $projectMilestonesRecords = $projectMilestonesQuery->transform(function ($i) {
                    return (array)$i;
                })->toArray();

                $projectMilestones = [];
                foreach ($projectMilestonesRecords as $ind => $milestone) {
                    $projectMilestoneTaskQuery = DB::table('project_milestone_tasks AS p')
                        ->leftJoin('project_milestones AS proj_ms', 'proj_ms.id', '=', 'p.project_milestones_id')
                        ->leftJoin('application_code AS ac', 'ac.id', '=', 'p.status_id')
                        ->leftJoin('application_code_type AS act', 'act.id', '=', 'ac.application_code_type_id')
                        ->leftJoin('contacts AS con', 'con.id', '=', 'p.contact_id')
                        ->leftJoin('persons AS per', 'per.contact_id', '=', 'con.id')
                        ->select(
                            'p.id AS id',
                            'proj_ms.id AS milestone_id',
                            'p.unique_title AS unique_title',
                            'p.title AS title',
                            'p.description AS description',
                            'p.due_date AS due_date',
                            'p.status_id AS status_id',
                            'ac.code AS status_code',
                            'ac.name AS status',
                            'p.created_by AS created_by',
                            'p.active AS active',
                            'p.created_at AS created_at',
                            'p.updated_at AS updated_at',
                            DB::raw('CONCAT(per.first_name," ", per.last_name) AS assignee')
                        )
                        ->where([
                            'p.active' => 1,
                            'proj_ms.id' => $milestone['id']
                        ])
                        ->get();

                    $projectMilestonesTaskRecords = $projectMilestoneTaskQuery->transform(function ($i) {
                        return (array)$i;
                    })->toArray();

                    if (count($projectMilestonesTaskRecords) > 0) {
                        $projectMilestones[$ind] = $milestone;
                        $projectMilestones[$ind]['tasks'] = $projectMilestonesTaskRecords;
                    }
                }

                if (count($projectMilestones) > 0) {
                    $result['project']['milestones'] = $projectMilestones;
                    $result['project']['users'] = DB::table('users AS u')
                        ->select(
                            'u.id AS id',
                            DB::raw('CONCAT(per.first_name," ", per.last_name) AS name')
                        )
                        ->leftJoin('contacts AS con', 'con.id', '=', 'u.contact_id')
                        ->leftJoin('persons AS per', 'per.contact_id', '=', 'con.id')
                        ->get()
                        ->toArray();
                } else {
                    $result['project'] = [];
                }
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

    public function updateMilestone($id, $data = [])
    {
        $result = [
            'success' => false,
            'message' => '',
            'milestone' => []
        ];

        DB::beginTransaction();
        try {
            $milestone = ProjectMilestone::find($id);
            $milestoneCurrentStatusId = $milestone->status_id;
            $status = null;

            if ($milestone) {
                $user = User::find($data['user_id']);
                if ($user) {
                    if (Hash::check($data['password'], $user->password)) {
                        $status = DB::table('application_code AS ac')
                            ->select('ac.id', 'ac.name')
                            ->leftJoin('application_code_type AS act', 'act.id', '=', 'ac.application_code_type_id')
                            ->where([
                                'act.code' => 'PROJECT_MILESTONE_STATUS',
                                'ac.code' => $data['status_code']
                            ])
                            ->first();

                        $milestone->status_id = $status->id;
                        $milestone->created_by = $user->id;
                        $milestone->save();

                        if ($data['status_code'] === 'CO') {
                            $taskStatus = DB::table('application_code AS ac')
                                ->select('ac.id', 'ac.name')
                                ->leftJoin('application_code_type AS act', 'act.id', '=', 'ac.application_code_type_id')
                                ->where([
                                    'act.code' => 'PROJECT_MILESTONE_TASK_STATUS',
                                    'ac.code' => 'CO'
                                ])
                                ->first();
                            ProjectMilestoneTask::where([
                                'project_milestones_id' => $milestone->id
                            ])->update([
                                'status_id' => $taskStatus->id
                            ]);
                        }

                        updateStatusChange([
                            'entity_name' => 'Project Milestone',
                            'entity_id' => (string)$milestone->id,
                            'message' => $data['message'] ?? null,
                            'status_from_id' => $milestoneCurrentStatusId,
                            'status_to_id' => $milestone->status_id,
                            'updated_by' => $user->id
                        ]);
                    } else {
                        throw new Exception('Wrong password!', 400);
                    }
                } else {
                    throw new Exception('User was not found!', 404);
                }
            } else {
                throw new Exception('Milestone was not found!', 404);
            }

            $result['milestone'] = $milestone;

            $projectMilestoneTaskQuery = DB::table('project_milestone_tasks AS p')
                ->leftJoin('project_milestones AS proj_ms', 'proj_ms.id', '=', 'p.project_milestones_id')
                ->leftJoin('application_code AS ac', 'ac.id', '=', 'p.status_id')
                ->leftJoin('application_code_type AS act', 'act.id', '=', 'ac.application_code_type_id')
                ->leftJoin('contacts AS con', 'con.id', '=', 'p.contact_id')
                ->leftJoin('persons AS per', 'per.contact_id', '=', 'con.id')
                ->select(
                    'p.id AS id',
                    'proj_ms.id AS milestone_id',
                    'p.unique_title AS unique_title',
                    'p.title AS title',
                    'p.description AS description',
                    'p.due_date AS due_date',
                    'p.status_id AS status_id',
                    'ac.code AS status_code',
                    'ac.name AS status',
                    'p.created_by AS created_by',
                    'p.active AS active',
                    'p.created_at AS created_at',
                    'p.updated_at AS updated_at',
                    DB::raw('CONCAT(per.first_name," ", per.last_name) AS assignee')
                )
                ->where([
                    'p.active' => 1,
                    'proj_ms.id' => $milestone->id
                ])
                ->get();

            $projectMilestonesTaskRecords = $projectMilestoneTaskQuery->transform(function ($i) {
                return (array)$i;
            })->toArray();

            if (count($projectMilestonesTaskRecords) > 0) {
                $result['milestone']['tasks'] = $projectMilestonesTaskRecords;
            }

            $result['milestone']['status'] = $status ? $status->name : '';
            $result['milestone']['status_code'] = $data['status_code'];

            $result['success'] = true;
            $result['message'] = 'Updated successfully!';
            DB::commit();
        } catch (Exception $ex) {
            DB::rollBack();
            $result['error'] = [
                'code' => $ex->getCode(),
                'message' => $ex->getMessage()
            ];
        }

        return $result;
    }

    public function updateTask($id, $data = [])
    {
        $result = [
            'success' => false,
            'message' => '',
            'task' => []
        ];

        DB::beginTransaction();
        try {
            $task = ProjectMilestoneTask::find($id);
            $taskCurrentStatusId = $task->status_id;
            $status = null;

            if ($task) {
                $user = User::find($data['user_id']);
                if ($user) {
                    if (Hash::check($data['password'], $user->password)) {
                        $status = DB::table('application_code AS ac')
                            ->select('ac.id', 'ac.name')
                            ->leftJoin('application_code_type AS act', 'act.id', '=', 'ac.application_code_type_id')
                            ->where([
                                'act.code' => 'PROJECT_MILESTONE_TASK_STATUS',
                                'ac.code' => $data['status_code']
                            ])
                            ->first();
                        $task->status_id = $status->id;
                        $task->contact_id = $user->contact_id;
                        $task->save();

                        updateStatusChange([
                            'entity_name' => 'Project Milestone Task',
                            'entity_id' => (string)$task->id,
                            'message' => $data['message'] ?? null,
                            'status_from_id' => $taskCurrentStatusId,
                            'status_to_id' => $task->status_id,
                            'updated_by' => $user->id
                        ]);
                    } else {
                        throw new Exception('Wrong password!', 400);
                    }
                } else {
                    throw new Exception('User was not found!', 404);
                }
            } else {
                throw new Exception('Task was not found!', 404);
            }

            $result['task'] = $task;
            $result['task']['status'] = $status ? $status->name : '';
            $result['task']['status_code'] = $data['status_code'];

            $result['success'] = true;
            $result['message'] = 'Updated successfully!';
            DB::commit();
        } catch (Exception $ex) {
            DB::rollBack();
            $result['error'] = [
                'code' => $ex->getCode(),
                'message' => $ex->getMessage()
            ];
        }

        return $result;
    }
}
