<?php

use App\Http\Controllers\Kassa\MenuItemController;
use App\Http\Controllers\Kassa\OfferController;
use App\Http\Controllers\Kassa\PlanningController;
use App\Http\Controllers\Kassa\SalesSummaryController;
use App\Http\Controllers\KassaController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\TabletController;
use App\Http\Middleware\KassaAdmin;
use App\Http\Middleware\KassaAuth;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');

Route::get('/menukaart', [PageController::class, 'menu'])->name('menu');
Route::get('/menu/pdf', [PageController::class, 'menuPdf'])->name('menu.pdf');
Route::get('/tablet', [TabletController::class, 'index'])->name('tablet.index');
Route::post('/tablet', [TabletController::class, 'store'])->name('tablet.store');
Route::post('/tablet/reset', [TabletController::class, 'resetTable'])->name('tablet.reset');
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

            Route::get('/aanbiedingen', [OfferController::class, 'index'])->name('aanbiedingen');
            Route::get('/aanbiedingen/nieuw', [OfferController::class, 'create'])->name('aanbiedingen.create');
            Route::post('/aanbiedingen', [OfferController::class, 'store'])->name('aanbiedingen.store');
            Route::get('/aanbiedingen/{offer}/bewerken', [OfferController::class, 'edit'])->name('aanbiedingen.edit');
            Route::patch('/aanbiedingen/{offer}', [OfferController::class, 'update'])->name('aanbiedingen.update');
            Route::delete('/aanbiedingen/{offer}', [OfferController::class, 'destroy'])->name('aanbiedingen.destroy');
            Route::patch('/aanbiedingen/{offer}/herstellen', [OfferController::class, 'restore'])->name('aanbiedingen.restore');

            Route::get('/planning', [PlanningController::class, 'index'])->name('planning');
            Route::post('/planning', [PlanningController::class, 'store'])->name('planning.store');
            Route::delete('/planning/{planningRule}', [PlanningController::class, 'destroy'])->name('planning.destroy');

            Route::get('/verkoop-samenvattingen', [SalesSummaryController::class, 'index'])->name('sales-summaries');
            Route::post('/verkoop-samenvattingen/genereren', [SalesSummaryController::class, 'generate'])->name('sales-summaries.generate');
            Route::get('/verkoop-samenvattingen/{salesSummary}/download', [SalesSummaryController::class, 'download'])->name('sales-summaries.download');
        });
    });
});
