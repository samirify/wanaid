<?php

namespace Modules\Core\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Modules\Core\Models\User;

class SystemMessageService
{
    public function getUserMessagesCounts(User $user): array
    {
        $newNotificationsCount = 0;
        $allNotificationsCount = 0;

        if ($user) {
            $newNotificationsCount = DB::table('system_messages AS sm')
                ->leftJoin('application_code AS msg_status_ac', 'msg_status_ac.id', '=', 'sm.status_id')
                ->leftJoin('application_code_type AS msg_status_act', 'msg_status_act.id', '=', 'msg_status_ac.application_code_type_id')
                ->select('*')
                ->where([
                    'sm.user_id' => $user->id,
                    'msg_status_ac.code' => 'N'
                ])
                ->count();

            $allNotificationsCount = DB::table('system_messages AS sm')
                ->leftJoin('application_code AS msg_status_ac', 'msg_status_ac.id', '=', 'sm.status_id')
                ->leftJoin('application_code_type AS msg_status_act', 'msg_status_act.id', '=', 'msg_status_ac.application_code_type_id')
                ->select('*')
                ->where([
                    'sm.user_id' => $user->id,
                ])
                ->count();
        }

        return [
            'notifications' =>  [
                'unreadCount' => $newNotificationsCount,
                'totalCount' => $allNotificationsCount,
            ]
        ];
    }

    public function getUserMessages()
    {
        $result = [
            'success' => false,
            'messages' => [],
            'msg' => '',
        ];

        try {
            $user = auth('api')->user();

            if ($user) {
                $messages = DB::table('system_messages AS sm')
                    ->leftJoin('application_code AS msg_type_ac', 'msg_type_ac.id', '=', 'sm.message_type_id')
                    ->leftJoin('application_code_type AS msg_type_act', 'msg_type_act.id', '=', 'msg_type_ac.application_code_type_id')
                    ->leftJoin('application_code AS msg_severity_ac', 'msg_severity_ac.id', '=', 'sm.severity_id')
                    ->leftJoin('application_code_type AS msg_severity_act', 'msg_severity_act.id', '=', 'msg_severity_ac.application_code_type_id')
                    ->leftJoin('application_code AS msg_status_ac', 'msg_status_ac.id', '=', 'sm.status_id')
                    ->leftJoin('application_code_type AS msg_status_act', 'msg_status_act.id', '=', 'msg_status_ac.application_code_type_id')
                    ->select('*')
                    ->where([
                        'sm.created_by' => $user->id
                    ])
                    ->orderBy('sm.created_by', 'asc')
                    ->get()
                    ->toArray();

                $result['messages'] = $messages;
                $result['success'] = true;
                $result['msg'] = 'Success!';
            } else {
                $result['error'] = [
                    'code' => 401,
                    'msg' => 'User not found or not logged in'
                ];
            }
        } catch (Exception $ex) {
            $result['error'] = [
                'code' => $ex->getCode(),
                'msg' => $ex->getMessage()
            ];
        }

        return $result;
    }
}
