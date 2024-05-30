<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\GoogleLogin;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\Merchance\CollecterController;
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
use Laravel\Socialite\Facades\Socialite;

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

Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
                ->middleware('guest')
                ->name('password.email');
                
Route::post('/reset-password', [NewPasswordController::class, 'store'])
                ->middleware('guest')
                ->name('password.store');
                
Route::get('/auth/google', [GoogleLogin::class, 'redirectToGoogle'])->middleware('guest');
Route::get('/auth/google/callback', [GoogleLogin::class, 'handleGoogleCallback'])->middleware('guest');


Route::middleware(['auth:sanctum'])->group(function(){
    //User Route
    Route::get('/user/list',[AdminController::class,'index'])->name('get.all.user');
    Route::get("user",[UserController::class,'index'])->name('user');
    Route::get("user/assign/customers",[CollecterController::class,'viewAssignCustomer'])->name('customer.assign.to.user.as.collector');
    Route::get("user/assign/receivables",[CollecterController::class,'viewtotalReceivableofAssignCustomer'])->name('view.assign.customer.recievable');
    Route::get("user/assign/upcoming/receivables",[CollecterController::class,'getUpcomingAssignReceivableofAssignCustomer'])->name('get.upcoming.assign.receivables.ofcustomer');
    Route::get('/user/receivables/upcoming',[ReceivableController::class,'upcoming'])->name('upcoming.receivable');
    Route::get('/user/receivables/overdue',[ReceivableController::class,'overdue'])->name('upcoming.receivable');
    Route::get('/user/payables/upcoming',[PayableController::class,'upcoming'])->name('upcoming.receivable');
    Route::get('/user/payables/overdue',[PayableController::class,'overdue'])->name('upcoming.receivable');
    Route::get('/customer/get_assign_customer_to_collector',[CustomerController::class,'getAssignedCustomers'])->name('get.assign.customer');
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
   //Route::post('/collector/invitation',[InvitationController::class,'store'])->name('collector.invatation');
  
   Route::post('/invitations', [InvitationController::class, 'sendInvitation'])->name('request.collector.invitation');
   Route::post('/invitations/respond', [InvitationController::class, 'respondInvitation'])->name('respond.collector.invitation');
   Route::post('/invitations/cancel',[InvitationController::class,'cancelInvitation'])->name('cancel.collector.invitation');
   Route::post('/collector/remove',[InvitationController::class,'remove'])->name('collector.remove');

   Route::post('/collector/customer/assign',[CollecterController::class,'assign'])->name('collector.assign');
   Route::post('/collector/customer/unassign',[CollecterController::class,'unassign'])->name('collector.assign');
   
   // show the invitation that user receive
   Route::get('user/show/invitation',[InvitationController::class,'showReceivedInvitation'])->name('show.user.invitation');
   // show the invitation that user request
   Route::get('user/show/request',[InvitationController::class,'showRequestInvitation'])->name('show.user.request');

   // subscription plan
   Route::post('/subscription',[SubscriptionController::class,'store'])->name('subscription');
   Route::patch('/subscription/update',[SubscriptionController::class,'update'])->name('subscription.update');

 

});
