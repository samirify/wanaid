<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;

class Colour extends Model
{
    protected $table = 'colours';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'red',
        'green',
        'blue',
        'hex'
    ];
}
