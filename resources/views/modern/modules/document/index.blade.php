@extends($is_in_tinymce ? 'layouts.embed' : 'layouts.default')

@section('content')

<?php load_js('tinymce.popup.urlgrabber.min.js');?>

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            @if($course_code)
            <div id="background-cheat-leftnav" class="col-xl-2 col-lg-3 col_sidebar_active">
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>
            @endif

            @if($course_code)
            <div class="col-xl-10 col-lg-9 col-12 col_maincontent_active">
            @else
            <div class="col-12 col_maincontent_active_Homepage">
            @endif

                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    @if(!$is_in_tinymce)
                        @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])
                    @endif


                    @if($course_code and !$is_in_tinymce)
                    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools" aria-labelledby="offcanvasExampleLabel">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                            @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                        </div>
                    </div>
                    @endif

                    @if(!$is_in_tinymce)
                        @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])
                    @endif

                    {!! $actionBar !!}

                    @if(Session::has('message'))
                    <div class='col-12 all-alerts'>
                        <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach
                            @else
                                {!! Session::get('message') !!}
                            @endif
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif



                    @if ($dialogBox)
                        @if($menuTypeID == 3 or $menuTypeID == 1)
                            <div class='col-lg-6 col-12 d-none d-md-none d-lg-block'>
                                <div class='col-12 h-100 left-form'></div>
                            </div>
                            <div class='col-lg-6 col-12'>
                        @else
                            <div class='col-12'>
                        @endif
                            @include("modules.document.$dialogBox", ['menuTypeID' => $menuTypeID])
                        </div>
                    @endif

                    @if($metaDataBox)
                        <div class='col-12'>
                            {!! $metaDataBox !!}
                        </div>
                    @endif

                    @if (count($fileInfo) or $curDirName)

                        <div class='col-12  @if($dialogBox or $metaDataBox) mt-3 @endif'>
                            <div class='panel Borders'>
                                <div class='panel-body docPanel Borders'>
                                    <div class='row'>
                                        <div class='col-9 d-flex justidy-content-center align-items-center'>
                                            {!! make_clickable_path($curDirPath) !!}
                                            @if ($downloadPath)
                                                &nbsp;&nbsp;{!! icon('fa-download', trans('langDownloadDir'), $downloadPath) !!}
                                            @endif
                                            @if ($curDirName and $dirComment)
                                                {{ $dirComment }}
                                            @endif
                                        </div>
                                        <div class='col-3 text-end'>
                                            @if ($curDirName)
                                                <a href='{{$parentLink}}' type='button' class='btn submitAdminBtn'>
                                                    <span class='fa fa-level-up'></span>&nbsp;{{ trans('langUp') }}
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class='col-12'>
                            <div class='table-responsive'>
                                <table class='table-default' id="document_table">

                                    <thead>
                                        <tr class="list-header">
                                            <th>{!! headlink(trans('langType'), 'type') !!}</th>
                                            <th>{!! headlink(trans('langName'), 'name') !!}</th>
                                            <th>{{trans('langCurrentStatus')}}</th>
                                            <th>{{ trans('langSize') }}</th>
                                            <th>{!! headlink(trans('langDate'), 'date') !!}</th>
                                            <th>{{trans('langShow')}}</th>
                                            @unless ($is_in_tinymce)
                                                <th class='text-center'>{!! icon('fa-cogs', trans('langCommands')) !!}</th>
                                            @endif
                                        </tr>
                                    </thead>

                                    <tbody>

                                    @forelse ($fileInfo as $file)

                                        <tr class="{{ !$file->visible || ($file->extra_path && !$file->common_doc_visible) ? 'not_visible' : 'visible' }}">


                                            <td class='text-start'>
                                                @if($file->visible == 1)
                                                    <span class='visibleFile fa {{ $file->icon }}'></span>
                                                @else
                                                    <span class='invisibleFile fa {{ $file->icon }}'></span>
                                                @endif
                                            </td>


                                            <td>
                                                @php $downloadfile = $base_url . "download=" . getIndirectReference($file->path); @endphp
                                                <input type='hidden' value={!!$downloadfile!!}>

                                                @if ($file->is_dir)
                                                    @if($file->visible == 1)
                                                        <a href='{!! $file->url !!}'>{{ $file->filename }}</a>
                                                    @else
                                                        <a class="opacity-50 text-secondary pe-none" href='{!! $file->url !!}'>{{ $file->filename }}</a>
                                                    @endif
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



                                            <td>
                                                @if($file->updated_message)
                                                    @if($file->visible == 1)
                                                        <button class="btn btn-success btn-sm UpdateMessage">{{ $file->updated_message }}</button>
                                                    @else
                                                        <button class="btn btn-secondary btn-sm UpdatedMessage">{{ $file->updated_message }}</button>
                                                    @endif
                                                @else
                                                    @if($file->visible == 1)
                                                        <button class="btn btn-secondary btn-sm DocVisible">{{trans('langDocument')}}</button>
                                                    @else
                                                        <button class="btn btn-secondary btn-sm DocInvisible">{{trans('langDocument')}}</button>
                                                    @endif
                                                @endif
                                            </td>



                                            @if ($file->is_dir)
                                                <td>&nbsp;</td>
                                                @if($file->visible == 1)
                                                    <td class='center'>{{ $file->date }}</td>
                                                @else
                                                    <td class='center'><span class="opacity-50 text-secondary">{{ $file->date }}</span></td>
                                                @endif

                                            @elseif ($file->format == '.meta')
                                                @if($file->visible == 1)
                                                    <td>{{ $file->size }}</td>
                                                    <td class='center'>{{ $file->date }}</td>
                                                @else
                                                    <td><span class="opacity-50">{{ $file->size }}</span></td>
                                                    <td class='center'><span class="opacity-50">{{ $file->date }}</span></td>
                                                @endif

                                            @else
                                                @if($file->visible == 1)
                                                    <td>{{ $file->size }}</td>
                                                    <td title='{{ format_locale_date(strtotime($file->date), 'short', false) }}' class='center'>{{ format_locale_date(strtotime($file->date), 'short') }}</td>
                                                @else
                                                    <td><span style="opacity-50">{{ $file->size }}</span></td>
                                                    <td title='{{ format_locale_date(strtotime($file->date), 'short', false) }}' class='center'><span class="opacity-50">{{ format_locale_date(strtotime($file->date), 'short') }}</span></td>
                                                @endif


                                            @endif
                                            <td>
                                                @if($file->visible == 1)
                                                    <span class="text-success"><i class="fas fa-eye"></i> {{trans('langVisible')}}</span>
                                                @else
                                                    <span class="text-danger"><i class="fas fa-eye"></i> {{trans('langInvisible ')}}</span>
                                                @endif
                                            </td>
                                            @unless ($is_in_tinymce)
                                                <td class='text-center {{ $can_upload? 'option-btn-cell': 'text-end'}}'>
                                                    {!! $file->action_button !!}
                                                </td>
                                            @endif
                                        </tr>



                                    @empty
                                        <tr>
                                            <td colspan='5'>
                                                <p class='not_visible text-center'> - {{ trans('langNoDocuments') }} - </p>
                                            </td>
                                        </tr>

                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    @else
                        <div class='col-12'><div class='alert alert-warning'>{{ trans('langNoDocuments') }}</div></div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>



<script>
    $('.fileModal').click(function (e)
    {
        e.preventDefault();
        var fileURL = $(this).attr('href');
        var downloadURL = $(this).prev('input').val();
        var fileTitle = $(this).attr('title');

        console.log('the fileURL:'+fileURL);
        console.log('the downloadURL:'+downloadURL);

        // BUTTONS declare
        var bts = {
            download: {
                label: '<span class="fa fa-download"></span> {{ trans('langDownload') }}',
                className: 'submitAdminBtn',
                callback: function (d) {
                    window.location = downloadURL;
                }
            },
            print: {
                label: '<span class="fa fa-print"></span> {{ trans('langPrint') }}',
                className: 'submitAdminBtn',
                callback: function (d) {
                    var iframe = document.getElementById('fileFrame');
                    iframe.contentWindow.print();
                }
            }
        };
        if (screenfull.enabled) {
            bts.fullscreen = {
                label: '<span class="fa fa-arrows-alt"></span> {{ trans('langFullScreen') }}',
                className: 'submitAdminBtn',
                callback: function() {
                    screenfull.request(document.getElementById('fileFrame'));
                    return false;
                }
            };
        }
        bts.newtab = {
            label: '<span class="fa fa-plus"></span> {{ trans('langNewTab') }}',
            className: 'submitAdminBtn',
            callback: function() {
                window.open(fileURL);
                return false;
            }
        };
        bts.cancel = {
            label: '{{ trans('langCancel') }}',
            className: 'cancelAdminBtn'
        };

        bootbox.dialog({
            size: 'large',
            title: fileTitle,
            message: '<div class="row">'+
                        '<div class="col-12">'+
                            '<div class="iframe-container" style="height:500px;"><iframe id="fileFrame" src="'+fileURL+'" style="width:100%; height:500px;"></iframe></div>'+
                        '</div>'+
                    '</div>',
            buttons: bts
        });
    });

</script>

@endsection
