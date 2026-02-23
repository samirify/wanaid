<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationCodeType extends Model
{
    protected $table = 'application_code_type';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'name'
    ];
}
