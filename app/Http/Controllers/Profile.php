<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;

class Profile extends Controller
{
    public function getProfile(Request $request)
    {

        $title = 'Profile';
        return view('auth.profile', [
            'user' => User::find(Auth::id()),
        ])->with('title', $title);
    }

    public function saveProfile(Request $request)
    {
        $data = $request->all();
        $newPass = $data['newpass'];
        $repeatPass = $data['repeatpass'];
        if ($newPass !== $repeatPass) {
            return redirect()->back()->with([
                'newPass' => $newPass,
                'repeatPass' => $repeatPass,
                'error' => 'Password are not same',
            ]);
        }
        $user = User::find(Auth::id());
        //$user->name=$data['name'];
        $user->password = Hash::make($newPass);
        $user->save();
        return redirect()->back()->with([
            'ok' => 'You are successfully change password',
        ]);
    }
}
