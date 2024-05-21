<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite ;
use App\Models\User; // Make sure to include the User model
use Illuminate\Support\Facades\Auth;
use Exception;

class GoogleLogin extends Controller
{
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function redirectToGoogle()
    {
        $redirectUrl = Socialite::driver('google')->stateless()->user();
        return response()->json($redirectUrl);
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            $user = Socialite::driver('google')->user();
            dd($user); // Dump and die to inspect $user object

            // Rest of your code...
        } catch (Exception $e) {
            return response()->json(['error' => 'Unable to authenticate the user.'], 500);
        }
    }
}
