<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;

class SfyPasswordReset extends Model
{
    protected $table = 'sfy_password_reset';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'token',
        'code',
        'created_at'
    ];
}
