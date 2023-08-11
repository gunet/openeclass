
@extends('layouts.default')

@section('content')


<div class="col-12 main-section">
    <div class='{{ $container }}'>
        <div class="row m-auto">

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
                            <p class='TextMedium text-center text-justify'>{!! trans('langInfoMyDeactiveProgramsText')!!}</p>
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

                    <!-- all programs -->
                    @if(count($all_programs) > 0)
                        <div class="card-group">
                            @foreach($all_programs as $mentoring_program)
                            <div class='col-xl-3 col-lg-4 col-md-6 col-12 d-flex align-items-strech p-3'>
                                <div class="card w-100">
                                    @if(!empty($mentoring_program->program_image))
                                        <img class="card-img-top cardImages HeightImageCard" alt="..." src="{{ $urlAppend }}mentoring_programs/{{ $mentoring_program->code }}/image/{{ $mentoring_program->program_image }}">
                                    @else
                                        <img class="card-img-top cardImages HeightImageCard" alt="..." src="{{ $urlAppend }}template/modern/images/nocontentyet.jpg">
                                    @endif
                                    <div class="card-body">
                                        <p class="card-title TextBold blackBlueText fs-5">{{ $mentoring_program->title }}</p>
                                        <p class="card-text">
                                            @php
                                                $tutor = show_mentoring_program_tutor($mentoring_program->id);
                                            @endphp
                                            @foreach($tutor as $t)
                                                &nbsp<span class='TextMedium blackBlueText'>{{ $t->givenname }}&nbsp{{ $t->surname }}</span>
                                            @endforeach
                                        </p>
                                    </div>
                                    <div class="card-footer text-center">
                                        <small class="text-muted">
                                            <a class='btn viewProgram bgEclass TextBold rounded-2 d-flex justify-content-center aling-items-center' href="{{ $urlAppend }}mentoring_programs/{{ $mentoring_program->code }}/index.php">
                                                <img class='img-info-programs' src='{{ $urlAppend }}template/modern/img/info_a.svg'>&nbsp{{ trans('langViewMentoringProgram') }}
                                            </a>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class='col-12 ps-4 pe-4'>
                            <div class='col-12 bg-white p-3 rounded-2 solidPanel'><div class='alert alert-warning rounded-2'>{{trans('langNoMentoringPrograms')}}</div></div>
                        </div>
                    @endif
                    
                

        </div>
      
    </div>
</div>

<script type="text/javascript">
    $(document).ready( function () {

        $('.viewProgram').on('click',function(){
            localStorage.removeItem("MenuMentoring");
        });

        $('.showProgramsBtn').on('click',function(){
            localStorage.setItem("MenuMentoring","program");
        });
    } );
</script>

@endsection