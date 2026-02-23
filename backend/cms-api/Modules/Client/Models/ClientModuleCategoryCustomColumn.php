<?php

namespace Modules\Client\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;

class ClientModuleCategoryCustomColumn extends Model
{
    use CreatedUpdatedBy;

    protected $table = 'client_module_category_custom_columns';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'client_module_categories_id',
        'name',
        'type',
        'foreign_table',
        'foreign_column',
        'required',
        'unique',
        'options',
    ];
}
