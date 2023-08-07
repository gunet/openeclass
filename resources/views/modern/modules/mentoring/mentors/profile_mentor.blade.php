@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }}'>
        <div class="row rowMargin">

                    @if(isset($_SESSION['uid']))
                        <nav class='breadcrumb_mentoring' style="--bs-breadcrumb-divider: url(&#34;data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='8' height='8'%3E%3Cpath d='M2.5 0L1 1.5 3.5 4 1 6.5 2.5 8l4-4-4-4z' fill='currentColor'/%3E%3C/svg%3E&#34;);" aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a class="@if(!isset($_SESSION['uid'])) no_uid_menu @endif TextSemiBold" href="{{ $urlAppend }}modules/mentoring/mentoring_platform_home.php"><span class='fa fa-home'></span>&nbsp{{ trans('langHomeMentoringPlatform') }}</a></li>
                                <li class="breadcrumb-item"><a class='TextSemiBold' href="{{ $urlAppend }}modules/mentoring/mentors/all_mentors.php">{{ trans('langOurMentors') }}</a></li>
                                <li class="breadcrumb-item active TextMedium" aria-current="page">{{ $toolName }}</li>
                            </ol>
                        </nav>
                    @endif

                    @include('modules.mentoring.common.common_current_title')

                    <div class='col-12 mb-4'>
                        <div class='col-lg-7 col-md-9 col-12 ms-auto me-auto ps-3 pe-3'>
                            <p class='TextMedium text-center text-justify'>{!! trans('langInfoMentorsText2') !!}</p>
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
                   
                    @if(count($details_mentor) > 0)
                       <div class='col-lg-9 col-12 ms-auto me-auto'>
                           <div class='card-group'>
                              
                                   
                                    
                               <div class='col-12 mentor_eportfolio'>

                                    <div class='card w-100'>
                                        <div class='row g-0'>

                                            
                                            <div class='col-xl-3 col-lg-4 col-md-5 p-3'>
                                                @php $profile_img = profile_image($mentor_id, IMAGESIZE_LARGE, 'img-responsive img-circle img-profile card-img-top ProfileProgramCard'); @endphp
                                                {!! $profile_img !!}

                                                @php 
                                                    $nameMentor = Database::get()->queryArray("SELECT givenname,surname FROM user WHERE id = ?d",$mentor_id);
                                                @endphp
                                                @foreach($nameMentor as $n)
                                                    <p class='TextBold blackBlueText fs-6 mt-3 text-center'>{{ $n->givenname }} {{ $n->surname }}</p>
                                                @endforeach

                                                @php
                                                    $now = date('Y-m-d H:i:s', strtotime('now')); 
                                                    $checking_availability = Database::get()->querySingle("SELECT *FROM mentoring_mentor_availability 
                                                                                                            WHERE user_id = ?d AND '$now' BETWEEN start AND end",$mentor_id);
                                                    
                                                @endphp

                                                @if($checking_availability)
                                                    <div class='col-12 d-flex justify-content-center align-items-center mb-3'>
                                                        <p class="badge badge-mentor text-white small-text rounded-pill px-3 py-2">
                                                            <span class='fa fa-check pe-1'></span>{{ trans('langAvailableMentorProfile') }}
                                                        </p>
                                                    </div>
                                                @else
                                                    <div class='col-12 d-flex justify-content-center align-items-center mb-3'>
                                                        <p class="badge bg-danger text-white small-text rounded-pill px-3 py-2">
                                                                <span class='fa fa-times pe-1'></span>{{ trans('langNoAvailableMentorProfile') }}
                                                        </p>
                                                    </div>
                                                   
                                                @endif

                                            </div>

                                            <div class='col-xl-9 col-lg-8 col-md-6 p-lg-3'>
                                                <div class='card-body'>
                                                    <div class='help-block fs-5'>{{ trans('langPlatformIdentity') }}</div>
                                                    @foreach($details_mentor as $mentor)
                                                        @if(!empty($mentor->description))
                                                            <p class='mt-0 mb-0 fs-6'>{!! $mentor->description !!}</p>
                                                        @else
                                                            <p class='mt-0 mb-0 fs-6'>{{ trans('langNoInfoAvailable')}}</p>
                                                        @endif
                                                    @endforeach
                                                    {!! $eportfolio_fields['panels'] !!}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                   
                               </div>
                           </div>
                       </div>
                    @endif

                    @if(count($mentoring_programs_as_mentor) > 0)
                        <div class='col-12 mt-5'>
                            <p class='text-center TextSemiBold blackBlueText fs-5'>{{ trans('langParticipateAsMentor')}}</p>
                            
                            <table class='table-default rounded-2' id="table_programs_of_mentors">
                                <thead>
                                    <tr class='list-header'>
                                        <th>{{ trans('langMentoringName') }}</th>
                                        <th>{{ trans('langStartDate') }}</th>
                                        <th>{{ trans('langEndDate') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($mentoring_programs_as_mentor as $p)
                                        <tr>
                                            <td>
                                                <div class='d-flex'>
                                                    @if(!empty($p->program_image))
                                                        <img style='height:25px; width:25px;' src="{{ $urlAppend }}mentoring_programs/{{ $p->code }}/image/{{ $p->program_image }}">
                                                    @else
                                                        <img style='height:25px; width:25px;' src="{{ $urlAppend }}template/modern/images/nocontentyet.jpg">
                                                    @endif
                                                    <a class='TextSemiBold ms-2 PageProgram' href="{{ $urlAppend }}mentoring_programs/{{ $p->code }}/index.php">{{ $p->title }}</a>
                                                </div>
                                            </td>
                                            <td>{{ $p->start_date }}</td>
                                            <td>{{ $p->finish_date }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                           
                        </div>
                    @else
                       <div class='col-lg-9 col-12 ms-auto me-auto mt-5'>
                        <div class='col-12 p-3 bg-white rounded-2 solidPanel'>
                          <div class='alert alert-warning rounded-2'>{{ trans('langNoExistMentorInProgram') }}</div>
                        </div>
                       </div>
                    @endif
                    
                

        </div>
      
    </div>
</div>

<script type="text/javascript">
    $(document).ready( function () {
       
        $('#table_programs_of_mentors').DataTable();

        $('.PageProgram').on('click',function(){
            localStorage.removeItem("MenuMentoring");
        });
    } );
</script>
@endsection