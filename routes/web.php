<?php

use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
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

Route::get('/reset-password/{token}/{expireIn}',[PasswordResetLinkController::class,'resetPasswordLoad'])->name('reset-password-view');
Route::post('/reset-password', [NewPasswordController::class, 'store'])->middleware('guest')->name('password.store');