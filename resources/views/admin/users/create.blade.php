@extends('admin.layout')

@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <h4 class="page-title">Create new user</h4>
        </div>
        <div class="row">
            <div class="col-md-5">
                <div class="card-box clearfix">
                    @foreach ($errors->all() as $error)
                        <div class="alert alert-danger mt-1">
                            <strong>{{ $error }}</strong>
                        </div>
                    @endforeach
                    {!! Form::open(['url' => route('users.store'), 'method' => 'POST','enctype'=>'multipart/form-data']) !!}

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
                    {!! Form::submit('Create', ['class'=>'btn btn-primary float-right']) !!}
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>
@endsection
