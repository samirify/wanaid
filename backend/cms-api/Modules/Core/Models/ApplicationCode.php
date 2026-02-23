<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationCode extends Model
{
    protected $table = 'application_code';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'application_code_type_id',
        'code',
        'name'
    ];
}
