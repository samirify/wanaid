<?php

namespace Modules\Department\Entities;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Modules\Team\Models\TeamMember;

class Department extends Model
{
    use CreatedUpdatedBy;

    protected $table = 'departments';

    protected $fillable = ['unique_title', 'name', 'sub_header', 'order'];

    public function team()
    {
        return $this->hasMany(TeamMember::class);
    }
}
