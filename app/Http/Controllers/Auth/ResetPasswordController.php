<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResets;
use App\Models\User;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */

    use ResetsPasswords;

    /**
     * Where to redirect users after resetting their password.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function resetPasswordOwn(Request $request)
    {
        $this->validate($request, $this->rules(), $this->validationErrorMessages());

        $token = $request->get('token') ?: null;
        $email = $request->get('email') ?: null;
        $password = $request->get('password') ?: null;
        $password_confirmation = $request->get('password_confirmation') ?: null;

        $password_reset = PasswordResets::where('email', '=', $email)
            ->where('token', '=', $token)
            ->first();
        if ($password_reset) {
            if ($password == $password_confirmation) {
                $user = User::where('email', '=', $email)->first();
                if ($user) {
                    $user->password = Hash::make($password);
                    $user->save();
                }
                $password_reset->delete();
                $response = Password::PASSWORD_RESET;
            } else {
                $response = Password::INVALID_PASSWORD;
            }
        } else {
            $response = Password::INVALID_TOKEN;
        }

        return $response == Password::PASSWORD_RESET
            ? redirect(route('login'))
            : $this->sendResetFailedResponse($request, $response);
    }
}
