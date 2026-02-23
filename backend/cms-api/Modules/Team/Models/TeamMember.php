<?php

namespace Modules\Team\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Modules\Department\Entities\Department;

class TeamMember extends Model
{
    use CreatedUpdatedBy;

    protected $table = 'team';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'unique_title',
        'position',
        'short_description',
        'description',
    ];

    public function departments()
    {
        return $this->belongsTo(Department::class);
    }
}
