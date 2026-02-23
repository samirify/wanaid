<?php

namespace Modules\Core\Services;

use Exception;
use Illuminate\Support\Facades\DB;

class ProjectMilestoneService
{
    /**
     * Fetches projects 
     * @param string|array $where
     * 
     * @return array $result
     */
    public function getProjectMilestones($where = [])
    {
        $result = [
            'success' => false,
            'message' => '',
            'project_milestones' => []
        ];

        try {
            $query = DB::table('project_milestones AS p')
                ->leftJoin('projects AS proj', 'proj.id', '=', 'p.projects_id')
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

            $result['project_milestones'] = $query->get();
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
}
