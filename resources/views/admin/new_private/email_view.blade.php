<style>
    .html:not(:last-child) {
        border-bottom: 2px solid #b2b2b2;
        margin-bottom: 10px;
    }
</style>
@foreach($htmls as $html)
    <div class="html">
        {!! $html !!}
    </div>
@endforeach