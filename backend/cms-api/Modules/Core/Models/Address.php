<?php

namespace Modules\Core\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use CreatedUpdatedBy;

    protected $table = 'address';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'contact_id',
        'type_id',
        'full_address',
        'address_line_1',
        'is_primary'
    ];
}
