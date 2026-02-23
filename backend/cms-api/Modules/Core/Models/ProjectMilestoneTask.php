<?php

namespace Modules\Core\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;

class ProjectMilestoneTask extends Model
{
    use CreatedUpdatedBy;

    protected $table = 'project_milestone_tasks';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'project_milestones_id',
        'code',
        'title',
        'description',
        'due_date',
        'status_id',
        'active',
        'contact_id',
        'created_by'
    ];
}
