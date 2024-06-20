<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordReset;
use App\Models\User;
use App\Models\UserandRole;
use App\Notifications\VerifyEmailNotification;
use App\Traits\HttpResponse;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    use HttpResponse;
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
      
        // event(new Registered($user));

        // Auth::login($user);
        $token =$user->createToken('api-token')->plainTextToken;

        // create token in passwordReset
        PasswordReset::updateOrCreate(
            ['email' => $request->email],
            [
                'email' => $request->email,
                'token' => $token,
                'created_at' => now()
            ]
        );
        $user->notify(new VerifyEmailNotification($token));
        // update the role and user table
        $userandrole=UserandRole::create(['user_id'=>$user->id,'role_id'=>1]);
        //return $this->success(["user"=>$user,"role"=>$userandrole,"token"=>$token],"Register Succesfully");
        return $this->success('','Please check your mail to verify your email');

    }

    public function verifyEmail(Request $request)
    {
        $resetData = PasswordReset::where('token', $request->token)->first();

        if (!empty($resetData)) {
            $user = User::where('email', $resetData->email)->first();
            if (!empty($user)) {
                return view('verify_email', ['user' => $user]);
            }
        }
        return view('404');
    }

    public function updateEmailVerifiedAt(Request $request){
        $user=User::find($request->id);
        $user->email_verified_at=now();
        $user->save();
        return redirect()->back()->with('message',"Your email has been verify successfully");
    }
}
