<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\VCardController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\PiggyBankController;
use Illuminate\Support\Facades\Auth;

//Rotas específicas
Route::post('/vcards/login', [AuthController::class, 'loginVcard']);
Route::post('/users/login', [AuthController::class, 'loginUser']);

Route::post('/vcards/', [VCardController::class, 'store']);
Route::post('/vcards/mobile', [VCardController::class, 'storeMobile']);

Route::post('/users/', [UserController::class, 'store']);

Route::middleware(['auth:api,vcard'])->group(function () {
    Route::get('/logout', [AuthController::class, 'logout']);
});

Route::get('/checkAuth', [AuthController::class, 'getAuthenticatedGuard']);

Route::get('/Unauthenticated', function () {
    return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);
})->name('Unauthenticated');


//COLOCAR DENTRO DO MIDDLEWARE DE AUTH
Route::get('/admins', [UserController::class, 'getAdmins']); //Returns all admins
Route::get('/vcards', [VCardController::class, 'getVcards']); //Returns all vcards


Route::middleware('auth:api')->group(function () {
    //ALL ADMINISTRATORS/USERS ROUTES ARE HERE
    Route::get('/testAdmin', function(){ return Auth::user(); });
    Route::get('/categories', [CategoryController::class, 'index']); //Returns all categories that exist in default categories
    Route::get('/categories/{vcard}', [CategoryController::class, 'getAllFromVcard']); //Returns all categories of certain vcard




    



    Route::resource('users', UserController::class)->except('store');
    // Route::post('/users/logout', [AuthController::class, 'logout']);
});

Route::middleware('auth:vcard')->group(function () {
    //VCARD USERS ROUTES, TAES IS HERE
    Route::get('/piggy-bank', [PiggyBankController::class, 'getPiggyBank']); //Returns vcard piggy bank transactions
    Route::get('/piggy-bank/transactions', [PiggyBankController::class, 'getTransactions']); //Returns vcard piggy bank transactions
    Route::post('/piggy-bank/withdraw', [PiggyBankController::class, 'withdraw']);
    Route::post('/piggy-bank/deposit', [PiggyBankController::class, 'deposit']);

    Route::get('/vcards/categories', [CategoryController::class, 'getMyCategories']); //Returns vcard's categories

    Route::get('/vcards/profile', [VCardController::class, 'profile']);
    Route::get('/vcards/balance', [VCardController::class, 'getBalance']);
    Route::get('/vcards/transactions', [TransactionController::class, 'getMyTransactions']);
    Route::post('/vcards/send', [VcardController::class, 'send']);
    // Route::post('/vcards/logout', [AuthController::class, 'logout']);

    Route::resource('vcards', VCardController::class)->except('store');
});

Route::fallback(function () {
    return response()->json(['status' => 'error', 'message' => 'Route not found'], 404);
});
