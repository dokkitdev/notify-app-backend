@extends('admin.layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            @if (!$log)
                <div class="page-title-right">
                    <a href="#"
                       class="btn btn-primary {{ $log ? 'processing-cursor' : '' }}"
                       data-toggle="modal"
                       data-target="#reparse-modal">Reparse Contract
                    </a>
                </div>
            @endif
            <h4 class="page-title">{{ $title }}</h4>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="card-box">
                    @if (session('error'))
                        <div class="alert alert-danger mt-1">
                            <strong>{{ session('error') }}</strong>
                        </div>
                    @elseif(session('ok'))
                        <div class="alert alert-success mt-1">
                            <strong>{{ session('ok') }}</strong>
                        </div>
                    @elseif($log)
                        <div class="alert-danger alert">
                            Letters are processing please try again in a few minutes
                        </div>
                    @endif
                    <div class="dataTables_wrapper">
                        <form action="{{ route('private.generate') }}" method="post" id="private-form">
                            @csrf
                            <div class="dataTables_wrapper dt-bootstrap4 no-footer">
                                <div class="table-responsive">
                                    <table class="table table-centered table-striped dt-responsive nowrap w-100"
                                           id="private-table">
                                        <thead>
                                        <tr>
                                            <th class="checkbox-th position-relative"><input type="checkbox"></th>
                                            <th>
                                                {!! \App\Service\Sorting::order('Customer ID', 'customer_id') !!}
                                            </th>
                                            <th>
                                                {!! \App\Service\Sorting::order('Customer', 'customer_title') !!}
                                            </th>
                                            <th>
                                                {!! \App\Service\Sorting::order('Recurring Invoice ID', 'recurring_invoice_id') !!}
                                            </th>
                                            <th>
                                                {!! \App\Service\Sorting::order('Invoice End Date', 'next_recurring_date') !!}
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
                                </div>

                            </div>
                        </form>
                        <div class="form-group  clearfix">
                            {{ $customers->links('admin.pagination.default') }}
                        </div>
                        <div class="form-group text-right">
                            <input class="btn btn-primary" form="private-form" type="submit" value="Process">
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div id="reparse-modal" class="modal fade" tabindex="-1" role="dialog" style="display: none;" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Reparse Contract</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="modal-body">
                    <form action="{{ route('private.reparse') }}" id="reparsing-form" method="post">
                        @csrf
                        <h4>Recurring Invoice ID:</h4>
                        <input type="text" class="form-control" name="id" required>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" form="reparsing-form">Parse</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
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
