<?php

namespace Modules\Client\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;

class ClientModuleCategory extends Model
{
    use CreatedUpdatedBy;

    protected $table = 'client_module_categories';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'code',
    ];
}
