<?php

namespace App\Http\Controllers;

use App\Mail\GetSupport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class SupportController extends Controller
{
    public function getSupport(Request $request)
    {
        $data=$request->all();

        if(!$data['message']||trim($data['message'])=='')return redirect()->back()->with('error','Empty message!');
        $error=false;
        $message=false;
        try {
            Mail::to(getenv('MAIL_FROM_ADDRESS'))->send(new GetSupport(
                [
                    'name' => Auth::user()->name,
                    'email' => Auth::user()->email,
                    'message' => $data['message']
                ]));
            $message='Sended';
        }catch (\Exception $e){
            $error=$e->getMessage();
        }
        return redirect()->back()->with('error',$error)->with('ok',$message);
    }
}
