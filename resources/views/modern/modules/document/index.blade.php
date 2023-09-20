@extends($is_in_tinymce ? 'layouts.embed' : 'layouts.default')

@section('content')

<?php load_js('tinymce.popup.urlgrabber.min.js');?>

<div class="col-12 main-section">
<div class='{{ $container }} @if($course_code) py-lg-0 @endif'>
        <div class="@if($course_code) course-wrapper d-lg-flex align-items-lg-strech w-100 @else row m-auto @endif">

            @if($course_code)
            <div id="background-cheat-leftnav" class="col_sidebar_active d-flex justify-content-start align-items-strech ps-lg-0 pe-lg-0">
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block ContentLeftNav">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>
            @endif

            @if($course_code)
            <div class="col_maincontent_active">
            @else
            <div class="col-12">
            @endif

                <div class="row">

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
                        @include('layouts.partials.legend_view')
                    @endif

                    {!! $actionBar !!}

                    @if(Session::has('message'))
                    <div class='col-12 all-alerts'>
                        <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @php 
                                $alert_type = '';
                                if(Session::get('alert-class', 'alert-info') == 'alert-success'){
                                    $alert_type = "<i class='fa-solid fa-circle-check fa-lg'></i>";
                                }elseif(Session::get('alert-class', 'alert-info') == 'alert-info'){
                                    $alert_type = "<i class='fa-solid fa-circle-info fa-lg'></i>";
                                }elseif(Session::get('alert-class', 'alert-info') == 'alert-warning'){
                                    $alert_type = "<i class='fa-solid fa-triangle-exclamation fa-lg'></i>";
                                }else{
                                    $alert_type = "<i class='fa-solid fa-circle-xmark fa-lg'></i>";
                                }
                            @endphp
                            
                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                {!! $alert_type !!}<span>
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach</span>
                            @else
                                {!! $alert_type !!}<span>{!! Session::get('message') !!}</span>
                            @endif
                            
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif



                    @if ($dialogBox)
                        <div class='col-12'>
                            @include("modules.document.$dialogBox", ['menuTypeID' => $menuTypeID])
                        </div>
                    @endif

                    @if($metaDataBox)
                        <div class='col-12'>
                            {!! $metaDataBox !!}
                        </div>
                    @endif

                    @if (count($fileInfo) or $curDirName)

                        <div class='col-12  @if($dialogBox or $metaDataBox) mt-4 @endif'>
                            <div class='panel smallRadius'>
                                <div class='panel-body docPanel smallRadius @if(isset($course_code) and $course_code) bg-light @else bg-white @endif'>
                                    <div class='row'>
                                        <div class='col-9 d-flex justify-content-start align-items-center flex-wrap'>
                                            {!! make_clickable_path($curDirPath) !!}
                                            @if ($downloadPath)
                                                &nbsp&nbsp{!! icon('fa-download', trans('langDownloadDir'), $downloadPath) !!}
                                            @endif
                                            @if ($curDirName and $dirComment)
                                                {{ $dirComment }}
                                            @endif
                                        </div>
                                        <div class='col-3 d-flex justify-content-end align-items-center flex-wrap'>
                                            @if ($curDirName)
                                                <a href='{{$parentLink}}' type='button' class='btn submitAdminBtn'>
                                                    <span class='fa fa-level-up'></span><span class='hidden-xs TextBold'>{{ trans('langUp') }}</span>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class='col-12'>
                            <div class='table-responsive mt-4'>
                                <table class='table-default' id="document_table">

                                    <thead>
                                        <tr class="list-header">
                                            
                                            <th>{!! headlink(trans('langName'), 'name') !!}</th>
                                            <th>{!! headlink(trans('langType'), 'type') !!}</th>
                                            <th>{{ trans('langSize') }}</th>
                                            <th>{!! headlink(trans('langDate'), 'date') !!}</th>

                                            @unless ($is_in_tinymce)
                                                <th>{!! icon('fa-cogs', trans('langCommands')) !!}</th>
                                            @endif
                                        </tr>
                                    </thead>

                                    <tbody>

                                    @forelse ($fileInfo as $file)

                                        @if($file->visible == 1 or $can_upload)
                                            <tr class="{{ !$file->visible || ($file->extra_path && !$file->common_doc_visible) ? 'not_visible' : 'visible' }}">


                                                

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

                                                    @if($file->updated_message)
                                                        @if($file->visible == 1)
                                                            <span class="badge bg-success">{{ $file->updated_message }}</span>
                                                        @else
                                                            <span class="badge bg-secondary">{{ $file->updated_message }}</span>
                                                        @endif
                                                    @endif
                                                </td>


                                                <td>
                                                    @if($file->visible == 1)
                                                        <span class='visibleFile'>{{ $file->format }}</span>
                                                    @else
                                                        <span class='invisibleFile'>{{ $file->format }}</span>
                                                    @endif
                                                </td>


                                                @if ($file->is_dir)
                                                    <td>&nbsp;</td>
                                                    @if($file->visible == 1)
                                                        <td>{{ $file->date }}</td>
                                                    @else
                                                        <td><span class="opacity-50 text-secondary">{{ $file->date }}</span></td>
                                                    @endif

                                                @elseif ($file->format == '.meta')
                                                    @if($file->visible == 1)
                                                        <td>{{ $file->size }}</td>
                                                        <td>{{ $file->date }}</td>
                                                    @else
                                                        <td><span class="opacity-50">{{ $file->size }}</span></td>
                                                        <td><span class="opacity-50">{{ $file->date }}</span></td>
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
                                                
                                                @unless ($is_in_tinymce)
                                                    <td class='text-end {{ $can_upload? 'option-btn-cell': '' }}'>
                                                        {!! $file->action_button !!}
                                                    </td>
                                                @endif
                                            </tr>
                                        @endif



                                    @empty
                                        <tr>
                                            <td colspan='5'>
                                                <p class='not_visible'> - {{ trans('langNoDocuments') }} - </p>
                                            </td>
                                        </tr>

                                    @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    @else
                        <div class='col-12'>
                            <div class='alert alert-warning'>
                                <i class="fa-solid fa-triangle-exclamation fa-lg"></i>
                                <span>{{ trans('langNoDocuments') }}</span>
                            </div>
                        </div>
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
