<?php

use App\Http\Controllers\Kassa\MenuItemController;
use App\Http\Controllers\KassaController;
use App\Http\Controllers\PageController;
use App\Http\Middleware\KassaAdmin;
use App\Http\Middleware\KassaAuth;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');

Route::get('/menukaart', [PageController::class, 'menu'])->name('menu');
Route::get('/nieuws', [PageController::class, 'news'])->name('news');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::get('/aanbiedingen', [PageController::class, 'offers'])->name('offers');

Route::prefix('kassa')->name('kassa.')->group(function () {
    Route::get('/', [KassaController::class, 'index'])->name('index');
    Route::post('/login', [KassaController::class, 'login'])->name('login');
    Route::post('/logout', [KassaController::class, 'logout'])->name('logout');

    Route::middleware(KassaAuth::class)->group(function () {
        Route::get('/verkoop-overzicht', [KassaController::class, 'verkoopOverzicht'])->name('sales');
        Route::post('/betalen', [KassaController::class, 'pay'])->name('pay');

        Route::middleware(KassaAdmin::class)->group(function () {
            Route::get('/gerechten', [MenuItemController::class, 'index'])->name('gerechten');
            Route::get('/gerechten/nieuw', [MenuItemController::class, 'create'])->name('gerechten.create');
            Route::post('/gerechten', [MenuItemController::class, 'store'])->name('gerechten.store');
            Route::patch('/gerechten/{menuItem}', [MenuItemController::class, 'update'])->name('gerechten.update');
            Route::delete('/gerechten/{menuItem}', [MenuItemController::class, 'destroy'])->name('gerechten.destroy');
            Route::patch('/gerechten/{menuItem}/herstellen', [MenuItemController::class, 'restore'])->name('gerechten.restore');
        });
    });
});
