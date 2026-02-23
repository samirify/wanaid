<?php

namespace Modules\PageComponents\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use CreatedUpdatedBy;

    protected $table = 'pages';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'name',
        'page_title',
        'header_size_id',
        'meta_desctiption',
        'meta_keywords',
        'is_template'
    ];
}
