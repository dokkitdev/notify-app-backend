@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/tablesorter/themes/blue/style.css') }}">
@endsection

@section('content')


        <h2>{{$title}}</h2>
        <table id="items-table" class="tablesorter" style="width: 100%">
            <thead>
                <tr>
                    <th>Generation time</th>
                    <th>Company name</th>
                    <th>Given name</th>
                    <th>Family name</th>
                    <th>E-mail</th>
                    <th>Address</th>
                    <th>City</th>
                    <th>State</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>

            @foreach ($data['letters'] as $item)
                <tr>
                    <td>{{$item['generated_at']}}</td>
                    <td>{{$item['company_name']}}</td>
                    <td>{{$item['given_name']}}</td>
                    <td>{{$item['family_name']}}</td>
                    <td>{{$item['email']}}</td>
                    <td>{{$item['address']}}</td>
                    <td>{{$item['city']}}</td>
                    <td>{{$item['state']}}</td>
                    <td>
                        @if($item['letter_s3_link'] !== NULL && trim($item['letter_s3_link']) != '')
                            <a href="{{ route('download-pdf-letter-from-s3') }}?link={{$item['letter_s3_link']}}">Download</a>
                        @endif
                    </td>
                </tr>
            @endforeach

            </tbody>
        </table>


@endsection

@section('js')
    <script src="{{ asset('vendor/tablesorter/jquery.tablesorter.min.js') }}"></script>
    <script>
        $(document).ready(function(){
            $("#items-table").tablesorter({sortList:[[0,0]], widgets: ['zebra'], headers: {8: {sorter: false}}});
        });
    </script>
@endsection
