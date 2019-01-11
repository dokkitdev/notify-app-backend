<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResets;
use App\Service\Sender\Sender;
use App\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function sendResetLinkEmailBySendGrid(Request $request)
    {
        $this->validateEmail($request);

        $email = $request->get('email');
        $user = User::where('email', '=', $email)->first();
        if ($user) {
            $password_reset = PasswordResets::where('email', '=', $email)->first();
            if (!$password_reset) {
                $password_reset = PasswordResets::create([
                    'email' => $email,
                    'token' => md5(uniqid('password_reset_token', true)),
                ]);
            }
            $view = view('mail.resetpassword', [
                'data' => [
                    'name' => $user->name,
                    'token' => $password_reset->token,
                ]
            ])->render();
            Sender::send($email, 'Resetting email', $view);
            $response = Password::RESET_LINK_SENT;

        } else {
            $response = Password::INVALID_USER;
        }

        return $response == Password::RESET_LINK_SENT
            ? $this->sendResetLinkResponse($response)
            : $this->sendResetLinkFailedResponse($request, $response);
    }


}
