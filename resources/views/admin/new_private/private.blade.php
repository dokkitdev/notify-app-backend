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

        .nav .nav-link {
            color: black;
        }

        .nav .active {
            border-bottom: 2px solid #2a2c83 !important;
        }
    </style>
@endsection

@section('content')

    <div id="reparse-modal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('private.reparse') }}" id="reparsing-form" method="post">
                        @csrf
                        <h4>Recurring Invoice ID:</h4>
                        <input type="text" class="form-control" name="id" required>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" form="reparsing-form">Parse</button>
                </div>
            </div>

        </div>
    </div>

    <form action="{{ route('private.generate') }}" method="post" id="private-form">
        @csrf
        <h2>{{ $title }}</h2>
        <div class="row">
            <div class="col-md-9">
                <ul class="nav">
                    <li class="nav-item">
                        <a class="nav-link {!! Route::current()->getName() == 'private.all' ? 'active' : '' !!}"
                           href="{{ route('private.all') }}">Annual payment</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {!! Route::current()->getName() == 'private.debit' ? 'active' : '' !!}"
                           href="{{ route('private.debit') }}">Direct Debit</a>
                    </li>
                </ul>
            </div>
            <div class="col-md-3 text-right">
                <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#reparse-modal">Reparse
                    Contract</a>
            </div>
        </div>
        <table id="private-table" class="tablesorter" style="width: 100%">
            <thead>
            <tr>
                <th class="col checkbox-th"><input type="checkbox"> <i
                        title="Select entries that should be processed"
                        class="fas fa-info"></i></th>
                <th>
                    <a href="{{ route(Route::current()->getAction('as')) }}?page=1&sort=customer_id&direction={{ request()->get('sort') == 'customer_id' && request()->get('direction') == 'asc' ? 'desc' : 'asc' }}">
                        Customer ID
                        @if (request()->get('sort') == 'customer_id')
                            <i class="fas {{ request()->get('direction') == 'asc' ? 'fa-sort-down' : 'fa-sort-up' }}"></i>
                        @else
                            <i class="fas fa-sort"></i>
                        @endif
                    </a>
                </th>
                <th>
                    <a href="{{ route(Route::current()->getAction('as')) }}?page=1&sort=customer_title&direction={{ request()->get('sort') == 'customer_title' && request()->get('direction') == 'asc' ? 'desc' : 'asc' }}">
                        Customer
                        @if (request()->get('sort') == 'customer_title')
                            <i class="fas {{ request()->get('direction') == 'asc' ? 'fa-sort-down' : 'fa-sort-up' }}"></i>
                        @else
                            <i class="fas fa-sort"></i>
                        @endif
                    </a>
                </th>
                <th>
                    <a href="{{ route(Route::current()->getAction('as')) }}?page=1&sort=recurring_invoice_id&direction={{ request()->get('sort') == 'recurring_invoice_id' && request()->get('direction') == 'asc' ? 'desc' : 'asc' }}">
                        Recurring Invoice ID
                        @if (request()->get('sort') == 'recurring_invoice_id')
                            <i class="fas {{ request()->get('direction') == 'asc' ? 'fa-sort-down' : 'fa-sort-up' }}"></i>
                        @else
                            <i class="fas fa-sort"></i>
                        @endif
                    </a>
                </th>
                <th>
                    <a href="{{ route(Route::current()->getAction('as')) }}?page=1&sort=next_recurring_date&direction={{ request()->get('sort') == 'next_recurring_date' && request()->get('direction') == 'asc' ? 'desc' : 'asc' }}">
                        Invoice End Date
                        @if (request()->get('sort') == 'next_recurring_date')
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
            @foreach($customers as $customer)
                <tr>
                    <td><input type="checkbox" name="private[]"
                               value="{!! $customer->id !!}">
                    </td>
                    <td>
                        {{ $customer->customer_id }}
                    </td>
                    <td>
                        {{ $customer->getName() }} <b>({{ $customer->recurring_type }})</b>
                    </td>
                    <td>
                        {{ $customer->recurring_invoice_id }}
                    </td>
                    <td>
                        {{ $customer->next_recurring_date }}
                    </td>
                    <td class="text-center">
                        <a target="_blank"
                           href="{!! route('private.view', ['id' => $customer->id]) !!}">View</a>
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
        {{ $customers->links('admin.pagination.default', [
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

        $('form').submit(function (e) {
            $('input[type="submit"]').attr('disabled', 'disabled');
        });
    </script>
@endsection
