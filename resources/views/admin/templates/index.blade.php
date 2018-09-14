@extends('layouts.app')

@section('content')

    <div class="container">

        <h2>Templates</h2>
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

@endsection