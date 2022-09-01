@extends('admin.layout')

@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <h4 class="page-title">Edit user – "{{$user['name']}}"</h4>
        </div>
        <div class="row">
            <div class="col-md-5">
                <div class="card-box clearfix">
                    @foreach ($errors->all() as $error)
                        <div class="alert alert-danger mt-1">
                            <strong>{{ $error }}</strong>
                        </div>
                    @endforeach
                    {!! Form::open(['url' => route('users.update', ['id' => $user->id]), 'method' => 'PUT','enctype'=>'multipart/form-data']) !!}
                    <input type="hidden" name="id" value={{$user->id}}>
                    <div class="form-group">
                        {!! Form::label('Name', 'Name') !!}
                        {!! Form::text('name',$user->name,['class'=>'form-control']) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('Email', 'Email') !!}
                        {!! Form::text('email',$user->email,['class'=>'form-control']) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('role', 'Role') !!}
                        <select name="role" class="form-control">
                            @foreach($roles as $value)
                                <option
                                    name="{{$value}}" {{($user['role']==$value)?'selected="selected"':''}}>{{$value}}</option>
                            @endforeach
                        </select>
                    </div>
                    {!! Form::submit('Update', ['class'=>'btn btn-primary float-right']) !!}
                    {{ Form::close() }}
                </div>
            </div>
        </div>
    </div>

@endsection
