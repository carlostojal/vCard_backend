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

Route::post('/users', [UserController::class, 'store']);

Route::middleware(['auth:api,vcard'])->group(function () {
    Route::get('/logout', [AuthController::class, 'logout']);
});

Route::get('/checkAuth', [AuthController::class, 'getAuthenticatedGuard']);

Route::get('/Unauthenticated', function () {
    return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);
})->name('Unauthenticated');


//COLOCAR DENTRO DO MIDDLEWARE DE AUTH ADMIN
Route::get('/admins', [UserController::class, 'getAdmins']); //Returns all admins
Route::get('/vcards/search/{phone_number}', [VCardController::class, 'show']);
Route::get('/vcards/search', [VCardController::class, 'indexBlocked']); //todos ou todos blocked ou todos unblocked
Route::get('/transactions/search/{vcard}', [TransactionController::class, 'show']); //Returns all transactions of certain vcard
Route::get('/transactions', [TransactionController::class, 'index']); //Returns all transactions
Route::get('/transactions/search', [TransactionController::class, 'indexType']); //todos ou todos debit ou todos credit
Route::delete('/users/{id}', [UserController::class, 'destroy']); //Deletes user
Route::patch('/vcards/block/{phone_number}', [VCardController::class, 'changeBlock']); //Updates Block vcard
Route::delete('/vcards/{phone_number}', [VCardController::class, 'deleteVcard']); //Deletes vcard
Route::patch('vcards/maxDebit/{phone_number}', [VCardController::class, 'updateMaxDebit']); //Updates vcard max debit
Route::get('/categories', [CategoryController::class, 'index']); //Returns all categories that exist in default categories
Route::get('/categories/search', [CategoryController::class, 'indexType']); //Returns all categories that exist in default categories
Route::get('/categories/search/{categorie}', [CategoryController::class, 'show']); //Returns the category
Route::get('/vcards/myTransactions', [TransactionController::class, 'MyTransactionsType']); //Returns vcard's transactions
Route::post('/categories', [CategoryController::class, 'store']); //Creates a new category

//Route::get('/vcards/myTransactions', [TransactionController::class, 'MyTransactionsType']); //Returns vcard's transactions

Route::middleware('auth:api')->group(function () {
    //ALL ADMINISTRATORS/USERS ROUTES ARE HERE
    Route::get('/testAdmin', function(){ return Auth::user(); });
    Route::get('/categories/{vcard}', [CategoryController::class, 'getAllFromVcard']); //Returns all categories of certain vcard

    Route::get('/users/profile', [UserController::class, 'profile']);
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
    Route::post('/vcards/send', [VcardController::class, 'makeTransaction']);

    Route::get('/vcards/photo/', [VcardController::class, 'getPhotoUrl']);
    // Route::post('/vcards/logout', [AuthController::class, 'logout']);

    Route::delete('/myVcard', [VCardController::class, 'deleteVcardMobile']); //Deletes vcard

    Route::resource('vcards', VCardController::class)->except('store');
});

Route::fallback(function () {
    return response()->json(['status' => 'error', 'message' => 'Route not found'], 404);
});
