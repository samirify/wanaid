<?php

namespace Modules\Client\Models;

use App\Traits\CreatedUpdatedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class ClientModuleRecord extends Model
{
    use CreatedUpdatedBy;

    const UNFILLABLE_COLUMNS = ['id', 'created_at', 'updated_at', 'created_by', 'updated_by'];

    public function setDynamicFillable(): ClientModuleRecord
    {
        $fields = Schema::getColumnListing($this->table);

        $this->fillable = array_diff($fields, self::UNFILLABLE_COLUMNS);

        return $this;
    }
}
