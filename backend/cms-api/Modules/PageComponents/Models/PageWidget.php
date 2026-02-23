<?php

namespace Modules\PageComponents\Models;

use Illuminate\Database\Eloquent\Model;

class PageWidget extends Model
{
    protected $table = 'page_widgets';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['code', 'name', 'active'];
}
