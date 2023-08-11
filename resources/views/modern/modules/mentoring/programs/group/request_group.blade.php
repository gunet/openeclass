
@extends('layouts.default')

@section('content')


<div class="col-12 main-section">
    <div class='{{ $container }}'>
        <div class="row m-auto">

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
                    
                    
                    @if(count($all_requests) > 0)
                        <div class="card-group">
                            
                            @foreach($all_requests as $r)
                                <div class='col-xl-3 col-lg-4 col-md-6 col-12 d-flex align-items-strech ps-md-0 pe-md-0 mb-3'>
                                    <div class="card w-100">
                                        @php $profile_img = profile_image($r->id, IMAGESIZE_LARGE, 'img-responsive img-circle img-profile card-img-top requestCardImage'); @endphp
                                        {!! $profile_img !!}

                                        <div class='card-body'>
                                            <div class='col-12 d-flex justify-content-center align-items-center fs-6 TextBold'>
                                                {{ $r->givenname }}&nbsp{{ $r->surname }}
                                            </div>
                                            <div class='col-12 user_request'>
                                                @php $details_user = render_eportfolio_fields_content($r->id); @endphp
                                                @if(!empty($details_user['panels']))
                                                    {!! $details_user['panels'] !!}
                                                @else
                                                    <p class='blackBlueText TextSemiBold text-center mt-3'>{{ trans('langNoInfoForMentees')}}</p>
                                                @endif
                                            </div>
                                        </div>
                                        <div class='panel-footer bg-light d-flex justify-content-center align-items-center rounded-2'>
                                            <button class="btn btn-outline-primary small-text rounded-2"
                                                data-bs-toggle="modal" data-bs-target="#AcceptRequestModal{{ $r->id }}" >
                                                <span class='fa fa-edit'></span><span class='hidden-xs-mentoring'>&nbsp{{ trans('langEditRequest') }}</span>
                                            </button>

                                            <div class="modal fade" id="AcceptRequestModal{{ $r->id }}" tabindex="-1" aria-labelledby="AcceptRequestModalLabel{{ $r->id }}" aria-hidden="true">
                                                <form method="post" action="{{ $_SERVER['SCRIPT_NAME'] }}?group_id={!! getInDirectReference($group_id) !!}">
                                                    <div class="modal-dialog modal-lg modal-primary">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="AcceptRequestModalLabel{{ $r->id }}">{{ trans('langAccept') }}</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                {!! trans('langAcceptRequestMsg') !!}
                                                                <input type='hidden' name='user_id' value='{{ $r->id }}'>

                                                            </div>
                                                            <div class="modal-footer">
                                                                <a class="btn btn-outline-secondary small-text rounded-2" href="" data-bs-dismiss="modal">{{ trans('langCancel') }}</a>
                                                                <button type='submit' class="btn btn-warning small-text rounded-2" name="accept_group_request" value="deny">
                                                                    {{ trans('langRejectRequest') }}
                                                                </button>
                                                                <button type='submit' class="btn btn-danger small-text rounded-2" name="accept_group_request" value="delete">
                                                                    {{ trans('langDelete') }}
                                                                </button>
                                                                <button type='submit' class="btn btn-success small-text rounded-2" name="accept_group_request" value="accept">
                                                                    {{ trans('langAccept') }}
                                                                </button>

                                                            </div>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            
                        </div>
                    @else
                        <div class='col-12'>
                            <div class='col-12 bg-white p-3 rounded-2 solidPanel'>
                                <div class='alert alert-warning rounded-2'>{{ trans('langRequestsRegisterGroupsNoExists') }}</div>
                            </div>
                        </div>
                    @endif

                    @if(count($all_denied_requests))
                       <div class='col-12 mt-4'>
                           <p class='text-center TextSemiBold fs-6 mb-2'>{{ trans('langRequestsHasDenied') }}</p>
                           <div class='table-responsive mt-0'>
                               <table class='table-default rounded-2'>
                                    <thead>
                                        <tr class='list-header'>
                                            <th>{{ trans('langName') }}</th>
                                            <th class='text-end'><span class='fa fa-cogs'></span></th>
                                        </tr></thead>
                                    @foreach($all_denied_requests as $r)
                                        <tr>
                                            <td class='d-flex justify-content-start align-items-center'>
                                                @php $profile_img = profile_image($r->id, IMAGESIZE_SMALL, 'img-responsive img-circle img-profile'); @endphp
                                                {!! $profile_img !!}&nbsp
                                                {{ $r->givenname }}&nbsp{{ $r->surname }}
                                            </td>
                                            <td class='text-end'>
                                                <a class='btn btn-outline-primary small-text rounded-2' 
                                                href="{{ $_SERVER['SCRIPT_NAME']}}?group_id={!! getIndirectReference($group_id) !!}&user={!! getIndirectReference($r->id) !!}&restore">{{ trans('langRestore')}}</a>
                                            </td>
                                        </tr>
                                    @endforeach
                               </table>
                           </div>
                       </div>
          
                    @endif
                  
                  
                

        </div>
      
    </div>
</div>

<script>

    $('.showProgramsBtn').on('click',function(){
            localStorage.setItem("MenuMentoring","program");
        });
  
</script>
@endsection