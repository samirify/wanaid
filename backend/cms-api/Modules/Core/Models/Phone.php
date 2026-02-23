<?php

namespace Modules\Core\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;

class Phone extends Model
{
    use CreatedUpdatedBy;

    protected $table = 'phone';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'contact_id',
        'type_id',
        'phone_number',
        'is_primary'
    ];
}
