@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/tablesorter/themes/blue/style.css') }}">
    <style>
        #appointment-table {
            table-layout: fixed;
        }

        input[type="checkbox"] {
            width: 15px;
            height: 15px;
        }
    </style>
@endsection

@section('content')
    <h2>Appointment Letters</h2>
    <form action="{{ route('appointments.generate') }}" method="post">
        @csrf
        <table id="appointment-table" class="tablesorter" style="width: 100%">
            <thead>
            <tr>
                <th class="col checkbox-th"><input type="checkbox"></th>
                <th class="col" style="width: 9%;">Contact name</th>
                <th class="col" style="width: 9%;">Address</th>
                <th class="col" style="width: 9%;">Address2</th>
                <th class="col" style="width: 9%;">City</th>
                <th class="col" style="width: 9%;">County</th>
                <th class="col" style="width: 9%;">Postcode</th>
                <th class="col" style="width: 9%;">Day to schedule</th>
                <th class="col" style="width: 9%;">Schedule date</th>
                <th class="col" style="width: 9%;">Schedule time</th>
                <th class="col" style="width: 9%;">Work type</th>
                <th class="col" style="width: 9%;">Job ID</th>
                <th class="col" style="width: 9%;"></th>
            </tr>
            </thead>
            <tbody>
            @foreach ($appointments as $a)
                <tr>
                    <td>
                        @if ($a->pdf === null || $a->docx === null)
                            <input type="checkbox" name="appointments[]" value="{!! $a->id !!}">
                        @endif
                    </td>
                    <td>{!! $a->getContact() !!}</td>
                    <td>{!! $a->address !!}</td>
                    <td>{!! $a->state !!}</td>
                    <td>{!! $a->city !!}</td>
                    <td>{!! $a->country !!}</td>
                    <td>{!! $a->postcode !!}</td>
                    <td>{!! $a->getDaysToScheduleDate() !!}</td>
                    <td>{!! $a->getFormatedScheduleDate() !!}</td>
                    <td>{!! $a->getFormatedScheduleTime() !!}</td>
                    <td>{!! $a->work_type !!}</td>
                    <td>{!! $a->job_id !!}</td>
                    <td>
                        @if ($a->pdf)
                            <a target="_blank" class="btn btn-dark" href="/storage/pdf/{!! $a->pdf !!}"
                               title="Download PDF template">PDF</a>
                        @endif
                        @if ($a->docx)
                            <a target="_blank" class="btn btn-dark" href="/storage/docx/{!! $a->docx !!}"
                               title="Download PDF template">DOC</a>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <div class="form-group text-right">
            <input class="btn btn-primary" type="submit" value="Process All">
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
                    12: {sorter: false}
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