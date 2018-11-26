@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/tablesorter/themes/blue/style.css') }}">
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
            right: 0px;
            box-shadow: 0 0 1px;
            width: 20px;
            height: 20px;
            text-align: center;
            line-height: 20px;
            border-radius: 50px;
            top: 7px;
            cursor: pointer;
            background: #f5a622;
        }
    </style>
@endsection

@section('content')
    <form action="{{ route('appointments.generate') }}" method="post">
        <h2>Appointment Letters <input class="btn btn-primary float-right" type="submit" value="Process"></h2>
        @csrf
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
                <th style="width: 10%;">Schedule Date</th>
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
                    <td>{!! $a->address !!}</td>
                    <td>{!! $a->city !!}</td>
                    <td>{!! $a->postcode !!}</td>
                    <td>{!! $a->getFormatedScheduleDate() !!}</td>
                    <td>{!! $a->getFormatedScheduleTime() !!}</td>
                    <td>{!! $a->work_type !!}</td>
                    <td class="text-center">
                        <a target="_blank" class="btn btn-dark"
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
        <div class="form-group text-right">
            <input class="btn btn-primary" type="submit" value="Process">
        </div>
    </form>

    <script>


    </script>
@endsection

@section('js')
    <script src="{{ asset('vendor/tablesorter/jquery.tablesorter.min.js') }}"></script>
    <script>
        $(document).ready(function () {
            $("#appointment-table").tablesorter({
                widgets: ['zebra'],
                headers: {
                    0: {sorter: false},
                    9: {sorter: false}
                }
            });
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
    </script>
@endsection