@extends($is_in_tinymce ? 'layouts.embed' : 'layouts.default')

@section('content')
<div class="col-12 main-section">
<div class='container module-container py-lg-0'>
        <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

            <div id="background-cheat-leftnav" class="col_sidebar_active d-flex justify-content-start align-items-strech ps-lg-0 pe-lg-0">
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block ContentLeftNav">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>

            <div class="col_maincontent_active col_maincontent_active_module">

                <div class="row">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                            @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                        </div>
                    </div>

                    @include('layouts.partials.legend_view')

                    {!! $actionBar !!}

                    @if ($dialogBox)
                        @include("modules.document.$dialogBox")
                    @endif

                    @if (count($fileInfo) or $curDirName)

                            <div class='col-md-12'>
                                <div class='panel'>
                                    <div class='panel-body'>
                                        @if ($curDirName)
                                            <div class='float-end'>
                                                <a href='{{ $parentLink }}' type='button' class='btn submitAdminBtn'>
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



                            <div class='col-md-12'>
                                <div class='table-responsive'>
                                    <table class='table-default'>
                                        <thead>
                                        <tr class='list-header'>
                                            <th>{!! trans('langType') !!}</th>
                                            <th>{!! trans('langName') !!}</th>
                                            <th>{{ trans('langSize') }}</th>
                                            <th>{!! trans('langDate') !!}</th>
                                        </tr></thead>

                                        @forelse ($fileInfo as $file)

                                            <tr class='{{ !$file->visible || ($file->extra_path && !$file->common_doc_visible) ? 'not_visible' : 'visible' }}'>
                                                <td><span class='fa {{ $file->icon }}'></span></td>
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
                                                    <td>{{ $file->date }}</td>
                                                @elseif ($file->format == '.meta')
                                                    <td>{{ $file->size }}</td>
                                                    <td>{{ $file->date }}</td>
                                                @else
                                                    <td>{{ $file->size }}</td>
                                                    <td title='{{ $file->date_time }}'>{{ $file->date }}</td>
                                                @endif
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan='4'>
                                                    <p class='not_visible'> - {{ trans('langNoDocuments') }} - </p>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </table>
                                </div>
                            </div>

                    @else
                        <div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>{{ trans('langNoDocuments') }}</span></div>
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
                        label: '<i class="fa fa-download"></i> {{ trans('langDownload') }}',
                        className: 'submitAdminBtn gap-1',
                        callback: function (d) {
                            window.location = downloadURL;
                        }
                    },
                    print: {
                        label: '<i class="fa fa-print"></i> {{ trans('langPrint') }}',
                        className: 'submitAdminBtn gap-1',
                        callback: function (d) {
                            var iframe = document.getElementById('fileFrame');
                            iframe.contentWindow.print();
                        }
                    },
                    cancel: {
                        label: '{{ trans('langCancel') }}',
                        className: 'cancelAdminBtn'
                    }
                }
            });
        });
    });
    </script>

@endsection
