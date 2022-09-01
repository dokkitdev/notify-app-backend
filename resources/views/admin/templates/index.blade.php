@extends('admin.layout')
@section('css')
    <style>
        a.disabled {
            cursor: not-allowed;
        }
    </style>
@endsection
@section('content')
    <div class="container-fluid">
        <div class="page-title-box">
            <h4 class="page-title">Templates</h4>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="card-box">
                    <div id="accordion" class="mb-3">
                        @foreach ($templateParents as $key => $parent)
                        <div class="card mb-1">
                            <div class="card-header" id="heading-{!! $key !!}">
                                <h5 class="m-0">
                                    <a class="text-dark collapsed" data-toggle="collapse" href="#collapse-{!! $key !!}" aria-expanded="false">
                                        {!! $parent->title !!}
                                    </a>
                                </h5>
                            </div>
                            <div id="collapse-{!! $key !!}" class="collapse" aria-labelledby="heading-{!! $key !!}" data-parent="#accordion" style="">
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
                </div>
            </div>
        </div>
    </div>

@endsection
@section('js')
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
                        $.toast({
                            heading: "Upload",
                            text: "File uploaded successfully",
                            position: "top-right",
                            loaderBg: '#3b98b5',
                            icon: "info",
                        });
                    }
                },
                fail: data => {
                    $.toast({
                        heading: "Upload",
                        text: "Error via upload file",
                        position: "top-right",
                        loaderBg: '#bf441d',
                        icon: "error",
                    });
                }
            });
        });

    </script>
@endsection
