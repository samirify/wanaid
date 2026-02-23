<?php

namespace Modules\Client\Database\Seeders;

use App\Traits\AppHelperTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
// use Illuminate\Support\Facades\DB;
// use Modules\Core\Models\User;

class ProjectPlannerSeeder extends Seeder
{
    use AppHelperTrait;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        // DB::table('project_milestone_tasks')->delete();
        // DB::table('project_milestones')->delete();
        // DB::table('projects')->delete();

        // $projectInProgressStatus = DB::table('application_code AS ac')
        //     ->select('ac.id')
        //     ->leftJoin('application_code_type AS act', 'act.id', '=', 'ac.application_code_type_id')
        //     ->where([
        //         'act.code' => 'PROJECT_STATUS',
        //         'ac.code' => 'IP',
        //     ])
        //     ->first();

        // $projectMilestoneStatuses = DB::table('application_code AS ac')
        //     ->select('ac.id', 'ac.code')
        //     ->leftJoin('application_code_type AS act', 'act.id', '=', 'ac.application_code_type_id')
        //     ->where([
        //         'act.code' => 'PROJECT_MILESTONE_STATUS',
        //     ])
        //     ->pluck('ac.id', 'ac.code')
        //     ->toArray();

        // $projectMilestoneTaskStatuses = DB::table('application_code AS ac')
        //     ->select('ac.id', 'ac.code')
        //     ->leftJoin('application_code_type AS act', 'act.id', '=', 'ac.application_code_type_id')
        //     ->where([
        //         'act.code' => 'PROJECT_MILESTONE_TASK_STATUS',
        //     ])
        //     ->pluck('ac.id', 'ac.code')
        //     ->toArray();

        // $users = User::get()->pluck('id')->toArray();

        // Create projects
        // $projects = [
        //     [
        //         'code' => $this->formatCode('Upgrade Samirify LTD - Phase 1'),
        //         'title' => 'Upgrade Samirify LTD - Phase 1',
        //         'unique_title' => $this->formatUniqueTitle('Upgrade Samirify LTD - Phase 1'),
        //         'description' => 'This is to move Samirify LTD to the next level',
        //         'due_date' => date('Y-m-d H:i:s', strtotime('2020-12-31 19:00:00')),
        //         'status_id' => $projectInProgressStatus->id,
        //         'active' => 1,
        //         'created_by' => $users[array_rand($users)],
        //         'milestones' => [
        //             [
        //                 'code' => $this->formatCode('Administration'),
        //                 'title' => 'Administration',
        //                 'unique_title' => $this->formatUniqueTitle('Administration'),
        //                 'description' => 'Administrative work for Samirify LTD',
        //                 'due_date' => date('Y-m-d H:i:s', strtotime('2020-12-21 00:00:00')),
        //                 'status_id' => $projectMilestoneStatuses['NS'],
        //                 'active' => 1,
        //                 'created_by' => $users[array_rand($users)],
        //                 'tasks' => [
        //                     [
        //                         'code' => $this->formatCode('Gmail account creation'),
        //                         'title' => 'Gmail account creation',
        //                         'unique_title' => $this->formatUniqueTitle('Gmail account creation'),
        //                         'description' => 'Create Samirify gmail account to be used for all Google Services (Analytics, Form human validation "reCaptcha", etc..)',
        //                         'due_date' => date('Y-m-d H:i:s', strtotime('2020-12-31 00:00:00')),
        //                         'status_id' => $projectMilestoneTaskStatuses['NS'],
        //                         'active' => 1,
        //                         'contact_id' => null,
        //                         'created_by' => $users[array_rand($users)],
        //                     ],
        //                     [
        //                         'code' => $this->formatCode('Pages data'),
        //                         'title' => 'Pages data',
        //                         'unique_title' => $this->formatUniqueTitle('Pages data'),
        //                         'description' => 'Prepare all data for About, Contact and Contact details pages in both English and Arabic as per the current version',
        //                         'due_date' => date('Y-m-d H:i:s', strtotime('2020-12-21 00:00:00')),
        //                         'status_id' => $projectMilestoneTaskStatuses['NS'],
        //                         'active' => 1,
        //                         'contact_id' => null,
        //                         'created_by' => $users[array_rand($users)],
        //                     ]
        //                 ]
        //             ],
        //             [
        //                 'code' => $this->formatCode('Website'),
        //                 'title' => 'Website',
        //                 'unique_title' => $this->formatUniqueTitle('Website'),
        //                 'description' => 'Re-designing Samirify LTD\'s website',
        //                 'due_date' => date('Y-m-d H:i:s', strtotime('2020-12-21 00:00:00')),
        //                 'status_id' => $projectMilestoneStatuses['NS'],
        //                 'active' => 1,
        //                 'created_by' => $users[array_rand($users)],
        //                 'tasks' => [
        //                     [
        //                         'code' => $this->formatCode('Layout'),
        //                         'title' => 'Layout',
        //                         'unique_title' => $this->formatUniqueTitle('Layout'),
        //                         'description' => 'Layout(Main colours & Logo)',
        //                         'due_date' => date('Y-m-d H:i:s', strtotime('2020-12-21 00:00:00')),
        //                         'status_id' => $projectMilestoneTaskStatuses['NS'],
        //                         'active' => 1,
        //                         'contact_id' => null,
        //                         'created_by' => $users[array_rand($users)],
        //                     ],
        //                     [
        //                         'code' => $this->formatCode('User Experience (UX)'),
        //                         'title' => 'User Experience (UX)',
        //                         'unique_title' => $this->formatUniqueTitle('User Experience (UX)'),
        //                         'description' => 'User Experience (UX)',
        //                         'due_date' => date('Y-m-d H:i:s', strtotime('2020-12-21 00:00:00')),
        //                         'status_id' => $projectMilestoneTaskStatuses['NS'],
        //                         'active' => 1,
        //                         'contact_id' => null,
        //                         'created_by' => $users[array_rand($users)],
        //                     ],
        //                     [
        //                         'code' => $this->formatCode('Pages: About, Contact'),
        //                         'title' => 'Pages: About, Contact',
        //                         'unique_title' => $this->formatUniqueTitle('Pages: About, Contact'),
        //                         'description' => 'Pages: About, Contact',
        //                         'due_date' => date('Y-m-d H:i:s', strtotime('2020-12-21 00:00:00')),
        //                         'status_id' => $projectMilestoneTaskStatuses['NS'],
        //                         'active' => 1,
        //                         'contact_id' => null,
        //                         'created_by' => $users[array_rand($users)],
        //                     ],
        //                     [
        //                         'code' => $this->formatCode('Fixtures, test data and About us content'),
        //                         'title' => 'Fixtures, test data and About us content',
        //                         'unique_title' => $this->formatUniqueTitle('Fixtures, test data and About us content'),
        //                         'description' => 'Fixtures, test data and About us content',
        //                         'due_date' => date('Y-m-d H:i:s', strtotime('2020-12-21 00:00:00')),
        //                         'status_id' => $projectMilestoneTaskStatuses['NS'],
        //                         'active' => 1,
        //                         'contact_id' => null,
        //                         'created_by' => $users[array_rand($users)],
        //                     ],
        //                     [
        //                         'code' => $this->formatCode('Email project URLs for testing'),
        //                         'title' => 'Email project URLs for testing',
        //                         'unique_title' => $this->formatUniqueTitle('Email project URLs for testing'),
        //                         'description' => 'Email project URLs for testing',
        //                         'due_date' => date('Y-m-d H:i:s', strtotime('2020-12-21 00:00:00')),
        //                         'status_id' => $projectMilestoneTaskStatuses['NS'],
        //                         'active' => 1,
        //                         'contact_id' => null,
        //                         'created_by' => $users[array_rand($users)],
        //                     ],
        //                     [
        //                         'code' => $this->formatCode('Training'),
        //                         'title' => 'Training',
        //                         'unique_title' => $this->formatUniqueTitle('Training'),
        //                         'description' => 'Overview and Training for Samirify LTD staff',
        //                         'due_date' => date('Y-m-d H:i:s', strtotime('2020-12-21 00:00:00')),
        //                         'status_id' => $projectMilestoneTaskStatuses['NS'],
        //                         'active' => 1,
        //                         'contact_id' => null,
        //                         'created_by' => $users[array_rand($users)],
        //                     ],
        //                 ]
        //             ],
        //             [
        //                 'code' => $this->formatCode('Mobile Application'),
        //                 'title' => 'Mobile Application',
        //                 'unique_title' => $this->formatUniqueTitle('Mobile Application'),
        //                 'description' => 'Mobile Application',
        //                 'due_date' => date('Y-m-d H:i:s', strtotime('2020-12-21 00:00:00')),
        //                 'status_id' => $projectMilestoneStatuses['NS'],
        //                 'active' => 1,
        //                 'created_by' => $users[array_rand($users)],
        //                 'tasks' => [
        //                     [
        //                         'code' => $this->formatCode('Layout'),
        //                         'title' => 'Layout',
        //                         'unique_title' => $this->formatUniqueTitle('Layout'),
        //                         'description' => 'Layout(Main colours & Logo)',
        //                         'due_date' => date('Y-m-d H:i:s', strtotime('2020-12-21 00:00:00')),
        //                         'status_id' => $projectMilestoneTaskStatuses['NS'],
        //                         'active' => 1,
        //                         'contact_id' => null,
        //                         'created_by' => $users[array_rand($users)],
        //                     ],
        //                     [
        //                         'code' => $this->formatCode('Finish off Paypal payment method'),
        //                         'title' => 'Finish off Paypal payment method',
        //                         'unique_title' => $this->formatUniqueTitle('Finish off Paypal payment method'),
        //                         'description' => 'Finish off Paypal payment method',
        //                         'due_date' => date('Y-m-d H:i:s', strtotime('2020-12-21 00:00:00')),
        //                         'status_id' => $projectMilestoneTaskStatuses['NS'],
        //                         'active' => 1,
        //                         'contact_id' => null,
        //                         'created_by' => $users[array_rand($users)],
        //                     ],
        //                     [
        //                         'code' => $this->formatCode('Release application'),
        //                         'title' => 'Release application',
        //                         'unique_title' => $this->formatUniqueTitle('Release application'),
        //                         'description' => 'Release application to Google Store and Apple App Store for approval',
        //                         'due_date' => date('Y-m-d H:i:s', strtotime('2020-12-21 00:00:00')),
        //                         'status_id' => $projectMilestoneTaskStatuses['NS'],
        //                         'active' => 1,
        //                         'contact_id' => null,
        //                         'created_by' => $users[array_rand($users)],
        //                     ],
        //                 ]
        //             ],
        //             [
        //                 'code' => $this->formatCode('Web Hosting'),
        //                 'title' => 'Web Hosting',
        //                 'unique_title' => $this->formatUniqueTitle('Web Hosting'),
        //                 'description' => 'Review the Web Hosting options with Siab',
        //                 'due_date' => date('Y-m-d H:i:s', strtotime('2020-12-21 00:00:00')),
        //                 'status_id' => $projectMilestoneStatuses['NS'],
        //                 'active' => 1,
        //                 'created_by' => $users[array_rand($users)],
        //                 'tasks' => [
        //                     [
        //                         'code' => $this->formatCode('Discuss hosting options'),
        //                         'title' => 'Discuss hosting options',
        //                         'unique_title' => $this->formatUniqueTitle('Discuss hosting options'),
        //                         'description' => 'Siab and Samir to discuss hosting options',
        //                         'due_date' => date('Y-m-d H:i:s', strtotime('2020-12-30 00:00:00')),
        //                         'status_id' => $projectMilestoneTaskStatuses['NS'],
        //                         'active' => 1,
        //                         'contact_id' => null,
        //                         'created_by' => $users[array_rand($users)],
        //                     ],
        //                     [
        //                         'code' => $this->formatCode('Move applications to hosting servers'),
        //                         'title' => 'Move applications to hosting servers',
        //                         'unique_title' => $this->formatUniqueTitle('Move applications to hosting servers'),
        //                         'description' => 'Move applications to hosting servers and run final verification tests',
        //                         'due_date' => date('Y-m-d H:i:s', strtotime('2020-12-30 00:00:00')),
        //                         'status_id' => $projectMilestoneTaskStatuses['NS'],
        //                         'active' => 1,
        //                         'contact_id' => null,
        //                         'created_by' => $users[array_rand($users)],
        //                     ],
        //                 ]
        //             ],
        //             [
        //                 'code' => $this->formatCode('Go LIVE'),
        //                 'title' => 'Go LIVE',
        //                 'unique_title' => $this->formatUniqueTitle('Go LIVE'),
        //                 'description' => 'Happy days!',
        //                 'due_date' => date('Y-m-d H:i:s', strtotime('2021-01-01 00:00:00')),
        //                 'status_id' => $projectMilestoneStatuses['NS'],
        //                 'active' => 1,
        //                 'created_by' => $users[array_rand($users)],
        //                 'tasks' => [
        //                     [
        //                         'code' => $this->formatCode('We\'re live!'),
        //                         'title' => 'We\'re live!',
        //                         'unique_title' => $this->formatUniqueTitle('We\'re live!'),
        //                         'description' => 'Happy days! Let\'s test the site, Admin Panel and mobile apps continuously and more frequently and report any issues to be resolved immediately',
        //                         'due_date' => date('Y-m-d H:i:s', strtotime('2021-01-01 00:00:00')),
        //                         'status_id' => $projectMilestoneTaskStatuses['NS'],
        //                         'active' => 1,
        //                         'contact_id' => null,
        //                         'created_by' => $users[array_rand($users)],
        //                     ],
        //                 ]
        //             ]
        //         ]
        //     ],
        //     [
        //         'code' => $this->formatCode('Upgrade Samirify LTD - Phase 2 (The extra mile!)'),
        //         'title' => 'Upgrade Samirify LTD - Phase 2 (The extra mile!)',
        //         'unique_title' => $this->formatUniqueTitle('Upgrade Samirify LTD - Phase 2 (The extra mile!)'),
        //         'description' => 'This is to take us to an even better level ;-)',
        //         'due_date' => date('Y-m-d H:i:s', strtotime('2021-06-01 19:00:00')),
        //         'status_id' => $projectInProgressStatus->id,
        //         'active' => 1,
        //         'created_by' => $users[array_rand($users)],
        //         'milestones' => [
        //             [
        //                 'code' => $this->formatCode('Media Improvement'),
        //                 'title' => 'Media Improvement',
        //                 'unique_title' => $this->formatUniqueTitle('Media Improvement'),
        //                 'description' => 'Enhance the media experience',
        //                 'due_date' => date('Y-m-d H:i:s', strtotime('2021-06-01 00:00:00')),
        //                 'status_id' => $projectMilestoneStatuses['NS'],
        //                 'active' => 1,
        //                 'created_by' => $users[array_rand($users)],
        //                 'tasks' => [
        //                     [
        //                         'code' => $this->formatCode('Zoom meetings integration'),
        //                         'title' => 'Zoom meetings integration',
        //                         'unique_title' => $this->formatUniqueTitle('Zoom meetings integration'),
        //                         'description' => 'Allow for users to attend our Zoom meetings from our website without necessarily having Zoom installed on thier computers/mmobiles"!',
        //                         'due_date' => date('Y-m-d H:i:s', strtotime('2021-06-01 00:00:00')),
        //                         'status_id' => $projectMilestoneTaskStatuses['NS'],
        //                         'active' => 1,
        //                         'contact_id' => null,
        //                         'created_by' => $users[array_rand($users)],
        //                     ],
        //                 ]
        //             ]
        //         ]
        //     ]
        // ];

        // foreach ($projects as $projectData) {
        //     $milestones = $projectData['milestones'];
        //     unset($projectData['milestones']);
        //     $project = Project::create($projectData);
        //     foreach ($milestones as $milestoneData) {
        //         $tasks = $milestoneData['tasks'];
        //         unset($milestoneData['tasks']);
        //         $milestoneData['projects_id'] = $project->id;
        //         $milestone = ProjectMilestone::create($milestoneData);
        //         foreach ($tasks as $taskData) {
        //             $taskData['project_milestones_id'] = $milestone->id;
        //             ProjectMilestoneTask::create($taskData);
        //         }
        //     }
        // }
    }
}
