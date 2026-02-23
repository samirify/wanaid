<?php

namespace Modules\Core\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;

class StatusChange extends Model
{
    use CreatedUpdatedBy;

    protected $table = 'status_changes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'entity_name',
        'entity_id',
        'message',
        'status_from_id',
        'status_to_id',
        'updated_by'
    ];
}
