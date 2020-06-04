@extends('layouts.app')

@section('content')
        <h1>Templates</h1>
        <h4 class="pt-3">Private</h4>
        @foreach ($templatesGroups as $tg)
            <div class="content">
                <table class="table table-bordered">
                    @foreach ($tg['templates'] as $template)
                    <tr>
                        <td>Letter {{$template->state}}</td>
                        <td>
                            {{$terms[$template->term]}}
                        </td>
                        <td>
                            @if($template->name=='')<a class="btn btn-danger" href="/admin/template/{{$template->id}}" title="Create template"><i class="fas fa-plus"></i></a>@endif
                            @if($template->name!='')<a class="btn btn-primary" href="/admin/template/{{$template->id}}/edit" title="Edit template"><i class="fas fa-pen"></i></a>@endif
                            @if($template->name!='')
                                @if(strpos($template->name,'Letter'))
                                    <button class="btn btn-danger"> <i class="fas fa-exclamation-triangle"></i></button>
                                @else
                                    <a class="btn btn-dark" href="/admin/template/{{$template->id}}/email" title="Email to Me"><i class="far fa-envelope"></i></a>
                                @endif
                            @endif
                            @if($template->html_pdf != '')<a class="btn btn-dark" href="/admin/template/{{$template->id}}/download-pdf" title="Download PDF template">PDF</a>@endif
                        </td>
                    </tr>
                    @endforeach
                </table>
            </div>
        @endforeach

    <div class="clearfix"></div>

        <h4 class="pt-5">Housing Authorities</h4>
        <div class="content">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
            {!! Form::open(['url' => '/admin/housingtemplategroup', 'method' => 'POST','enctype'=>'multipart/form-data']) !!}
            <div class="form-group">
                {!! Form::label('Customer', 'Customer') !!}
                {!! Form::select('customer',$customers,'',['class'=>'form-control']) !!}
            </div>
            {!! Form::submit('Add new', (count($customers) > 0 ? (['class'=>'btn btn-primary float-right mb-3']) : (['class'=>'btn btn-primary float-right mb-3', 'disabled' => 'disabled', 'title' => 'No customers available']))) !!}
            {{ Form::close() }}
        </div>

    <div class="clearfix"></div>

    @foreach ($housingTemplatesGroups as $tg)
        <div class="content">
        <h3>{{$tg->company_name}}</h3>
            <table class="table table-bordered">
                @foreach ($tg['housingTemplates'] as $housingTemplate)
                    <tr>
                        <td>Letter {{$housingTemplate->state}}</td>
                        <td>
                            @if($housingTemplate->name=='')<a class="btn btn-danger" href="/admin/housingtemplate/{{$housingTemplate->id}}" title="Create template"><i class="fas fa-plus"></i></a>@endif
                            @if($housingTemplate->name!='')<a class="btn btn-primary" href="/admin/housingtemplate/{{$housingTemplate->id}}/edit" title="Edit template"><i class="fas fa-pen"></i></a>@endif
                            @if($housingTemplate->name!='')
                                @if(strpos($housingTemplate->name,'Letter'))
                                    <button class="btn btn-danger"> <i class="fas fa-exclamation-triangle"></i></button>
                                @else
                                    <a class="btn btn-dark" href="/admin/housingtemplate/{{$housingTemplate->id}}/email" title="Email to Me"><i class="far fa-envelope"></i></a>
                                @endif
                            @endif
                            @if($housingTemplate->html_pdf != '')<a class="btn btn-dark" href="/admin/housingtemplate/{{$housingTemplate->id}}/download-pdf" title="Download PDF template">PDF</a>@endif
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    @endforeach

    <div class="clearfix"></div>
@endsection
