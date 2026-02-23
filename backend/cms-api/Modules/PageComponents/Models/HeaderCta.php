<?php

namespace Modules\PageComponents\Models;

use Illuminate\Database\Eloquent\Model;

class HeaderCta extends Model
{
    protected $table = 'header_ctas';

    protected $fillable = [
        'name', 
        'label', 
        'url', 
        'url_type', 
        'style', 
        'order', 
        'pages_id',
        'active'
    ];
}
