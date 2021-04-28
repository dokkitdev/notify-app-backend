@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/tablesorter/themes/blue/style.css') }}">
    <link rel="stylesheet" href="{{asset('css/daterangepicker.css')}}">
    <style>
        /*#appointment-table {*/
        /*table-layout: fixed;*/
        /*}*/

        input[type="checkbox"] {
            width: 15px;
            height: 15px;
        }

        .fa-info {
            position: absolute;
            right: 2px;
            box-shadow: 0 0 1px;
            width: 16px;
            height: 16px;
            text-align: center;
            line-height: 16px;
            border-radius: 50px;
            top: 10px;
            cursor: pointer;
            background: #f5a622;
            font-size: 10px;
        }
    </style>
@endsection

@section('content')
    <form action="{{ route('reports.generate') }}" method="post" id="appointment-form">
        @csrf
        <h2>Warehouse Report</h2>
        <div class="row">
            <div class="col-md-4 col-xs-12 col-sm-12 form-group">
                <label for="">Date</label>
                <select name="date" required class="form-control">
                    <option value="1">One day</option>
                    <option value="3">Three day</option>
                </select>
            </div>
        </div>
        <input class="btn btn-primary" form="appointment-form" type="submit" value="Process">

    </form>
@endsection

@section('js')
    <script type="text/javascript" src="{{ asset('js/moment.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/daterangepicker.min.js') }}"></script>
@endsection