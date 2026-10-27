<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        foreach (array_keys(config('permissions')) as $permission) {
            \Illuminate\Support\Facades\Gate::define($permission, fn (\App\Models\User $user): bool => $user->hasPermission($permission));
        }
    }
}
