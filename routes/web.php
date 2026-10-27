<?php

<<<<<<< HEAD

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ReparationController;




// 🔁 Redirection par défaut vers login
Route::redirect('/', '/login');

// ✅ Authentification
Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ✅ Enregistrement (protégé par un code secret)
Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

// ✅ Tableau de bord (dashboard) après login
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware('auth')->name('dashboard');



// ✅ Clients
Route::resource('clients', ClientController::class)->middleware('auth');

// ✅ Réparations
Route::get('/reparations/find-client', [ReparationController::class, 'findClientForm'])->name('reparations.findClientForm')->middleware('auth');
Route::post('/reparations/find-client', [ReparationController::class, 'findClient'])->name('reparations.findClient')->middleware('auth');
Route::put('/reparations/{id}/statut', [ReparationController::class, 'updateStatut'])->name('reparations.updateStatut')->middleware('auth');
Route::resource('reparations', ReparationController::class)->middleware('auth');

// ✅ Utilisateurs (accès réservé aux admins via middleware personnalisé)
Route::middleware(['auth'])->group(function () {
    Route::resource('users', UserController::class);
});

// ✅ Route de test de message flash
Route::get('/test-message', function () {
    return redirect()->route('login')->with('success', 'Test OK');
});


Route::view('/admin', 'users.index')->middleware('auth')->name('admin');
=======
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceCatalogController;
use App\Http\Controllers\ReparationController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\PublicPageController::class, 'home'])->name('home');
Route::get('/login', [AuthController::class, 'loginForm'])->middleware('guest')->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::get('/suivi', [ReparationController::class, 'tracking'])->name('suivi');
Route::post('/suivi', [ReparationController::class, 'tracking'])->middleware('throttle:20,1')->name('suivi.search');

Route::middleware('auth')->group(function (): void {
    Route::get('/acces-en-attente', [\App\Http\Controllers\PublicPageController::class, 'pending'])->name('access.pending');
    Route::get('/dashboard', DashboardController::class)->middleware('can:dashboard.view')->name('dashboard');

    Route::resource('clients', ClientController::class)->only(['index', 'create', 'store', 'show'])
        ->middlewareFor(['index', 'show'], 'can:clients.view')
        ->middlewareFor(['create', 'store'], 'can:clients.create');

    Route::get('/reparations/find-client', [ReparationController::class, 'findClientForm'])->middleware('can:repairs.create')->name('reparations.findClientForm');
    Route::post('/reparations/find-client', [ReparationController::class, 'findClient'])->middleware('can:repairs.create')->name('reparations.findClient');
    Route::put('/reparations/{id}/statut', [ReparationController::class, 'updateStatut'])->middleware('can:repairs.change_status')->name('reparations.updateStatut');
    Route::put('/reparations/{reparation}/prix', [ReparationController::class, 'updatePrix'])->middleware('can:repairs.set_price')->name('reparations.prix');
    Route::get('/reparations/{reparation}/ticket', [ReparationController::class, 'ticket'])->middleware('can:repairs.view')->name('reparations.ticket');
    Route::resource('reparations', ReparationController::class)->only(['index', 'show', 'create', 'store'])
        ->middlewareFor(['index', 'show'], 'can:repairs.view')
        ->middlewareFor(['create', 'store'], 'can:repairs.create');

    Route::patch('/users/{user}/status', [UserController::class, 'status'])->middleware('can:users.manage')->name('users.status');
    Route::resource('users', UserController::class)->except('show')->middleware('can:users.manage');
    Route::resource('roles', RoleController::class)->only(['index', 'create', 'store', 'edit', 'update'])->middleware('can:roles.manage');
    Route::middleware('can:catalog.manage')->group(function (): void {
        Route::get('/catalogue', [DeviceCatalogController::class, 'index'])->name('catalog.index');
        Route::post('/catalogue/marques', [DeviceCatalogController::class, 'storeBrand'])->name('catalog.brands.store');
        Route::put('/catalogue/marques/{brand}', [DeviceCatalogController::class, 'updateBrand'])->name('catalog.brands.update');
        Route::delete('/catalogue/marques/{brand}', [DeviceCatalogController::class, 'destroyBrand'])->name('catalog.brands.destroy');
        Route::post('/catalogue/marques/{brand}/modeles', [DeviceCatalogController::class, 'storeModel'])->name('catalog.models.store');
        Route::put('/catalogue/marques/{brand}/modeles/{deviceModel}', [DeviceCatalogController::class, 'updateModel'])->name('catalog.models.update');
        Route::delete('/catalogue/marques/{brand}/modeles/{deviceModel}', [DeviceCatalogController::class, 'destroyModel'])->name('catalog.models.destroy');
    });
    Route::redirect('/admin', '/users')->middleware('can:users.manage')->name('admin');
});

Route::get('/test-message', fn () => redirect()->route('login')->with('success', 'Test OK'));
>>>>>>> version1
