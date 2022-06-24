@extends($is_in_tinymce ? 'layouts.embed' : 'layouts.default')

@section('content')
    {!! $actionBar !!}

    @if ($dialogBox)
        @include("modules.document.$dialogBox")
    @endif

    @if (count($fileInfo) or $curDirName)
        <div class='row'>
            <div class='col-md-12'>
                <div class='panel'>
                    <div class='panel-body'>
                        @if ($curDirName)
                            <div class='pull-right'>
                                <a href='{{ $parentLink }}' type='button' class='btn btn-success'>
                                    <span class='fa fa-level-up'></span>&nbsp;{{ trans('langUp') }}
                                </a>
                            </div>
                        @endif
                        <div>
                            {!! make_clickable_path($curDirPath) !!}
                        </div>
                        @if ($curDirName and $dirComment)
                            <div>{{ $dirComment }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class='row'>
            <div class='col-md-12'>
                <div class='table-responsive'>
                    <table class='table-default'>
                        <tr class='list-header'>
                            <th class='text-left' width='60'>{!! trans('langType') !!}</th>
                            <th class='text-left'>{!! trans('langName') !!}</th>
                            <th class='text-left'>{{ trans('langSize') }}</th>
                            <th class='text-left'>{!! trans('langDate') !!}</th>
                        </tr>

                        @forelse ($fileInfo as $file)
                        
                            <tr class='{{ !$file->visible || ($file->extra_path && !$file->common_doc_visible) ? 'not_visible' : 'visible' }}'>
                                <td class='text-center'><span class='fa {{ $file->icon }}'></span></td>
                                <td>
                                    @if ($file->updated_message)
                                        <span class='label label-success pull-right'>
                                            {{ $file->updated_message }}
                                        </span>
                                    @endif
                                    @if ($file->is_dir)
                                        <a href='{{ $file->url }}'>{{ $file->title }}</a>
                                    @else
                                        {!! $file->link !!}
                                    @endif
                                    @if ($can_upload)
                                        @if ($file->extra_path)
                                            @if ($file->common_doc_path)
                                                @if ($file->common_doc_visible)
                                                    {!! icon('common', trans('langCommonDocLink')) !!}
                                                @else
                                                    {!! icon('common-invisible', trans('langCommonDocLinkInvisible')) !!}
                                                @endif
                                            @else
                                                {!! icon('fa-external-link', trans('langExternalFile')) !!}
                                            @endif
                                        @endif
                                        @if (!$file->public)
                                            {!! icon('fa-lock', trans('langNonPublicFile')) !!}
                                        @endif
                                        @if ($file->editable)
                                            {!! icon('fa-edit', trans('langEdit'), $file->edit_url) !!}
                                        @endif
                                    @endif
                                    @if ($file->copyrighted)
                                        {!! icon($file->copyright_icon, $file->copyright_title, $file->copyright_link,
                                            'target="_blank" style="color:#555555;"') !!}
                                    @endif
                                    @if ($file->comment)
                                        <br>
                                        <span class='comment text-muted'>
                                            <small>
                                                {!! nl2br(e($file->comment)) !!}
                                            </small>
                                        </span>
                                    @endif
                                </td>
                                @if ($file->is_dir)
                                    <td>&nbsp;</td>
                                    <td class='center'>{{ $file->date }}</td>
                                @elseif ($file->format == '.meta')
                                    <td>{{ $file->size }}</td>
                                    <td class='center'>{{ $file->date }}</td>
                                @else
                                    <td>{{ $file->size }}</td>
                                    <td title='{{ $file->date_time }}' class='center'>{{ $file->date }}</td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan='4'>
                                    <p class='not_visible text-center'> - {{ trans('langNoDocuments') }} - </p>
                                </td>
                            </tr>
                        @endforelse
                    </table>
                </div>
            </div>
        </div>
    @else
        <div class='alert alert-warning'>{{ trans('langNoDocuments') }}</div>
    @endif

    <script>
    $(function(){
        $('.fileModal').click(function (e)
        {
            e.preventDefault();
            var fileURL = $(this).attr('href');
            var downloadURL = $(this).prev('input').val();
            var fileTitle = $(this).attr('title');
            bootbox.dialog({
                size: 'large',
                title: fileTitle,
                message: '<div class="row">'+
                            '<div class="col-sm-12">'+
                                '<div class="iframe-container"><iframe id="fileFrame" src="'+fileURL+'"></iframe></div>'+
                            '</div>'+
                        '</div>',
                buttons: {
                    download: {
                        label: '<span class="fa fa-download"></span> {{ trans('langDownload') }}',
                        className: 'btn-success',
                        callback: function (d) {
                            window.location = downloadURL;
                        }
                    },
                    print: {
                        label: '<span class="fa fa-print"></span> {{ trans('langPrint') }}',
                        className: 'btn-primary',
                        callback: function (d) {
                            var iframe = document.getElementById('fileFrame');
                            iframe.contentWindow.print();
                        }
                    },
                    cancel: {
                        label: '{{ trans('langCancel') }}',
                        className: 'btn-default'
                    }
                }
            });
        });
    });
    </script>

@endsection

