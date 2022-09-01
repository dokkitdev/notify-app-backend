@extends('admin.layout')

@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <h4 class="page-title">System Logs</h4>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="card-box">
                    <div class="dataTables_wrapper">

                        <div class="dataTables_wrapper dt-bootstrap4 no-footer">
                            <div class="table-responsive">
                                <table class="table table-centered table-striped dt-responsive nowrap w-100"
                                       id="report-logs-table">
                                    <thead>
                                    <tr>
                                        <th>
                                            {!! \App\Service\Sorting::order('Date', 'created_at') !!}
                                        </th>
                                        <th>
                                            {!! \App\Service\Sorting::order('Customer Type', 'customer_type') !!}
                                        </th>
                                        <th>
                                            {!! \App\Service\Sorting::order('Letters Generated', 'letters_generated') !!}
                                        </th>
                                        <th>
                                            {!! \App\Service\Sorting::order('Email Generated', 'email_generated') !!}
                                        </th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($logs as $log)
                                        <tr>
                                            <td>{{$log->getCreatedAt()}}</td>
                                            <td>{{$log->customer_type}}</td>
                                            <td>
                                                @if (!$log->is_finished && $log->is_started  && $log->command)
                                                    Processing
                                                @elseif (!$log->is_started && $log->command)
                                                    Queuing for execution
                                                @else
                                                    {{$log->letters_generated ?: 0}}
                                                @endif
                                            </td>
                                            <td>{{$log->email_generated ?: 0}}</td>
                                            <td>
                                                @if ($log->pdf)
                                                    <a target="_blank"
                                                       href="{!! url('storage/pdf/' . $log->pdf) !!}"
                                                       title="Download PDF template">Letters</a>
                                                @endif
                                                @if ($log->emails)
                                                    <a href="{{route('logs.emails', ['id' => $log->id])}}" target="_blank">
                                                        Emails
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="form-group  clearfix">
                            {{ $logs->links('admin.pagination.default') }}
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
