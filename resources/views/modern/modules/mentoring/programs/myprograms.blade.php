
@extends('layouts.default')

@section('content')


<div class="col-12 main-section">
    <div class='{{ $container }}'>
        <div class="row rowMargin">

                    <div class='col-12 ps-4 pe-4'>
                        <nav class='breadcrumb_mentoring' style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/mentoring_platform_home.php"><span class='fa fa-home'></span>&nbsp{{ trans('langHomeMentoringPlatform') }}</a></li>
                                <li class="breadcrumb-item"><a class='TextSemiBold showProgramsBtn' href="{{ $urlAppend }}modules/mentoring/programs/show_programs.php">{{ trans('langOurMentoringPrograms') }}</a></li>
                                <li class="breadcrumb-item active TextMedium" aria-current="page">{{ $toolName }}</li>
                            </ol>
                        </nav>
                    </div>

                    @include('modules.mentoring.common.common_current_title')

                    <div class='col-12 mb-4'>
                        <div class='col-lg-7 col-md-9 col-12 ms-auto me-auto ps-3 pe-3'>
                            <p class='TextMedium text-center text-justify'>{!! trans('langInfoMyProgramsText')!!}</p>
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

                    
                    @if($is_editor_mentoring or $user_student_is_mentor)
                        <div class='col-12'>
                            @if(count($mentoring_programs_as_tutor_or_mentor) > 0 or count($no_available_mentoring_programs) > 0 or count($tutor_mentor_as_mentee) > 0) 
                                <div class='card-group'>
                                    @if(count($mentoring_programs_as_tutor_or_mentor) > 0)
                                        @foreach($mentoring_programs_as_tutor_or_mentor as $mentoring_program)
                                            @php $Name_tutors = show_mentoring_program_tutor($mentoring_program->id); @endphp
                                            <div class='col-xl-3 col-lg-4 col-md-6 col-12 d-flex align-items-strech p-3'>
                                                <div class='card w-100'>
                                                    @if(!empty($mentoring_program->program_image))
                                                        <img class="card-img-top cardImages HeightImageCard" alt="..." src='{{ $urlAppend }}mentoring_programs/{{ $mentoring_program->code }}/image/{{ $mentoring_program->program_image }}'>
                                                    @else
                                                        <img class="card-img-top cardImages HeightImageCard" alt="..." src="{{ $urlAppend }}template/modern/images/nocontentyet.jpg">
                                                    @endif
                                                    <div class='card-body'>
                                                        <span class='badge bg-success text-white TextSemiBold text-capitalize mb-3'>{{ trans('langActive') }}</span>
                                                        <div class='col-12'>
                                                            <div class='row ms-0'>
                                                                <div class='col-12'>
                                                                    <p class='card-title TextBold fs-5 blackBlueText text-center'>{{ $mentoring_program->title }}</p>
                                                                </div>
                                                                @if(count($Name_tutors) >0)
                                                                    <div class='col-12'>
                                                                       
                                                                        <ul class='text-center p-0' style=' list-style-type: none;'>
                                                                            @foreach($Name_tutors as $name)
                                                                                <li class='p-0 blackBlueText TextMedium'>{{ $name->givenname }}&nbsp{{ $name->surname }}</li>
                                                                            @endforeach
                                                                        </ul>
                                                                    </div>
                                                                @endif
                                                                <div class='col-12'>
                                                                    @php 
                                                                        $are_you_tutor_or_mentor = check_if_uid_is_mentor_or_tutor_or_guided_of_mentoring_program($mentoring_program->id ,$uid);
                                                                    @endphp
                                                                    @foreach($are_you_tutor_or_mentor as $key)
                                                                        @if($key['tutor_or_mentor'] == 1 or $key['tutor_or_mentor'] == 4)
                                                                            <p class='text-center'>{{ trans('langAreYouTutor') }}&nbsp<span class='fa fa-check text-success fs-6'></span></p>
                                                                        @endif
                                                                        @if($key['tutor_or_mentor'] == 0 or $key['tutor_or_mentor'] == 4)
                                                                            <p class='text-center'>{{ trans('langAreYouMentor') }}&nbsp<span class='fa fa-check text-success fs-6'></span></p>
                                                                        @endif
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class='card-footer'>
                                                        <a class='btn viewProgram bgEclass TextBold small-text text-uppercase rounded-2 d-flex justify-content-center aling-items-center' href='{{ $urlAppend }}mentoring_programs/{{ $mentoring_program->code }}/index.php'>
                                                            <img class='img-info-programs' src='{{ $urlAppend }}template/modern/img/info_a.svg'>&nbsp{{ trans('langViewMentoringProgram') }}
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach   
                                    @endif 

                                    @if(count($no_available_mentoring_programs) > 0)
                                        @foreach($no_available_mentoring_programs as $mentoring_program)
                                            @php $Name_tutors = show_mentoring_program_tutor($mentoring_program->id); @endphp
                                            <div class='col-xl-3 col-lg-4 col-md-6 col-12 d-flex align-items-strech p-3'>
                                                <div class='card w-100'>
                                                    @if(!empty($mentoring_program->program_image))
                                                        <div class='col-12 d-flex justify-content-center aling-items-center'>
                                                            <img class="card-img-top cardImages HeightImageCard" alt="..." src='{{ $urlAppend }}mentoring_programs/{{ $mentoring_program->code }}/image/{{ $mentoring_program->program_image }}'>
                                                        </div>
                                                    @else
                                                    
                                                        <div class='col-12 d-flex justify-content-center align-items-center'>
                                                            <img class="card-img-top cardImages HeightImageCard" alt="..." src="{{ $urlAppend }}template/modern/images/nocontentyet.jpg">
                                                        </div>
                                                
                                                    @endif
                                                    <div class='card-body'>
                                                        <span class='badge bg-danger text-white TextSemiBold mb-3'>{{ trans('langHasExpired') }}</span>
                                                        <div class='col-12'>
                                                            <div class='row ms-0'>
                                                                <div class='col-12'>
                                                                    <p class='card-title TextBold fs-5 blackBlueText text-center'>{{ $mentoring_program->title }}</p>
                                                                </div>
                                                                @if(count($Name_tutors) >0)
                                                                    <div class='col-12'>
                                                                        
                                                                        <ul class='text-center p-0' style=' list-style-type: none;'>
                                                                            @foreach($Name_tutors as $name)
                                                                                <li class='p-0 blackBlueText TextMedium'>{{ $name->givenname }}&nbsp{{ $name->surname }}</li>
                                                                            @endforeach
                                                                        </ul>
                                                                    </div>
                                                                @endif
                                                                <div class='col-12'>
                                                                    @php 
                                                                        $are_you_tutor_or_mentor = check_if_uid_is_mentor_or_tutor_or_guided_of_mentoring_program($mentoring_program->id ,$uid);
                                                                    @endphp
                                                                    @foreach($are_you_tutor_or_mentor as $key)
                                                                        @if($key['tutor_or_mentor'] == 1 or $key['tutor_or_mentor'] == 4)
                                                                            <p class='text-center'>{{ trans('langAreYouTutor') }}&nbsp<span class='fa fa-check text-success fs-6'></span></p>
                                                                        @endif
                                                                        @if($key['tutor_or_mentor'] == 0 or $key['tutor_or_mentor'] == 4)
                                                                            <p class='text-center'>{{ trans('langAreYouMentor') }}&nbsp<span class='fa fa-check text-success fs-6'></span></p>
                                                                        @endif
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        </div>
                                        
                                                    </div>
                                                    <div class='card-footer'>
                                                        <a class='btn viewProgram bgEclass TextBold small-text text-uppercase rounded-2 d-flex justify-content-center aling-items-center' href='{{ $urlAppend }}mentoring_programs/{{ $mentoring_program->code }}/index.php'>
                                                            <img class='img-info-programs' alt="..." src='{{ $urlAppend }}template/modern/img/info_a.svg'>&nbsp{{ trans('langViewMentoringProgram') }}
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif

                                    @if(count($tutor_mentor_as_mentee) > 0)
                                        @foreach($tutor_mentor_as_mentee as $mentoring_program)
                                            @php $Name_tutors = show_mentoring_program_tutor($mentoring_program->id); @endphp
                                            <div class='col-xl-3 col-lg-4 col-md-6 col-12 d-flex align-items-strech p-3'>
                                                <div class='card w-100'>
                                                    @if(!empty($mentoring_program->program_image))
                                                        <img class="card-img-top cardImages HeightImageCard" alt="..." src='{{ $urlAppend }}mentoring_programs/{{ $mentoring_program->code }}/image/{{ $mentoring_program->program_image }}'>
                                                    @else
                                                        <img class="card-img-top cardImages HeightImageCard" alt="..." src="{{ $urlAppend }}template/modern/images/nocontentyet.jpg">
                                                    @endif
                                                    <div class='card-body'>
                                                        <span class='badge bg-success text-white TextSemiBold text-capitalize mb-3'>{{ trans('langActive') }}</span>
                                                        <div class='col-12'>
                                                            <div class='row ms-0'>
                                                                <div class='col-12'>
                                                                    <p class='card-title TextBold fs-5 blackBlueText text-center'>{{ $mentoring_program->title }}</p>
                                                                </div>
                                                                @if(count($Name_tutors) >0)
                                                                    <div class='col-12'>
                                                                       
                                                                        <ul class='text-center p-0' style=' list-style-type: none;'>
                                                                            @foreach($Name_tutors as $name)
                                                                                <li class='p-0 blackBlueText TextMedium'>{{ $name->givenname }}&nbsp{{ $name->surname }}</li>
                                                                            @endforeach
                                                                        </ul>
                                                                    </div>
                                                                @endif
                                                                <div class='col-12'>
                                                                    @php 
                                                                        $are_you_tutor_or_mentor = check_if_uid_is_mentor_or_tutor_or_guided_of_mentoring_program($mentoring_program->id ,$uid);
                                                                    @endphp
                                                                    @foreach($are_you_tutor_or_mentor as $key)
                                                                        @if($key['tutor_or_mentor'] == 1 or $key['tutor_or_mentor'] == 4)
                                                                            <p class='text-center'>{{ trans('langAreYouTutor') }}&nbsp<span class='fa fa-check text-success fs-6'></span></p>
                                                                        @endif
                                                                        @if($key['tutor_or_mentor'] == 0 or $key['tutor_or_mentor'] == 4)
                                                                            <p class='text-center'>{{ trans('langAreYouMentor') }}&nbsp<span class='fa fa-check text-success fs-6'></span></p>
                                                                        @endif
                                                                        @if($key['tutor_or_mentor'] == 2)
                                                                            <p class='text-center'>{{ trans('langAreYouMentee') }}&nbsp<span class='fa fa-check text-success fs-6'></span></p>
                                                                        @endif
                                                                    @endforeach
                                                                </div>
                                                                @if($mentoring_program->allow_unreg_mentee == 1)
                                                                    <div class='col-12 mt-3'>
                                                                        <button class="btn btn-outline-danger btn-sm small-text m-auto d-block rounded-2"
                                                                            data-bs-toggle="modal" data-bs-target="#UnregTeacherProgramModal{{ $mentoring_program->code }}" >
                                                                            {{ trans('langUnregProgramMentee')}}
                                                                        </button>

                                                                        <div class="modal fade" id="UnregTeacherProgramModal{{ $mentoring_program->code }}" tabindex="-1" aria-labelledby="UnregTeacherProgramModalLabel{{ $mentoring_program->code }}" aria-hidden="true">
                                                                            <form method="post" action="{{ $_SERVER['SCRIPT_NAME'] }}">
                                                                                <div class="modal-dialog modal-md modal-danger">
                                                                                    <div class="modal-content">
                                                                                        <div class="modal-header">
                                                                                            <h5 class="modal-title" id="UnregTeacherProgramModalLabel{{ $mentoring_program->code }}">
                                                                                                {{ trans('langUnregProgramMentee') }}
                                                                                            </h5>
                                                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                                        </div>
                                                                                        <div class="modal-body text-start">
                                                                                            {!! trans('langContinueActionRequest') !!}
                                                                                            <input type='hidden' name='del_program_id' value='{{ $mentoring_program->id }}'>
                                                                                            <input type='hidden' name='del_mentee_id' value='{{ $uid }}'>
                                                                                        </div>
                                                                                        <div class="modal-footer">
                                                                                            <a class="btn btn-outline-secondary small-text rounded-2" href="" data-bs-dismiss="modal">{{ trans('langCancel') }}</a>
                                                                                            <button type='submit' class="btn btn-danger small-text rounded-2" name="unreg_mentee_from_program">
                                                                                                {{ trans('langSubmit') }}
                                                                                            </button>

                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </form>
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class='card-footer'>
                                                        <a class='btn viewProgram bgEclass TextBold small-text text-uppercase rounded-2 d-flex justify-content-center aling-items-center' href='{{ $urlAppend }}mentoring_programs/{{ $mentoring_program->code }}/index.php'>
                                                            <img class='img-info-programs' src='{{ $urlAppend }}template/modern/img/info_a.svg'>&nbsp{{ trans('langViewMentoringProgram') }}
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach   
                                    @endif
                                </div>
                            @else
                                <div class='col-12'>
                                    <div class='col-12 bg-white p-3 rounded-2 solidPanel'><div class='alert alert-warning rounded-2'>{{trans('langNoMentoringPrograms')}}</div></div>
                                </div>
                            @endif
                        </div>
                    @endif


                    @if(!$is_editor_mentoring)
                        <div class='col-12'>
                            @if(count($programs_as_guided) > 0)
                                <div class="card-group mt-3">
                                    @foreach($programs_as_guided as $program)
                                        @php $Name_tutors = show_mentoring_program_tutor($program->id); @endphp
                                        <div class='col-xl-3 col-lg-4 col-md-6 col-12 d-flex align-items-strech p-3'>
                                            <div class='card w-100'>
                                                @if(!empty($program->program_image))
                                                    <div class='col-12 d-flex justify-content-center align-items-center'>
                                                        <img class="card-img-top cardImages HeightImageCard" alt="..." src='{{ $urlAppend }}mentoring_programs/{{ $program->code }}/image/{{ $program->program_image }}'>
                                                    </div>
                                                @else
                                                    <div class='col-12 d-flex justify-content-center align-items-center'>
                                                        <img class="card-img-top cardImages HeightImageCard" alt="..." src="{{ $urlAppend }}template/modern/images/nocontentyet.jpg">
                                                    </div>
                                                @endif
                                                <div class='card-body'>
                                                    <span class='badge bg-success text-white TextSemiBold text-capitalize'>{{ trans('langActive') }}</span>
                                                    
                                                    <div class='col-12 p-2'>
                                                        <p class='TextBold blackBlueText text-start fs-5 text-center'>{{ $program->title }}</p>
                                                        <p class='help-block text-center'>{{ $program->tutor }}</p>
                                                    </div>

                                                    @if(count($Name_tutors) >0)
                                                        <div class='col-12'>
                                                            <p class='card-text TextBold fs-6 blackBlueText mb-0 text-center'>{{ trans('langMentoringTutors') }}</p>
                                                            <ul class='text-center p-0' style=' list-style-type: none;'>
                                                                @foreach($Name_tutors as $name)
                                                                    <li class='p-0 blackBlueText TextMedium'>{{ $name->givenname }}&nbsp{{ $name->surname }}</li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    @endif

                                                    <div class='col-12'>
                                                        <p class='text-center'>{{ trans('langAreYouMentee') }}&nbsp<span class='fa fa-check text-success fs-6'></span></p>
                                                    </div>

                                                    @if($program->allow_unreg_mentee == 1)
                                                        <div class='col-12 mt-3'>
                                                        
                                                            <button class="btn btn-outline-danger btn-sm small-text m-auto d-block rounded-2"
                                                                data-bs-toggle="modal" data-bs-target="#UnregProgramModal{{ $program->code }}" >
                                                                {{ trans('langUnregProgramMentee')}}
                                                            </button>

                                                            <div class="modal fade" id="UnregProgramModal{{ $program->code }}" tabindex="-1" aria-labelledby="UnregProgramModalLabel{{ $program->code }}" aria-hidden="true">
                                                                <form method="post" action="{{ $_SERVER['SCRIPT_NAME'] }}">
                                                                    <div class="modal-dialog modal-md modal-danger">
                                                                        <div class="modal-content">
                                                                            <div class="modal-header">
                                                                                <h5 class="modal-title" id="UnregProgramModalLabel{{ $program->code }}">
                                                                                    {{ trans('langUnregProgramMentee') }}
                                                                                </h5>
                                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                            </div>
                                                                            <div class="modal-body text-start">
                                                                                {!! trans('langContinueActionRequest') !!}
                                                                                <input type='hidden' name='del_program_id' value='{{ $program->id }}'>
                                                                                <input type='hidden' name='del_mentee_id' value='{{ $uid }}'>
                                                                            </div>
                                                                            <div class="modal-footer">
                                                                                <a class="btn btn-outline-secondary small-text rounded-2" href="" data-bs-dismiss="modal">{{ trans('langCancel') }}</a>
                                                                                <button type='submit' class="btn btn-danger small-text rounded-2" name="unreg_mentee_from_program">
                                                                                    {{ trans('langSubmit') }}
                                                                                </button>

                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                       
                                                        </div>
                                                    @endif
                                                    
                                                </div>
                                                <div class='card-footer'>
                                                    <a class='btn viewProgram bgEclass TextBold small-text text-uppercase rounded-2 d-flex justify-content-center aling-items-center' href='{{ $urlAppend }}mentoring_programs/{{ $program->code }}/index.php'>
                                                        <img class='img-info-programs' src='{{ $urlAppend }}template/modern/img/info_a.svg'>&nbsp{{ trans('langViewMentoringProgram') }}
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        
                                    @endforeach
                                </div>
                            @else
                                <div class='col-12'>
                                    <div class='col-12 bg-white p-3 rounded-2 solidPanel'><div class='alert alert-warning rounded-2'>{{trans('langNoMentoringProgramsAsMentee')}}</div></div>
                                </div>
                            @endif
                        </div>
                    @endif
                           
               

        </div>
      
    </div>
</div>

<script type="text/javascript">
    $(document).ready( function () {

        $('#table_all_available_programs').DataTable();

        $('.viewProgram').on('click',function(){
            localStorage.removeItem("MenuMentoring");
        });

        $('.showProgramsBtn').on('click',function(){
            localStorage.setItem("MenuMentoring","program");
        });
    } );
</script>

@endsection