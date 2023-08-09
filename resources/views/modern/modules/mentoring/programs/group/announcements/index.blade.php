
@extends('layouts.default')

@section('content')


<div class="col-12 main-section">
    <div class='{{ $container }}'>
        <div class="row rowMargin">

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

                    @include('modules.mentoring.common.common_current_title')

                    <div class='col-12 mb-4 ps-3 pe-3'>
                        <div class='col-lg-7 col-md-9 col-12 ms-auto me-auto ps-3 pe-3'>
                            <p class='TextMedium text-center text-justify'>{!! trans('langInfoGroupAnnouncementText')!!}</p>
                        </div>
                    </div>
                    
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
                   
                    <div class='col-3'>
                        {!! $action_bar !!}
                    </div>
                    
                   
                    @if($is_editor_current_group or $is_tutor_of_mentoring_program or $is_admin)
                        <div class='col-9 d-flex justify-content-end align-items-start'>
                            <button class="btn btn-outline-success small-text" id='addannouncement'>
                                <span class='fa fa-plus'></span>&nbsp{{ trans('langAddAnn') }}
                            </button>
                        </div>

                        <div class='contentAddAnnounce m-auto d-none mb-3'>
                            <form class='form-wrapper form-edit' method="post" action="{{ $_SERVER['SCRIPT_NAME'] }}?group_id={!! getInDirectReference($group_id) !!}">
                                <div class="w-100 p-0 border-0">
                                    <div class="modal-content border-0">
                                        <div class="modal-header border-0">
                                            <h3 class="modal-title TextBold" id="AddAnnounceGroupModalLabel">{{ trans('langAddAnn') }}</h3>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">

                                            <div class="row mt-4 ms-0 form-group {{ $antitle_error }}">
                                                <label class="col-md-3 col-12 control-label-notes">{{ trans('langAnnTitle') }}:</label>
                                                <div class="col-md-9 col-12 ">
                                                    <input class="form-control" placeholder="{{ trans('langAnnTitle') }}..." type="text" name="antitle" value="{{ $titleToModify }}" required>
                                                    <span class='help-block'>{{ Session::getError('antitle') }}</span>
                                                </div>
                                            </div>

                                            <div class='row ms-0 mt-4 form-groum'>
                                                <label class='col-md-3 col-12 control-label-notes'>{{ trans('langAnnBody') }}:</label>
                                                <div class='col-md-9 col-12'>{!! $contentToModify !!}</div>
                                            </div>

                                            <div class='row ms-0 mt-4 form-group'>
                                                <label class='col-md-3 col-12 control-label-notes'>{{ trans('langEmailOption') }}:</label>
                                                <div class='col-md-9 col-12'>
                                                    <select class='form-select' name='recipients[]' multiple='multiple' id='select-users'>
                                                        <option value='-1' selected>{{ trans('langAllUsers') }}</option>
                                                        @foreach ($group_users as $cu)
                                                            <option value='{{ $cu->user_id }}'>{{$cu->name}} ({{$cu->email}})</option>
                                                        @endforeach
                                                    </select>
                                                    <a href='#' id='selectAll'>{{ trans('langJQCheckAll') }}</a> | <a href='#' id='removeAll'>{{ trans('langJQUncheckAll') }}</a>
                                                </div>
                                            </div>
                                                
                                            <div class='row ms-0 mt-4 form-group {{ $startdate_error }}'>
                                                <label for='startdate' class='col-md-3 col-12 control-label-notes'>{{ trans('langStartDate') }} :</label>
                                                <div class='col-md-9 col-12'>
                                                    <div class='input-group'> 
                                                        <span class='input-group-addon'>
                                                            <label class='label-container'>
                                                                <input class='mt-0' type='checkbox' name='startdate_active' {{ $start_checkbox }}>
                                                                <span class='checkmark'></span>
                                                            </label>
                                                        </span>
                                                        <input class='form-control' name='startdate' id='startdate' type='text' value = '{{ $showFrom }}'>
                                                    </div>
                                                    <span class='help-block'>{{ $startdate_error }}</span>
                                                </div>
                                            </div>
                                        
                                            <div class='row ms-0 mt-4 form-group {{ $enddate_error }}'>
                                                <label for='enddate' class='col-md-3 col-12 control-label-notes'>{{ trans('langEndDate') }} :</label>
                                                <div class='col-md-9 col-12'>
                                                    <div class='input-group'>
                                                        <span class='input-group-addon'>
                                                            <label class='label-container'>
                                                                <input class='mt-0' type='checkbox' name='enddate_active' {{ $end_checkbox}} {{ $end_disabled}}>
                                                                <span class='checkmark'></span>
                                                            </label>
                                                        </span>
                                                        <input class='form-control' name='enddate' id='enddate' type='text' value = '{{ $showUntil }}'>
                                                    </div>
                                                    <span class='help-block'>{{ $enddate_error }}</span>
                                                </div>
                                            </div>

                                            <div class='row ms-0 mt-4 form-group'>
                                                <div class='col-md-9 offset-md-3'>
                                                    <div class='checkbox'>
                                                    <label class='label-container'>
                                                            <input type='checkbox' name='show_public' {{  $checked_public }}><span class='checkmark'></span> {{ trans('langViewShow') }}
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>

                                        <div class="modal-footer">
                                            <input type='hidden' name='id' value=''>
                                            
                                            <button type='submit' class="btn btn-outline-success small-text rounded-2" name="submitAnnouncement">
                                                {{ trans('langSubmit') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    @endif
                       

                    <!--------------------------------------------------------------------------------------------------------------------------->

                    @if(count($allAnnouncements) > 0)
                        @if(!$is_mentee)
                            <div class='col-12'>
                                <table class='table-default rounded-2' id='table_announcements'>
                                    <thead class='list-header'>
                                        <tr>
                                            <th>{{ trans('langTitle') }}</th>
                                            <th>{{ trans('langDate') }}</th>
                                            <th>{{ trans('langFrom') }}</th>
                                            <th>{{ trans('langUntil') }}</th>
                                            <th class='text-center'>{{ trans('langVisible')}}</th>
                                            <th class='text-center'><span class='fa fa-cogs'></span></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($allAnnouncements as $a)
                                            <tr>
                                                <td>
                                                    <a href='{{ $urlAppend }}modules/mentoring/programs/group/announcements/index.php?group_id={!! getInDirectReference($group_id) !!}&show_an_id={!! getInDirectReference($a->id) !!}'>
                                                        {{ $a->title }}
                                                    </a>
                                                </td>
                                                <td>
                                                    {!! format_locale_date(strtotime($a->date ?? '')) !!}
                                                </td>
                                                <td>
                                                    {!! format_locale_date(strtotime($a->start_display ?? '')) !!}
                                                </td>
                                                <td>
                                                    {!! format_locale_date(strtotime($a->stop_display ?? '')) !!}
                                                </td>
                                                <td class='text-center'>
                                                    @if($a->visible == 1)
                                                        <span class='fa fa-check fs-6 text-success'></span>
                                                    @else
                                                        <span class='fa fa-times fs-6 text-danger'></span>
                                                    @endif
                                                </td>
                                                <td class='d-flex justify-content-center'>
                                                    <a id='showEdit' href='{{ $urlAppend }}modules/mentoring/programs/group/announcements/index.php?group_id={!! getInDirectReference($group_id) !!}&modify={!! getInDirectReference($a->id) !!}'>
                                                        <span class='fa fa-edit fs-6' data-bs-toggle="tooltip" data-bs-placement="bottom" title data-bs-original-title="{{ trans('langEdit') }}"></span>
                                                    </a>
                                                    <button class="btn text-danger small-text ms-2 p-0"
                                                        data-bs-toggle="modal" data-bs-target="#DeleteAnnouncementModal{{ $a->id }}" >
                                                        <span class='fa fa-trash fs-6'></span>
                                                    </button>
                                                </td>
                                            </tr>
                                            <div class="modal fade" id="DeleteAnnouncementModal{{ $a->id }}" tabindex="-1" aria-labelledby="DeleteAnnouncementModalLABEL{{ $a->id }}" aria-hidden="true">
                                                <form method="post" action="{{ $_SERVER['SCRIPT_NAME'] }}?group_id={!! getInDirectReference($group_id) !!}">
                                                    <div class="modal-dialog modal-md modal-danger">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="DeleteAnnouncementModalLABEL{{ $a->id }}">{{ trans('langDelete') }}</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                {!! trans('langDeleteAnnouncementGroup') !!}
                                                                <input type='hidden' name='announcement_del_id' value='{{ $a->id }}'>

                                                            </div>
                                                            <div class="modal-footer">
                                                                <a class="btn btn-outline-secondary small-text rounded-2" href="" data-bs-dismiss="modal">{{ trans('langCancel') }}</a>
                                                                <button type='submit' class="btn btn-danger small-text rounded-2" name="delete_announce">
                                                                    {{ trans('langDelete') }}
                                                                </button>

                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class='col-12'>
                                <table class='table-default rounded-2' id='table_announcements'>
                                    <thead class='list-header'>
                                        <tr>
                                            <th>{{ trans('langTitle') }}</th>
                                            <th>{{ trans('langDate') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($allAnnouncements as $a)
                                            <tr>
                                                <td>
                                                    <a href='{{ $urlAppend }}modules/mentoring/programs/group/announcements/index.php?group_id={!! getInDirectReference($group_id) !!}&show_an_id={!! getInDirectReference($a->id) !!}'>
                                                        {{ $a->title }}
                                                    </a>
                                                </td>
                                                <td>
                                                    {!! format_locale_date(strtotime($a->date ?? '')) !!}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    @else
                        <div class='col-12'>
                            <div class='col-12 bg-white p-3 rounded-2 solidPanel'>
                                <div class='alert alert-warning rounded-2'>{{ trans('langNoExistAnnGroup')}}</div>
                            </div>
                        </div>
                    @endif

                    

               

        </div>
      
    </div>
</div>

<script type="text/javascript">
    $(document).ready( function () {

        $('#table_announcements').DataTable();
   
        var langEmptyGroupName = '{{ js_escape(trans('langEmptyAnTitle')) }}';

        if($('input[name=startdate_active]').prop('checked')){
            $('input[name=startdate]').attr('disabled', false);
        }else{
            $('input[name=startdate]').attr('disabled', true);
        }

        if($('input[name=enddate_active]').prop('checked')){
            $('input[name=enddate]').attr('disabled', false);
        }else{
            $('input[name=enddate]').attr('disabled', true);
        }

        $('input[name=startdate_active]').on('click', function() {
            if ($('input[name=startdate_active]').prop('checked')) {
                $('input[name=enddate_active]').prop('disabled', false);
            } else {
                $('input[name=enddate_active]').prop('disabled', true);
                $('input[name=enddate_active]').prop('checked', false);
                $('input[name=enddate_active]').parents('.input-group').children('input').prop('disabled', true);
            }
        });

        $('.input-group-addon input[type=checkbox]').on('click', function(){
            var prop = $(this).parents('.input-group').children('input').prop('disabled');
            if(prop){
                $(this).parents('.input-group').children('input').prop('disabled', false);
            } else {
                $(this).parents('.input-group').children('input').prop('disabled', true);
            }
        });

        $('#selectAll').click(function(e) {
            e.preventDefault();
            var stringVal = [];
            $('#select-users').find('option').each(function(){
                stringVal.push($(this).val());
            });
            $('#select-users').val(stringVal).trigger('change');
        });
        $('#removeAll').click(function(e) {
            e.preventDefault();
            var stringVal = [];
            $('#select-users').val(stringVal).trigger('change');
        });

        $('#startdate, #enddate').datetimepicker({
            format: 'yyyy-mm-dd hh:ii',
            pickerPosition: 'bottom-right',
            language: '{{ $language }}',
            autoclose: true
        });

        $('#addannouncement,#showEdit').on('click',function(){
            $('.contentAddAnnounce').removeClass('d-none');
            $('.contentAddAnnounce').addClass('d-block');
        });

        $('.btn-close').on('click',function(){
            $('.contentAddAnnounce').addClass('d-none');
            $('.contentAddAnnounce').removeClass('d-block');
        });

        $('.showProgramsBtn').on('click',function(){
            localStorage.setItem("MenuMentoring","program");
        });

    } );
</script>
@endsection




