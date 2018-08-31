<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class Profile extends Controller
{
    public function getProfile()
    {
        $title='Profile';
        return view('auth.profile', ['user'=>User::find(Auth::id())])->with('title',$title);
    }
    public function saveProfile($data){
        $user=User::find(Auth::id());
        $user->name=$data['name'];
        $user->password=Hash::make($data['newpass']);
        $user->save();
    }
}
