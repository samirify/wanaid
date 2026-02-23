<?php

namespace Modules\Core\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use CreatedUpdatedBy;

    protected $table = 'projects';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'title',
        'description',
        'due_date',
        'status_id',
        'active',
        'created_by'
    ];
}
