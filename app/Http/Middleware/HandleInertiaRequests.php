<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => function () use ($request): array {
                $user = $request->user();

                return [
                    'user' => $user ? [...$user->only('id', 'name', 'email', 'role'), 'role_name' => $user->roleDefinition?->name, 'landing_url' => route($user->landingRoute())] : null,
                    'permissions' => collect(array_keys(config('permissions')))->mapWithKeys(fn (string $permission): array => [$permission => $user?->can($permission) ?? false])->all(),
                ];
            },
            'flash' => fn (): array => ['success' => $request->session()->get('success'), 'error' => $request->session()->get('error')],
            'timezone' => config('app.timezone'),
            'statusLabels' => \App\Enums\RepairStatus::labels(),
        ]);
    }
}
