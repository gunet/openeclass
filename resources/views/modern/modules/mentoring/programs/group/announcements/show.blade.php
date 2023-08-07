
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
                   
                    
                    {!! $action_bar !!}
               
                    @foreach($announcementShow as $a)
                        <div class='col-12'>
                            <div class="panel panel-admin rounded-2 border-1 BorderSolid bg-white py-md-4 px-md-4 py-3 px-3 shadow-none">
                                <div class='panel-heading bg-body p-0'>
                                    <span class='text-uppercase blackBlueText TextBold fs-6'>{{ $a->title }}</span>
                                </div>
                                <div class="panel-body ps-0 pe-0 pt-0 pb-0 rounded-2">
                                    <p class='TextBold small-text mb-3'><span class='small-text TextRegular'>{{ trans('langSent')}}</span>&nbsp--{!! format_locale_date(strtotime($a->date ?? '')) !!}&nbsp--</p>
                                    {!! $a->content !!}
                                </div>
                            </div>
                        </div>
                    @endforeach
                    
                   
                    

                    

                   
                    

                

        </div>
      
    </div>
</div>

<script type="text/javascript">
    $(document).ready( function () {
     
        $('.showProgramsBtn').on('click',function(){
            localStorage.setItem("MenuMentoring","program");
        });
    });
</script>
@endsection




