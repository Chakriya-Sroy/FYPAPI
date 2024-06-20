<?php

use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

require __DIR__.'/auth.php';

// Route::get('/reset-password',function(){
//     return view('reset_password');
// })->name('reset-password');

Route::get('/reset-password/{token}',[PasswordResetLinkController::class,'resetPasswordLoad'])->name('reset-password-view');
Route::post('/reset-password', [NewPasswordController::class, 'store'])->middleware('guest')->name('password.store');
Route::get('/verify/{token}',[RegisteredUserController::class,'verifyEmail'])->name('verify-email');
Route::post('/updateEmailVerify',[RegisteredUserController::class,'updateEmailVerifiedAt'])->name('update-verify-email');