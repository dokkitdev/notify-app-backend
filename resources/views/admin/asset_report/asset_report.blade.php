@extends('layouts.app')
@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/tablesorter/themes/blue/style.css') }}">
    <link rel="stylesheet" href="{{asset('css/daterangepicker.css')}}">


    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/css/bootstrap-select.css"/>
    <style>
        .bootstrap-select > .dropdown-toggle {
            background: white !important;
        }
    </style>
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
                    <div class="col-md-3">
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
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="service_level_name">Service Level</label>
                            <select multiple name="service_level_name[]" id="service_level_name"
                                    class="selectpicker form-control"
                                    multiple data-live-search="true">
                                @foreach($service_level_names as $a)
                                    <option
                                        {{  in_array($a->service_level_name, $service_level_name) ? 'selected' : '' }} value="{{$a->service_level_name}}">{{$a->service_level_name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="asset_type">Asset Type</label>
                            <select multiple name="asset_type[]" id="asset_type" class="selectpicker form-control"
                                    multiple data-live-search="true">
                                @foreach($asset_types as $a)
                                    <option
                                        {{ in_array($a->asset_type, $asset_type) ? 'selected' : '' }} value="{{$a->asset_type}}">{{$a->asset_type}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="error_selected">Error</label>
                            <select multiple name="error_selected[]" id="error_selected"
                                    class="selectpicker form-control"
                                    multiple data-live-search="true">
                                @foreach($errors as $k => $a)
                                    <option
                                        {{ in_array($k, $error_selected) ? 'selected' : '' }} value="{{ $k }}">{{$a}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="stages">Stage</label>
                            <select multiple name="stages[]" id="stages"
                                    class="selectpicker form-control"
                                    multiple data-live-search="true">
                                @foreach($stages as $k => $a)
                                    <option
                                        {{ in_array($a->job_stage, $stages_selected) ? 'selected' : '' }} value="{{ $a->job_stage }}">{{$a->job_stage}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <br>
                        <input type="submit"  class="btn btn-primary" value="Save Filters" name="save_filter">
                    </div>
                </div>
            </div>
            <div class="col-md-4 text-right">
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
                @include('admin.pagination.sort', ['sortName' => 'Service Level','sortId' => 'service_level_name'])
            </th>
            <th style="width:20%;">
                @include('admin.pagination.sort', ['sortName' => 'Error','sortId' => 'error'])
            </th>
        </tr>
        </thead>
        <tbody>
        @foreach($validations as $validation)
            <tr>
                <td>{{ $validation->site_id }} {{ $validation->job_stage }}</td>
                <td>{{ $validation->uprn }}</td>
                <td>{{ $validation->asset_id }}</td>
                <td>{{ $validation->asset_type }}</td>
                <td>{{ $validation->service_level_name }}</td>
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
        'service_level_name' => $service_level_name,
        'error_selected' => $error_selected,
        'stages_selected' => $stages_selected,
        ]
        ) }}
    </div>
@endsection

@section('js')
    <script type="text/javascript" src="{{ asset('js/moment.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/daterangepicker.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/js/bootstrap-select.min.js"></script>
    <script src="{{ asset('vendor/tablesorter/jquery.tablesorter.min.js') }}"></script>
    <script>
        $(document).ready(function () {
            $('body').on('click', '.disabled', e => {
                e.preventDefault();
            })
        });

        $('#site_id').on('change', function (e) {
            $('#filter-asset-form').trigger('submit');
        });
        $('#site_id,#asset_type,#error_selected,#service_level_name, #stages').on('hide.bs.select', function (e) {
            $('#filter-asset-form').trigger('submit');
        });
    </script>
@endsection
