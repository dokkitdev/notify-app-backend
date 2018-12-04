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
    <form action="{{ route('appointments.generate') }}" method="post" id="appointment-form">
        @csrf
        <h2>Appointment Letters</h2>
        <table id="appointment-table" class="tablesorter" style="width: 100%">
            <thead>
            <tr>
                <th class="col checkbox-th"><input type="checkbox"> <i
                            title="Select entries that should be processed"
                            class="fas fa-info"></i></th>
                <th style="width: 5%;">Job ID</th>
                <th>Contact Name</th>
                <th>Address</th>
                <th>City</th>
                <th style="width: 7%;">Postcode</th>
                <th style="width: 10%;">
                    Schedule Date
                    <i id="filter" class="fas fa-filter cursor-pointer"></i>
                    <input type="text" class="datepicker hidden-input" id="range">
                </th>
                <th style="width: 10%;">Schedule time</th>
                <th>Work type</th>
                <th class="col" style="width: 9%;"></th>
            </tr>
            </thead>
            <tbody>
            @foreach ($appointments as $a)
                <tr>
                    <td>
                        <input type="checkbox" name="appointments[]" value="{!! $a->id !!}">
                    </td>
                    <td>{!! $a->job_id !!}</td>
                    <td>{!! $a->getContact() !!}</td>
                    <td>{!! $a->getAddress() !!}</td>
                    <td>{!! $a->city !!}</td>
                    <td>{!! $a->postcode !!}</td>
                    <td>{!! $a->getYmd() !!}</td>
                    <td>{!! $a->getFormatedScheduleTime() !!}</td>
                    <td>{!! $a->work_type !!}</td>
                    <td class="text-center">
                        <a target="_blank"
                           href="{{ route('appointments.view', ['id' => $a->id]) }}">View</a>
                        {{--@if ($a->pdf || $a->docx)--}}
                        {{--<div class="dropdown" style="display: inline-block;">--}}
                        {{--<a href="#" data-toggle="dropdown" aria-haspopup="true"--}}
                        {{--aria-expanded="false"><i class="fa fa-bars"></i></a>--}}
                        {{--<div class="dropdown-menu actions-menu" aria-labelledby="dropdownMenuButton">--}}
                        {{--@if ($a->pdf)--}}
                        {{--<a target="_blank" class="dropdown-item" href="/storage/pdf/{!! $a->pdf !!}">Download--}}
                        {{--PDF</a>--}}
                        {{--@endif--}}
                        {{--@if ($a->docx)--}}
                        {{--<a target="_blank" class="dropdown-item"--}}
                        {{--href="/storage/docx/{!! $a->docx !!}">Download DOC</a>--}}
                        {{--@endif--}}
                        {{--<a class="dropdown-item"--}}
                        {{--href="{{ route('appointments.clear', ['id' => $a->id]) }}">Clear PDF and DOC</a>--}}
                        {{--</div>--}}
                        {{--</div>--}}
                        {{--@endif--}}
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </form>
    <form method="get" id="filter-form" class="d-none">
        <input type="text" name="limit" value="{!! $limit !!}">
        <input type="text" name="start">
        <input type="text" name="end">
    </form>
    <div class="form-group  clearfix">
        {{ $appointments->links('admin.pagination.default', [
        'limit' => $limit,
        'start' => $start,
        'end' => $end,
        ]
        ) }}
    </div>
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
                    6: {sorter: false},
                    9: {sorter: false}
                }
            });
            $('body').on('click', '.disabled', e => {
                e.preventDefault();
            })
        });

        const checkboxAll = $('.checkbox-th input[type="checkbox"]'),
            checkboxes = $('#appointment-table > tbody input[type="checkbox"]');

        checkboxAll.click(function (e) {
            const checked = this.checked;
            checkboxes.each((i, el) => {
                el.checked = checked;
                clickCheckbox.call(el);
            });
        });

        checkboxes.click(clickCheckbox);

        function clickCheckbox() {
            const tr = $(this).closest('tr');
            if (this.checked) {
                tr.addClass('checked')
            } else {
                tr.removeClass('checked');
            }
        }

        $('#filter').click(e => {
            e.preventDefault();
            $('#range').trigger('click');
        });

        $('.datepicker').daterangepicker().on('apply.daterangepicker', function (ev, picker) {
            $('[name="start"]').val(picker.startDate.format('DD.MM.YYYY'));
            $('[name="end"]').val(picker.endDate.format('DD.MM.YYYY'));
            $('#filter-form').submit();
        });

    </script>
@endsection