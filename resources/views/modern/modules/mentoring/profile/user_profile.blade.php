
@extends('layouts.default')

@section('content')


<div class="col-12 main-section">
    <div class='{{ $container }}'>
        <div class="row m-auto">

                    <div class='col-12 ps-4 pe-4'>
                        <nav class='breadcrumb_mentoring' style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/mentoring_platform_home.php"><span class='fa fa-home'></span>&nbsp{{ trans('langHomeMentoringPlatform') }}</a></li>
                                <li class="breadcrumb-item active TextMedium" aria-current="page">{{ $toolName }}</li>
                            </ol>
                        </nav>
                    </div>

                    @include('modules.mentoring.common.common_current_title')

                    <div class='col-12 mb-4'>
                        <div class='col-lg-7 col-md-9 col-12 ms-auto me-auto ps-3 pe-3'>
                            <p class='TextMedium text-center text-justify'>{!! trans('langInfoMyProfileText')!!}</p>
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

                    @if(count($user_info) > 0)
                        <div class='col-12 ps-4 pe-4'>
                            @if($user_id == $uid)
                                <div class="btn-group btn-group-sm flew-wrap">
                                    <a class="btn submitAdminBtn rounded-pill d-flex justify-content-center align-items-center me-2" 
                                        href="{{ $urlAppend }}main/profile/profile.php?fromMentoring=true" data-bs-placement="bottom" data-bs-toggle="tooltip" title="" data-bs-original-title="{{ trans('langModProfile') }}">
                                        <span class="fa fa-edit space-after-icon TextButton"></span><span class="hidden-xs TextBold TextButton">{{ trans('langModProfile') }}</span>
                                    </a>

                                    <a class="btn submitAdminBtn rounded-pill d-flex justify-content-center align-items-center me-2" 
                                        href="{{ $urlAppend }}main/profile/password.php?fromMentoring=true" data-bs-placement="bottom" data-bs-toggle="tooltip" title="" data-bs-original-title="{{ trans('langChangePass') }}">
                                        <span class="fa fa-key space-after-icon TextButton"></span><span class="hidden-xs TextBold TextButton">{{ trans('langChangePass') }}</span>
                                    </a>
                                    
                                </div>
                            @endif
                            @foreach($user_info as $info)
                                <div class='card w-100 m-auto d-block mt-3'>
                                    <div class="row g-0">
                                        <div class='col-lg-3 col-md-4 col-12 p-3'>
                                            {!! $profile_img !!}
                                            <p class='fs-5 normalBlueText TextBold mb-2 mt-3 text-center'>{{ $info->givenname }} {{ $info->surname }}</p>
                                            @if(($is_mentor_user == 1 or $user_student_is_mentor == 1) and $user_id == $uid)
                                                <div class='col-12 mt-3'>
                                                    <button class="btn bgEclass availableBtn small-text rounded-2 TextSemiBold ms-auto me-auto d-flex justify-content-center align-items-center" data-bs-toggle="modal" data-bs-target="#CalendarModal">
                                                        {{ trans('langIsAvailableInPlatform') }}
                                                    </button>
                                                </div>
                                                <div class="modal fade" id="CalendarModal" tabindex="-1" aria-labelledby="CalendarModalLabel" aria-hidden="true">
                                                    <form method="post" action="{{ $_SERVER['SCRIPT_NAME'] }}">
                                                        <div class="modal-dialog modal-md modal-primary">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="CalendarModalLabel">{{ trans('langAvailability') }}</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body">

                                                                    <div class='col-12'><p class='TextBold text-end'>(<span class='text-danger'>*</span>) {{trans('langCPFFieldRequired')}}</p></div>

                                                                    <div class='form-group'>
                                                                        <div for='startdate' class='col-12 control-label-notes mb-1'>{{ trans('langFrom') }}<span class='text-danger'>*</span></div>
                                                                        <div class='col-12'>
                                                                            @php 
                                                                                $startTime = Database::get()->querySingle("SELECT start FROM mentoring_mentor_availability 
                                                                                                                            WHERE user_id = ?d",$uid);

                                                                                $endTime = Database::get()->querySingle("SELECT end FROM mentoring_mentor_availability 
                                                                                                                            WHERE user_id = ?d",$uid);

                                                                                if($startTime){
                                                                                    $availableStart = $startTime->start;
                                                                                }else{
                                                                                    $availableStart = '';
                                                                                }

                                                                                if($endTime){
                                                                                    $availableEnd = $endTime->end;
                                                                                }else{
                                                                                    $availableEnd = '';
                                                                                }

                                                                            @endphp
                                                                            <input name='startdate' id='startdate' type='text' class='form-control rounded-2 bgEclass' value='{{ $availableStart }}' required>
                                                                        </div>
                                                                    </div>

                                                                    <div class='form-group mt-3'>
                                                                        <div for='enddate' class='col-12 control-label-notes mb-1'>{{ trans('langUntil') }}<span class='text-danger'>*</span></div>
                                                                        <div class='col-12'>
                                                                            <input name='enddate' id='enddate' type='text' class='form-control rounded-2 bgEclass' value='{{ $availableEnd }}' required>
                                                                        </div>
                                                                    </div>

                                                                </div>
                                                                <div class="modal-footer">
                                                                    <a class="btn btn-outline-secondary small-text rounded-2" href="" data-bs-dismiss="modal">{{ trans('langCancel') }}</a>
                                                                    <button type='submit' class="btn btn-primary small-text rounded-2" name="mentor_date_availability">
                                                                        {{ trans('langSubmit') }}
                                                                    </button>

                                                                </div>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            @endif
                                        </div>
                                        <div class='col-lg-9 col-md-8 col-12'>
                                        <div class='card-body'>
                                            <div class='col-12 p-0'>
                                                <div class='row'>
                                                    <div class='col-md-6 col-12'>
                                                        @if(!empty($info->email))
                                                            <p class='text-start mb-3'>
                                                                <span class='TextBold blackBlueText fs-6'>Email</span></br>
                                                                <span class='TextRegular blackBlueText small-text'>{{ $info->email }}</span>
                                                            </p>
                                                        @else
                                                            <p class='text-start mb-3'>
                                                                <span class='TextBold blackBlueText fs-6'>Email</span></br>
                                                                <span class='TextRegular blackBlueText small-text'>{{ trans('langNoInfoAvailable') }}</span>
                                                            </p>
                                                        @endif
                                                    </div>
                                                    <div class='col-md-6 col-12'>
                                                        @if(!empty($info->registered_at))
                                                            <p class='text-start mb-3'>
                                                                <span class='TextBold blackBlueText fs-6'>{{ trans('langProfileMemberSince') }}</span></br>
                                                                <span class='TextRegular blackBlueText small-text'>{!! format_locale_date(strtotime($info->registered_at)) !!}</span>
                                                            </p>
                                                        @else
                                                            <p class='text-start mb-3'>
                                                                <span class='TextBold blackBlueText fs-6'>Email</span></br>
                                                                <span class='TextRegular blackBlueText small-text'>{{ trans('langNoInfoAvailable') }}</span>
                                                            </p>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class='row'>
                                                    <div class='col-12'>
                                                        <p class='text-start mt-3'>
                                                            <span class='TextBold blackBlueText fs-6'>{{ trans('langAvailability') }}</span></br>
                                                            @if(count($available_start_end) > 0)
                                                                <ul>
                                                                    @foreach($available_start_end as $a)
                                                                        <li class='p-0'>
                                                                            <span class='TextSemiBold text-success small-text'>{!! format_locale_date(strtotime($a->start)) !!}</span>
                                                                            &nbsp<span>-</span>&nbsp
                                                                            <span class='TextSemiBold text-danger small-text'>{!! format_locale_date(strtotime($a->end)) !!}</span>
                                                                        </li> 
                                                                    @endforeach
                                                                </ul>
                                                            @else
                                                                <span class='TextRegular blackBlueText small-text'>{{ trans('langNoInfoAvailable') }}</span>
                                                            @endif
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class='card-body pt-0 ps-3 pe-3'>
                                            @if(!empty($info->description))
                                                <div class='col-12 ms-auto me-auto p-0 mb-4'>
                                                    <div class="accordion accordion-flush" id="accordionFlushAboutUser">
                                                        <div class="accordion-item">
                                                            <h2 class="accordion-header" id="flush-headingOne">
                                                                <button style='font-size:14px;' class="accordion-button collapsed solidPanel bgEclass text-uppercase TextBold normalBlueText profileAbout" type="button" data-bs-toggle="collapse" data-bs-target="#flush-collapseUser" aria-expanded="false" aria-controls="flush-collapseUser">
                                                                    {{ trans('langAboutUser') }}: {{ $info->givenname }}&nbsp{{ $info->surname }}
                                                                </button>
                                                            </h2>
                                                            <div id="flush-collapseUser" class="accordion-collapse collapse" aria-labelledby="flush-headingOne" data-bs-parent="#accordionFlushAboutUser">
                                                                <div class="accordion-body solidPanel border-bottom-0 border-start-0 border-end-0">
                                                                    {!! $info->description !!}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif

                                            <div class='col-12 mt-3'>
                                                @php 
                                                    $all_programs = Database::get()->queryArray("SELECT *FROM mentoring_programs WHERE id IN (SELECT mentoring_program_id FROM mentoring_programs_user WHERE user_id = ?d)",$user_id);
                                                @endphp
                                                @if(count($all_programs) > 0)
                                                    <div class='card-group'>
                                                        <div class='row ms-0'>
                                                            @foreach($all_programs as $p)
                                                                <div class='col-xl-4 col-md-6 col-12 d-flex align-items-strech'>
                                                                    <div class='card w-100 mb-4'>
                                                                        @if(!empty($p->program_image))
                                                                            <img class='cardProfileProgram HeightImageCard card-img-top' alt="..." src='{{ $urlAppend }}mentoring_programs/{{ $p->code }}/image/{{ $p->program_image }}'>
                                                                        @else
                                                                            <img class="cardProfileProgram HeightImageCard card-img-top" alt="..." src="{{ $urlAppend }}template/modern/images/nocontentyet.jpg">
                                                                        @endif
                                                                        <div class='card-body text-center'>
                                                                            <a class='TextSemiBold fs-6 PageProgram' href="{{ $urlAppend }}mentoring_programs/{{ $p->code }}/index.php">{{ $p->title }}</a>

                                                                            @php $exist = Database::get()->querySingle("SELECT COUNT(user_id) as u FROM mentoring_programs_user WHERE mentoring_program_id = ?d AND user_id = ?d",$p->id,$user_id)->u; @endphp

                                                                            <div class='d-flex justify-content-center align-items-start mt-3'>
                                                                                <p class='text-center TextSemiBold small-text pe-2 mb-0'>{{ trans('langTutorProgram') }}</p>
                                                                                @php  
                                                                                    if($exist > 0){
                                                                                        $is_tutor = Database::get()->querySingle("SELECT tutor FROM mentoring_programs_user
                                                                                                                                WHERE mentoring_program_id = ?d and user_id = ?d",$p->id,$user_id)->tutor;
                                                                                    }
                                                                                @endphp
                                                                                @if($exist > 0)
                                                                                    @if($is_tutor == 1)
                                                                                        <span class='fa fa-check text-success TextBold fs-5'></span>
                                                                                    @else
                                                                                        <span class='fa fa-times text-danger TextBold fs-5'></span>
                                                                                    @endif
                                                                                @else
                                                                                    <span class='fa fa-times text-danger TextBold fs-5'></span>
                                                                                @endif
                                                                            </div>

                                                                            <div class='d-flex justify-content-center align-items-start mt-2'>
                                                                                <p class='text-center TextSemiBold small-text pe-2 mb-0'>{{ trans('langMentorProgram') }}</p>
                                                                                @php  
                                                                                    if($exist > 0){
                                                                                        $is_mentor = Database::get()->querySingle("SELECT mentor FROM mentoring_programs_user
                                                                                                                                WHERE mentoring_program_id = ?d and user_id = ?d",$p->id,$user_id)->mentor;
                                                                                    }
                                                                                @endphp
                                                                                @if($exist > 0)
                                                                                    @if($is_mentor == 1)
                                                                                        <span class='fa fa-check text-success TextBold fs-5'></span>
                                                                                    @else
                                                                                        <span class='fa fa-times text-danger TextBold fs-5'></span>
                                                                                    @endif
                                                                                @else
                                                                                    <span class='fa fa-times text-danger TextBold fs-5'></span>
                                                                                @endif
                                                                            </div>

                                                                            <div class='d-flex justify-content-center align-items-start mt-2'>
                                                                                <p class='text-center TextSemiBold small-text pe-2 mb-0'>{{ trans('langMenteeProgram') }}</p>
                                                                                @php 
                                                                                    if($exist > 0){
                                                                                        $is_mentee = Database::get()->querySingle("SELECT is_guided FROM mentoring_programs_user
                                                                                                                                WHERE mentoring_program_id = ?d and user_id = ?d",$p->id,$user_id)->is_guided;
                                                                                    }
                                                                                @endphp
                                                                                @if($exist > 0)
                                                                                    @if($is_mentee == 1)
                                                                                        <span class='fa fa-check text-success TextBold fs-5'></span>
                                                                                    @else
                                                                                    <span class='fa fa-times text-danger TextBold fs-5'></span>
                                                                                    @endif
                                                                                @else
                                                                                    <span class='fa fa-times text-danger TextBold fs-5'></span>
                                                                                @endif
                                                                            </div>

                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    
                                </div>
                            @endforeach
                        </div>
                    @endif
                    
                
               

        </div></div>
      
    </div>
</div>

<script>

    $('#startdate,#enddate').datetimepicker({
        format: 'yyyy-mm-dd hh:ii:ss',
        pickerPosition: 'bottom-right',
        language: '{{ $language }}',
        autoclose: true
    });

    $('.PageProgram').on('click',function(){
        localStorage.removeItem("MenuMentoring");
    });
    
    
</script>

@endsection