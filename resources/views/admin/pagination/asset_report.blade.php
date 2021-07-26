<div class="pagination">
    <span>
        Show rows:
    </span>
    <form method="get" id="pagination-form">
        <select name="limit" class="form-control" onchange="changeLimit(this)">
            <option value="20" {!! $limit == 20 ? 'selected' : '' !!}>20</option>
            <option value="50" {!! $limit == 50 ? 'selected' : '' !!}>50</option>
            <option value="100" {!! $limit == 100 ? 'selected' : '' !!}>100</option>
            <option value="99999" {!! $limit == 99999 ? 'selected' : '' !!}>All</option>
        </select>
        <input type="hidden" name="start" value="{!! $start !!}">
        <input type="hidden" name="end" value="{!! $end !!}">
        <input type="hidden" name="sort" value="{{ request()->get('sort') }}">
        <input type="hidden" name="direction" value="{{ request()->get('direction') }}">
        <input type="hidden" name="site_id" value="{{ $site_id }}"/>
        @foreach($asset_type as $a)
            <input type="hidden" name="asset_type[]" value="{{ $a }}"/>
        @endforeach
        @foreach($service_level_name as $a)
            <input type="hidden" name="service_level_name[]" value="{{ $a }}"/>
        @endforeach
        @foreach($error_selected as $a)
            <input type="hidden" name="error_selected[]" value="{{ $a }}"/>
        @endforeach
    </form>
    <span>
        @php
            $pagination = '';
            foreach ($asset_type as $a) {
                $pagination .= '&asset_type[]=' . $a;
            }
            foreach ($service_level_name as $a) {
                $pagination .= '&service_level_name[]=' . $a;
            }
            foreach ($error_selected as $a) {
                $pagination .= '&error_selected[]=' . $a;
            }
        @endphp



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
        of
        {!! $paginator->total() !!}
    </span>
    <a class="pagination-button prev {{ ($paginator->currentPage() == 1) ? 'disabled' : '' }}"
       href="{{ $paginator->url($paginator->currentPage()-1) }}&limit={!! $limit !!}&start={!! $start !!}&end={!! $end !!}&sort={{ request()->get('sort') }}&direction={{ request()->get('direction') }}&site_id={{$site_id}}&{{ $pagination }}">
        < </a>
    <a class="pagination-button next {{ ($paginator->currentPage() == $paginator->lastPage()) ? 'disabled' : '' }}"
       href="{{ $paginator->url($paginator->currentPage()+1) }}&limit={!! $limit !!}&start={!! $start !!}&end={!! $end !!}&sort={{ request()->get('sort') }}&direction={{ request()->get('direction') }}&site_id={{$site_id}}&{{ $pagination }}">
        > </a>
</div>

<script>
    function changeLimit(that) {
        document.getElementById('pagination-form').submit();
    }
</script>
