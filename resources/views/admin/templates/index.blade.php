@extends('layouts.app')

@section('content')

    <div class="container">

        <h1>Templates</h1>
        @foreach ($templatesGroups as $tg)
            <div class="content">
                <h3>{{$tg->customer_group_tag}}</h3>
                <div class="border">
                    <table class="table">
                        @foreach ($tg['templates'] as $template)
                        <tr>
                            <td>Letter {{$template->state}}</td>
                            <td>
                                {{$terms[$template->term]}}
                            </td>
                            <td>
                                @if($template->name=='')<a class="btn btn-danger" href="/admin/template/{{$template->id}}" title="Create template"><i class="fas fa-plus"></i></a>@endif
                            </td>
                            <td>
                                @if($template->name!='')<a class="btn btn-primary" href="/admin/template/{{$template->id}}/edit" title="Edit template"><i class="fas fa-pen"></i></a>@endif
                            </td>
                            <td>
                                @if($template->name!='')<a class="btn btn-dark" href="/admin/template/{{$template->id}}/email" title="Email to Me"><i class="far fa-envelope"></i></a>@endif
                            </td>
                        </tr>
                        @endforeach
                    </table>
                </div>

            </div>
        @endforeach

    </div>
    <div class="container">
        <h2>Housing Authorities</h2>
        <div class="content">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
            {!! Form::open(['url' => '/admin/housingtemplategroup', 'method' => 'POST','enctype'=>'multipart/form-data']) !!}
            <div class="form-group">
                {!! Form::label('Customer', 'Customer') !!}
                {!! Form::select('customer',$customers,'',['class'=>'form-control']) !!}
            </div>
            {!! Form::submit('Add new', ['class'=>'btn btn-primary float-right mb-3']) !!}
        </div>
    </div>
    <div class="container">
    @foreach ($housingTemplatesGroups as $tg)
        <div class="content">
            <h3>{{$tg->company_name}}</h3>
            <div class="border">
                <table class="table">
                    @foreach ($tg['housingTemplates'] as $housingTemplate)
                        <tr>
                            <td>Letter {{$housingTemplate->state}}</td>
                            <td>
                                @if($housingTemplate->name=='')<a class="btn btn-danger" href="/admin/housingtemplate/{{$template->id}}" title="Create template"><i class="fas fa-plus"></i></a>@endif
                            </td>
                            <td>
                                @if($housingTemplate->name!='')<a class="btn btn-primary" href="/admin/housingtemplate/{{$template->id}}/edit" title="Edit template"><i class="fas fa-pen"></i></a>@endif
                            </td>
                            <td>
                                @if($housingTemplate->name!='')<a class="btn btn-dark" href="/admin/housingtemplate/{{$template->id}}/email" title="Email to Me"><i class="far fa-envelope"></i></a>@endif
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>

        </div>
    @endforeach
    </div>

@endsection