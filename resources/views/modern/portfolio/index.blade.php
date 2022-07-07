@extends('layouts.default')

@section('content')

<div class="pb-3 pt-3">

<div class="container-fluid main-container details-section">

    <div class="row">
        <div class="col-lg-12 user-details" >
            <div class="row p-lg-5 p-md-5 ps-1 pe-2 pt-3 pb-5">

                <div class="container-fluid">
                    <div class="row block-title-2 justify-content-between">
                        <div class="col-3 col-md-5 ps-0">
                            <h5>{{ trans('langSummaryProfile') }}</h5>
                        </div>
                        <div class="col-4 col-xl-4 col-md-7 pe-0">
                            <div class="collapse-details-button" data-bs-toggle="collapse" data-bs-target=".user-details-collapse" aria-expanded="false" onclick="switch_user_details_toggle()" >
                                <span class="user-details-collapse-more fs-lg-5 fs-6 float-end"> {{ trans('langMoreInfo') }} <i class="fas fa-chevron-down"></i> </span>
                                <span class="user-details-collapse-less fs-lg-5 fs-6 float-end"> {{ trans('langSummaryProfile') }} <i class="fas fa-chevron-up"></i> </span>
                            </div>
                        </div>

                    </div>

                </div>

                <div class="container-fluid collapse user-details-collapse show">

                    <div class="row">

                        <div class="col-xl-4 col-lg-6 col-md-7 col-sm-12 col-12">

                            <div class="row">
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12">
                                    <img class="user-detals-photo" src="{{ user_icon($uid, IMAGESIZE_LARGE) }}" alt="{{ $_SESSION['surname'] }} {{ $_SESSION['givenname'] }}">
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12 ps-5">
                                    <div class="user-detals-fullname mt-3 ps-3 pe-3">
                                        <h5 class='text-lg-start text-center text-primary'> {{ $_SESSION['surname'] }} {{ $_SESSION['givenname'] }} </h5>
                                    </div>
                                    <div class="text-lg-start text-center text-secondary mt-3 ps-3 pe-3"> {{ $_SESSION['uname'] }} </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-8 col-lg-6 col-md-5 col-sm-12 col-12 pe-0 ps-5">
                            <div class="row">
                                <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                                    <p class='text-secondary mt-3 ps-3 pe-3'>{{ trans('langSumCoursesEnrolled') }}: <strong class='text-primary'>{{ $student_courses_count }}  </strong></p>
                                    <p class='text-secondary mt-3 ps-3 pe-3'>{{ trans('langSumCoursesSupport') }}:  <strong class='text-primary'>{{ $teacher_courses_count }}</strong></p>
                                </div>
                                <div class="col-xl-6 col-lg-6 col-md-12 col-sm-12 col-12">
                                    <p class='text-secondary mt-3 ps-3 pe-lg-0 pe-3'>{{ trans('langProfileLastVisit') }}:
                                       <strong class='text-primary'>{{ claro_format_locale_date(trans('dateFormatLong'), strtotime($lastVisit->when)) }}</strong>

                                    </p>

                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="container-fluid collapse user-details-collapse">
                    <div class="row">
                        <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12 col-12">
                            <div class="container-fluid">
                                <div class="row justify-content-center">
                                    <div class="user-detals-photo-2">
                                        <img class='rounded-circle ps-4' src="{{ user_icon($uid, IMAGESIZE_LARGE) }}" alt="{{ $_SESSION['surname'] }} {{ $_SESSION['givenname'] }}">
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

                        <div class="col-xl-8 col-lg-8 col-md-8 col-sm-12 col-12">

                                <div class="row">
                                    <div class="container-fluid">
                                        <div class="shadow-sm bg-body rounded bg-primary">
                                            <p class='text-center ps-2 pe-2 pb-2 pt-2 control-label-notes'>{{ trans('langCourseDescription') }}</p>
                                        </div>
                                        <div class="row mt-0">
                                            <div class="col-lg-6 col-md-12 col-sm-12 col-12 ps-lg-3 pe-lg-2 ps-3 pe-2">
                                                <p class='text-lg-start text-start text-secondary'>{{ trans('langSumCoursesEnrolled') }}: <strong class='text-primary'>{{ $student_courses_count }}  </strong></p>
                                                <p class='text-lg-start text-start text-secondary'>{{ trans('langSumCoursesSupport') }}:  <strong class='text-primary'>{{ $teacher_courses_count }}</strong></p>
                                            </div>
                                            <div class="col-lg-6 col-md-12 col-sm-12 col-12 ps-lg-3 pe-lg-2 ps-3 pe-2">

                                                <p class='text-lg-start text-start text-secondary'>{{ trans('langProfileLastVisit') }}:

                                                    <strong class='text-primary'>{{ claro_format_locale_date(trans('dateFormatLong'), strtotime($lastVisit->when)) }}</strong>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">

                                    <div class="container-fluid">

                                        <div class="shadow-sm bg-body rounded bg-primary">
                                            <p class='text-center ps-2 pe-2 pb-2 pt-2 control-label-notes'>{{ trans('langPersInfo') }}</p>
                                        </div>
                                        <div class="row mt-0">
                                            <div class="col-xl-5 col-lg-6 col-md-12 col-sm-12 col-12 ps-lg-3 pe-lg-2 ps-3 pe-2">
                                                <p id="info" class="text-lg-start text-start text-primary font-weight-bold" >E-mail: <span class='text-secondary'>{{ $_SESSION['email'] }}</span></p>
                                            </div>
                                            <div class="col-xl-3 col-lg-3 col-md-12 col-sm-12 col-12 ps-lg-3 pe-lg-2 ps-3 pe-2">
                                                <p id="info" class="text-lg-start text-start text-primary font-weight-bold" >{{ trans('langStatus') }}: <span class='text-secondary'>{{ trans('langMetaTeacher') }}</span></p>
                                            </div>
                                            <div class="col-xl-4 col-lg-3 col-md-12 col-sm-12 col-12 ps-lg-3 pe-lg-2 ps-3 pe-2">
                                                <p id="info" class="text-lg-start text-start text-primary font-weight-bold" >{{ trans('langFaculty') }}: <span class='text-secondary'>-</span></p>
                                            </div>

                                            <div class="col-xl-5 col-lg-6 col-md-12 col-sm-12 col-12 mt-lg-2 ps-lg-3 pe-lg-2 ps-3 pe-2">
                                                <p id="info" class="text-lg-start text-start text-primary font-weight-bold" >{{trans('langPhone')}}: <span class='text-secondary'>{{ $userdata->phone }}</span></p>
                                            </div>
                                            <div class="col-xl-3 col-lg-3 col-md-12 col-sm-12 col-12 mt-lg-2 ps-lg-3 pe-lg-2 ps-3 pe-2">

                                                <p id="info" class="text-lg-start text-start text-primary font-weight-bold">{{ trans('langAm') }}: <span class='text-secondary'>{{ $userdata->am }}</span></p>
                                            </div>
                                            <div class="col-xl-4 col-lg-3 col-md-12 col-sm-12 col-12 mt-lg-2 ps-lg-3 pe-lg-2 ps-3 pe-2">

                                                <p id="info" class="text-lg-start text-start text-primary font-weight-bold" >{{ trans('langProfileMemberSince') }}: <span class='text-secondary'>{{ $userdata->registered_at }}</span></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12 ps-lg-3 pe-lg-2 ps-3 pe-2">
                                        <div class="shadow-sm bg-body rounded bg-primary">
                                            <p class='text-center ps-2 pe-2 pb-2 pt-2 control-label-notes'>{{ trans('langAboutMe') }}</p>
                                        </div>
                                        <p class='ps-1'> Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet
                                            dolore magna aliquam erat volutpat. Ut wisi enim ad minim veniam, quis nostrud exerci tation ullamcorper suscipit
                                            lobortis nisl ut aliquip ex ea commodo. </p>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12 ps-lg-3 pe-lg-2 ps-3 pe-2">
                                        <div class="shadow-sm bg-body rounded bg-primary">
                                            <p class='text-center ps-2 pe-2 pb-2 pt-2 control-label-notes'>{{ trans('langMyInterests') }}</p>
                                        </div>
                                        <p class='ps-1'> Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet
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

<div class="container-fluid main-container cources-section mt-3">
    <div class="row">
        <div class="col-12 col-xl-8 user-details">
            <div class="row p-lg-5 p-md-5 ps-1 pe-2 pt-3 pb-5">

                <div class="col-xxl-4 col-xl-4 col-lg-4 col-md-5 col-sm-8 col-8">
                    <span class="text-primary fs-4">{{ trans('langMyCoursesSide') }}</span>
                </div>

                <div class="col-xxl-8 col-xl-8 col-lg-8 col-md-7 col-sm-4 col-4">
                    <div id="bars-active" class='float-end mt-0' style="display:flex;">
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

                    <div id="pics-active" class='float-end mt-0' style="display:none">
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

                <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'><hr class='text-primary mt-0 fs-1'></div>

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

                    <a class="btn btn-xs btn-primary float-md-end text-center mt-5" href="{{$urlServer}}main/my_courses.php">{{ trans('langRegCourses') }} <span class="fa fa-arrow-right"></span></a>

                </div>

                <div id="cources-pics" class="container-fluid cources-paging" style="display:none">
                    <div class="row cources-pics-page" id="cources-pics-page-1">
                        <?php $i=0; ?>
                        @foreach($cources as $cource)
                        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-xs-12">
                            <div class="lesson">
                                <figure class="lesson-image">
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
                                    <a class="fs-5" href="{{$urlServer}}courses/{{$cource->code}}/index.php">{{ $cource->title }}</a>
                                    <span class="lesson-id fs-5 text-secondary">({{ $cource->code }})</span>
                                </h3>
                                <div class="lesson-professor fs-5 text-secondary">{{ $cource->professor }}</div>

                            </div>
                            <hr>
                        </div>
                            @if( $i>0 && ($i+1)%$items_per_page==0 )
                    </div>
                    <div class="row cources-pics-page" style="display:none" id="cources-pics-page-{{ceil($i/$items_per_page)+1}}" >
                            @endif
                            <?php $i++; ?>
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
                <div class="row p-lg-5 p-md-5 ps-1 pe-2 pt-5 pb-5">

                    <h5 class="text-primary">{{ trans('langMyPersoAnnouncements') }}</h5>
                    <div class="container-fluid">
                        <hr class='text-primary'>
                        {!! $user_announcements !!}
                    </div>

                    <div class="container-fluid mt-3">
                        <hr class='text-primary'>
                        <a href="{{$urlAppend}}modules/announcements/myannouncements.php">{{ trans('langAllAnnouncements') }} <span class='fa fa-arrow-right'></span></a>
                    </div>

                </div>
            </div>


            <div class="container-fluid user-messages-portfolio">
                <div class="row p-lg-5 p-md-5 ps-1 pe-2 pt-5 pb-5">

                    <h5 class="text-primary">{{ trans('langMyPersoMessages') }}</h5>

                    <div class="container-fluid">

                        <hr class='text-primary'>
                        <ul class='tablelist pt-2 pb-2 ps-2 pe-2'>
                            @if(!empty($user_messages))
                                {!! $user_messages !!}
                            @else
                                <li class='list-item' style='border-bottom:none;'>
                                    <div class='text-title not_visible'> - {{ trans('langDropboxNoMessage') }} - </div>
                                </li>
                            @endif
                        </ul>
                    </div>

                    <div class="container-fluid mt-3">
                        <hr class='text-primary'>
                        <a href="{{$urlAppend}}modules/message/index.php">{{ trans('langAllMessages') }} <span class='fa fa-arrow-right'></span></a>
                    </div>

                </div>
            </div>


        </div>
    </div>
</div>

</div>

<script>
    var user_cources = <?php echo json_encode($cources); ?>;
    var user_cource_pages = <?php echo $cource_pages; ?>;
</script>
@endsection
