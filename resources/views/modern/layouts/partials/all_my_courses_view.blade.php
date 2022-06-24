
    <!-- <link rel="stylesheet" type="text/css" href="{{ $urlAppend }}template/modern/css/all_my_courses_view.css"/>
    <script type="text/javascript" src="{{ $urlAppend }}template/modern/js/my_courses_color_header.js"></script> -->

    <div class="col-xl-12 col-lg-12 col-md-12 col-12 col-12 justify-content-center courses-details">

        <div class="row p-5">



            <nav class="navbar navbar-expand-lg navrbar_menu_btn">
                <a type="button" class="d-none d-sm-block d-md-none d-lg-block ms-2 btn btn-primary btn btn-primary" href="{{$urlAppend}}modules/help/help.php?language={{$language}}&topic=message" style='margin-top:-10px'>
                    <i class="fas fa-question"></i>
                </a>
            </nav>

            <nav class="navbar_breadcrumb" aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ $urlAppend }}main/portfolio.php">{{trans('langPortfolio')}}</a></li>
                    <li class="breadcrumb-item"><a href="{{ $urlAppend }}main/my_courses.php">{{trans('mycourses')}}</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{$toolName}}</li>
                </ol>
            </nav>

            <div class="d-lg-none mr-auto">
               <div class="row p-2"></div><div class="row p-2"></div><div class="row p-2"></div>
            </div>

            <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                <div class="row p-2"></div><div class="row p-2"></div>
                <legend class="float-none w-auto py-2 px-4 notes-legend">
                    <span style="margin-left:-20px;"><i class="fas fa-graduation-cap"></i> {{ trans('langMyCourses') }}</span>
                    <p class="text-end registerToCourseText">{{ trans('langRegCourses') }}: </p>
                    <a class="add_course_a" href="{{ $urlAppend }}modules/auth/courses.php"><i class='fas fa-graduation-cap fa-3x courses_fas'></i>
                        <span class="span_add_course_a">+</span>
                    </a>
                </legend> 
            </div>

            <div class="row p-2"></div><div class="row p-2"></div>


            <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 ColContentCourses">
                <div class="table-responsive">
                    <table id="courses_table_pag" class="table_my_courses">
                        <thead class="thead_courses text-light">
                            <tr>
                                <th scope="col"><span class="th_courses_comment">#</span></th>
                                <th><span class="th_courses_comment">{{ trans('langTitle') }}</span></th>
                                <th><span class="th_courses_comment">{{ trans('langCode') }}</span></th>
                                <th><span class="th_courses_comment">{{ trans('langTeacher') }}</span></th>
                                <th><span class="th_courses_comment">{{ trans('langUnCourse') }}</span></th>
                            </tr>
                        </thead>
                        <tbody>
                        <div class="row cources-bars-page" id="cources-bars-page-1">


                                <?php $i=0; ?>
                                    @foreach ($myCourses as $course)
                                    <?php $i++; ?>
                                    <tr>
                                        <th scope="row">{{$i}}</th>
                                        <td><a class="course_title_table_mycourses" href="{{$urlServer}}courses/{{$course->code}}/index.php">{{ q($course->title) }}</a></td>
                                        <td><span class="course_prof_code">({{ q($course->public_code) }})</span></td>
                                        <td><span class="course_prof_code">{{ q($course->professor) }}</span></td>
                                        <td>
                                            <a href="" data-bs-toggle="modal" data-bs-target="#exampleModal{{$course->course_id}}" >
                                                <i class="fas fa-remove-format"></i>
                                            </a>

                                        </td>
                                    </tr>

                                    
                                    <div class="modal fade" id="exampleModal{{$course->course_id}}" tabindex="-1" aria-labelledby="exampleModalLabel{{$course->course_id}}" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="exampleModalLabel{{$course->course_id}}">{{ trans('langUnCourse') }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    {{ trans('langConfirmUnregCours') }}<strong class="unregCourseStrong">{{$course->title}}</strong>;
                                                </div>
                                                <div class="modal-footer">
                                                    <a class="btn btn-secondary" href="" data-bs-dismiss="modal">{{trans('langCancel')}}</a>

                                                    <a class="btn btn-danger" href="my_courses.php?title={{ q($course->title) }}">{{trans('langDelete')}}</a>

                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                @endforeach

                            </div>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>


