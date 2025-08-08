<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PropertiesController;
use App\Http\Controllers\InventoryController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('app')->group(function () {
    // UserController
    Route::post('/login', [UserController::class, 'appLogin'])->name('appLogin');
    // PropertiesController
    Route::post('/gene-check', [PropertiesController::class, 'geneCheck'])->name('gene-check');
    // InventoryController
    Route::post('/check-inv', [InventoryController::class, 'checkInv'])->name('check-inv');
    Route::post('/gene-qr', [InventoryController::class, 'geneQr'])->name('gene-qr');
});