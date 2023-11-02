<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

//Rotas específicas
Route::post('login', [UserController::class, 'login']);




//Rotas para CRUD
Route::apiResource('users', UserController::class);