<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class PublicPageController extends Controller
{
    public function home(): Response
    {
        return Inertia::render('Public/Home', ['workshop' => config('workshop')]);
    }

    public function pending(): Response
    {
        return Inertia::render('Auth/Pending');
    }
}
