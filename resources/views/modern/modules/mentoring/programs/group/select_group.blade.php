
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
                            <li class="breadcrumb-item active TextMedium" aria-current="page">{{ $toolName }}</li>
                        </ol>
                    </nav>
                    
                    @include('modules.mentoring.common.common_current_title')

                    <div class='col-12 mb-4 ps-3 pe-3'>
                        <div class='col-lg-7 col-md-9 col-12 ms-auto me-auto ps-3 pe-3'>
                            <p class='TextMedium text-center text-justify'>{!! trans('langInfoGroupMentoringSpaceText')!!}</p>
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
                   
                   <div class='col-lg-12 col-6'>{!! $action_bar !!}</div>

                   <div class='col-12'>
                        <div class='col-12 ContentSpaceGroup d-flex justify-content-center align-items-center p-5 solidPanel rounded-2'>
                            <div class='card-group w-100'>
                                <div class='row ms-0'>
                                    <div class='col-md-6 col-12 d-flex align-items-strech'>
                                        <a class='w-100 h-100 mb-md-0 mb-3' href='{{ $urlAppend }}modules/mentoring/programs/group/index.php?commonGroupView=1'>
                                            <div class='card cardGroupSelect'>
                                                <div class='card-body'>
                                                    <div class='card-title TextBold text-center fs-5 mb-2 blackBlueText'>
                                                        {{ trans('langCommonGroup') }}
                                                    </div>
                                                    <div class='card-text TextRegular normalBlueText text-center small-text mb-1'>
                                                        {!! trans('langShareInCommonGroup') !!}
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                    <div class='col-md-6 col-12 d-flex align-items-strech'>
                                        <a class='w-100 h-100 mb-md-0 mb-3' href='{{ $urlAppend }}modules/mentoring/programs/group/index.php'>
                                            <div class='card cardGroupSelect'>
                                                <div class='card-body'>
                                                    <div class='card-title TextBold text-center fs-5 mb-2 blackBlueText'>
                                                        {{ trans('langGroups') }}
                                                    </div>
                                                    <div class='card-text TextRegular normalBlueText small-text text-center mb-1'>
                                                        {!! trans('langShareInGroups') !!}
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                

        </div>
      
    </div>
</div>

<script type="text/javascript">
    $(document).ready( function () {
   
        $('.showProgramsBtn').on('click',function(){
            localStorage.setItem("MenuMentoring","program");
        });

    } );
</script>
@endsection
