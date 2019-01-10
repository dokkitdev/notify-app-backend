<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\UserRequest;
use App\Mail\AdminRegister;
use App\Service\Sender\Sender;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class UsersController extends Controller
{
    public $roles = ['user', 'admin'];

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = 'Users';

        $users = User::paginate(15);
        return view('admin.users.index', ['usersArray' => $users])->with('title', $title);
    }

    public function blocking($id)
    {
        $user = \App\User::find($id);
        $user->active = $user->active == 1 ? 0 : 1;
        $user->save();
        return redirect('admin/users');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = 'Create new user';
        return view('admin.users.create', ['roles' => $this->roles])->with('title', $title);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $user = new User();
        $data = $request->all();
        $pass = str_random(8);
        $data['password'] = Hash::make($pass);

        try {
            $create = $user->create($data);
        } catch (\Exception $error) {
            return redirect('admin/users')->with('error', 'User with same email already in use');
        }
        //$create=true;
        $message = false;
        if ($create) {
            $view = view('mail.adminregister', [
                'data' => [
                    'name' => $data['name'],
                    'pass' => $pass,
                    'domain' => $_SERVER['SERVER_NAME'],
                ]
            ])->render();
            Sender::send($create->email, 'Registration email', $view);
            die;
        }

        return redirect('admin/users')->with('error', $message);
    }


    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = \App\User::find($id);
        $title = 'Edit user ' . $user->name;
        return view('admin.users.edit', ['user' => $user, 'roles' => $this->roles])->with('title', $title);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(UserRequest $request, $id)
    {

        $user = new User();
        $message = false;
        try {
            $user->updateUser($request->all(), $id);
        } catch (\Exception $e) {
            $message = $e->getMessage();
        }
        return redirect('admin/users')->with('error', $message);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::find($id);
        if ($user->id != Auth::id()) {
            $user->delete();
        }
        return redirect('admin/users');
    }
}
