<?php

namespace Modules\Client\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Core\Models\Colour;

class ClientIdentityTheme extends Model
{
    use CreatedUpdatedBy;

    protected $table = 'client_identity_themes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'client_identity_id',
        'code',
        'name',
        'primary_colour_id',
        'secondary_colour_id',
        'logo_coloured_light_id',
        'logo_coloured_dark_id',
        'logo_contrast_light_id',
        'logo_contrast_dark_id',
        'active',
        'default'
    ];

    public function clientIdentity(): BelongsTo
    {
        return $this->belongsTo(ClientIdentity::class);
    }

    public function primaryColour(): HasOne
    {
        return $this->hasOne(Colour::class, 'id', 'primary_colour_id');
    }

    public function secondaryColour(): HasOne
    {
        return $this->hasOne(Colour::class, 'id', 'secondary_colour_id');
    }
}
