<?php

namespace Modules\Core\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;

class LanguageCode extends Model
{
    use CreatedUpdatedBy;

    protected $table = 'language_code';

    protected $fillable = [
        'code',
        'is_html'
    ];
}
