@extends('layouts.app')
@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/tablesorter/themes/blue/style.css') }}">
    <link rel="stylesheet" href="{{asset('css/daterangepicker.css')}}">
@endsection
@section('content')

    @if ($asset_constant->is_need_parsing == true and !session('ok'))
        <div class="alert-info alert">
            Parsing is scheduled
        </div>
    @endif

    <h2>Asset Report</h2>
    <form method="get" id="filter-asset-form">
        <div class="row">
            <div class="col-md-8">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="site_id">Site</label>
                            <select name="site_id" id="site_id" class="form-control">
                                <option></option>
                                @foreach($sites as $site)
                                    <option
                                        {{ $site_id == $site->site_id ? 'selected' : '' }} value="{{$site->site_id}}">{{$site->site_id}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="asset_type">Asset Type</label>
                            <select name="asset_type" id="asset_type" class="form-control">
                                <option></option>
                                @foreach($asset_types as $a)
                                    <option
                                        {{ $asset_type == $a->asset_type ? 'selected' : '' }} value="{{$a->asset_type}}">{{$a->asset_type}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="error_selected">Error</label>
                            <select name="error_selected" id="error_selected" class="form-control">
                                <option></option>
                                @foreach($errors as $k => $a)
                                    <option
                                        {{ $error_selected!== null && $error_selected == $k ? 'selected' : '' }} value="{{ $k }}">{{$a}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-right">
                @if ($asset_constant->is_need_parsing == true)
                    <button type="button" disabled class="btn btn-primary">Schedule Validation</button>
                @else
                    <a href="{{ route('asset_report.schedule') }}"
                       class="btn btn-primary">Schedule Validation</a>
                @endif
                <a href="{{ route('asset_report.download_csv') }}"
                   class="btn btn-primary">Process Report</a>
            </div>
        </div>
    </form>

    <table id="assets-report-table" class="tablesorter" style="width: 100%">
        <thead>
        <tr>
            <th style="width:10%;">
                @include('admin.pagination.sort', ['sortName' => 'Site ID','sortId' => 'site_id'])
            </th>
            <th style="width:20%;">
                @include('admin.pagination.sort', ['sortName' => '~UPRN','sortId' => 'uprn'])
            </th>
            <th style="width:10%;">
                @include('admin.pagination.sort', ['sortName' => 'Asset ID','sortId' => 'asset_id'])
            </th>
            <th style="width:20%;">
                @include('admin.pagination.sort', ['sortName' => 'Asset Type','sortId' => 'asset_type'])
            </th>
            <th style="width:20%;">
                @include('admin.pagination.sort', ['sortName' => 'Fuel Type','sortId' => 'fuel_type'])
            </th>
            <th style="width:20%;">
                @include('admin.pagination.sort', ['sortName' => 'Error','sortId' => 'error'])
            </th>
        </tr>
        </thead>
        <tbody>
        @foreach($validations as $validation)
            <tr>
                <td>{{ $validation->site_id }}</td>
                <td>{{ $validation->uprn }}</td>
                <td>{{ $validation->asset_id }}</td>
                <td>{{ $validation->asset_type }}</td>
                <td>{{ $validation->fuel_type }}</td>
                <td>{{ $validation->error }}</td>
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
        {{ $validations->links('admin.pagination.asset_report', [
        'limit' => $limit,
        'start' => '',
        'end' => '',
        'site_id' => $site_id,
        'asset_type' => $asset_type,
        ]
        ) }}
    </div>
@endsection

@section('js')
    <script type="text/javascript" src="{{ asset('js/moment.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/daterangepicker.min.js') }}"></script>

    <script src="{{ asset('vendor/tablesorter/jquery.tablesorter.min.js') }}"></script>
    <script>
        $(document).ready(function () {
            $('body').on('click', '.disabled', e => {
                e.preventDefault();
            })
        });

        $('#site_id,#asset_type,#error_selected').change(function (e) {
            console.log('hei');
            $('#filter-asset-form').trigger('submit');
        });
    </script>
@endsection
