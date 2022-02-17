<?php

namespace App\Http\Controllers;

use App\Mail\GetSupport;
use App\Service\Sender\Sender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class SupportController extends Controller
{
    public function getSupport(Request $request)
    {
        $data = $request->all();

        if (!$data['message'] || trim($data['message']) == '') return redirect()->back()->with('error', 'Empty message!');
        $error = false;
        $message = false;
        try {
            $view = view('mail.support', [
                'data' => [
                    'name' => Auth::user()->name,
                    'email' => Auth::user()->email,
                    'message' => $data['message']
                ]
            ])->render();
            Sender::send('support@dokkit.co.uk', 'Notify App', $view);
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }
        return redirect()->back()->with('error', $error)->with('ok', $message);
    }
}
