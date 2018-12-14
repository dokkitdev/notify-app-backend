@extends('layouts.app')

@section('css')
    <link rel="stylesheet" href="{{ asset('vendor/tablesorter/themes/blue/style.css') }}">
    <link rel="stylesheet" href="{{asset('css/daterangepicker.css')}}">
    <style>
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
    <form action="{{ route('private.generate') }}" method="post" id="private-form">
        @csrf
        <h2>Private Letters</h2>
        <table id="private-table" class="tablesorter" style="width: 100%">
            <thead>
            <tr>
                <th class="col checkbox-th"><input type="checkbox"> <i
                            title="Select entries that should be processed"
                            class="fas fa-info"></i></th>
                <th class="header">Contract ID</th>
                <th class="header">End date</th>
                <th class="header">Customer</th>
                <th class="header">Asset</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @foreach($contracts as $contract)
                <tr>
                    <td>
                        <input type="checkbox" name="private[]" value="{!! $contract->id !!}">
                    </td>
                    <td>{!! $contract->contract_id !!}</td>
                    <td>{!! $contract->getEndDateYmd() !!}</td>
                    <td>{!! $contract->customer->getName() !!}</td>
                    <td>{!! $contract->asset ? $contract->asset->value : 'Heating Equipment' !!}</td>
                    <td>
                        <a href="{!! route('private.view', ['id' => $contract->id]) !!}">View</a>
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
        {{ $contracts->links('admin.pagination.default', [
            'limit' => $limit,
            'start' => '',
            'end' => ''
        ]
        ) }}
    </div>
    <div class="form-group text-right">
        <input class="btn btn-primary" form="private-form" type="submit" value="Process">
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
            $("#private-table").tablesorter({
                widgets: ['zebra'],
                headers: {
                    0: {sorter: false},
                    5: {sorter: false},
                    6: {sorter: false},
                }
            });
            $('body').on('click', '.disabled', e => {
                e.preventDefault();
            })
        });

        const checkboxAll = $('.checkbox-th input[type="checkbox"]'),
            checkboxes = $('#private-table > tbody input[type="checkbox"]:not(:disabled)');

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