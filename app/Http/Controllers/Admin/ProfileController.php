<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use function redirect;
use function view;

class ProfileController extends Controller
{
    public function getProfile(Request $request)
    {
        $title = 'Profile';

        return view('admin.profile.profile', [
            'user' => User::find(Auth::id()),
        ])->with('title', $title);
    }

    public function saveProfile(ProfileRequest $request)
    {
        $data = $request->validated();
        $password = $data['password'];
        $user = User::find(Auth::id());

        $user->password = Hash::make($password);
        $user->save();

        return redirect()->back()->with([
                                            'ok' => 'You are successfully change password',
                                        ]);
    }
}
