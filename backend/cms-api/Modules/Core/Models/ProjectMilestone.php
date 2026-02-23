<?php

namespace Modules\Core\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;

class ProjectMilestone extends Model
{
    use CreatedUpdatedBy;

    protected $table = 'project_milestones';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'projects_id',
        'code',
        'title',
        'description',
        'due_date',
        'status_id',
        'active',
        'created_by'
    ];
}
