<?php

namespace Modules\Client\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientIdentity extends Model
{
    use CreatedUpdatedBy;

    protected $table = 'client_identity';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'business_name',
        'business_slogan',
        'business_short_description',
        'active',
        'default'
    ];

    public function themes(): HasMany
    {
        return $this->hasMany(ClientIdentityTheme::class);
    }
}
