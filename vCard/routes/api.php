<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;

//Rotas específicas
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [UserController::class, 'store']);

//Rotas para CRUD
Route::middleware('auth:api')->group(function () {
    //All routes inside need to be authenticated
    Route::resource('users', UserController::class)->except(['store']);
});
