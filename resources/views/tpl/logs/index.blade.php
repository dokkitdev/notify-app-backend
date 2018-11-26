@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/tablesorter/themes/blue/style.css') }}">
@endsection

@section('content')


    <h2>{{$title}}</h2>
    <table id="items-table" class="tablesorter" style="width: 100%">
        <thead>
        <tr>
            <th scope="col">Date</th>
            <th scope="col">Customer Type</th>
            <th scope="col">Letters Generated</th>
            <th scope="col">Email Generated</th>
            <th scope="col"></th>
        </tr>
        </thead>
        <tbody>
        @foreach ($logs as $log)
            <tr>
                <td>{{$log->getCreatedAt()}}</td>
                <td>{{$log->customer_type}}</td>
                <td>{{$log->letters_generated ?: 0}}</td>
                <td>{{$log->emails_generated ?: 0}}</td>
                <td>
                    @if ($log->pdf)
                        <a target="_blank"
                           href="/storage/pdf/{!! $log->pdf !!}"
                           title="Download PDF template">View</a>
                    @endif
                </td>
            </tr>
        @endforeach
        @foreach ($data as $item)
            <tr>
                <td>{{$item['date']}}</td>
                <td>{{$item['customer_type']}}</td>
                <td>{{$item['letters_generated']}}</td>
                <td>{{$item['emails_generated']}}</td>
                <td>@if($item['letters_generated'] > 0)<a
                            href="{{ route('daily-letters-log', [$item['customer_type'], $item['date']]) }}">Download
                        letters</a>@endif</td>
            </tr>
        @endforeach

        </tbody>
    </table>


@endsection

@section('js')
    <script src="{{ asset('vendor/tablesorter/jquery.tablesorter.min.js') }}"></script>
    <script>
        $(document).ready(function () {
            $("#items-table").tablesorter({sortList: [[0, 1]], widgets: ['zebra'], headers: {4: {sorter: false}}});
        });
    </script>
@endsection
