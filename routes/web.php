<?php


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
