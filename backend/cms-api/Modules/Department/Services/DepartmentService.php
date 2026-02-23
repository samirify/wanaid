<?php

namespace Modules\Department\Services;

use Exception;
use Illuminate\Support\Facades\DB;

class DepartmentService
{
    /**
     * Fetches departments
     * @param string|array $where
     * 
     * @return array $result
     */
    public function getDepartments($where = [], $dropdown = false)
    {
        $result = [
            'success' => false,
            'message' => '',
            'departments' => []
        ];

        try {
            $query = DB::table('departments AS d')
                ->select(
                    'd.id AS id',
                    DB::raw("(
                        SELECT lt.text FROM language_translation lt
                            LEFT JOIN language_code lc ON lc.id = lt.language_code_id
                            LEFT JOIN language l ON l.id = lt.language_id
                            WHERE l.default = 1
                            AND lc.code = d.name
                    ) AS name"),
                );

            if (count($where) > 0) {
                $query->where($where);
            }

            if ($dropdown) {
                $result['departments'] = $query->pluck('name', 'id')->toArray();
            } else {
                $result['departments'] = $query->get();
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
}
