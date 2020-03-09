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

    <h2>Parsing logs</h2>
    <table id="private-table" class="tablesorter" style="width: 100%">
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
                    <a class="show-action" target="_blank" href="#" data-toggle="modal" data-target="#myModal"
                       data-reasons="{{ implode('!!!', $log->reasons) }}"
                       data-ids="{!! implode(',', $log->ids) !!}">Show</a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <form method="get" id="filter-form" class="d-none">
        <input type="text" name="limit" value="{!! $limit !!}">
        <input type="text" name="start">
        <input type="text" name="end">
    </form>
    <div class="form-group  clearfix">
        {{ $logs->links('admin.pagination.default', [
        'limit' => $limit,
        'start' => '',
        'end' => ''
        ]
        ) }}
    </div>
    <div class="form-group text-right">
        <input class="btn btn-primary" form="private-form" type="submit" value="Process">
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
