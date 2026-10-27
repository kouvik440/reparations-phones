<?php

namespace App\Http\Controllers;

use App\Actions\Catalog\DeleteDeviceEntry;
use App\Http\Requests\SaveDeviceBrandRequest;
use App\Http\Requests\SaveDeviceModelRequest;
use App\Models\DeviceBrand;
use App\Models\DeviceModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class DeviceCatalogController extends Controller
{
    public function index(Request $request): Response
    {
        $request->validate(['q' => ['nullable', 'string', 'max:100'], 'brand' => ['nullable', 'integer', 'exists:device_brands,id']]);
        $search = trim((string) $request->input('q', ''));
        $brands = DeviceBrand::query()->when($search !== '', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))->orderBy('name')->paginate(15)->withQueryString();
        $selectedBrand = $request->filled('brand') ? DeviceBrand::findOrFail($request->integer('brand')) : null;
        $models = $selectedBrand?->deviceModels()->paginate(20, ['*'], 'models_page')->withQueryString();

        return \Inertia\Inertia::render('Catalog/Index', compact('brands', 'selectedBrand', 'models', 'search'));
    }

    public function storeBrand(SaveDeviceBrandRequest $request): RedirectResponse
    {
        $brand = DeviceBrand::create($request->validated());

        return redirect()->to(route('catalog.index', ['brand' => $brand->id]).'#models')->with('success', 'Marque ajoutée. Vous pouvez créer ses modèles.');
    }

    public function updateBrand(SaveDeviceBrandRequest $request, DeviceBrand $brand): RedirectResponse
    {
        $brand->update($request->validated());

        return back()->with('success', 'Marque modifiée. Les anciens dossiers conservent leur libellé.');
    }

    public function destroyBrand(DeviceBrand $brand, DeleteDeviceEntry $delete): RedirectResponse
    {
        $delete->handle($brand);

        return to_route('catalog.index')->with('success', 'Marque supprimée.');
    }

    public function storeModel(SaveDeviceModelRequest $request, DeviceBrand $brand): RedirectResponse
    {
        $brand->deviceModels()->create($request->validated());

        return redirect()->to(route('catalog.index', ['brand' => $brand->id]).'#models')->with('success', 'Modèle ajouté.');
    }

    public function updateModel(SaveDeviceModelRequest $request, DeviceBrand $brand, DeviceModel $deviceModel): RedirectResponse
    {
        abort_unless($deviceModel->device_brand_id === $brand->id, 404);
        $deviceModel->update($request->validated());

        return redirect()->to(route('catalog.index', ['brand' => $brand->id]).'#models')->with('success', 'Modèle modifié. Les anciens dossiers conservent leur libellé.');
    }

    public function destroyModel(DeviceBrand $brand, DeviceModel $deviceModel, DeleteDeviceEntry $delete): RedirectResponse
    {
        abort_unless($deviceModel->device_brand_id === $brand->id, 404);
        $delete->handle($deviceModel);

        return redirect()->to(route('catalog.index', ['brand' => $brand->id]).'#models')->with('success', 'Modèle supprimé.');
    }
}
