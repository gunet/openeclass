@extends('layouts.default')

@section('content')

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

<div class="container-fluid details-section">
    <div class="row rowMedium px-lg-0 px-3 py-lg-0 py-3">
        <div class="col-12 px-0">
            <div class='panel panel-admin border-0 BorderSolid bg-white px-lg-4 py-lg-3'>
                <div class='panel-heading bg-white'>
                    <div class='col-12 Help-panel-heading'>
                        <div class="row">
                            <div class="col-xl-8 col-md-5 col-10">
                                <span class='mt-2 Help-text-panel-heading'>{{ trans('langSummaryProfile') }}</span>
                            </div>
                            <div class="col-xl-4 col-md-7 col-2">
                                <div class="collapse-details-button" data-bs-toggle="collapse" data-bs-target=".user-details-collapse" aria-expanded="false" onclick="switch_user_details_toggle()" >
                                    <span class="user-details-collapse-less float-end"><span class='hidden-xs TextMedium text-uppercase small-text text-primary'>{{ trans('langMyProfile') }}</span> <i class="fas fa-chevron-up text-primary"></i></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class='panel-body'>
                    <div class="container-fluid collapse user-details-collapse show p-0 mt-2">
                        <div class="row px-2">
                            <div class='col-xl-2 col-lg-2 col-md-6 col-12 d-flex justify-content-lg-start justify-content-center'>
                                <img class="user-detals-photo" src="{{ user_icon($uid, IMAGESIZE_LARGE) }}" alt="{{ $_SESSION['surname'] }} {{ $_SESSION['givenname'] }}">
                            </div>
                            <div class='col-xl-3 col-lg-3 col-md-6 col-12'>
                                <div class="mt-3">
                                    <h6 class='text-xl-start text-center blackBlueText TextSemiBold'> {{ $_SESSION['surname'] }} {{ $_SESSION['givenname'] }} </h6>
                                </div>
                                <p class='small-text text-xl-start text-center TextMedium blackBlueText mb-3'>
                                    @if(($session->status == USER_TEACHER))
                                        {{ trans('langMetaTeacher') }}
                                    @elseif(($session->status == USER_STUDENT))
                                        {{ trans('langCStudent') }}
                                    @else
                                        {{ trans('langAdministrator')}}
                                    @endif
                                </p>
                                <p class="small-text text-xl-start text-center text-secondary TextMedium mt-3"> {{ $_SESSION['uname'] }} </p>
                            </div>
                            <div class='col-xl-3 col-lg-4 col-md-6 col-12'>
                                <p class='TextMedium text-center mt-3'>{{ trans('langSumCoursesEnrolled') }}&nbsp;&nbsp;{{ $student_courses_count }}</p>
                                <p class='TextMedium text-center'>{{ trans('langSumCoursesSupport') }}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $teacher_courses_count }}</p>
                            </div>
                            <div class='col-xl-4 col-lg-3 col-md-6 col-12 d-xl-flex justify-content-xl-center'>
                                <p class='small-text TextSemiBold text-center text-secondary mt-3 mb-0 pe-1 '>{{ trans('langProfileLastVisit') }}:</p>
                                <p class='blackBlueText text-center small-text TextSemiBold mt-xl-3 mt-1'>{{ format_locale_date(strtotime($lastVisit->when)) }}</p>
                            </div>
                            <div class='col-12 d-flex justify-content-md-end justify-content-center pe-0 mt-lg-0 mt-5'>
                                <button class='bg-transparent border-0 text-end'data-bs-toggle="collapse" data-bs-target=".user-details-collapse" aria-expanded="false" onclick="switch_user_details_toggle()" >
                                    <span class="user-details-collapse-more">
                                        <span class='text-primary text-uppercase mt-2 small-text'>{{ trans('langMoreInfo') }}</span>
                                        <i class="fas fa-chevron-down text-primary"></i>
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="container-fluid collapse user-details-collapse p-0 mt-2">
                        <div class="row px-2">
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                                <div class="row">
                                    <div class="col-12 d-flex justify-content-md-start justify-content-center">
                                        <img class='user-detals-photo m-auto d-block' src="{{ user_icon($uid, IMAGESIZE_LARGE) }}" alt="{{ $_SESSION['surname'] }} {{ $_SESSION['givenname'] }}">
                                    </div>
                                </div>
                                <div class="row justify-content-md-start justify-content-center text-center mt-2" >
                                    <h5 class='text-center blackBlueText TextSemiBold'> {{ $_SESSION['surname'] }} {{ $_SESSION['givenname'] }} </h5>
                                    <p class='text-center'>
                                        @if(($session->status == USER_TEACHER))
                                        {{ trans('langMetaTeacher') }}
                                        @elseif(($session->status == USER_STUDENT))
                                        {{ trans('langCStudent') }}
                                        @else
                                        {{ trans('langAdministrator')}}
                                        @endif
                                    </p>
                                </div>
                                <div class="row justify-content-md-start justify-content-center text-center mt-3">
                                    <div class="py-1 d-flex justify-content-center align-items-center" >
                                        <a href="{{ $urlAppend }}main/profile/profile.php" class="btn submitAdminBtn"><span class="fa fa-pencil me-1"></span>{{ trans('langModProfile') }}</a>
                                    </div>
                                    <div class="py-1 d-flex justify-content-center align-items-center">
                                        <a href="{{$urlAppend}}main/unreguser.php" class="btn deleteAdminBtn"><span class="fa fa-trash-o me-1"></span>{{ trans('langUnregUser') }} </a>
                                    </div>
                                    <div class="py-1">
                                        {{ trans('langExplain') }}
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-8 col-lg-8 col-md-8 col-sm-12 col-12 ps-md-3 pe-md-0 ps-0 pe-0">

                                <div class='panel panel-admin border-0 shadow-none mt-md-2 mt-3 bg-white rounded-0 px-0 shadow-none'>
                                    <div class='panel-heading rounded-0 bg-white ps-md-3 pe-md-1 px-0'>
                                        <div class='panel-heading bg-white ps-md-3 pe-md-0'>
                                            <div class='col-12 Help-panel-heading border-0'>
                                                <p class='TextMedium text-md-start text-center blackBlueText mb-2'>{{ trans('langSumCoursesEnrolled') }}&nbsp;&nbsp;{{ $student_courses_count }}</p>
                                                <p class='TextMedium text-md-start text-center blackBlueText'>{{ trans('langSumCoursesSupport') }}&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $teacher_courses_count }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class='panel panel-admin border-0 shadow-none mt-md-2 mt-3 bg-white rounded-0 shadow-none'>
                                    <div class='panel-heading rounded-0 bg-white ps-md-3 pe-md-1 px-0'>
                                        <div class='panel-heading bg-white ps-md-3 pe-md-0'>
                                            <div class='col-12 Help-panel-heading'>
                                                <span class='text-uppercase Help-text-panel-heading'>{{ trans('langPersInfo') }}</span>
                                            </div>
                                        </div>

                                        <div class='panel-body rounded-0'>
                                            <div class="row mt-0">
                                                <div class="col-xl-4 col-lg-6 col-12 mb-2">
                                                    <p class='text-secondary TextMedium small-text'>E-mail:
                                                    <p class='blackBlueText'>{{ $userdata->email }}</p>
                                                </div>

                                                <div class="col-xl-4 col-lg-6 col-12 mb-2">
                                                    <p class='text-secondary TextMedium small-text'>{{ trans('langStatus') }}:</p>
                                                    <p class='blackBlueText'>
                                                        @if(($session->status == USER_TEACHER))
                                                        {{ trans('langMetaTeacher') }}
                                                        @elseif(($session->status == USER_STUDENT))
                                                        {{ trans('langCStudent') }}
                                                        @else
                                                        {{ trans('langAdministrator')}}
                                                        @endif

                                                    </p>
                                                </div>

                                                <div class="col-xl-4 col-lg-6 col-12 mb-lg-3 mt-lg-3 mt-xl-0 mb-2">
                                                    <p class='text-secondary TextMedium small-text'>{{trans('langPhone')}}: </p>
                                                    <p class='blackBlueText'>{{ $userdata->phone }}</p>
                                                </div>


                                                <div class="col-xl-4 col-lg-6 col-12 mt-xl-0 mt-lg-3 mb-lg-3 mb-2">
                                                    <p class='text-secondary TextMedium small-text'>{{ trans('langAm') }}:</p>
                                                    <p class='blackBlueText'>{{ $userdata->am }}</p>
                                                </div>

                                                <div class="col-xl-4 col-lg-6 col-12 mb-lg-3 mb-2">
                                                    <p class='text-secondary TextMedium small-text'>{{ trans('langProfileMemberSince') }}:</p>
                                                    <p class='blackBlueText'>{{ $userdata->registered_at }}</p>
                                                </div>

                                                <div class="col-xl-4 col-lg-6 col-12">
                                                    <p class='text-secondary TextMedium small-text'>{{ trans('langFaculty') }}:</p>
                                                    <p class='blackBlueText'>
                                                        @php
                                                            $user = new User();
                                                            $departments = $user->getDepartmentIds($uid);
                                                        @endphp
                                                        @foreach ($departments as $dep)
                                                            {!! $tree->getFullPath($dep) !!}
                                                        @endforeach
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class='panel panel-admin border-0 bg-white rounded-0 px-0'>
                                    <div class='panel-heading rounded-0 bg-white ps-md-3 pe-md-1 px-0'>
                                        <div class='panel-heading bg-body ps-md-3 pe-md-0'>
                                            <div class='col-12 Help-panel-heading'>
                                                <span class='text-uppercase Help-text-panel-heading'>{{ trans('langAboutMe') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class='panel-body rounded-0 ps-md-3 pt-0 px-0'>
                                        <div class='col-12 ps-3'>
                                            @if (!empty($userdata->description))
                                                {!! standard_text_escape($userdata->description) !!}
                                            @else
                                                <p class='text-center mb-0'>{{ trans('langNoInfoAvailable') }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>








<div class="container-fluid details-section">
    <div class="row rowMedium px-lg-0 px-3 py-lg-0 py-0">
        <div class='col-xl-8 col-12 Courses-Content pe-lg-0 mt-lg-3 px-0'>
            <div class='panel panel-admin border-0 BorderSolid shadow-none bg-white px-lg-4 py-lg-3'>
                <div class='panel-heading bg-white'>
                    <div class='col-12 Help-panel-heading'>
                        <div class='row'>
                            <div class='col-8 d-inline-flex align-items-top'>
                                <span class="text-uppercase TextSemiBold mb-0 Help-text-panel-heading">{{ trans('langMyCoursesSide') }}</span>
                                <a href="{{$urlAppend}}modules/auth/courses.php" class='viewAllCourseBtn btn btn-outline-primary d-flex justify-content-center align-items-center ms-2' data-bs-toggle='tooltip'
                                    data-bs-placement='bottom' title data-bs-original-title="{{ trans('langRegCourses') }}">
                                    <span class='fa fa-check'></span>
                                </a>
                            </div>
                            <div class="col-4">
                                <div id="bars-active" type='button' class='float-end mt-0' style="display:flex;">
                                    <div id="cources-bars-button" class="collapse-cources-button text-primary">
                                        <span class="list-style active pe-2"><i class="fas fa-custom-size fa-bars custom-font" style='font-size:15px;'></i></span>
                                    </div>
                                    <div id="cources-pics-button" class="collapse-cources-button text-secondary collapse-cources-button-deactivated" onclick="switch_cources_toggle()">
                                        <span class="grid-style"><i class="fas fa-custom-size fa-th-large custom-font" style='font-size:15px;'></i></span>
                                    </div>
                                </div>
                                <div id="pics-active" type='button' class='float-end mt-0' style="display:none">
                                    <div id="cources-bars-button" class="collapse-cources-button text-secondary collapse-cources-button-deactivated" onclick="switch_cources_toggle()">
                                        <span class="list-style active pe-2"><i class="fas fa-custom-size fa-bars custom-font" style='font-size:15px;'></i></span>
                                    </div>
                                    <div id="cources-pics-button" class="collapse-cources-button text-primary">
                                        <span class="grid-style"><i class="fas fa-custom-size fa-th-large custom-font" style='font-size:15px;'></i></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class='panel-body'>
                    <div class='container-fluid p-0'>
                        <div class='row rowMedium px-lg-2'>
                            @if(Session::has('message'))
                                <div class='col-12 mt-3 px-0'>
                                    <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                                        @if(is_array(Session::get('message')))
                                            @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                            @foreach($messageArray as $message)
                                                {!! $message !!}
                                            @endforeach
                                        @else
                                            {!! Session::get('message') !!}
                                        @endif
                                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                    </p>
                                </div>
                            @endif
                            <div id="cources-bars" class="col-12 px-lg-1 px-0">
                                {!! $perso_tool_content['lessons_content'] !!}
                            </div>

                            <div id="cources-pics" class="col-12 px-lg-1 px-0" style="display:none">
                                <div class="row rowMedium cources-pics-page px-lg-2" id="cources-pics-page-1">
                                    @php $i=0; @endphp
                                    @foreach($cources as $cource)
                                    <div class="col-md-6 col-12 @if($i==0 or $i==2) ps-lg-1 ps-md-0 pe-md-2 @else pe-lg-1 ps-md-2 pe-md-0 @endif ps-0 pe-0">
                                        <div class="lesson border-bottom pb-1 pt-3">
                                            <figure class="lesson-image">
                                                <a href="{{$urlServer}}courses/{{$cource->code}}/index.php">
                                                <picture>
                                                    @if($cource->course_image == NULL)
                                                        <img class="imageCourse mb-md-2 mb-0" src="{{ $urlAppend }}template/modern/img/ph1.jpg" alt="{{ $cource->course_image }}" /></a>
                                                    @else
                                                        <img class="imageCourse mb-md-2 mb-0" src="{{$urlAppend}}courses/{{$cource->code}}/image/{{$cource->course_image}}" alt="{{ $cource->course_image }}" /></a>
                                                    @endif
                                                </picture>
                                            </figure>
                                            <div class="lesson-title">
                                                <a class='TextSemiBold fs-6' href="{{$urlServer}}courses/{{$cource->code}}/index.php">{{ $cource->title }}</a>
                                                <span class="lesson-id blackBlueText">({{ $cource->public_code }})</span>
                                            </div>
                                            <div class="small-text text-secondary lesson-professor mt-0">{{ $cource->professor }}</div>
                                        </div>

                                    </div>
                                        @if( $i>0 && ($i+1)%$items_per_page==0 )
                                </div>
                                <div class="row cources-pics-page ps-lg-1 pe-lg-2 ps-3 pe-3" style="display:none" id="cources-pics-page-{{ceil($i/$items_per_page)+1}}" >
                                        @endif
                                        @php $i++; @endphp
                                    @endforeach
                                </div>
                                @include('portfolio.portfolio-courcesnavbar', ['paging_type' => 'pics', 'cource_pages' => $cource_pages ,'cources' => $cources])
                            </div>

                        </div>
                    </div>
                </div>

                {{--<div class='panel panel-admin border-0 BorderSolid bg-white mt-lg-3 mt-3 py-md-4 px-md-4 py-3 px-3 shadow-none'>
                    {!! $portfolio_page_main_widgets !!}
                </div>--}}

            </div>
        </div>
        <div class='col-xl-4 col-12 ColumnCalendarAnnounceMessagePortfolio mt-lg-3 mt-2 ps-xl-3 px-lg-0 px-0 pb-lg-0 pb-3'>
            @include('portfolio.portfolio-calendar')
            <div class='panel panel-admin border-0 BorderSolid bg-white mt-lg-3 mt-3 py-md-4 px-md-4 py-3 px-3 shadow-none'>
                <div class='panel-heading bg-body p-0'>
                    <div class='col-12 Help-panel-heading'>
                        <span class='panel-title text-uppercase Help-text-panel-heading'>{{ trans('langMyPersoAnnouncements') }}</span>
                    </div>
                </div>
                <div class='panel-body p-0'>
                    @if(empty($user_announcements))
                        <div class='text-start mt-3 mb-2'><span class='text-title not_visible'>{{ trans('langNoRecentAnnounce') }}</span></div>
                    @else
                        {!! $user_announcements !!}
                    @endif
                </div>
                <div class='panel-footer d-flex justify-content-end p-0'>
                    <a class='TextMedium' href="{{$urlAppend}}modules/announcements/myannouncements.php" class='mt-1'>
                        {{ trans('langAllAnnouncements') }} <span class='fa fa-chevron-right'></span>
                    </a>
                </div>
            </div>

            <div class='panel panel-admin border-0 BorderSolid bg-white mt-lg-3 mt-3 py-md-4 px-md-4 py-3 px-3 shadow-none'>
                <div class='panel-heading bg-body p-0'>
                    <div class='col-12 Help-panel-heading'>
                    <span class='panel-title text-uppercase Help-text-panel-heading'>{{ trans('langMyPersoMessages') }}</span>
                    </div>
                </div>
                <div class='panel-body p-0'>
                    {!! $user_messages !!}
                </div>
                <div class='panel-footer d-flex justify-content-end p-0'>
                    <a class='TextMedium' href="{{$urlAppend}}modules/message/index.php">
                        {{ trans('langAllMessages') }} <span class='fa fa-chevron-right'></span>
                    </a>
                </div>
            </div>

            @if($portfolio_page_main_widgets)
                <div class='panel panel-admin border-0 BorderSolid bg-white mt-lg-3 mt-3 py-md-4 px-md-4 py-3 px-3 shadow-none'>
                    <div class='panel-heading bg-body p-0'>
                        <div class='col-12 Help-panel-heading'>
                            <span class='panel-title text-uppercase Help-text-panel-heading'>{{ trans('langMyWidgets') }}</span>
                        </div>
                    </div>
                    <div class='panel-body'>
                        {!! $portfolio_page_main_widgets !!}
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>

</div>

<script>
    var user_cources = <?php echo json_encode($cources); ?>;
    var user_cource_pages = <?php echo $cource_pages; ?>;
</script>
@endsection
