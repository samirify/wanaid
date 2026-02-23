<?php

namespace Modules\Core\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;

class SocialMedia extends Model
{
    use CreatedUpdatedBy;

    protected $table = 'social_media';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'contact_id',
        'brand_id',
        'url',
        'is_primary'
    ];
}
