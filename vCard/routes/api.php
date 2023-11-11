<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\VCardController;

//Rotas específicas
Route::post('/vcards/login', [AuthController::class, 'loginVcard']);
Route::post('/users/login', [AuthController::class, 'loginUser']);

Route::post('/vcards/', [VCardController::class, 'store']);
Route::post('/vcards/mobile', [VCardController::class, 'storeMobile']);

Route::post('/users/', [UserController::class, 'store']);

Route::middleware('auth:api')->group(function () {
    //api/user
    //ALL ADMINISTRATORS/USERS ROUTES ARE HERE
    Route::resource('users', UserController::class)->except('store');
    Route::get('/testAdmin', function () {
        return 'You need to have a user admin token';
    });
    Route::post('logout', [AuthController::class, 'logout']);
});

Route::middleware('auth:vcard')->group(function () {
// Route::group(['middleware' => 'auth:vcard'], function () {
    //api/vcard/
    //VCARD USERS ROUTES, TAES IS HERE
    Route::resource('vcards', VCardController::class)->except('store');
    Route::get('/testVcard', function () {
        return 'You need to have a vcard token';
    });
    Route::get('/profile', [VCardController::class, 'profile']);

});

Route::resource('vcards', VCardController::class)->except('store');
