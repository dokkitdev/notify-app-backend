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
                            <form action="/admin/template/{{$template->id}}" method="POST" enctype="multipart/form-data">
                                {{ csrf_field() }}
                        <tr>
                            <td>Letter {{$template->state}}</td>
                            <td>
                                <select name="term">
                                    @foreach($terms as $termKey=>$term)
                                        <option value="{{$termKey}}" {{$termKey==$template->term?'selected=selected':''}}>{{$term}}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="file" name="upltemplate">
                                <input type="submit" value="Update template">
                            </td>
                            <td>
                                @if($template->file_link!='')<a target="_blank" href="/admin/template/{{$template->id}}">Download</a>@endif</td>

                            <td>Email</td>
                        </tr>
                            </form>
                        @endforeach
                    </table>
                </div>

            </div>
        @endforeach

    </div>

@endsection