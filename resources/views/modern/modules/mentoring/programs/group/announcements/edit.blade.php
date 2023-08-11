
@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }}'>
        <div class="row m-auto">

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

                    @include('modules.mentoring.common.common_current_title')
                    
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
                        
                        <div class='contentAddAnnounce'>
                            <form class='form-wrapper form-edit' method="post" action="{{ $_SERVER['SCRIPT_NAME'] }}?group_id={!! getInDirectReference($group_id) !!}">
                                <div class="w-100 p-0 border-0">
                                    <div class="modal-content border-0">
                                        <div class="modal-header border-0">
                                            <h3 class="modal-title TextBold" id="AddAnnounceGroupModalLabel">{{ trans('langAddAnn') }}</h3>
                                     
                                        </div>
                                        <div class="modal-body">

                                            <div class="row ms-0 mt-4 form-group {{ $antitle_error }}">
                                                <label class="col-md-3 col-12 control-label-notes">{{ trans('langAnnTitle') }}:</label>
                                                <div class="col-md-9 col-12 ">
                                                    <input class="form-control" placeholder="{{ trans('langAnnTitle') }}..." type="text" name="antitle" value="{{ $titleToModify }}"/>
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
                                                    @php 
                                                        $arrayRecievers = array();
                                                        foreach($allRecievers as $r){
                                                            $arrayRecievers[] = $r->toUser;
                                                        }
                                                    @endphp
                                                    <select class='form-select' name='recipients[]' multiple='multiple' id='select-users'>
                                                        <option value='-1' {{ (in_array('-1',$arrayRecievers)) ? 'selected' : ''}}>{{ trans('langAllUsers') }}</option>
                                                        
                                                        @foreach ($group_users as $cu)
                                                            <option value='{{ $cu->user_id }}' {{ (in_array($cu->user_id,$arrayRecievers)) ? 'selected' : ''}}>{{$cu->name}} ({{$cu->email}})</option>
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
                                            <input type='hidden' name='id' value='{{ $announce_id }}'>
                                           
                                            <button type='submit' class="btn submitAdminBtn" name="submitAnnouncement">
                                                {{ trans('langSubmit') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    @endif
                   

                    

                   
                    

                

        </div>
      
    </div>
</div>

<script type="text/javascript">
    $(document).ready( function () {
     
   
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

        $('.showProgramsBtn').on('click',function(){
            localStorage.setItem("MenuMentoring","program");
        });

    } );
</script>
@endsection




