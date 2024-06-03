<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User; // Make sure to include the User model
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\Log;

class GoogleLogin extends Controller
{
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function redirectToGoogle()
    {
        // $redirectUrl = Socialite::driver('google')->stateless()->user();
        // return response()->json($redirectUrl);
        $redirectUrl = Socialite::driver('google')->stateless()->redirect()->getTargetUrl();
        return response()->json(['redirect_url' => $redirectUrl]);
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            // Retrieve user information from Google
            $googleUser = Socialite::driver('google')->stateless()->user();

            // Find or create the user in your database
            $user = User::firstOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'name' => $googleUser->getName(),
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'email_verified_at' => now()
                ]
            );

            // Log the user in
            //Auth::login($user, true);

            // Redirect to the intended page
            return response()->json('login successfully');

        } catch (\Exception $e) {
            Log::error('Google Authentication Error: ' . $e->getMessage());

            // Redirect to the login page with an error message
           // return redirect('/login')->with('error', 'Failed to authenticate using Google. Please try again.');
        }
    
    }
}
