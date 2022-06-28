@extends('layouts.default')

@section('content')

<div class="pb-3 pt-3">

<div class="container-fluid main-container details-section">

    <div class="row">
        <div class="col-lg-12 user-details" >
            <div class="row p-5">

                <div class="container-fluid">
                    <div class="row block-title-2 justify-content-between">
                        <div class="col-3 col-md-6">
                            <h4 style="margin-left:-10px; font-size:20px;"class="">{{ trans('langSummaryProfile') }}</h4>
                        </div>
                        <div class="col-4 col-xl-4 col-md-6">
                            <div class="collapse-details-button" data-bs-toggle="collapse" data-bs-target=".user-details-collapse" aria-expanded="false" onclick="switch_user_details_toggle()" >
                                <span style="float:right; margin-right:-10px;" class="user-details-collapse-more"> {{ trans('langMoreInfo') }} <i class="fas fa-chevron-down"></i> </span>
                                <span style="float:right; margin-right:-10px;"class="user-details-collapse-less"> {{ trans('langSummaryProfile') }} <i class="fas fa-chevron-up"></i> </span>
                            </div>
                        </div>

                    </div>

                </div>

                <div class="container-fluid collapse user-details-collapse show">

                    <div class="row">

                        <div class="col-xl-4 col-lg-6 col-md-8 col-sm-12 col-xs-12">
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-xs-12" style="margin-left:-20px;">
                                        <img class="user-detals-photo" src="{{ user_icon($uid, IMAGESIZE_LARGE) }}" alt="{{ $_SESSION['surname'] }} {{ $_SESSION['givenname'] }}">
                                    </div>
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-xs-12" style="margin-left:20px;">
                                        <div class="user-detals-fullname">
                                            <h5> {{ $_SESSION['surname'] }} {{ $_SESSION['givenname'] }} </h5>
                                        </div>
                                        <div>{{ trans('langTeacher') }}</div>
                                        <div class="text-secondary" style="margin-top: 40px;"> {{ $_SESSION['uname'] }} </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="col-xl-8 col-lg-6 col-md-4 col-sm-12 col-xs-12">
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                        <p>{{ trans('langSumCoursesEnrolled') }}: <strong>{{ $student_courses_count }}  </strong></p>
                                        <p>{{ trans('langSumCoursesSupport') }}:  <strong>{{ $teacher_courses_count }}</strong></p>
                                    </div>
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-xs-12">
                                        <span>{{ trans('langProfileLastVisit') }}:</span> {{ claro_format_locale_date(trans('dateFormatLong'), strtotime($lastVisit->when)) }}
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>
                </div>

                <div class="container-fluid collapse user-details-collapse">
                    <div class="row">
                        <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <div class="container-fluid">
                                <div class="row justify-content-center">
                                    <div class="user-detals-photo-2">
                                        <img src="{{ user_icon($uid, IMAGESIZE_LARGE) }}" alt="{{ $_SESSION['surname'] }} {{ $_SESSION['givenname'] }}">
                                    </div>
                                </div>
                                <div class="row justify-content-center text-center" >
                                    <h5> {{ $_SESSION['surname'] }} {{ $_SESSION['givenname'] }} </h5>
                                    <p> {{ trans('langTeacher') }} </p>
                                </div>
                                <div class="row justify-content-center text-center">
                                    <div class="py-1" >
                                        <a href="{{ $urlAppend }}main/profile/profile.php" class="btn btn-outline-primary btn-rounded"><i class="fas fa-pen"></i>{{ trans('langModProfile') }}</a>
                                    </div>
                                    <div class="py-1">
                                        <a href="#" class="btn btn-outline-warning btn-rounded"><i class="fas fa-trash"></i>{{ trans('langUnregUser') }} </a>
                                    </div>
                                    <div class="py-1">
                                        {{ trans('langExplain') }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-8 col-lg-8 col-md-8 col-sm-12 col-xs-12">
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-6 ">
                                        <p>{{ trans('langSumCoursesEnrolled') }}: <strong>{{ $student_courses_count }}  </strong></p>
                                        <p>{{ trans('langSumCoursesSupport') }}:  <strong>{{ $teacher_courses_count }}</strong></p>
                                    </div>
                                    <div class="col-6  ">
                                        <span>{{ trans('langProfileLastVisit') }}:</span> {{ claro_format_locale_date(trans('dateFormatLong'), strtotime($lastVisit->when)) }}
                                    </div>
                                </div>

                                <div class="row">

                                    <div class="container-fluid">

                                        <div class="row block-title-3">
                                            <p>{{ trans('langPersonalInfo') }}</p>
                                        </div>
                                        <div class="row">
                                            <div class="col-xl-5 col-lg-5 col-md-6 col-sm-6 col-xs-12">
                                                <label for="info" class="text-secondary" >E-mail:</label>
                                                <h5 id="info" class="text-primary font-weight-bold" > {{ $_SESSION['email'] }}</h5>
                                            </div>
                                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-xs-12">
                                                <label for="info" class="text-secondary" >{{ trans('langStatus') }}:</label>
                                                <h5 id="info" class="text-primary font-weight-bold" > {{ trans('langMetaTeacher') }}: </h5>
                                            </div>
                                            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-xs-12">
                                                <label for="info" class="text-secondary" >{{ trans('langFaculty') }}:</label>
                                                <h5 id="info" class="text-primary font-weight-bold" > - </h5>
                                            </div>
                                            <div class="col-xl-5 col-lg-5 col-md-6 col-sm-6 col-xs-12">
                                                <label for="info" class="text-secondary">{{trans('langPhone')}}:</label>
                                                <h5 id="info" class="text-primary font-weight-bold" > {{ $userdata->phone }}</h5>
                                            </div>
                                            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6 col-xs-12">
                                                <label for="info" class="text-secondary" >{{ trans('langAm') }}:</label>
                                                <h5 id="info" class="text-primary font-weight-bold" > {{ $userdata->am }}</h5>
                                            </div>
                                            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-6 col-xs-12">
                                                <label for="info" class="text-secondary" >{{ trans('langProfileMemberSince') }}:</label>
                                                <h5 id="info" class="text-primary font-weight-bold" > {{ $userdata->registered_at }}</h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <div class="row border-bottom border-primary block-title-3">
                                            <p>{{ trans('langAboutMe') }}</p>
                                        </div>
                                        <p> Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet
                                            dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit
                                            lobortis nisl ut aliquip ex ea commodo. </p>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <div class="row border-bottom border-primary block-title-3">
                                            <p>{{ trans('langMyInterests') }}</p>
                                        </div>
                                        <p> Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet
                                            dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit
                                            lobortis nisl ut aliquip ex ea commodo.Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit
                                            lobortis nisl ut aliquip ex ea commodo. </p>
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

<div class="container-fluid main-container cources-section mt-3">
    <div class="row">
        <div class="col-12 col-xl-8 user-details">
            <div class="row p-5">
                <div class="container-fluid mycourses_view_container" id="mycourses_view">
                    <div class="row border-bottom border-primary justify-content-between mb-4">

                        <div class="col-4">
                            <span class="text-primary" style="font-size:20px; margin-left:-10px;">{{ trans('langMyCoursesSide') }}</span>
                        </div>

                        <div class="col-xl-1 col-lg-1 col-md-2 col-sm-2 col-xs-2">
                            <div id="bars-active" style="display:flex;">
                                <div id="cources-bars-button"
                                    class="collapse-cources-button text-primary" >
                                    <span class="list-style active"><i class="fas fa-custom-size fa-bars"></i></span>
                                </div>
                                <div id="cources-pics-button"
                                    class="collapse-cources-button text-secondary collapse-cources-button-deactivated"
                                    onclick="switch_cources_toggle()">
                                    <span class="grid-style"><i class="fas fa-custom-size fa-th-large"></i></span>
                                </div>
                            </div>

                            <div id="pics-active" style="display:none">
                                <div id="cources-bars-button"
                                    class="collapse-cources-button text-secondary collapse-cources-button-deactivated"
                                    onclick="switch_cources_toggle()">
                                    <span class="list-style active"><i class="fas fa-custom-size fa-bars"></i></span>
                                </div>
                                <div id="cources-pics-button" class="collapse-cources-button text-primary">
                                    <span class="grid-style"><i class="fas fa-custom-size fa-th-large"></i></span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Courses List --}}

                <div id="cources-bars" class="container-fluid">

                    {!! $perso_tool_content['lessons_content'] !!}

                    {{-- <div class="row cources-bars-page" id="cources-bars-page-1">
                        @foreach($_SESSION['courses'] as $i => $cource )
                        <div class="col-12">
                            <div class="lesson">
                                <h3 class="lesson-title">
                                    <a class="lesson_title_a" style="text-decoration:none; font-size:18px;" href="{{$urlAppend}}courses/{{$cource->code}}/index.php">{{ $cource->title }}</a>
                                    <span class="lesson-id" style="font-size:17px;">({{ $cource->code }})</span>
                                </h3>
                                <div class="lesson-professor">{{ $cource->prof_names }}</div>
                            </div>
                            <hr>
                        </div>
                            @if( $i>0 && ($i+1)%$items_per_page==0 )
                    </div>
                    <div class="row cources-bars-page" style="display:none" id="cources-bars-page-{{ceil($i/$items_per_page)+1}}" >
                            @endif
                        @endforeach
                    </div>
                    @include('portfolio.portfolio-courcesnavbar', ['paging_type' => 'bars', 'cource_pages' => $cource_pages])
                        --}}
                </div>

                <div id="cources-pics" class="container-fluid cources-paging" style="display:none">
                    <div class="row cources-pics-page" id="cources-pics-page-1">
                        @foreach($cources as $i => $cource )
                        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-xs-12">
                            <div class="lesson">
                                <figure class="lesson-image" style="background-color:#f5f5f5">
                                    <a href="{{$urlServer}}courses/{{$cource->code}}/index.php">
                                    <picture>
                                        @if($cource->course_image == NULL)
                                            <img class="imageCourse" src="{{ $urlAppend }}template/modern/images/no-image-found-360x250.png" alt="{{ $cource->course_image }}" /></a>
                                        @else
                                            <img class="imageCourse" src="{{$urlAppend}}courses/{{$cource->code}}/image/{{$cource->course_image}}" alt="{{ $cource->course_image }}" /></a>
                                        @endif
                                    </picture>

                                </figure>
                                <h3 class="lesson-title">
                                    <a class="lesson_title_a" style="text-decoration:none; font-size:18px;" href="{{$urlServer}}courses/{{$cource->code}}/index.php">{{ $cource->title }}</a>
                                    <span class="lesson-id" style="font-size:18px;">({{ $cource->code }})</span>
                                </h3>
                                <div class="lesson-professor">{{ $cource->prof_names }}</div>
                            </div>
                            <hr>
                        </div>
                            @if( $i>0 && ($i+1)%$items_per_page==0 )
                    </div>
                    <div class="row cources-pics-page" style="display:none" id="cources-pics-page-{{ceil($i/$items_per_page)+1}}" >
                            @endif
                        @endforeach
                    </div>
                    @include('portfolio.portfolio-courcesnavbar', ['paging_type' => 'pics', 'cource_pages' => $cource_pages])
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">


            <div class="container-fluid container_fluid_calendar">
                @include('portfolio.portfolio-calendar')
            </div>


            <div class="container-fluid user-announcements-portfolio">
                <div class="row p-5">

                    <h5 class="text-primary">{{ trans('langMyPersoAnnouncements') }}</h5>
                    <hr style="color:blue">


                    <div class="container-fluid">
                        {!! $user_announcements !!}
                    </div>

                    <div class="row p-2"></div>
                    <div class="row p-2"></div>

                    <div class="container-fluid">
                        <a href="{{$urlAppend}}modules/announcements/myannouncements.php">{{ trans('langAllAnnouncements') }}</a>
                    </div>

                </div>
            </div>


            <div class="container-fluid user-messages-portfolio">
                <div class="row p-5">

                    <h5 class="text-primary">{{ trans('langMyPersoMessages') }}</h5>
                    <hr style="color:blue">

                    <div class="container-fluid">
                        <ul class='tablelist'>
                            @if(!empty($user_messages))
                                {!! $user_messages !!}
                            @else
                                <li class='list-item' style='border-bottom:none;'>
                                    <div class='text-title not_visible'> - {{ trans('langDropboxNoMessage') }} - </div>
                                </li>
                            @endif
                        </ul>
                    </div>

                    <div class="row p-2"></div>
                    <div class="row p-2"></div>


                    <div class="container-fluid">
                        <a href="{{$urlAppend}}modules/message/index.php">{{ trans('langAllMessages') }}</a>
                    </div>


                </div>
            </div>


        </div>
    </div>
</div>

</div>
@endsection
