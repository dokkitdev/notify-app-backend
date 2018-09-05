@extends('admin.tpl.wrapper')

@section('content')

    <div class="container">

        <h2>Templates</h2>
        @foreach ($templatesGroups as $tg)
            <div class="content">
                <h3>{{$tg->customer_type}}</h3>
                <div class="border">
                    <table>
                        @foreach ($tg['templates'] as $template)
                        <tr>
                            <td>Letter {{$template->state}}</td>
                            <td>
                                <select name="term[{{$tg->id}}][{{$template->id}}]">
                                    <option value="{{$template->term}}">{{$template->term}}</option>
                                </select>
                            </td>
                            <td>Download</td>
                            <td>Upload</td>
                            <td>Email</td>
                        </tr>
                        @endforeach
                    </table>
                </div>

            </div>
        @endforeach

    </div>

@endsection