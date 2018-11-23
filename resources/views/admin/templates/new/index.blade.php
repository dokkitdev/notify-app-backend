@extends('layouts.app')

@section('content')
    <h1>Templates</h1>
    @foreach ($templateParents as $parent)
        <h2 class="pt-3">{!! $parent->title !!}</h2>
        <div class="content">
            <table class="table table-bordered" style="table-layout: fixed;">
                @foreach ($parent->templates as $t)
                    <tr>
                        <td>{!! $t->title !!} {!! $t->tag !!}</td>
                        @if ($t->term !== null)
                            <td>{!! $t->term !!} {!! $t->term > 1 ? 'weeks' : 'week' !!}</td>
                        @endif
                        <td>
                            <a class="btn btn-primary" href="{{ route('templates.edit', ['id' => $t->id]) }}"
                               title="Edit template"><i class="fas fa-pen"></i></a>
                            @if ($t->html_body)
                                <a class="btn btn-dark" href="/admin/template/{{$t->id}}/email"
                                   title="Email to Me"><i class="far fa-envelope"></i></a>
                            @endif
                            @if ($t->pdf)
                                <a target="_blank" class="btn btn-dark" href="/storage/pdf/{!! $t->pdf !!}"
                                   title="Download PDF template">PDF</a>
                            @endif
                            @if ($t->docx)
                                <a target="_blank" class="btn btn-dark" href="/storage/{!! $t->docx !!}"
                                   title="Download PDF template">DOC</a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
        <div class="clearfix"></div>
    @endforeach
@endsection