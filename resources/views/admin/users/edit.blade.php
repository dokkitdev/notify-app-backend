@extends('layouts.app')

@section('content')

    <div class="container">

        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach

        <h2>Edit user – "{{$user['name']}}"</h2>
        {!! Form::open(['url' => '/admin/users/'.$user['id'], 'method' => 'PUT','enctype'=>'multipart/form-data']) !!}

        <div class="form-group">
            {!! Form::label('Name', 'Name') !!}
            {!! Form::text('name',$user['name'],['class'=>'form-control']) !!}
        </div>

        <div class="form-group">
            {!! Form::label('Email', 'Email') !!}
            {!! Form::text('email',$user['email'],['class'=>'form-control']) !!}
        </div>


        <div class="form-group">
        {!! Form::label('role', 'Role') !!}
            <select name="role" class="form-control">
                @foreach($roles as $value)
                    <option name="{{$value}}" {{($user['role']==$value)?'selected="selected"':''}}>{{$value}}</option>
                @endforeach
            </select>
        </div>
        {!! Form::submit('Update', ['class'=>'btn btn-primary']) !!}
    </div>

@endsection
