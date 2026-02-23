<?php

namespace Modules\Client\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;

class ClientModule extends Model
{
    use CreatedUpdatedBy;

    protected $table = 'client_modules';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'code',
        'category_id',
        // 'url', 
        'active'
    ];
}
