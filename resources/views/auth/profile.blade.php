@extends('layouts.app')

@section('content')

    <div class="container">

        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach

        <div class="bounded-box">
            <h2>My profile</h2>
            {!! Form::open(['url' => '/profile', 'method' => 'PUT','enctype'=>'multipart/form-data']) !!}

            <div class="form-group">
                {!! Form::label('Name', 'Name') !!}
                {!! Form::text('name',$user['name'],['class'=>'form-control','disabled'=>'disabled']) !!}
            </div>
            <div class="form-group">
                {!! Form::label('newpass', 'New password') !!}
                {!! Form::text('newpass','',['class'=>'form-control']) !!}
            </div>
            {!! Form::submit('Update', ['class'=>'btn btn-primary float-right']) !!}
            <div class="clearfix"></div>
        </div>
    </div>

@endsection
