<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordReset;
use App\Models\User;
use App\Notifications\CustomResetPasswordNotification;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use PharIo\Manifest\Url;
use Symfony\Component\Mailer\Mailer;

class PasswordResetLinkController extends Controller
{
    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {

        // Validate the email address
        $request->validate(['email' => 'required|email']);

        // Find the user by email
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $token = $user->createToken('forgot-password')->plainTextToken;
        $user->notify(new CustomResetPasswordNotification($token));
        PasswordReset::updateOrCreate(
            ['email' => $request->email],
            [
                'email' => $request->email,
                'token' => $token,
                'created_at' => now()
            ]
        );
        return response()->json(['message' => 'Please check your mail to reset your password']);



        // $request->validate([
        //     'email' => ['required', 'email'],
        // ]);

        // // We will send the password reset link to this user. Once we have attempted
        // // to send the link, we will examine the response then see the message we
        // // need to show to the user. Finally, we'll send out a proper response.
        // $status = Password::sendResetLink(
        //     $request->only('email')
        // );

        // if ($status != Password::RESET_LINK_SENT) {
        //     throw ValidationException::withMessages([
        //         'email' => [__($status)],
        //     ]);
        // }

        // return response()->json(['status' => __($status)]);
    }
    public function resetPasswordLoad(Request $request)
    {
        $resetData = PasswordReset::where('token', $request->token)->first();

        if (!empty($resetData)) {
            $user = User::where('email', $resetData->email)->first();
            if (!empty($user)) {
                return view('reset_password', ['user' => $user]);
            }
        }

        return view('404');
    }
}
