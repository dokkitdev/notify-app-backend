@extends('layouts.app')

@section('content')

    <div class="container">

        <h2>Users</h2>

        <a class="btn btn-primary" href="{{route('users.create')}}">Create user</a>
        <table class="table">
            <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Name</th>
                <th scope="col">Actions</th>
            </tr>
            </thead>
            <tbody>

                @foreach ($usersArray as $user)
                        <tr>
                            <th scope="row">{{$user->id}}</th>
                            <td>{{$user->name}}/<span {{($user->role=='admin')?'class=text-danger':''}}>{{$user->role}}</span></td>
                            <td><a href="{{route('users.edit',$user['id'])}}">Edit</a>
                                <a href="{{route('users.destroy',$user['id'])}}">Destroy</a>
                            </td>
                        </tr>
                @endforeach

            </tbody>
        </table>

        {{ $usersArray->links() }}

    </div>

@endsection