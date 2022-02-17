@extends('layouts.app')
@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/tablesorter/themes/blue/style.css') }}">
    <link rel="stylesheet" href="{{asset('css/daterangepicker.css')}}">
@endsection
@section('content')
    <h2>Report Logs</h2>
    <table id="assets-report-table" class="tablesorter" style="width: 100%">
        <thead>
        <tr>
            <th>
                @include('admin.pagination.sort', ['sortName' => 'Date','sortId' => 'created_at'])
            </th>
            <th>
                @include('admin.pagination.sort', ['sortName' => 'Report Type','sortId' => 'type'])
            </th>
            <th>
                @include('admin.pagination.sort', ['sortName' => 'Filename','sortId' => 'filename'])
            </th>
            <th></th>
        </tr>
        </thead>
        <tbody>
        @foreach($logs as $log)
            <tr>
                <td>{{ $log->created_at }}</td>
                <td>{{ $log->type }}</td>
                <td>
                    {{ $log->filename }}
                </td>
                <td>
                    <a download="{{$log->filename}}"
                       href="{{ route('report_logs.download', ['id' => $log->id]) }}">Download</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <form method="get" id="filter-form" class="d-none">
        <input type="text" name="limit" value="{!! $limit !!}">
        <input type="text" name="start">
        <input type="text" name="end">
    </form>
    <div class="form-group  clearfix">
        {{ $logs->links('admin.pagination.default', [
        'limit' => $limit,
        'start' => '',
        'end' => '',
        ]
        ) }}
    </div>
@endsection

@section('js')
    <script type="text/javascript" src="{{ asset('js/moment.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/daterangepicker.min.js') }}"></script>
    <script>
        $(document).ready(function () {
            $('body').on('click', '.disabled', e => {
                e.preventDefault();
            })
        });
    </script>
@endsection
