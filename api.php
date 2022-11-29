<?php

Route::post('/login', [App\Http\Controllers\AuthenticationController::class, 'adminLogin']);
/*LOGOUT*/
Route::get('/logout', [App\Http\Controllers\AuthenticationController::class, 'logout']);