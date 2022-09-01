@extends('layouts.app')

@section('content')

    @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
    @endforeach

    <h2>Edit {{$template->title}}</h2>
    {!! Form::open(['url' => route('templates.edit', ['id' => $template->id]), 'method' => 'PUT','enctype'=>'multipart/form-data']) !!}

    <div class="form-group">
        {!! Form::label('Term', 'Term') !!}
        {!! Form::text('term',$template->term . ($template->term > 1 ? ' weeks' : ' week'),['class'=>'form-control', 'disabled'=>'disabled']) !!}
    </div>
    <div class="form-group">
        {!! Form::label('Name', 'Name') !!}
        {!! Form::text('name',$template->title,['class'=>'form-control', 'disabled'=>'disabled']) !!}
    </div>
    @if ($template->is_html)
        <div class="form-group">
            {!! Form::label('Subject', 'subject') !!}
            {!! Form::text('subject',$template->subject,['class'=>'form-control']) !!}
        </div>
        <div class="form-group">
            {!! Form::label('Html body', 'html_body') !!}
            {!! Form::textarea('html_body',$template->html_body,['class'=>'form-control', 'required' => 'required']) !!}
        </div>
    @endif
    <div class="form-group">
        {!! Form::label('file', 'Docx') !!}
        {!! Form::file('file', ['class' => 'w-100', 'accept' => '.docx']) !!}
    </div>

    {!! Form::submit('Update', ['class'=>'btn btn-primary float-right mb-3']) !!}
    {{ Form::close() }}
@endsection
