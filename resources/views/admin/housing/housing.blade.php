@extends('admin.layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <h4 class="page-title">Housing Letters</h4>
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
                    @endif
                    <div class="dataTables_wrapper">
                        <form action="{{ route('housing.generate') }}" method="post" id="housing-form">
                        @csrf
                            <div class="dataTables_wrapper dt-bootstrap4 no-footer">
                                <div class="table-responsive">
                                    <table class="table table-centered table-striped dt-responsive nowrap w-100"
                                           id="housing-table">
                                        <thead>
                                        <tr>
                                            <th class="checkbox-th position-relative">
                                                <input type="checkbox">
                                                <i
                                                    title="Select entries that should be processed"
                                                    class="fas fa-info"></i>
                                            </th>
                                            <th style="width: 5%;">
                                                {!! \App\Service\Sorting::order('Job ID', 'job_id') !!}
                                            </th>
                                            <th>
                                                {!! \App\Service\Sorting::order('Company Name', 'company_name') !!}
                                            </th>
                                            <th>
                                                {!! \App\Service\Sorting::order('Schedule date', 'schedule_date') !!}
                                            </th>
                                            <th>
                                                {!! \App\Service\Sorting::order('Service type', 'job_name') !!}
                                            </th>
                                            <th>
                                                {!! \App\Service\Sorting::order('Tag', 'tags') !!}
                                            </th>
                                            <th class="col" style="width: 9%;"></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach ($housing as $a)
                                            <tr role="row">
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
                                </div>

                            </div>
                        </form>
                        <div class="form-group  clearfix">
                            {{ $housing->links('admin.pagination.default') }}
                        </div>
                        <div class="form-group text-right">
                            <input class="btn btn-primary" form="housing-form" type="submit" value="Process">
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
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
