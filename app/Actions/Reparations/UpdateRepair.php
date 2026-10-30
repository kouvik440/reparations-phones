<?php

namespace App\Actions\Reparations;

use App\Models\DeviceBrand;
use App\Models\DeviceModel;
use App\Models\Reparation;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

final class UpdateRepair
{
    public function handle(int $id, array $data): Reparation
    {
        return DB::transaction(function () use ($id, $data): Reparation {
            $repair = Reparation::query()->lockForUpdate()->findOrFail($id);
            if ($repair->status_version !== (int) $data['status_version']) {
                throw new ConflictHttpException('Ce dossier a changé. Actualisez avant de le modifier.');
            }
            if ($repair->date_retrait && $data['date_depot'] > $repair->date_retrait) {
                throw ValidationException::withMessages(['date_depot' => 'Le dépôt ne peut pas être postérieur au retrait.']);
            }
            $brand = DeviceBrand::query()->lockForUpdate()->findOrFail($data['device_brand_id']);
            $model = DeviceModel::query()->where('device_brand_id', $brand->id)->lockForUpdate()->findOrFail($data['device_model_id']);
            unset($data['status_version']);
            // Preserve historical names when the selected device is unchanged.
            if ($repair->device_brand_id !== $brand->id || $repair->device_model_id !== $model->id) {
                $data['marque'] = $brand->name;
                $data['modele'] = $model->name;
            }
            $repair->fill($data);
            $repair->status_version++;
            $repair->save();

            return $repair;
        });
    }
}
