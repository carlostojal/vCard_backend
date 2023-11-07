<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserPhoneNumberController;
use App\Http\Controllers\VCardController;

//Rotas específicas
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [UserController::class, 'store']);

//Rotas para CRUD
Route::middleware('auth:api')->group(function () {
    //All routes inside need to be authenticated
    Route::resource('users', UserController::class)->except(['store']);
    Route::post('logout', [AuthController::class, 'logout']);
});
Route::resource('users-phone-numbers', UserPhoneNumberController::class);


Route::resource('vcards', VCardController::class);