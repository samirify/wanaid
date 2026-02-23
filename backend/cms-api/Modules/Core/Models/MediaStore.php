<?php

namespace Modules\Core\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;

class MediaStore extends Model
{
    use CreatedUpdatedBy;

    protected $table = 'media_store';

    protected $fillable = [
        'mime_type',
        'file_name',
        'file_size',
        'file_extension',
        'entity_name',
        'entity_id',
        'content',
        'width',
        'height',
        'dpi',
        'temp_token',
        'to_delete',
        'batch_id',
    ];
}
