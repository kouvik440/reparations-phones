<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeviceBrand extends Model
{
    protected $fillable = ['name', 'normalized_name'];

    public function deviceModels(): HasMany
    {
        return $this->hasMany(DeviceModel::class)->orderBy('name');
    }
}
