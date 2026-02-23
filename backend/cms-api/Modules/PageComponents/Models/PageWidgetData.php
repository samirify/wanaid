<?php

namespace Modules\PageComponents\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;

class PageWidgetData extends Model
{
    use CreatedUpdatedBy;

    protected $table = 'page_widgets_data';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['page_contents_id', 'page_widgets_id', 'module_id', 'data'];
}
