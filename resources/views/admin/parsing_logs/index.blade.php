@extends('admin.layout')
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <h4 class="page-title">Parsing logs</h4>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="card-box">
                    <div class="dataTables_wrapper">
                        <div class="dataTables_wrapper dt-bootstrap4 no-footer">
                            <div class="table-responsive">
                                <table class="table table-centered table-striped dt-responsive nowrap w-100"
                                       id="private-table">
                                    <thead>
                                    <tr>
                                        <th>Created</th>
                                        <th>Type</th>
                                        <th>Total</th>
                                        <th>Success</th>
                                        <th>Parsing for</th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($logs as $log)
                                        <tr>
                                            <td>{!! $log->created_at !!}</td>
                                            <td>{!! ucfirst($log->type) !!}</td>
                                            <td>{!! $log->total_count !!}</td>
                                            <td>{!! $log->total_success !!}</td>
                                            <td>{!! $log->parsing_date !!}</td>
                                            <td>
                                                <a class="show-action" target="_blank" href="#" data-toggle="modal"
                                                   data-target="#myModal"
                                                   data-reasons="{{ implode('!!!', $log->reasons) }}"
                                                   data-ids="{!! implode(',', $log->ids) !!}">Show</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <div id="myModal" class="modal fade" role="dialog">
        <div class="modal-dialog" style="max-width: calc(80% - 20px);">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <h4>Ids:</h4>
                    <div id="ids"></div>
                    <h4>Errors:</h4>
                    <div id="reasons"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
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
            });
            $('.show-action').click(function (e) {
                var text = this.dataset.ids;
                $('#ids').html($('<p/>', {
                    'text': text.replace(/,/g, ', '),
                }));

                var reasonsBlock = $('#reasons');
                reasonsBlock.html('');
                var reasons = this.dataset.reasons.split('!!!');
                $.each(reasons, function (index, text) {
                    reasonsBlock.append($('<p/>', {
                        'html': text,
                    }));
                });

            });
        });
    </script>
@endsection
