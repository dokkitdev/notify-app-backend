@extends('admin.layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <h4 class="page-title">Warehouse Report</h4>
        </div>
        <div class="row">
            <div class="col-sm-12 col-md-4">
                <div class="card-box clearfix">
                    @if (session('error'))
                        <div class="alert alert-danger mt-1">
                            <strong>{{ session('error') }}</strong>
                        </div>
                    @elseif(session('ok'))
                        <div class="alert alert-success mt-1">
                            <strong>{{ session('ok') }}</strong>
                        </div>
                    @endif
                    <form action="{{ route('reports.generate') }}" method="post" id="appointment-form">
                        @csrf
                        <div class="form-group">
                            <label for="">Date</label>
                            <select name="date" required class="form-control">
                                <option value="1">One day</option>
                                <option value="3">Three day</option>
                                <option value="4">Four day</option>
                            </select>
                        </div>
                        <input class="btn btn-primary float-right" form="appointment-form" type="submit" value="Process">

                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script type="text/javascript" src="{{ asset('js/moment.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/daterangepicker.min.js') }}"></script>
@endsection
