<?php

namespace App\Actions\Catalog;

use App\Models\DeviceBrand;
use App\Models\DeviceModel;
use App\Models\Reparation;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class DeleteDeviceEntry
{
    public function handle(DeviceBrand|DeviceModel $entry): void
    {
        DB::transaction(function () use ($entry): void {
            $entry = $entry->newQuery()->lockForUpdate()->findOrFail($entry->id);
            $column = $entry instanceof DeviceBrand ? 'device_brand_id' : 'device_model_id';
            if (($entry instanceof DeviceBrand && $entry->deviceModels()->exists()) || Reparation::where($column, $entry->id)->exists()) {
                throw ValidationException::withMessages(['catalog' => 'Suppression impossible : cet élément possède des modèles ou est utilisé par une réparation.']);
            }
            $entry->delete();
        });
    }
}
