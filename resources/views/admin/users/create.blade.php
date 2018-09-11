@extends('admin.tpl.wrapper')

@section('content')

    <div class="container">

        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach

        <h2>Create new user</h2>
        {!! Form::open(['url' => '/admin/users', 'method' => 'POST','enctype'=>'multipart/form-data']) !!}

        <div class="form-group">
            {!! Form::label('Name', 'Name') !!}
            {!! Form::text('name','',['class'=>'form-control']) !!}
        </div>
        <div class="form-group">
            {!! Form::label('Email', 'Email') !!}
            {!! Form::text('email','',['class'=>'form-control']) !!}
        </div>


        <div class="form-group">
            {!! Form::label('role', 'Role') !!}
            <select name="role" class="form-control">
                @foreach($roles as $value)
                    <option name="{{$value}}">{{$value}}</option>
                @endforeach
            </select>
        </div>
        {!! Form::submit('Create', ['class'=>'btn btn-primary']) !!}
    </div>

@endsection
