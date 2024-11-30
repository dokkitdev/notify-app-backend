@extends('admin.layout')
@section('css')
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.1/css/bootstrap-select.css"/>
    <style>
        .bootstrap-select > .dropdown-toggle {
            background: white !important;
        }
    </style>
@endsection
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="page-title-right">
                <a href="{{ route('asset_report.download_csv') }}"
                   class="btn btn-primary">Process Report</a>
            </div>
            <h4 class="page-title">Asset Report</h4>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="card-box">
                    <form method="get" id="filter-asset-form">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-12">
                                        <h4>
                                            Filters
                                        </h4>
                                    </div>
                                    <div class="col-md-2">
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
                                    <div class="col-md-2">
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
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="asset_type">Asset Type</label>
                                            <select multiple name="asset_type[]" id="asset_type"
                                                    class="selectpicker form-control"
                                                    multiple data-live-search="true">
                                                @foreach($asset_types as $a)
                                                    <option
                                                        {{ in_array($a->asset_type, $asset_type) ? 'selected' : '' }} value="{{$a->asset_type}}">{{$a->asset_type}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
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
                                    <div class="col-md-2">
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
                                    <div class="col-md-2 pt-3">
                                        <input type="submit" class="btn btn-primary" value="Save Filters"
                                               name="save_filter">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="card-box">
                    @if ($asset_constant->is_need_parsing == true and !session('ok'))
                        <div class="alert alert-danger mt-1">
                            <strong>Parsing is scheduled</strong>
                        </div>
                    @endif

                    <div class="dataTables_wrapper">
                        <form action="{{ route('appointments.generate') }}" method="post" id="appointment-form">
                            @csrf
                            <div class="dataTables_wrapper dt-bootstrap4 no-footer">
                                <div class="table-responsive">
                                    <table class="table table-centered table-striped dt-responsive nowrap w-100"
                                           id="assets-report-table">
                                        <thead>
                                        <tr>
                                            <th style="width:10%;">
                                                {!! \App\Service\Sorting::order('Site ID', 'site_id') !!}
                                            </th>
                                            <th style="width:20%;">
                                                {!! \App\Service\Sorting::order('~UPRN', 'uprn') !!}
                                            </th>
                                            <th style="width:10%;">
                                                {!! \App\Service\Sorting::order('Asset ID', 'asset_id') !!}
                                            </th>
                                            <th style="width:20%;">
                                                {!! \App\Service\Sorting::order('Asset Type', 'asset_type') !!}
                                            </th>
                                            <th style="width:20%;">
                                                {!! \App\Service\Sorting::order('Service Level', 'service_level_name') !!}
                                            </th>
                                            <th style="width:20%;">
                                                {!! \App\Service\Sorting::order('Error', 'error') !!}
                                            </th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach ($validations as $validation)
                                            <tr role="row">
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
                                </div>

                            </div>
                        </form>
                        <div class="form-group  clearfix">
                            {{ $validations->links('admin.pagination.default') }}
                        </div>
                        <div class="form-group text-right">
                            <input class="btn btn-primary" form="appointment-form" type="submit" value="Process">
                        </div>
                    </div>

                </div>
            </div>
        </div>
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
