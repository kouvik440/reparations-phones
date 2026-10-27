<?php

namespace App\Http\Controllers;

use App\Actions\Dashboard\GetDashboardData;
use Inertia\Response;

final class DashboardController extends Controller
{
    public function __invoke(GetDashboardData $dashboard): Response
    {
        return \Inertia\Inertia::render('Dashboard', $dashboard->handle());
    }
}
