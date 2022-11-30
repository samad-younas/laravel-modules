<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\HomeController;
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
    Route::controller(BlogController::class)->prefix('/blogs')->group(function () {
        Route::post('/save', 'store');
        Route::get('/edit/{id}', 'edit');
        Route::post('/update', 'update');
        Route::get('/list', 'list');
        Route::get('/status_act_inact/{id}', 'active_inactive');
        Route::get('/remove/{id}', 'destroy');
    });
});

Route::get('/home', [HomeController::class,'index'])->name('home');
Route::get('admin/home', [HomeController::class,'handleAdmin'])->name('admin.route')->middleware('admin');

