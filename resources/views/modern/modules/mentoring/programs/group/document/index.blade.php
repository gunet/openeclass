@extends($is_in_tinymce ? 'layouts.embed' : 'layouts.default')

@section('content')

<?php load_js('tinymce.popup.urlgrabber.min.js');?>

<div class="col-12 main-section">
    <div class='{{ $container }}'>
        <div class="row m-auto">

                    @if($is_group_doc == 1)
                        @if($isCommonGroup == 1)
                            <nav class='breadcrumb_mentoring' style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/mentoring_platform_home.php"><span class='fa fa-home'></span>&nbsp{{ trans('langHomeMentoringPlatform') }}</a></li>
                                    <li class="breadcrumb-item"><a class='TextSemiBold showProgramsBtn' href="{{ $urlAppend }}modules/mentoring/programs/show_programs.php">{{ trans('langOurMentoringPrograms') }}</a></li>
                                    <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/myprograms.php">{{ trans('langMyPrograms') }}</a></li>
                                    <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}mentoring_programs/{{ $mentoring_program_code }}/index.php">{!! show_mentoring_program_title($mentoring_program_code) !!}</a></li>
                                    <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/group/select_group.php">{{ trans('langMentoringSpace')}}</a></li>
                                    <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/group/group_space.php?space_group_id={!! getInDirectReference($group_id) !!}">{!! show_mentoring_program_group_name($group_id) !!}</a></li>
                                    <li class="breadcrumb-item active TextMedium" aria-current="page">{{ $toolName }}</li>
                                </ol>
                            </nav>
                        @else
                            <nav class='breadcrumb_mentoring' style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/mentoring_platform_home.php"><span class='fa fa-home'></span>&nbsp{{ trans('langHomeMentoringPlatform') }}</a></li>
                                    <li class="breadcrumb-item"><a class='TextSemiBold showProgramsBtn' href="{{ $urlAppend }}modules/mentoring/programs/show_programs.php">{{ trans('langOurMentoringPrograms') }}</a></li>
                                    <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/myprograms.php">{{ trans('langMyPrograms') }}</a></li>
                                    <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}mentoring_programs/{{ $mentoring_program_code }}/index.php">{!! show_mentoring_program_title($mentoring_program_code) !!}</a></li>
                                    <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/group/select_group.php">{{ trans('langMentoringSpace')}}</a></li>
                                    <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/group/index.php">{{ trans('langGroupMentorsMentees') }}</a></li>
                                    <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/group/group_space.php?space_group_id={!! getInDirectReference($group_id) !!}">{!! show_mentoring_program_group_name($group_id) !!}</a></li>
                                    <li class="breadcrumb-item active TextMedium" aria-current="page">{{ $toolName }}</li>
                                </ol>
                            </nav>
                        @endif
                    @else
                        <nav class='breadcrumb_mentoring' style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/mentoring_platform_home.php"><span class='fa fa-home'></span>&nbsp{{ trans('langHomeMentoringPlatform') }}</a></li>
                                <li class="breadcrumb-item active TextMedium" aria-current="page">{{ $toolName }}</li>
                            </ol>
                        </nav>
                    @endif

                    @include('modules.mentoring.common.common_current_title')

                    @if(!isset($_GET['mydocs']) and !isset($_GET['common_docs']))
                    <div class='col-12 mb-4 ps-3 pe-3'>
                        <div class='col-lg-7 col-md-9 col-12 ms-auto me-auto ps-3 pe-3'>
                            <p class='TextMedium text-center text-justify'>{!! trans('langInfoGroupDocsText')!!}</p>
                        </div>
                    </div>
                    @endif

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
                    
                    {!! $actionBar !!}


                    @if($dialogBox)
                        <div class='col-12'>
                            @include("modules.mentoring.programs.group.document.$dialogBox", ['menuTypeID' => $menuTypeID])
                        </div>
                    @endif

                    @if($metaDataBox)
                        <div class='col-12'>
                            {!! $metaDataBox !!}
                        </div>
                    @endif

                    @if (count($fileInfo) or $curDirName)
                        <div class='col-12  @if($dialogBox or $metaDataBox) mt-3 @endif'>
                            <div class='panel rounded-2 solidPanel'>
                                <div class='panel-body docPanel rounded-2 bg-white'>
                                    <div class='row'>
                                        <div class='col-9 d-md-flex justify-content-md-start align-items-md-center'>
                                            {!! make_clickable_path($curDirPath) !!}
                                            @if ($downloadPath)
                                                &nbsp&nbsp{!! icon('fa-download', trans('langDownloadDir'), $downloadPath) !!}
                                            @endif
                                            @if ($curDirName and $dirComment)
                                                {{ $dirComment }}
                                            @endif
                                        </div>
                                        <div class='col-3 d-flex justify-content-end align-items-center'>
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
                                <table class='table-default rounded-2 bg-white' id="document_table">

                                    <thead>
                                        <tr class="list-header">
                                            <th>{!! trans('langType') !!}</th>
                                            <th>{!! trans('langName') !!}</th>
                                            <th>{{ trans('langSize') }}</th>
                                            <th>{{ trans('langDate') }}</th>
                                            
                                            @unless ($is_in_tinymce)
                                                <th>{!! icon('fa-cogs', trans('langCommands')) !!}</th>
                                            @endif
                                        </tr>
                                    </thead>

                                    <tbody>

                                    @forelse ($fileInfo as $file)
                                       @if($file->visible == 1 or $can_upload_mentoring)
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
                                                @if ($can_upload_mentoring or ($uploading_as_user and $file->lock_user_id == $uid))
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
                                                    <td>@php $sizeKb = 0.000977*$file->size; @endphp {{ $sizeKb }}KB</td>
                                                    <td title='{!! format_locale_date(strtotime($file->date), 'FULL', false) !!}' class='center'>{{ format_locale_date(strtotime($file->date), 'short') }}</td>
                                                @else
                                                    <td><span style="opacity-50">{{ $file->size }}</span></td>
                                                    <td title='{!! format_locale_date(strtotime($file->date), 'FULL', false) !!}' class='center'><span class="opacity-50">{{ format_locale_date(strtotime($file->date), 'short') }}</span></td>
                                                @endif


                                            @endif
                                           
                                            @unless ($is_in_tinymce)
                                                <td class='text-end {{ $can_upload_mentoring? 'option-btn-cell': '' }}'>
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
                            <div class='col-12 bg-white p-3 rounded-2 solidPanel'>
                                <div class='alert alert-warning rounded-2'>{{ trans('langNoDocuments') }}</div>
                            </div>
                        </div>
                    @endif
                
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



<script type='text/javascript'>
    $(document).ready(function(){

        if($('#settingsDocYes').is(":checked")){
           $('#settingsDocNo').attr('disabled',true);
        }
        $('#settingsDocYes').on('click',function(){
            if($('#settingsDocYes').is(":checked")){
                $('#settingsDocNo').attr('disabled',true);
            }else{
                $('#settingsDocNo').prop("disabled", false);
            }
            
        });


        if($('#settingsDocNo').is(":checked")){
           $('#settingsDocYes').attr('disabled',true);
        }
        $('#settingsDocNo').on('click',function(){
            if($('#settingsDocNo').is(":checked")){
                $('#settingsDocYes').attr('disabled',true);
            }else{
                $('#settingsDocYes').prop("disabled", false);
            }
            
        });

        $('.showProgramsBtn').on('click',function(){
            localStorage.setItem("MenuMentoring","program");
        });

    })
   
</script>

@endsection
