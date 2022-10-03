@extends($is_in_tinymce ? 'layouts.embed' : 'layouts.default')

@section('content')
<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">
    <div class="container-fluid main-container">
        <div class="row rowMedium">

            <div id="background-cheat-leftnav" class="col-xl-2 col-lg-3 col-md-0 col-sm-0 col-0 justify-content-center col_sidebar_active">
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>

            <div class="col-xl-10 col-lg-9 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active">

                <div class="row p-lg-5 p-md-5 ps-1 pe-2 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools" aria-labelledby="offcanvasExampleLabel">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                            @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                        </div>
                    </div>

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

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
                                            <div class='float-end'>
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
                                                        <span class='label label-success float-end'>
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
                </div>
            </div>
        </div>
    </div>
</div>

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

