<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::controller(CategoryController::class)->prefix('/category')->group(function () {
    Route::post('/save', 'store');
    Route::post('/edit/{id}', 'edit');
    Route::post('/update', 'update');
    Route::get('/list', 'list');
    Route::get('/remove/{id}', 'destroy');
});

