<form method="get" id="pagination-form">
    <div class="row mb-4">
        @foreach(app('request')->query->all() as $key => $val)
            @if(!in_array($key, ['page', '_token', 'limit']))
                @if(is_array($val))
                    @foreach($val as $subVal)
                        <input type="hidden" name="{{$key}}[]" value="{{$subVal}}">
                    @endforeach
                @else
                    <input type="hidden" name="{{$key}}" value="{{$val}}">
                @endif
            @endif
        @endforeach
        <div class="col-sm-12 col-md-5">
            <div class="dataTables_length">
                <label>Showing
                    @php ($limit = app('request')->query->get('limit', 20))
                    <select name="limit" class="custom-select custom-select-sm form-control form-control-sm"
                            onchange="changeLimit(this)">
                        <option value="20" {!! $limit == 20 ? 'selected' : '' !!}>20</option>
                        <option value="50" {!! $limit == 50 ? 'selected' : '' !!}>50</option>
                        <option value="100" {!! $limit == 100 ? 'selected' : '' !!}>100</option>
                        <option value="99999" {!! $limit == 99999 ? 'selected' : '' !!}>All</option>
                    </select>
                    @php ($start_item = ($paginator->currentPage() - 1) * $paginator->perPage() + 1)
                    @if ($start_item > $paginator->total())
                        {!! $paginator->total() !!}
                        -
                        {!! $paginator->total() !!}
                    @else
                        {!! $start_item !!}
                        -
                        {!! $start_item + count($paginator->items()) - 1 !!}
                    @endif
                    entries of {!! $paginator->total() !!}
                </label>
            </div>
        </div>
        <div class="col-sm-12 col-md-7">
            <div class="dataTables_paginate paging_simple_numbers">
                <ul class="pagination">
                    <li class="paginate_button page-item previous {{ $paginator->onFirstPage() ? 'disabled' : '' }}">
                        <a href="{{ $paginator->previousPageUrl() }}"
                           class="page-link">
                            <i class="mdi mdi-chevron-left"></i>
                        </a>
                    </li>
                    <li class="paginate_button page-item next {{ !$paginator->hasMorePages() ? 'disabled' : '' }}"
                        id="tickets-table_next">
                        <a href="{{ $paginator->nextPageUrl() }}"
                           class="page-link">
                            <i class="mdi mdi-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</form>

<script>
    function changeLimit(that) {
        document.getElementById('pagination-form').submit();
    }
</script>
