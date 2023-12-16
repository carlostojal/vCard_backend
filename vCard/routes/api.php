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
use App\Http\Controllers\PDFController;

//Rotas específicas
Route::post('/vcards/login', [AuthController::class, 'loginVcard']);
Route::post('/users/login', [AuthController::class, 'loginUser']);

Route::post('/vcards/', [VCardController::class, 'store']);
Route::post('/vcards/mobile', [VCardController::class, 'storeMobile']);

Route::post('/users', [UserController::class, 'store']);

Route::middleware(['auth:api,vcard'])->group(function () {
    Route::get('/logout', [AuthController::class, 'logout']);

    //PDF
    Route::get('/extract/pdf', [PDFController::class, 'index']);

    Route::get('/vcards/mycategories', [CategoryController::class, 'getMyCategoriesDAD']); //Returns vcard's categories
    Route::post('/vcards/mycategories', [CategoryController::class, 'storeMyCategoriesDAD']); //Creates a new category in vcard
    Route::delete('/myCategories/{id}', [CategoryController::class, 'destroyMyCategoriesDAD']); //Deletes a category in vcard
});

Route::get('/checkAuth', [AuthController::class, 'getAuthenticatedGuard']);

Route::get('/Unauthenticated', function () {
    return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);
})->name('Unauthenticated');




//COLOCAR DENTRO DO MIDDLEWARE DE AUTH ADMIN
Route::get('/admins', [UserController::class, 'getAdmins']); //Returns all admins
Route::get('/vcards/search/{phone_number}', [VCardController::class, 'show']);
Route::get('/vcards/search', [VCardController::class, 'indexBlocked']); //todos ou todos blocked ou todos unblocked 
Route::get('/transactions/search/{query}', [TransactionController::class, 'indexAllTransactions_search']); //Returns all transactions of certain vcard | email | name
Route::get('/transactions', [TransactionController::class, 'index']); //Returns all transactions
Route::get('/transactions/search', [TransactionController::class, 'indexAllTransactions_type']); //todos ou todos debit ou todos credit
Route::delete('/users/{id}', [UserController::class, 'destroy']); //Deletes user
Route::patch('/vcards/block/{phone_number}', [VCardController::class, 'changeBlock']); //Updates Block vcard
Route::delete('/vcards/{phone_number}', [VCardController::class, 'deleteVcard']); //Deletes vcard
Route::patch('vcards/maxDebit/{phone_number}', [VCardController::class, 'updateMaxDebit']); //Updates vcard max debit
Route::get('/categories', [CategoryController::class, 'index']); //Returns all categories that exist in default categories
Route::get('/categories/search', [CategoryController::class, 'indexType']); //Returns all categories that exist in default categories
Route::get('/categories/search/{categorie}', [CategoryController::class, 'show']); //Returns the category
Route::delete('/categories/{id}', [CategoryController::class, 'destroyCategoriesDAD']); //Deletes a default category
Route::post('/categories', [CategoryController::class, 'store']); //Creates a new default category


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

    Route::get('/myTransactions/search/{query}', [TransactionController::class, 'indexMyTransactions_search']); //Returns all transactions of certain vcard | email | name
    Route::get('/vcards/myTransactions', [TransactionController::class, 'MyTransactionsType']); //Returns vcard's transactions with type (Credit or Debit)
    
    Route::post('/vcards/verifyPassword', [VcardController::class, 'verifyPassword']); //Verifies password
    Route::post('/vcards/verifyPin', [VcardController::class, 'verifyPin']); //Verifies pin
    Route::delete('/ownVcard', [VcardController::class, 'deleteOwnVcard']); //Deletes own vcard
    Route::get('/transactions/{id}', [TransactionController::class, 'show']); //Returns the transaction


    Route::delete('/myVcard', [VCardController::class, 'deleteVcardMobile']); //Deletes vcard 

    Route::resource('vcards', VCardController::class)->except('store');
});

Route::fallback(function () {
    return response()->json(['status' => 'error', 'message' => 'Route not found'], 404);
});
