@extends('admin.layout')
@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/tablesorter/themes/blue/style.css') }}">
    <link rel="stylesheet" href="{{asset('css/daterangepicker.css')}}">
@endsection
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <h4 class="page-title">Zero Report</h4>
        </div>
        <div class="row">
            <div class="col-sm-12 col-md-5">
                <div class="card-box clearfix">
                    @if ($zero_constant->is_need_parsing == true and !session('ok'))
                        <div class="alert alert-danger mt-1">
                            <strong>Parsing is running</strong>
                        </div>
                    @elseif(session('ok'))
                        <div class="alert alert-success mt-1">
                            <strong>{{ session('ok') }}</strong>
                        </div>
                    @endif
                    <form method="POST" id="zero-form">
                        @csrf
                        <div class="form-group">
                            <label for="daterange">Dates</label>
                            <input type="text" id="daterange" name="dates"
                                   value="{{ Date('d/m/Y') }} - {{ Date('d/m/Y') }}"
                                   class="form-control"/>
                        </div>
                        @if ($zero_constant->is_need_parsing == true)
                            <button type="button" disabled class="btn btn-primary float-right">Run Report</button>
                        @else
                            <button type="submit"
                                    class="btn btn-primary float-right">Run Report
                            </button>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
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
