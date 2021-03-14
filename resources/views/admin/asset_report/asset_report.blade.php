@extends('layouts.app')
@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/tablesorter/themes/blue/style.css') }}">
    <link rel="stylesheet" href="{{asset('css/daterangepicker.css')}}">
@endsection
@section('content')


    <form action="{{ route('private.generate') }}" method="post" id="private-form">
        @csrf
        <h2>Asset Report</h2>
        <div class="row">
            <div class="col-md-9">
            </div>
            <div class="col-md-3 text-right">
                <a href="#"
                   class="btn btn-primary">Run Validation</a>
                <a href="#"
                   class="btn btn-primary">Process Report</a>
            </div>
        </div>
        <table id="assets-report-table" class="tablesorter" style="width: 100%">
            <thead>
            <tr>
                <th>
                    <a href="#">
                        Site ID
                    </a>
                </th>
                <th>
                    <a href="#">
                        ~UPRN
                    </a>
                </th>
                <th>
                    <a href="#">
                        Asset ID
                    </a>
                </th>
                <th>
                    <a href="#">
                        Asset Type
                    </a>
                </th>
                <th>
                    <a href="#">
                        Fuel Type
                    </a>
                </th>
                <th>
                    <a href="#">
                        Error
                    </a>
                </th>
            </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </form>
    <form method="get" id="filter-form" class="d-none">
        <input type="text" name="limit" value="{!! $limit !!}">
        <input type="text" name="start">
        <input type="text" name="end">
    </form>
    <div class="form-group  clearfix">
{{--        {{ $customers->links('admin.pagination.default', [--}}
{{--        'limit' => $limit,--}}
{{--        'start' => '',--}}
{{--        'end' => ''--}}
{{--        ]--}}
{{--        ) }}--}}
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
    </script>
@endsection
