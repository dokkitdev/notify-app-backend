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

        if(!$data['message']||trim($data['message'])=='')return false;
        $message=false;
        try {
            Mail::to(getenv('MAIL_FROM_ADDRESS'))->send(new GetSupport(
                [
                    'name' => Auth::user()->name,
                    'email' => Auth::user()->email,
                    'message' => $data['message']
                ]));
        }catch (\Exception $e){
            $message=$e->getMessage();
        }


        return redirect()->back()->with('error',$message);
    }
}
