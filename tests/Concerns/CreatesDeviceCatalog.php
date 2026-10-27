<?php

namespace Tests\Concerns;

use App\Models\DeviceBrand;
use App\Models\DeviceModel;

trait CreatesDeviceCatalog
{
    protected function deviceSelection(string $brand = 'Samsung', string $model = 'A10'): array
    {
        $brand = DeviceBrand::firstOrCreate(['normalized_name' => mb_strtolower($brand)], ['name' => $brand]);
        $model = DeviceModel::firstOrCreate(['device_brand_id' => $brand->id, 'normalized_name' => mb_strtolower($model)], ['name' => $model]);

        return ['device_brand_id' => $brand->id, 'device_model_id' => $model->id];
    }
}
