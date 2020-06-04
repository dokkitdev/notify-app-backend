@extends('layouts.app')
@section('css')
    <style>
        a.disabled {
            cursor: not-allowed;
        }
    </style>
@endsection
@section('content')
    <h1>Templates</h1>
    <div class="accordion" id="myAccordion">
        @foreach ($templateParents as $key => $parent)
            <div class="card">
                <div class="card-header" id="accordion-{!! $key !!}">
                    <h2 class="mb-0">
                        <button type="button" class="btn btn-link" data-toggle="collapse"
                                data-target="#accordion-collapse-{!! $key !!}">{!! $parent->title !!}</button>
                    </h2>
                </div>
                <div id="accordion-collapse-{!! $key !!}" class="collapse" aria-labelledby="accordion-{!! $key !!}"
                     data-parent="#myAccordion">
                    <div class="card-body">
                        <table class="table table-bordered" style="table-layout: fixed;">
                            @foreach ($parent->templates as $t)
                                <tr>
                                    <td>{!! $t->title !!} {!! $t->tag !!}</td>
                                    {{--@if ($t->term !== null)--}}
                                        {{--<td>{!! $t->term !!} {!! $t->term > 1 ? 'weeks' : 'week' !!}</td>--}}
                                    {{--@endif--}}
                                    <td>
                                        <form>
                                            @csrf
                                            {{--@if ($t->is_html)--}}
                                                {{--<a class="btn btn-primary"--}}
                                                   {{--href="{{ route('templates.edit', ['id' => $t->id]) }}"--}}
                                                   {{--title="Edit template"><i class="fas fa-pen"></i></a>--}}
                                            {{--@endif--}}
                                            <input type="file" name="file" class="d-none" accept=".docx"/>
                                            <input type="hidden" name="alias" value="{!! $t->alias !!}">
                                            <a href="#" style="margin: 0 20px;" class="upload-file"><i
                                                        class="fas fa-upload"></i>
                                                Upload</a>
                                            <a download @if ($t->docx)
                                            href="/storage/docx/{!! $t->docx !!}" class='download'
                                               @else
                                               href="#" class='download disabled'
                                                    @endif
                                            ><i class="fas fa-download"></i> Download</a>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </table>
                    </div>
                </div>
            </div>

        @endforeach
    </div>
@endsection
@section('js')
    <script src="{{ asset('js/jquery.toaster.js') }}"></script>
    <script>
        const storage_path = '/storage/docx/';

        $('.upload-file').click(function (e) {
            e.preventDefault()
            const form = $(this).closest('form'),
                inputFile = form.find('[type="file"]');
            inputFile.trigger('click');
        });

        $(document).on('click', 'a.disabled', e => {
            e.preventDefault();
        });

        $('input[type="file"]').on('change', function (e) {
            e.preventDefault();
            const form = $(this).closest('form'),
                url = "{{ route('templates.upload_docx') }}",
                data = new FormData(form[0]);

            $.ajax({
                url: url,
                type: 'POST',
                data: data,
                processData: false,
                contentType: false,
                success: data => {
                    if (data) {
                        const a = form.find('a.download');
                        a.removeClass('disabled');
                        a.attr('href', storage_path + data);
                        $.toaster({priority: 'success', title: 'Upload', message: 'File uploaded successfully'});
                    }
                },
                fail: data => {
                    $.toaster({priority: 'danger', title: 'Upload', message: 'Error via upload file'});
                }
            });
        });

    </script>
@endsection
