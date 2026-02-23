<?php

namespace Modules\PageComponents\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;

class PageContent extends Model
{
    use CreatedUpdatedBy;

    protected $table = 'page_contents';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'page_sections_id',
        'code',
        'value',
        'cta_target',
        'cta_label',
        'cta_pages_id',
        'cta_url',
        'active'
    ];
}
