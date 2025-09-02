<?php

use App\Http\Controllers\Controller;
use App\Http\Controllers\welcomeController;
use Illuminate\Support\Facades\Route;



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
