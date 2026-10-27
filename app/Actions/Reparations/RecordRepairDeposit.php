<?php

namespace App\Actions\Reparations;

use App\Enums\RepairStatus;
use App\Models\DeviceBrand;
use App\Models\DeviceModel;
use App\Models\Reparation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class RecordRepairDeposit
{
    public function handle(array $data, User $actor): Reparation
    {
        return DB::transaction(function () use ($data, $actor): Reparation {
            $brand = DeviceBrand::query()->lockForUpdate()->findOrFail($data['device_brand_id']);
            $model = DeviceModel::query()->where('device_brand_id', $brand->id)->lockForUpdate()->findOrFail($data['device_model_id']);
            $data['marque'] = $brand->name;
            $data['modele'] = $model->name;
            $repair = Reparation::create(array_merge($data, ['statut' => RepairStatus::Waiting->value]));
            $repair->statusChanges()->create([
                'user_id' => $actor->id,
                'actor_name' => $actor->name,
                'from_status' => null,
                'to_status' => RepairStatus::Waiting->value,
                'version' => 0,
            ]);

            return $repair;
        });
    }
}
