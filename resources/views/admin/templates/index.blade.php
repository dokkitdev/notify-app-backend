@extends('admin.tpl.wrapper')

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
                                @if($template->name=='')<a href="/admin/template/{{$template->id}}">Create</a>@endif
                            </td>
                            <td>
                                @if($template->name!='')<a href="/admin/template/{{$template->id}}/edit">Edit</a>@endif
                            </td>
                            <td>
                                @if($template->name!='')<a href="/admin/template/{{$template->id}}/email">Email to Me</a>@endif
                            </td>
                        </tr>
                        @endforeach
                    </table>
                </div>

            </div>
        @endforeach

    </div>

@endsection