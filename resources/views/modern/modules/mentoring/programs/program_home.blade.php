
@extends('layouts.default')

@section('content')


<div class="col-12 main-section">
    <div class='{{ $container }}'>
        <div class="row rowMargin">

                    @if(isset($_SESSION['uid']))
                    <div class='col-12 ps-4 pe-4'>
                        <nav class='breadcrumb_mentoring' style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/mentoring_platform_home.php"><span class='fa fa-home'></span>&nbsp{{ trans('langHomeMentoringPlatform') }}</a></li>
                                <li class="breadcrumb-item"><a class='TextSemiBold showProgramsBtn' href="{{ $urlAppend }}modules/mentoring/programs/show_programs.php">{{ trans('langOurMentoringPrograms') }}</a></li>
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/programs/myprograms.php">{{ trans('langMyPrograms') }}</a></li>
                                <li class="breadcrumb-item active TextMedium" aria-current="page">{{ $toolName }}</li>
                            </ol>
                        </nav>
                    </div>
                    @endif

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
                   
                    @php 
                        $tutor = show_mentoring_program_tutor($mentoring_program_id);
                        $type_of_uid = check_if_uid_is_mentor_or_tutor_or_guided_of_mentoring_program($mentoring_program_id,$uid);
                        
                        $mentee_denied = Database::get()->queryArray("SELECT guided_id FROM mentoring_programs_requests
                                                                       WHERE mentoring_program_id = ?d
                                                                       AND status_request = ?d",$mentoring_program_id,2);
                                                                       
                    @endphp

                    @if(count($mentee_denied) > 0)
                        <div class='col-12 ps-4 pe-4'>
                            @foreach($mentee_denied as $m)
                               @if($m->guided_id == $uid)
                                    <div class='alert alert-info rounded-2'>{{ trans('langTutotHasDeniedRequest') }}</div>
                               @endif
                            @endforeach
                        </div>
                    @endif
                    
                    
                    <!-- requests from guided -->
                    <div class='col-12'>
                        <!-- because tutor_or_mentor = 3 means that doesnt exist record in db for current uid in current program
                             So, check if uid is a mentor of platform before sending request by him -->
                        @php 
                            $is_uid_a_mentor_of_platform = Database::get()->querySingle("SELECT COUNT(id) as ui FROM user
                                                            WHERE id = ?d AND status = ?d AND is_mentor = ?d",$uid,USER_STUDENT,0)->ui; 
                        
                        @endphp
                        @foreach($type_of_uid as $exist)
                            @if(($exist['tutor_or_mentor'] == 3 and $is_uid_a_mentor_of_platform > 0) or (get_config('mentoring_tutor_as_mentee') and !$is_admin and $exist['tutor_or_mentor'] != 1 and $exist['tutor_or_mentor'] != 0 and $exist['tutor_or_mentor'] == 3))
                                @php  
                                    $check_if_send_request = check_if_send_request($mentoring_program_id,$uid);
                                    $if_accepted_request = check_accepted_or_denied_request_uid_from_program($mentoring_program_id,$uid);
                                    
                                    foreach($if_accepted_request as $accept){
                                        $if_accepted_request = $accept->status_request;
                                    }
                                @endphp

                                <div class='col-12 d-md-flex justify-content-md-center align-items-md-center'>
                                    @if($check_if_send_request)
                                        <!-- is loaded request accepted or denied -->
                                        @if($if_accepted_request != 1 and $if_accepted_request != 2)
                                            <p class='mb-md-0 mb-2 text-md-start text-center'>{{ trans('langHasSendRequestMentoring') }}</p>
                                            <p class='mb-md-0 mb-2 ps-3 pe-3 TextBold blackBlueText text-md-start text-center'><span class='fa fa-spinner'></span>&nbsp{{ trans('langStandByState') }}</p>
                                        @endif
                                    @endif
                                    @if($if_accepted_request != 1 and $if_accepted_request != 2)
                                        <button class="btn {{ $check_if_send_request ? 'btn-outline-danger' : 'bgEclass blackBlueText TextBold text-uppercase sendingRequestMentee' }} small-text rounded-2 cancelRequestProgram"
                                                data-bs-toggle="modal" data-bs-target="#RequestModal{{ $mentoring_program_id }}" >
                                                {{ $check_if_send_request ? trans('langCancelRequest') : trans('langRequestMentoring') }}
                                                @if(!$check_if_send_request)
                                                    <span class='fa-solid fa-paper-plane'></span>
                                                @endif
                                        </button>
                                    @endif
                                </div>
                               
                                <div class="modal fade" id="RequestModal{{ $mentoring_program_id }}" tabindex="-1" aria-labelledby="RequestModalLabel{{ $mentoring_program_id }}" aria-hidden="true">
                                    <form method="post" action="{{ $urlAppend }}modules/mentoring/programs/program_request.php">
                                        <div class="modal-dialog modal-md @if($check_if_send_request) modal-danger @else modal-primary @endif">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="RequestModalLabel{{ $mentoring_program_id }}">{{ trans('langRequest') }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    {{ trans('langContinueActionRequest') }}
                                                    <input type='hidden' name='mentoring_program_id' value="{{ $mentoring_program_id }}">
                                                    <input type='hidden' name='guided_id' value="{{ $uid }}">
                                                    <input type='hidden' name='request_from_program_home' value="1">
                                                    @if(!$check_if_send_request) 
                                                        <input type='hidden' name='request_val' value="0">
                                                    @endif
                                                </div>
                                                <div class="modal-footer">
                                                    <a class="btn btn-outline-secondary small-text rounded-2" href="" data-bs-dismiss="modal">{{ trans('langCancel') }}</a>
                                                    <button type='submit' class="btn {{ $check_if_send_request ? 'btn-danger' : 'btn-primary'}} small-text rounded-2" 
                                                            name="{{ $check_if_send_request ? 'cancel_request' : 'request_submit' }}">
                                                            {{ $check_if_send_request ? trans('langCancelRequest') : trans('langSubmit') }}
                                                    </button>

                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                               
                            @endif
                        @endforeach
                    </div>

                    <div class='col-12 d-flex justify-content-center align-items-center mt-3'>
                        <ul class="nav nav-pills mb-3 mentoring_program_ul p-2 rounded-2" id="ex1" role="tablist">

                            <li class="nav-item mentoring_program_nav_item rounded-0" role="presentation">
                                <a class="nav-link mentoring_program_nav_item_nav_linkProgram active TextSemiBold rounded-0" id="info_mentoring" data-bs-toggle="tab" href="#info_mentoring_program" role="tab" aria-controls="info_mentoring_program" aria-selected="true">
                                    <span class='fa fa-info'></span><span class='hidden-xs-mentoring hidden-md-mentoring'>&nbsp{{ trans('langMentoringInfo')}}</span>
                                </a>
                            </li>

                            <li class="nav-item mentoring_program_nav_item rounded-0" role="presentation">
                                <a class="nav-link mentoring_program_nav_item_nav_linkProgram TextMedium rounded-0" id="mentor_mentoring" data-bs-toggle="tab" href="#mentors_mentoring_program" role="tab" aria-controls="mentors_mentoring_program" aria-selected="false">
                                    <span class='fa fa-user'></span><span class='hidden-xs-mentoring hidden-md-mentoring'>&nbsp{{ trans('langMentoringMentorss') }}</span>
                                </a>
                            </li>
                           
                            @if($is_editor_mentoring_program or $is_admin)
                                @php 
                                    $sum_requests = Database::get()->querySingle("SELECT COUNT(id) AS ri FROM mentoring_programs_requests 
                                                      WHERE mentoring_program_id = ?d AND status_request = ?d", $mentoring_program_id, 0)->ri;
                                @endphp
                                <li class="nav-item mentoring_program_nav_item rounded-0" role="presentation">
                                    <a class="nav-link mentoring_program_nav_item_nav_linkProgram TextMedium rounded-0" id="request_mentoring" data-bs-toggle="tab" href="#requests_mentoring_program" role="tab" aria-controls="requests_mentoring_program" aria-selected="false">
                                        <span class='fa-solid fa-paper-plane'></span><span class='hidden-xs-mentoring hidden-md-mentoring'>&nbsp{{ trans('langRequestsPrograms')}}</span>
                                        @if($sum_requests > 0)&nbsp<span class='badge bg-primary text-white'>{{ $sum_requests }}</span>@endif
                                    </a>
                                </li>
                            @endif

                            
                            @if($is_editor_mentoring_program or $is_admin)
                                <li class="nav-item mentoring_program_nav_item dropdown rounded-pill" role="presentation">
                                    <a class="nav-link mentoring_program_nav_item_nav_linkProgram dropdown-toggle TextMedium rounded-pill" data-bs-display='static' data-bs-toggle="dropdown" role="button" aria-expanded="false">
                                        <span class='fa fa-cogs'></span><span class='hidden-xs-mentoring hidden-md-mentoring'>&nbsp{{ trans('langMoreChoices') }}</span>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end programTools">
                                        <li><a class="dropdown-item" href="{{ $urlAppend }}modules/mentoring/programs/actions_program.php?program_id={!! getInDirectReference($mentoring_program_id) !!}"><span class='fa fa-tasks pe-2'></span>{{ trans('langMentoringAction' )}}</a></li>
                                        <li><a class="dropdown-item" href="{{ $urlAppend }}modules/mentoring/programs/users/index.php"><span class='fa fa-user pe-2'></span>{{ trans('langProgramUsers' )}}</a></li>
                                        <li><a class="dropdown-item" href="{{ $urlAppend }}modules/mentoring/programs/edit_delete_program.php?edit={!! getInDirectReference($mentoring_program_id) !!}"><span class='fa fa-edit pe-2'></span>{{ trans('langMentoringEdit' )}}</a></li>
                                        <li><a class="dropdown-item" data-bs-toggle="modal" data-bs-target="#del_mentoring_program" href="#"><span class='fa fa-times pe-2 text-danger'></span>{{ trans('langDelete') }}</a></li>
                                    </ul>
                                </li>
                            @endif
                        </ul>

                    </div>
                     <!-- Tabs navs -->

                        <!-- Tabs content -->
                    <div class='col-12 mt-3 pe-0'>
                        @php $is_access_user = false; @endphp
                        @foreach($type_of_uid as $exist)
                            @if($exist['tutor_or_mentor'] != 3 or $is_admin)
                                @php $is_access_user = true; @endphp
                            @endif
                        @endforeach
                        <div class='row'>
                            <div class='col-12 pe-0'>
                                <div class="tab-content p- rounded-2" id="ex1-content">

                                    <div class="tab-pane fade show active p-3" id="info_mentoring_program" role="tabpanel" aria-labelledby="info_mentoring">
            
                                        @if(count($mentoring_program_details) > 0)
                                            @foreach($mentoring_program_details as $detail)
                                                <div class="card">
                                                    <div class="row g-0">
                                                        <div class='col-lg-6 col-12 d-flex align=items-strech'>
                                                            @if(!empty($detail->program_image))
                                                                <img class='CardProgramImg img-fluid rounded-start w-100' src='{{ $urlAppend }}mentoring_programs/{{ $mentoring_program_code }}/image/{{ $detail->program_image }}'>
                                                            @else
                                                                <img class="CardProgramImg img-fluid rounded-start w-100" alt="..." src="{{ $urlAppend }}template/modern/images/nocontentyet.jpg">
                                                            @endif
                                                        </div>
                                                        <div class='col-lg-6 col-12 d-flex justify-content-center align-items-center bg-light'>
                                                            <div class="card-body">
                                                                <div class='col-12'>
                                                                    <div class="card-title text-center fs-5 blackBlueText TextBold">{{ $detail->title }}</div>
                                                                    <p class="card-text text-center">
                                                                        @php
                                                                            $tutor = show_mentoring_program_tutor($detail->id);
                                                                        @endphp
                                                                        @foreach($tutor as $t)
                                                                            &nbsp<span class='TextRegular small-text'>{{ $t->givenname }}&nbsp{{ $t->surname }},</span>
                                                                        @endforeach
                                                                    
                                                                    </p>
                                                                    <p class='text-center mb-0'>
                                                                        <!-- uid has access in program -->
                                                                        @if($is_access_user)
                                                                            {{--<a class="btn btn-sm viewOptionToolBtn rounded-2" href="{{ $urlAppend }}modules/mentoring/programs/group/index.php?commonGroupView=1">
                                                                                <span class='fa fa-comment'></span>&nbsp<span class='small-text'>{{ trans('langMentoringGroupSpace' )}}</span>
                                                                            </a>--}}
                                                                            <a class="btn viewOptionToolBtn text-uppercase TextBold rounded-2 d-flex justify-content-center align-items-center w-75 ms-auto me-auto" href="{{ $urlAppend }}modules/mentoring/programs/group/select_group.php">
                                                                                <span class='fa fa-comment'></span>&nbsp<span class='small-text'>{{ trans('langMentoringGroupSpace' )}}</span>
                                                                            </a>
                                                                        @endif
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        @if(!empty($detail->description))
                                                            <div class='col-12'>
                                                                <div class="card-body">
                                                                    <div class='col-12 blackBlueText'>{!! $detail->description !!}</div>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    </div>
                                                    
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>


                                    <div class="tab-pane fade" id="mentors_mentoring_program" role="tabpanel" aria-labelledby="mentor_mentoring">
                                        @php $mentors_of_program = show_mentoring_program_mentors($mentoring_program_id); @endphp
                                        @if(count($mentors_of_program)>0)
                                            <div class='col-12'>
                                                <div class='row ms-0'>     
                                                    <div class='card-group p-0 d-md-flex justify-content-md-center'>  
                                                        @foreach($mentors_of_program as $mentor) 
                                                            <div class='col-xl-3 col-lg-4 col-md-6 col-12 d-flex align-items-strech p-3'>
                                                                <div class="card MentorCard w-100">
                                                                    <a class='MentorCardBtn' href='{{ $urlAppend }}modules/mentoring/mentors/profile_mentor.php?mentor={!! getIndirectReference($mentor->id) !!}'>
                                                                        
                                                                        <div class="card-body">
                                                                            @php $profile_img = profile_image($mentor->id, IMAGESIZE_LARGE, 'img-responsive img-circle img-profile card-img-top MentorProgramCard'); @endphp
                                                                            {!! $profile_img !!}
                                                                            <div class='col-12 mt-3 d-flex justify-content-center align-items-start'>
                                                                                <p class='fs-6 blackBlueText TextBold text-center'>{{ $mentor->givenname }} {{ $mentor->surname }}</p>
                                                                                @php 
                                                                                    $is_tutor_mentor_or_guided_uid = check_if_uid_is_mentor_or_tutor_or_guided_of_mentoring_program($mentoring_program_id , $mentor->id);
                                                                                @endphp
                                                                                @foreach($is_tutor_mentor_or_guided_uid as $key)
                                                                                    <!-- only mentor -->
                                                                                    @if(($key['tutor_or_mentor'] == 0 and $key['uid'] == $uid ))
                                                                                        <div class='d-flex'>
                                                                                            <a class='ms-2' type="button" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ trans('langIsMendorChooser') }}">
                                                                                                <i class="fa fa-check text-success fs-4" aria-hidden="true"></i>
                                                                                            </a>
                                                                                        </div>
                                                                                    @endif
                                                                                    <!-- tutor and mentor -->
                                                                                    @if(($key['tutor_or_mentor'] == 4 and $key['uid'] == $uid ))
                                                                                        <div class='d-flex'>
                                                                                            <a class='ms-2' type="button" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ trans('langIsTutorChooser') }}">
                                                                                                <i class="fa fa-user text-primary fs-4" aria-hidden="true"></i>
                                                                                            </a>
                                                                                            <a class='ms-2' type="button" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ trans('langIsMendorChooser') }}">
                                                                                                <i class="fa fa-check text-success fs-4" aria-hidden="true"></i>
                                                                                            </a></br>
                                                                                        </div>
                                                                                    @endif
                                                                                @endforeach
                                                                            </div>
                                                                        </div>
                                                                    </a>
                                                                </div> 
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class='col-12'>
                                                <div class='alert alert-warning'>{{ trans('langNoAvailableMentoringMentors') }}</div>
                                            </div>
                                        @endif
                                    </div>

                                    @if($is_editor_mentoring_program or $is_admin)
                                        <div class="tab-pane fade p-3" id="requests_mentoring_program" role="tabpanel" aria-labelledby="request_mentoring">

                                            <div class='panel panel-admin rounded-2 border-1 BorderSolid bg-white py-md-4 px-md-4 py-3 px-3 shadow-none'>
                                                <div class='panel-heading bg-body p-0'>
                                                    <span class='text-uppercase blackBlueText TextBold fs-6'>{{ trans('langRequestsExist')}}</span>
                                                </div>
                                                <div class='panel-body p-0 rounded-2'>
                                                    @if(count($mentoring_program_requests) > 0)
                                                            @php $counterPadding = 0; @endphp
                                                        <div class='row ms-0'>
                                                            @foreach($mentoring_program_requests as $r)
                                                                <div class='col-lg-6 col-12 @if($counterPadding % 2 == 1) ps-lg-2 pe-lg-0 pe-0 ps-0 @else ps-lg-0 pe-lg-2 pe-0 ps-0 @endif'>
                                                                    <div class='panel panel-default rounded-0 mb-3 bg-light'>
                                                                        <div class='panel-body bg-light'>
                                                                            <div class='row ms-0'>
                                                                                <div class='col-md-4 col-12'>
                                                                                    <img class="m-auto d-block rounded-2 mb-md-0 mb-3" src="{{ user_icon($r->guided_id, IMAGESIZE_LARGE) }}" style='width:auto; max-height:120px;'>
                                                                                </div>
                                                                                <div class='col-md-8 col-12'>
                                                                                    @php 
                                                                                    $name = Database::get()->queryArray("SELECT givenname,surname,email,registered_at,description FROM user WHERE id = ?d", $r->guided_id);
                                                                                    @endphp
                                                                                    @foreach($name as $n)
                                                                                    <span class='TextSemiBold fs-6'>{{ $n->givenname }}&nbsp{{ $n->surname }}</span></br>
                                                                                    <p class='mt-3 small-text'><strong>Email:</strong> {{ $n->email }}</p>
                                                                                    <p class='mt-3 small-text'><strong>{{ trans('langProfileMemberSince' )}}:</strong> {{ $n->registered_at }}</p>
                                                                                    {{-- <p class='mt-3 small-text help-block'>{!! $n->description !!}</p> --}}
                                                                                    @endforeach
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class='panel-footer rounded-0'>
                                                                            <button class="btn btn-outline-primary small-text rounded-2"
                                                                                    data-bs-toggle="modal" data-bs-target="#AcceptRequestModal{{ $r->id }}" >
                                                                                    {{ trans('langEditRequest') }}
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="modal fade" id="AcceptRequestModal{{ $r->id }}" tabindex="-1" aria-labelledby="AcceptRequestModalLabel{{ $r->id }}" aria-hidden="true">
                                                                    <form method="post" action="{{ $urlAppend }}modules/mentoring/programs/program_request.php">
                                                                        <div class="modal-dialog modal-lg modal-primary">
                                                                            <div class="modal-content">
                                                                                <div class="modal-header">
                                                                                    <h5 class="modal-title" id="AcceptRequestModalLabel{{ $r->id }}">{{ trans('langRequest') }}</h5>
                                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                                </div>
                                                                                <div class="modal-body">
                                                                                    {{ trans('langContinueActionRequest') }}
                                                                                    <input type='hidden' name='mentoring_program_id' value="{{ $r->mentoring_program_id }}">
                                                                                    <input type='hidden' name='guided_id' value="{{ $r->guided_id }}">
                                                                                    <input type='hidden' name='key_id' value="{{ $r->id }}">
                                                                                    
                                                                                </div>
                                                                                <div class="modal-footer">
                                                                                    <a class="btn btn-outline-secondary small-text rounded-2" href="" data-bs-dismiss="modal">{{ trans('langCancel') }}</a>
                                                                                    <button type='submit' class="btn btn-primary small-text rounded-2" name="action_request" value="accepted">
                                                                                        {{ trans('langAcceptRequest') }}
                                                                                    </button>
                                                                                    <button type='submit' class="btn btn-warning small-text rounded-2" name="action_request" value="denied">
                                                                                        {{ trans('langDenyRequest') }}
                                                                                    </button>
                                                                                    <button type='submit' class="btn btn-danger small-text rounded-2" name="action_request" value="deleted">
                                                                                        {{ trans('langDeleteRequest') }}
                                                                                    </button>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                                @php $counterPadding++; @endphp
                                                            @endforeach
                                                        </div>  
                                                    @else
                                                        <div class='col-12'>
                                                            <p class='blackBlueText TextRegular'>{{ trans('langNoExistMentoringRequests') }}</p>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- accepted requests show -->
                                            <div class="panel panel-admin rounded-2 border-1 BorderSolid bg-white mt-lg-4 mt-4 py-md-4 px-md-4 py-3 px-3 shadow-none">
                                                <div class='panel-heading bg-body p-0'>
                                                    <span class='text-uppercase blackBlueText TextBold fs-6'>{{ trans('langRequestsHasDenied') }}</span>
                                                </div>
                                                
                                                <div class="panel-body p-md-1 p-0 rounded-2">
                                                    @if(count($denied_requests) > 0)
                                                        <table class='table-default rounded-2' id="table_no_accepted_requests">
                                                            <thead>
                                                                <tr class='list-header'>
                                                                    <th>{{ trans('langName') }}</th>
                                                                    <th class='text-center'><span class='fa fa-cogs'></span></th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach($denied_requests as $de)
                                                                <tr>
                                                                    <td>
                                                                        @php 
                                                                            $name = Database::get()->queryArray("SELECT givenname,surname,email,registered_at,description FROM user WHERE id = ?d", $de->guided_id);
                                                                        @endphp
                                                                        <img class="mt-0" src="{{ user_icon($de->guided_id, IMAGESIZE_SMALL) }}">
                                                                        <span class='TextSemiBold'>
                                                                            @foreach($name as $n)
                                                                                {{ $n->givenname }}&nbsp{{ $n->surname }}
                                                                            @endforeach
                                                                        </span>
                                                                    </td>
                                                                    <td class='text-center'>
                                                                        <form id='reset_{{ $de->id }}' method='post' action='{{ $urlAppend }}modules/mentoring/programs/program_request.php'>
                                                                            <input type='hidden' name='key_id' value='{{ $de->id }}'>
                                                                            <input type='hidden' name='mentoring_program_id' value='{{ $de->mentoring_program_id }}'>
                                                                            <input type='hidden' name='guided_id' value='{{ $de->guided_id }}'>
                                                                            <button class='btn btn-outline-primary btn-sm small-text rounded-2' type='submit' name='action_request' value='reset'>{{ trans('langRequestReset') }}</button>
                                                                        </form>
                                                                    </td>
                                                                </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    @else
                                                        <div class='col-12'>
                                                            <p class='blackBlueText TextRegular'>{{trans('langNoExistMentoringRequests')}}</p>
                                                        </div>
                                                    @endif
                                                </div>
                                                
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                   
                    <div class="modal fade" id="del_mentoring_program" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <form action="{{ $urlAppend }}modules/mentoring/programs/edit_delete_program.php" method="post">
                            <div class="modal-dialog modal-md modal-danger">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLabel">{{ trans('langDelete')}}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        {{ trans('langDoDeleteMentoringProgram') }} {{ $mentoring_program_code }};
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary small-text rounded-2" data-bs-dismiss="modal">{{ trans('langCancel')}}</button>
                                        <button type="submit" name="delete_mentoring_program" class="btn btn-danger small-text rounded-2">{{ trans('langDelete') }}</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                
               

        </div>
      
    </div>
</div>

<script>

    $('#table_accepted_requests').DataTable();
    $('#table_no_accepted_requests').DataTable();

    $('.showProgramsBtn').on('click',function(){
        localStorage.setItem("MenuMentoring","program");
    });
</script>

@endsection