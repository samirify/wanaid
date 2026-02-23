<?php

namespace Modules\PageComponents\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;

class PageSection extends Model
{
    use CreatedUpdatedBy;

    protected $table = 'page_sections';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['pages_id', 'code', 'value'];
}
