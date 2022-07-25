    
    <div class="col-xl-12 col-lg-12 col-md-12 col-12 col-12 justify-content-center col_maincontent_active_Homepage">

        <div class="row p-lg-5 p-md-5 ps-1 pe-2 pt-5 pb-5">

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
                        <thead class="list-header text-light">
                            <tr>
                                <th scope="col">#</th>
                                <th>{{ trans('langTitle') }}</th>
                                <th>{{ trans('langCode') }}</th>
                                <th>{{ trans('langTeacher') }}</th>
                                <th>{{ trans('langUnCourse') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                        @if($myCourses)
                        <div class="row cources-bars-page" id="cources-bars-page-1">
                            <?php $i=0; ?>
                                @foreach ($myCourses as $course)
                                <?php $i++; ?>
                                <tr>
                                    <th scope="row">{{$i}}</th>
                                    <td><a href="{{$urlServer}}courses/{{$course->code}}/index.php">{{ q($course->title) }}</a></td>
                                    <td><span class="text-secondary">({{ q($course->public_code) }})</span></td>
                                    <td><span class="text-secondary">{{ q($course->professor) }}</span></td>
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
                                                {{ trans('langConfirmUnregCours') }}<strong class="text-warning">{{$course->title}}</strong>;
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
                        @else
                            <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                                <div class='alert alert-warning'>{{ trans('langNoCourses') }}</div>
                            </div> 
                        @endif

                            
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>


