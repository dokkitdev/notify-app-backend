@extends('admin.layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <h4 class="page-title">CHL Letters</h4>
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
                        <form action="{{ route('chl.appointments.generate') }}" method="post" id="appointment-form">
                            @csrf
                            <div class="dataTables_wrapper dt-bootstrap4 no-footer">
                                <div class="table-responsive">
                                    <table class="table table-centered table-striped dt-responsive nowrap w-100"
                                           id="appointment-table">
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
                                            <th style="width: 8%;">
                                                {!! \App\Service\Sorting::order('Letter Type', 'letter_type') !!}
                                            </th>
                                            <th>
                                                {!! \App\Service\Sorting::order('Company Name', 'company_name') !!}
                                            </th>
                                            <th>
                                                {!! \App\Service\Sorting::order('Contact Name', 'given_name') !!}
                                            </th>
                                            <th>
                                                {!! \App\Service\Sorting::order('Address', 'address') !!}
                                            </th>
                                            <th>
                                                {!! \App\Service\Sorting::order('City', 'city') !!}
                                            </th>
                                            <th>
                                                {!! \App\Service\Sorting::order('Postcode', 'postcode') !!}
                                            </th>
                                            <th>
                                                {!! \App\Service\Sorting::order('Schedule Date', 'send_date') !!}
                                            </th>
                                            <th style="width: 10%;">
                                                Schedule time
                                            </th>
                                            <th>
                                                {!! \App\Service\Sorting::order('Work type', 'work_type') !!}
                                            </th>
                                            <th class="col" style="width: 9%;"></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach ($appointments as $a)
                                            <tr role="row">
                                                <td>
                                                    <input type="checkbox" name="appointments[]" value="{!! $a->id !!}">
                                                </td>
                                                <td>
                                                    {!! $a->job_id !!}
                                                </td>
                                                <td>{{$a->letter_type_name}}</td>
                                                <td>{!! $a->company_name !!}</td>
                                                <td>{!! $a->getContact() !!}</td>
                                                <td>{!! $a->getAddress() !!}</td>
                                                <td>{!! $a->city !!}</td>
                                                <td>{!! $a->postcode !!}</td>
                                                <td>{!! $a->getYmd() !!}</td>
                                                <td>{!! $a->getFormatedScheduleTime() !!}</td>
                                                <td>{!! $a->work_type !!}</td>
                                                <td class="text-center">
                                                    <a target="_blank"
                                                       href="{{ route('chl.appointments.view', ['id' => $a->id]) }}">View</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </form>
                        <div class="form-group  clearfix">
                            {{ $appointments->links('admin.pagination.default') }}
                        </div>
                        <div class="form-group text-right">
                            <input class="btn btn-primary" form="appointment-form" type="submit" value="Process">
                        </div>
                    </div>

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

        $('form').submit(function (e) {
            $('input[type="submit"]').attr('disabled', 'disabled');
        });

    </script>
@endsection
