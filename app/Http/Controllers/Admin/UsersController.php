<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Service\Sender\Sender;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
    public $roles = ['user', 'admin'];


    public function index(Request $request)
    {

        $limit = $request->get('limit') ?? 20;
        $sort = $request->get('sort') ?: 'id';
        $direction = $request->get('direction') ?: 'DESC';
        $users = User::query()
            ->orderBy($sort, $direction)
            ->paginate($limit);

        $users->appends($request->except(['page', '_token']));
        return view('admin.users.index', [
            'users' => $users,
        ]);
    }

    public function blocking($id)
    {
        $user = \App\Models\User::find($id);
        $user->active = $user->active == 1 ? 0 : 1;
        $user->save();

        return redirect()->route('users.index');
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
     * @param \Illuminate\Http\Request $request
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
            return redirect()->route('users.index')->with('error', 'User with same email already in use');
        }
        if ($create) {
            $view = view('mail.adminregister', [
                'data' => [
                    'name' => $data['name'],
                    'pass' => $pass,
                    'domain' => $_SERVER['SERVER_NAME'],
                ],
            ])->render();
            Sender::send($create->email, 'Registration email', $view);
        }

        return redirect()->route('users.index')->with('ok', 'User created.');
    }


    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = \App\Models\User::find($id);
        $title = 'Edit user '.$user->name;

        return view('admin.users.edit', ['user' => $user, 'roles' => $this->roles])->with('title', $title);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(UserRequest $request, $id)
    {
        $user = new User();
        try {
            $user->updateUser($request->all(), $id);
        } catch (\Exception $e) {
            $message = $e->getMessage();

            return redirect()->route('users.index')->with('error', $message);
        }

        return redirect()->route('users.index')->with(
            [
                'ok' => 'User updated.',
            ]
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::find($id);
        if (!$user) {
            return redirect()->route('users.index')->with(
                [
                    'error' => 'User not found.',
                ]
            );
        }
        if ($user->id != Auth::id()) {
            $user->delete();
        }

        return redirect()->route('users.index')->with(
            [
                'ok' => 'User deleted.',
            ]
        );
    }

}
