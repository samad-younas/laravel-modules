<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:sanctum')->group(function () {
    Route::controller(ProductController::class)->prefix('/product')->group(function () {
        Route::post('/save', 'store');
        Route::post('/update', 'update');
        Route::get('/list', 'list');
        Route::get('/remove/{id}', 'destroy');
        Route::get('/edit/{id}', 'edit');
        Route::get('/status_act_inact/{id}', 'active_inactive');
    });
});
