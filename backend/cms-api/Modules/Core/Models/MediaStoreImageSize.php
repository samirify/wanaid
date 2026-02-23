<?php

namespace Modules\Core\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;

class MediaStoreImageSize extends Model
{
    use CreatedUpdatedBy;

    protected $table = 'media_store_image_sizes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'media_store_id',
        'width',
        'height',
        'content',
    ];
}
