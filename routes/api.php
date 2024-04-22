<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Merchance\CustomerController;
use App\Http\Controllers\Merchance\ReceivableController;
use App\Http\Controllers\Merchance\ReceivablePaymentController;
use App\Http\Controllers\Merchance\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/register', [RegisteredUserController::class, 'store'])
                ->middleware('guest')
                ->name('register');
Route::post('/login', [AuthenticatedSessionController::class, 'store'])
                ->middleware('guest')
                ->name('login');

Route::middleware(['auth:sanctum'])->group(function(){
    //User Route
    Route::get("user/{user}",[UserController::class,'show'])->name('user');
    //customer Route
    Route::prefix('customer')->group(function(){
        Route::get('/list',[CustomerController::class,'index'])->name("customer.list");
        Route::post('/create',[CustomerController::class,'store'])->name("customer.create");
        Route::patch('/update/{customer}',[CustomerController::class,'update'])->name("customer.update");
        Route::get('/view/{customer}',[CustomerController::class,'show'])->name("customer.view");
        Route::delete('/delete/{customer}',[CustomerController::class,'destroy'])->name("customer.delete");
        Route::prefix('{customer}/receivable')->group(function() {
            Route::get('/list', [ReceivableController::class,'index'])->name("customer.receivable.list");
            Route::post('/create',[ReceivableController::class,'store'])->name("customer.receivable.create");
            Route::patch('/update/{receivable}', [ReceivableController::class,'update'])->name("customer.receivable.update");
            Route::get('/view/{receivable}', [ReceivableController::class,'show'])->name("customer.receivable.view");
            Route::delete('/delete/{receivable}', [ReceivableController::class,'destroy'])->name("customer.receivable.delete");
        });
        Route::prefix('receivable/{receivable}/payment')->group(function() {
            Route::post('/create', [ReceivablePaymentController::class, 'store'])->name('receivables.payments.store');
            Route::get('/list', [ReceivablePaymentController::class, 'show'])->name('receivables.payments.show');
        }); 
    });    
  //  Route::post('/collector/invitation',);
});
