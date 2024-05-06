<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\Merchance\CustomerController;
use App\Http\Controllers\Merchance\PayableController;
use App\Http\Controllers\Merchance\PayablePaymentController;
use App\Http\Controllers\Merchance\ReceivableController;
use App\Http\Controllers\Merchance\ReceivablePaymentController;
use App\Http\Controllers\Merchance\SupplierController;
use App\Http\Controllers\Merchance\UserController;
use App\Http\Controllers\SubscriptionController;
use App\Models\PayablePayment;
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
Route::post('/reset-password', [NewPasswordController::class, 'store'])
                ->middleware('guest')
                ->name('password.store');
Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
                ->middleware('guest')
                ->name('password.email');


Route::middleware(['auth:sanctum'])->group(function(){
    //User Route
    Route::get("user",[UserController::class,'index'])->name('user');
    //customer Route
    Route::prefix('customer')->group(function(){
        Route::get('/list',[CustomerController::class,'index'])->name("customer.list");
        Route::post('/create',[CustomerController::class,'store'])->name("customer.create");
        Route::patch('/update/{id}',[CustomerController::class,'update'])->name("customer.update");
        Route::get('/view/{id}',[CustomerController::class,'show'])->name("customer.view");
        Route::delete('/delete/{id}',[CustomerController::class,'destroy'])->name("customer.delete");
        Route::get('transaction/{id}',[CustomerController::class,'transaction'])->name("customer.transaction");

        Route::prefix('/receivable')->group(function() {
            Route::get('/list', [ReceivableController::class,'index'])->name("customer.receivable.list");
            Route::post('/create',[ReceivableController::class,'store'])->name("customer.receivable.create");
            Route::patch('/update/{id}', [ReceivableController::class,'update'])->name("customer.receivable.update");
            Route::get('/view/{id}', [ReceivableController::class,'show'])->name("customer.receivable.view");
            Route::delete('/delete/{id}', [ReceivableController::class,'destroy'])->name("customer.receivable.delete");
        });
        Route::prefix('receivable/payment')->group(function() {
            Route::post('/create', [ReceivablePaymentController::class, 'store'])->name('receivables.payments.store');
            Route::get('/list', [ReceivablePaymentController::class, 'show'])->name('receivables.payments.show');
        }); 
        Route::get('/receivable/transaction',[ReceivableController::class,"transaction"]);
    }); 
    
    Route::prefix('supplier')->group(function(){
        Route::get('/list',[SupplierController::class,'index'])->name('supplier.list');
        Route::post('/create',[SupplierController::class,'store'])->name('supplier.store');
        Route::patch('/update/{id}',[SupplierController::class,'update'])->name('supplier.update');
        Route::get('/view/{id}',[SupplierController::class,'show'])->name('supplier.view');
        Route::delete('/delete/{id}',[SupplierController::class,'destroy'])->name('supplier.delete');
        Route::get('/transaction/{id}',[SupplierController::class,'transaction'])->name("supplier.transaction");

        Route::prefix('/payable')->group(function(){
            Route::get('/list',[PayableController::class,'index'])->name('supplier.payable.list');
            Route::post('/create',[PayableController::class,'store'])->name('supplier.payable.store');
            Route::get('/view/{id}',[PayableController::class,'show'])->name('supplier.payable.view');
        });
        Route::prefix('payable/payment')->group(function() {
            Route::post('/create', [PayablePaymentController::class, 'store'])->name('payables.payments.store');
            Route::get('/list', [PayablePaymentController::class, 'show'])->name('payables.payments.show');
        }); 
    });
   // meaning user 1 invite user 2 as there collector and user 2 accept the request 
   Route::post('/collector/invitation',[InvitationController::class,'store'])->name('collector.invatation');
   Route::post('/collector/remove',[InvitationController::class,'remove'])->name('collector.remove');
   Route::post('/collector/customer/assign',[InvitationController::class,'assign'])->name('collector.assign');
   Route::post('/collector/customer/unassign',[InvitationController::class,'unassign'])->name('collector.assign');


   // subscription plan
   Route::post('/subscription',[SubscriptionController::class,'store'])->name('subscription');

});
