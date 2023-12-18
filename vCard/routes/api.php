<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DefaultCategoryController;
use App\Http\Controllers\VCardController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\PiggyBankController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\PDFController;

//Rotas específicas
Route::post('/vcards/login', [AuthController::class, 'loginVcard']);
Route::post('/users/login', [AuthController::class, 'loginUser']);

Route::post('/vcards/', [VCardController::class, 'store']);
Route::post('/vcards/mobile', [VCardController::class, 'storeMobile']);


Route::get('/checkAuth', [AuthController::class, 'getAuthenticatedGuard']);

Route::get('/Unauthenticated', function () {
    return response()->json(['status' => 'error', 'message' => 'Unauthenticated'], 401);
})->name('Unauthenticated');



// Route::get('/admins', [UserController::class, 'getAdmins']); //Returns all admins
// Route::get('/vcards/search/{phone_number}', [VCardController::class, 'show']);
// Route::get('/vcards/search', [VCardController::class, 'indexBlocked']); //todos ou todos blocked ou todos unblocked
// Route::get('/transactions/search/{query}', [TransactionController::class, 'indexAllTransactions_search']); //Returns all transactions of certain vcard | email | name
// Route::get('/transactions', [TransactionController::class, 'index']); //Returns all transactions
// Route::get('/transactions/search', [TransactionController::class, 'indexAllTransactions_type']); //todos ou todos debit ou todos credit
// Route::delete('/users/{id}', [UserController::class, 'destroy']); //Deletes user
// Route::patch('/vcards/block/{phone_number}', [VCardController::class, 'changeBlock']); //Updates Block vcard
// Route::delete('/vcards/{phone_number}', [VCardController::class, 'deleteVcard']); //Deletes vcard
// Route::patch('vcards/maxDebit/{phone_number}', [VCardController::class, 'updateMaxDebit']); //Updates vcard max debit
// Route::get('/categories', [CategoryController::class, 'index']); //Returns all categories that exist in default categories
// Route::get('/categories/search', [CategoryController::class, 'indexType']); //Returns all categories that exist in default categories
// Route::get('/categories/search/{categorie}', [CategoryController::class, 'show']); //Returns the category
// Route::delete('/categories/{id}', [CategoryController::class, 'destroyCategoriesDAD']); //Deletes a default category
// Route::post('/categories', [CategoryController::class, 'store']); //Creates a new default category


Route::middleware('auth:api')->group(function () {
    // Route::post('/users/credit-vcard', [TransactionController::class, 'creditVcard']);
    // Route::get('/categories/{vcard}', [CategoryController::class, 'getAllFromVcard']); //Returns all categories of certain vcard
    Route::get('/vcards/{vcard}/photo/', [VcardController::class, 'getPhotoUrl']);

    // Route::post('/users', [UserController::class, 'store']);

    Route::get('/users/profile', [UserController::class, 'profile']);

    Route::resource('default-categories', DefaultCategoryController::class);
    Route::resource('users', UserController::class);
});

Route::middleware('auth:vcard')->group(function () {
    // Route::get('/statistics/DebitPerMonth', [StatisticsController::class, 'getStatisticsDebitPerMonth']);
    // Route::get('/statistics/DebitPerYear', [StatisticsController::class, 'getStatisticsDebitPerYear']);
    //
    // Route::get('/statistics/CreditPerMonth', [StatisticsController::class, 'getStatisticsCreditPerMonth']);
    // Route::get('/statistics/CreditPerYear', [StatisticsController::class, 'getStatisticsCreditPerYear']);
    //
    // Route::get('/statistics/MoneySpentPerCard', [StatisticsController::class, 'getMoneySpentPerCardType']);
    // Route::get('/statistics/MoneyReceivedPerCard', [StatisticsController::class, 'getMoneyReceivedPerCardType']);
    //
    // Route::get('/statistics/CategoriesSpent', [StatisticsController::class, 'getMoneySpentByCategories']);
    // Route::get('/statistics/CategoriesReceived', [StatisticsController::class, 'getMoneyReceivedByCategories']);
    //
    // Route::get('/piggy-bank', [PiggyBankController::class, 'getPiggyBank']);
    // Route::get('/piggy-bank/transactions', [PiggyBankController::class, 'getTransactions']);
    // Route::post('/piggy-bank/withdraw', [PiggyBankController::class, 'withdraw']);
    // Route::post('/piggy-bank/deposit', [PiggyBankController::class, 'deposit']);
    //
    // Route::get('/vcards/categories', [CategoryController::class, 'getMyCategories']);
    // Route::get('/vcards/profile', [VCardController::class, 'profile']);
    // Route::get('/vcards/balance', [VCardController::class, 'getBalance']);
    // // Route::get('/vcards/transactions', [TransactionController::class, 'getMyTransactions']);
    Route::post('/vcards/send', [VcardController::class, 'makeTransaction']);
    Route::get('/vcards/photo/', [VcardController::class, 'getPhotoUrl']);
    // Route::get('/myTransactions/search/{query}', [TransactionController::class, 'indexMyTransactions_search']); //Returns all transactions of certain vcard | email | name
    // Route::get('/vcards/transactions/search/{query}', [TransactionController::class, 'indexMyTransactions_search']);
    // Route::get('/vcards/myTransactions', [TransactionController::class, 'MyTransactionsType']); //Returns vcard's transactions with type (Credit or Debit)
    // Route::get('/vcards/{vcard}/transactions', [TransactionController::class, 'MyTransactionsType']); //Returns vcard's transactions with type (Credit or Debit)

    // Route::get('/vcards/mycategories', [CategoryController::class, 'getMyCategoriesDAD']); //Returns vcard's categories
    // Route::post('/vcards/mycategories', [CategoryController::class, 'storeMyCategoriesDAD']); //Creates a new category in vcard
    // Route::delete('/myCategories/{id}', [CategoryController::class, 'destroyMyCategoriesDAD']); //Deletes a category in vcard
    //
    Route::post('/vcards/verifyPassword', [VcardController::class, 'verifyPassword']); //Verifies password
    Route::post('/vcards/verifyPin', [VcardController::class, 'verifyPin']); //Verifies pin

    // Route::delete('/ownVcard', [VcardController::class, 'deleteOwnVcard']); //Deletes own vcard
    // Route::get('/transactions/{id}', [TransactionController::class, 'show']); //Returns the transaction

    // Route::delete('/myVcard', [VCardController::class, 'deleteVcardMobile']);
});


Route::middleware(['auth:api,vcard'])->group(function () {
    Route::get('/logout', [AuthController::class, 'logout']);

    // Route::get('/extract/pdf', [PDFController::class, 'index']);

    // Route::get('/vcards/mycategories', [CategoryController::class, 'getMyCategoriesDAD']); //Returns vcard's categories
    // Route::post('/vcards/mycategories', [CategoryController::class, 'storeMyCategoriesDAD']); //Creates a new category in vcard
    // Route::delete('/myCategories/{id}', [CategoryController::class, 'destroyMyCategoriesDAD']); //Deletes a category in vcard
    // Route::get('/vcards/{vcard}/transactions/search/{query}', [TransactionController::class, 'indexMyTransactions_search']);
    Route::resource('/vcards.transactions', TransactionController::class);
    Route::resource('/vcards/transactions', TransactionController::class);


    Route::resource('/vcards.categories', CategoryController::class);
    Route::resource('/vcards/categories', CategoryController::class);

    Route::resource('vcards', VCardController::class)->except('store');
    Route::resource('categories', CategoryController::class);
    Route::resource('transactions', TransactionController::class);
});

Route::fallback(function () {
    return response()->json(['status' => 'error', 'message' => 'Route not found'], 404);
});
