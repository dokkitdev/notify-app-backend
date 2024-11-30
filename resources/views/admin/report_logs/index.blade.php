@extends('admin.layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <h4 class="page-title">Report Logs</h4>
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
                                            {!! \App\Service\Sorting::order('Report Type', 'type') !!}
                                        </th>
                                        <th>
                                            {!! \App\Service\Sorting::order('Filename', 'filename') !!}
                                        </th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($logs as $log)
                                        <tr>
                                            <td>{{ $log->created_at }}</td>
                                            <td>{{ $log->type }}</td>
                                            <td>
                                                {{ $log->filename }}
                                            </td>
                                            <td>
                                                <a download="{{$log->filename}}"
                                                   href="{{ route('report_logs.download', ['id' => $log->id]) }}">Download</a>
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

@section('js')
    <script>
        $(document).ready(function () {
            $('body').on('click', '.disabled', e => {
                e.preventDefault();
            })
        });
    </script>
@endsection
