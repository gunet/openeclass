
@extends('layouts.default')

@section('content')


<div class="col-12 main-section">
    <div class='{{ $container }}'>
        <div class="row rowMargin">

                    <nav class='breadcrumb_mentoring' style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/mentoring_platform_home.php"><span class='fa fa-home'></span>&nbsp{{ trans('langHomeMentoringPlatform') }}</a></li>
                            <li class="breadcrumb-item"><a class='TextSemiBold showProgramsBtn' href="{{ $urlAppend }}modules/mentoring/programs/show_programs.php">{{ trans('langOurMentoringPrograms') }}</a></li>
                            <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/myprograms.php">{{ trans('langMyPrograms') }}</a></li>
                            <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}mentoring_programs/{{ $mentoring_program_code }}/index.php">{!! show_mentoring_program_title($mentoring_program_code) !!}</a></li>
                            <li class="breadcrumb-item active TextMedium" aria-current="page">{{ $toolName }}</li>
                        </ol>
                    </nav>

                    @include('modules.mentoring.common.common_current_title')

                    <div class='col-12 mb-4'>
                        <div class='col-lg-7 col-md-9 col-12 ms-auto me-auto ps-3 pe-3'>
                            <p class='TextMedium text-center text-justify'>{!! trans('langInfoActionProgramText')!!}</p>
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

                    {!! $action_bar !!}

                    @if($is_tutor_of_mentoring_program or $is_admin)

                        
                        <div class='col-lg-6 col-12'>
                            <form class='form-wrapper form-edit rounded-2 p-3 solidPanel' method="post" action="{{ $_SERVER['SCRIPT_NAME'] }}">
                                <div class='form-group'>
                                    <label for='startdate' class='col-12 control-label-notes mb-1'>{{ trans('langStartDate') }}<span class='small-text'>({{ trans('langUntilNow') }})</span></label>
                                    <div class='col-12'>
                                        <div class='input-group'>
                                            <input class='form-control mt-0' name='startdate' id='startdate' type='text' required>
                                            <div class='input-group-addon input-group-text h-30px border-0 BordersRightInput bgEclass'><span class='fa fa-calendar'></span></div>
                                        </div>
                                    </div>
                                </div>
                                <div class='form-group mt-3'>
                                    <label class='col-12 control-label-notes mb-1'>{{ trans('langAboutWith') }}</label>
                                    <div class='col-12'>
                                        <div class='checkbox'>
                                            <label class='label-container'>
                                                <input name='type' type='checkbox' value='1'><span class='checkmark'></span>{{ trans('langInsert') }}
                                            </label>
                                            <label class='label-container'>
                                                <input name='type' type='checkbox' value='2'><span class='checkmark'></span>{{ trans('langModify') }}
                                            </label>
                                            <label class='label-container'>
                                                <input name='type' type='checkbox' value='3'><span class='checkmark'></span>{{ trans('langDelete') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class='form-group mt-4 d-flex justify-content-center align-items-center flex-wrap'>
                                    <button class='btn submitAdminBtn m-2' type='submit' name='log_program' value="doc">{{ trans('langDoc')}}</button>
                                    <button class='btn submitAdminBtn m-2' type='submit' name='log_program' value="forum">{{ trans('langForum')}}</button> 
                                    <button class='btn submitAdminBtn m-2' type='submit' name='log_program' value="request">{{ trans('langRequests')}}&nbsp--&nbsp{{ trans('langUsers')}}</button>   
                                    <button class='btn submitAdminBtn m-2' type='submit' name='log_program' value="group">{{ trans('langGroup')}}</button>
                                    <button class='btn submitAdminBtn m-2' type='submit' name='log_program' value="meeting">Meeting</button>     
                                    <button class='btn submitAdminBtn m-2' type='submit' name='log_program' value="program">{{ trans('langProgram') }}</button>                         
                                </div>     
                                
                            </form>
                        </div>
                        <div class='col-lg-6 col-12 d-none d-md-none d-lg-block'>
                            <div class='col-12 h-100 left-form'></div>
                        </div>
                        @if(!empty($logs))
                            <div class='col-12 mt-4'>{!! $logs !!}</div>
                        @else
                            <div class='col-12 mt-4'><div class='alert alert-warning rounded-2'>{{ trans('langNoActionsExists')}}</div></div>
                        @endif
                    @endif
               

        </div>
      
    </div>
</div>

<script>

    $('#startdate').datetimepicker({
        format: 'yyyy-mm-dd hh:ii:ss',
        pickerPosition: 'bottom-right',
        language: '{{ $language }}',
        autoclose: true
    });

    $('#mentoring_log_results_table').DataTable();

    $('.showProgramsBtn').on('click',function(){
            localStorage.setItem("MenuMentoring","program");
        });
</script>

@endsection