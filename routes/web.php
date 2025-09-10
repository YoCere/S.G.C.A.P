<?php


use App\Http\Controllers\welcomeController;
use Illuminate\Support\Facades\Route;

require __DIR__.'/admin.php';


Route::get("/", [welcomeController::class, "welcome"]);
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});
