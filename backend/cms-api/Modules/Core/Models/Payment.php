<?php

namespace Modules\Core\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use CreatedUpdatedBy;

    protected $table = 'payments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'entity_name',
        'entity_id',
        'amount',
        'payment_method_id',
        'status_id'
    ];
}
