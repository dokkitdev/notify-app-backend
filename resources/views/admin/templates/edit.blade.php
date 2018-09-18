@extends('layouts.app')

@section('content')


        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach

        <h2>Edit {{$template['name']}}</h2>
        {!! Form::open(['url' => '/admin/template/'.$template->id, 'method' => 'PUT','enctype'=>'multipart/form-data']) !!}

        <div class="form-group">
            {!! Form::label('Term', 'Term') !!}
            {!! Form::select('term',$terms,$template->term,['class'=>'form-control']) !!}
        </div>
        <div class="form-group">
            {!! Form::label('Name', 'Name') !!}
            {!! Form::text('name',$template->name,['class'=>'form-control', 'disabled'=>'disabled']) !!}
        </div>
        <div class="form-group">
            {!! Form::label('Subject', 'subject') !!}
            {!! Form::text('subject',$ses['SubjectPart'],['class'=>'form-control']) !!}
        </div>
        <div class="form-group">
            {!! Form::label('Html body', 'html_body') !!}
            {!! Form::textarea('html_body',$ses['HtmlPart'],['class'=>'form-control']) !!}
        </div>
       {{--<div class="form-group">
            {!! Form::label('plaintext_body', 'plaintext_body') !!}
            {!! Form::textarea('plaintext_body',$ses['TextPart'],['class'=>'form-control']) !!}
        </div>--}}
        <div class="form-group">
            {!! Form::label('HtmlToPDF', 'HtmlToPDF') !!}
            {!! Form::textarea('html_pdf',$template->html_pdf,['class'=>'form-control']) !!}
        </div>

        {!! Form::submit('Update', ['class'=>'btn btn-primary float-right mb-3']) !!}

@endsection
