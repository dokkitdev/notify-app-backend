@extends('layouts.app')

@section('content')

    <div class="container">



        <h2>Users</h2>
        @if (session('error'))
            <div class="alert alert-error">
                {{ session('error') }}
            </div>
        @endif
        <div class="float-right mb-3">
        <a class="btn btn-primary" href="{{route('users.create')}}">Create user</a>
</div>
        <table class="table table-bordered">
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
                            <td><a class="btn btn-primary" href="{{route('users.edit',$user['id'])}}"><i class="fa fa-eye" aria-hidden="true">Edit</a>
                                <a class="btn btn-danger" href="{{route('users.destroy',$user['id'])}}">Destroy</a>
                            </td>
                        </tr>
                @endforeach

            </tbody>
        </table>

        {{ $usersArray->links() }}

    </div>

@endsection