<?php

namespace Modules\Core\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;

class Email extends Model
{
    use CreatedUpdatedBy;

    protected $table = 'email';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'contact_id',
        'type_id',
        'email_address',
        'is_primary'
    ];
}
