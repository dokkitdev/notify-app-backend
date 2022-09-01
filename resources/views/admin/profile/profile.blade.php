@extends('admin.layout')

@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <h4 class="page-title">My profile</h4>
        </div>
        <div class="row">
            <div class="col-md-5">
                <div class="card-box clearfix">
                    @if(session('ok'))
                        <div class="alert alert-success mt-1">
                            <strong>{{ session('ok') }}</strong>
                        </div>
                    @endif

                    @foreach ($errors->all() as $error)
                        <div class="alert alert-danger mt-1">
                            <strong>{{ $error }}</strong>
                        </div>
                    @endforeach
                    {!! Form::open(['url' => route('profile'), 'method' => 'POST','enctype'=>'multipart/form-data']) !!}
                    <div class="form-group">
                        {!! Form::label('Name', 'Name') !!}
                        {!! Form::text('name',$user['name'],['class'=>'form-control','disabled'=>'disabled']) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('password', 'New password') !!}
                        {!! Form::text('password', session('password'),['class'=>'form-control']) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('password_confirmation', 'Repeat password') !!}
                        {!! Form::text('password_confirmation', session('password_confirmation'),['class'=>'form-control']) !!}
                    </div>
                    {!! Form::submit('Update', ['class'=>'btn btn-primary float-right']) !!}
                </div>
            </div>
        </div>
    </div>
@endsection
