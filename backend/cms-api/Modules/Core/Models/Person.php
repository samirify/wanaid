<?php

namespace Modules\Core\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    use CreatedUpdatedBy;

    protected $table = 'persons';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'contact_id',
        'title_id',
        'first_name',
        'middle_names',
        'last_name',
        'date_of_birth'
    ];
}
