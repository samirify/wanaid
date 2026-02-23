<?php

namespace Modules\Core\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    use CreatedUpdatedBy;

    protected $table = 'language';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'countries_id',
        'name',
        'locales_id',
        'direction',
        'default',
        'active',
        'available'
    ];
}
