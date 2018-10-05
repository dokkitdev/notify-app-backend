@extends('layouts.app')

@section('content')
        <h2>Users</h2>
        <div class="float-right mb-3">
        <a class="btn btn-primary" href="{{route('users.create')}}">Create user</a>
        </div>

        <table class="table table-bordered">
            <thead>
            <tr>
                <th scope="col">#</th>
                <th scope="col">Name</th>
                <th scope="col">E-mail</th>
                <th scope="col">Role</th>
                <th scope="col"></th>
            </tr>
            </thead>
            <tbody>

                @foreach ($usersArray as $user)
                        <tr>
                            <th scope="row">{{$user->id}}</th>
                            <td>{{$user->name}}</td>
                            <td>{{$user->email}}</td>
                            <td><span {{($user->role=='admin')?'class=text-danger':''}}>{{$user->role}}</span></td>
                            <td>
                                <a href="{{route('users.edit',$user['id'])}}"><i class="fas fa-pen" style="color: #2b2d83; width: 25px" title="Edit"></i></a>
                                <a href="{{route('users.destroy',$user['id'])}}"><i class="fas fa-trash" style="color: #dc3545; width: 25px" title="Delete"></i></a>
                            </td>
                        </tr>
                @endforeach

            </tbody>
        </table>
        {{ $usersArray->links() }}

@endsection