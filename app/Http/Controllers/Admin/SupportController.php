<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Service\Sender\Sender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use function redirect;
use function view;

class SupportController extends Controller
{
    public function getSupport(Request $request)
    {
        $data = $request->all();

        if (!$data['message'] || trim($data['message']) == '') {
            return redirect()->route('dashboard')->with('error', 'Empty message!');
        }
        try {
            $view = view('mail.support', [
                'data' => [
                    'name' => Auth::user()->name,
                    'email' => Auth::user()->email,
                    'message' => $data['message'],
                ],
            ])->render();
            Sender::send('support@dokkit.co.uk', 'Notify App', $view);

            return redirect()->route('dashboard')->with('ok', 'Your email sent.');
        } catch (\Exception $e) {
            $error = $e->getMessage();

            return redirect()->route('dashboard')->with('error', $error);
        }
    }
}
