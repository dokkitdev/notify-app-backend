@extends('admin.layout')
@section('content')
    <form action="{{ route('appointments.generate') }}" method="post" id="appointment-form">
        @csrf
        <h2>Warehouse Report</h2>
        <table id="appointment-table" class="tablesorter" style="width: 100%">
            <thead>
            <tr>
                <th class="col checkbox-th"><input type="checkbox"> <i
                            title="Select entries that should be processed"
                            class="fas fa-info"></i></th>
                <th style="width: 5%;">Job ID</th>
                <th>Site name</th>
                <th>Engineer</th>
                <th class="col" style="width: 9%;"></th>
            </tr>
            </thead>
            <tbody>
            @foreach ($reports as $a)
                <tr>
                    <td>
                        <input type="checkbox" name="appointments[]" value="{!! $a->id !!}">
                    </td>
                    <td>{!! $a->job_id !!}</td>
                    <td>{!! $a->site_name !!}</td>
                    <td>{!! $a->engineer !!}</td>
                    <td class="text-center">
                        <a target="_blank"
                           href="{{ route('appointments.view', ['id' => $a->job_id]) }}">View</a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </form>
    <div class="form-group text-right">
        <input class="btn btn-primary" form="appointment-form" type="submit" value="Process">
    </div>
    <script>


    </script>
@endsection

@section('js')
    <script type="text/javascript" src="{{ asset('js/moment.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/daterangepicker.min.js') }}"></script>

    <script src="{{ asset('vendor/tablesorter/jquery.tablesorter.min.js') }}"></script>
    <script>
        $(document).ready(function () {
            $("#appointment-table").tablesorter({
                widgets: ['zebra'],
                headers: {
                    0: {sorter: false},
                    // 6: {sorter: false},
                    9: {sorter: false}
                }
            });
            $('body').on('click', '.disabled', e => {
                e.preventDefault();
            })
        });
    </script>
@endsection
