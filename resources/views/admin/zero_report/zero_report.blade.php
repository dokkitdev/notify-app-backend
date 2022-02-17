@extends('layouts.app')
@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/tablesorter/themes/blue/style.css') }}">
    <link rel="stylesheet" href="{{asset('css/daterangepicker.css')}}">
@endsection
@section('content')
    @if ($zero_constant->is_need_parsing == true and !session('ok'))
        <div class="alert-info alert">
            Parsing is running
        </div>
    @endif

    <h2>Zero Report</h2>
    <form method="POST" id="zero-form">
        @csrf
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="daterange">Dates</label>
                    <input type="text" id="daterange" name="dates" value="{{ Date('d/m/Y') }} - {{ Date('d/m/Y') }}"
                           class="form-control"/>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 text-right">
                @if ($zero_constant->is_need_parsing == true)
                    <button type="button" disabled class="btn btn-primary">Run Report</button>
                @else
                    <button type="submit"
                            class="btn btn-primary">Run Report
                    </button>
                @endif

            </div>
        </div>
    </form>
@endsection

@section('js')
    <script type="text/javascript" src="{{ asset('js/moment.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/daterangepicker.min.js') }}"></script>
    <script>
        $('#daterange').daterangepicker({
            locale: {
                format: 'DD/MM/YYYY'
            },
            opens: 'left'
        }, function (start, end, label) {
        });
    </script>
@endsection
