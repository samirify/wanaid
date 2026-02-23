<?php

namespace Modules\Core\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;

class LanguageTranslation extends Model
{
    use CreatedUpdatedBy;

    protected $table = 'language_translation';

    protected $fillable = [
        'code',
    ];
}
