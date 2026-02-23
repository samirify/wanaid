<?php

namespace Modules\Core\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    use CreatedUpdatedBy;

    protected $table = 'countries';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'iso',
        'name',
        'nickname',
        'iso3',
        'numcode',
        'phonecode'
    ];
}
