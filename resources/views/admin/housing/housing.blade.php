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
    <form action="{{ route('housing.generate') }}" method="post" id="housing-form">
        @csrf
        <h2>Housing Letters</h2>
        <table id="housing-table" class="tablesorter" style="width: 100%">
            <thead>
            <tr>
                <th class="col checkbox-th"><input type="checkbox"> <i
                            title="Select entries that should be processed"
                            class="fas fa-info"></i></th>
                <th>
                    <a href="{{ route('housing.all') }}?page=1&sort=job_id&direction={{ request()->get('sort') == 'job_id' && request()->get('direction') == 'asc' ? 'desc' : 'asc' }}">
                        Job ID
                        @if (request()->get('sort') == 'job_id')
                            <i class="fas {{ request()->get('direction') == 'asc' ? 'fa-sort-down' : 'fa-sort-up' }}"></i>
                        @else
                            <i class="fas fa-sort"></i>
                        @endif
                    </a>
                </th>
                <th>
                    <a href="{{ route('housing.all') }}?page=1&sort=company_name&direction={{ request()->get('sort') == 'company_name' && request()->get('direction') == 'asc' ? 'desc' : 'asc' }}">
                        Company Name
                        @if (request()->get('sort') == 'company_name')
                            <i class="fas {{ request()->get('direction') == 'asc' ? 'fa-sort-down' : 'fa-sort-up' }}"></i>
                        @else
                            <i class="fas fa-sort"></i>
                        @endif
                    </a>
                </th>
                <th>
                    <a href="{{ route('housing.all') }}?page=1&sort=schedule_date&direction={{ request()->get('sort') == 'schedule_date' && request()->get('direction') == 'asc' ? 'desc' : 'asc' }}">
                        Schedule date
                        @if (request()->get('sort') == 'schedule_date')
                            <i class="fas {{ request()->get('direction') == 'asc' ? 'fa-sort-down' : 'fa-sort-up' }}"></i>
                        @else
                            <i class="fas fa-sort"></i>
                        @endif
                    </a>
                </th>
                <th>
                    <a href="{{ route('housing.all') }}?page=1&sort=job_name&direction={{ request()->get('sort') == 'job_name' && request()->get('direction') == 'asc' ? 'desc' : 'asc' }}">
                        Service type
                        @if (request()->get('sort') == 'job_name')
                            <i class="fas {{ request()->get('direction') == 'asc' ? 'fa-sort-down' : 'fa-sort-up' }}"></i>
                        @else
                            <i class="fas fa-sort"></i>
                        @endif
                    </a>
                </th>
                <th>
                    <a href="{{ route('housing.all') }}?page=1&sort=tags&direction={{ request()->get('sort') == 'tags' && request()->get('direction') == 'asc' ? 'desc' : 'asc' }}">
                        Tag
                        @if (request()->get('sort') == 'tags')
                            <i class="fas {{ request()->get('direction') == 'asc' ? 'fa-sort-down' : 'fa-sort-up' }}"></i>
                        @else
                            <i class="fas fa-sort"></i>
                        @endif
                    </a>
                </th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @foreach ($housing as $a)
                <tr>
                    <td>
                        <input type="checkbox" name="housing[]"
                               {!! $templates[$a->tags]->docx ? '' : 'disabled' !!} value="{!! $a->id !!}">
                    </td>
                    <td>{{$a->job_id}}</td>
                    <td>{{$a->company_name}}</td>
                    <td>{{$a->getScheduleDate()}}</td>
                    <td>{{$a->job_name}}</td>
                        <td>{{$a->isLivewest() ? 'Letter No Access 2 (Livewest Properties)' : $a->tags}} </td>
                    <td>
                        @if( $templates[$a->tags]->docx)
                            <a href="{{ route('housing.view', ['id' => $a->id]) }}"
                               target="_blank">View</a>
                        @endif
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
        {{ $housing->links('admin.pagination.default', [
        'limit' => $limit,
        'start' => $start,
        'end' => $end,
        ]
        ) }}
    </div>
    <div class="form-group text-right">
        <input class="btn btn-primary" form="housing-form" type="submit" value="Process">
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
            $('body').on('click', '.disabled', e => {
                e.preventDefault();
            })
        });

        const checkboxAll = $('.checkbox-th input[type="checkbox"]'),
            checkboxes = $('#housing-table > tbody input[type="checkbox"]:not(:disabled)');

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

        // $('#filter').click(e => {
        //     e.preventDefault();
        //     $('#range').trigger('click');
        // });
        //
        // $('.datepicker').daterangepicker().on('apply.daterangepicker', function (ev, picker) {
        //     $('[name="start"]').val(picker.startDate.format('DD.MM.YYYY'));
        //     $('[name="end"]').val(picker.endDate.format('DD.MM.YYYY'));
        //     $('#filter-form').submit();
        // });
        $('form').submit(function(e) {
            $('input[type="submit"]').attr('disabled', 'disabled');
        });
    </script>
@endsection