
   <div class="col-xl-12 col-lg-12 col-md-12 col-12 col-12 justify-content-center courses-details">

        <div class="row p-lg-5 p-md-5 ps-1 pe-2 pt-5 pb-5">

            <nav class="navbar navbar-expand-lg navrbar_menu_btn">
                <button type="button" class="ms-2 btn btn-primary btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal" style='margin-top:-10px'>
                    <i class="fas fa-question"></i>
                </button>
            </nav>

            @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

            <div class='d-none d-md-block'>
                <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-3">
                    <div class='h-100 shadow-lg p-3 bg-body rounded bg-primary'>
                        <span class='float-md-start'><i class="fas fa-graduation-cap text-warning"></i> <span class='control-label-notes'>{{ trans('langMyCourses') }}</span></span>
                        <span class='float-md-end'>
                            <span class='text-secondary'>{{ trans('langRegCourses') }}:</span>
                            <a class='btn btn-primary' href="{{ $urlAppend }}modules/auth/courses.php">
                                <span class='fa fa-plus'></span>
                            </a>
                        </span>
                    </div> 
                </div>
            </div>

            <div class='d-md-none d-block'>
                <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-3">
                    <div class='h-100 shadow-lg p-3 bg-body rounded bg-primary'>
                        <div class='row'>
                            <div class='col-12'>
                                <p class='text-center'><i class="fas fa-graduation-cap text-warning"></i> <span class='control-label-notes'>{{ trans('langMyCourses') }}</span></p>
                            </div>
                            <div class='col-12 mt-2'>
                                <div class='d-flex justify-content-center'>
                                    <span>
                                        <span class='text-secondary'>{{ trans('langRegCourses') }}:</span>
                                        <a class='btn btn-primary' href="{{ $urlAppend }}modules/auth/courses.php">
                                            <span class='fa fa-plus'></span>
                                        </a>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

           



            <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 mt-3">
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


            <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">{{trans('langHelp')}}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <iframe frameborder="0" width="100%" height="500px" src="https://docs.openeclass.org/el/teacher/portfolio/?do=export_xhtml"></iframe>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{trans('langClose')}}</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>


