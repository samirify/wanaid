<?php

namespace Modules\Core\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;

class SystemMessage extends Model
{
    use CreatedUpdatedBy;

    protected $table = 'system_messages';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'message',
        'entity_name',
        'entity_id',
        'message_type_id',
        'severity_id',
        'status_id'
    ];
}
