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
                            <form>
                                @csrf
                                <a class="btn btn-primary" href="{{ route('templates.edit', ['id' => $t->id]) }}"
                                   title="Edit template"><i class="fas fa-pen"></i></a>
                                <input type="file" name="file" class="d-none" accept=".docx"/>
                                <input type="hidden" name="alias" value="{!! $t->alias !!}">
                                <a href="#" style="margin: 0 20px;" class="upload-file"><i class="fas fa-upload"></i>
                                    Upload</a>
                                <a download @if ($t->docx)
                                href="/storage/docx/{!! $t->docx !!}"
                                   @else
                                   href="#" class='disabled'
                                        @endif
                                ><i class="fas fa-download"></i> Download</a>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
        <div class="clearfix"></div>
    @endforeach
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
                        const a = form.find('a');
                        a.removeClass('disabled');
                        a.attr('href', storage_path + data);
                    }
                }
            });
        });

    </script>
@endsection