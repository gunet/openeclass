

            @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

            {!! $action_bar !!}

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

            @if($myCourses)
                <div class='col-12'>
                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                        @php
                            $pagesPag = 0;
                            $allCourses = 0;
                            $temp_pages = 0;
                            $countCards = 1;
                            if($countCards == 1){
                                $pagesPag++;
                            }
                        @endphp
                        @foreach($myCourses as $course)
                            @php $temp_pages++; @endphp
                            <div class="col cardCourse{{ $pagesPag }}">
                                <div class="card h-100 card{{ $pagesPag }} Borders border-card px-2 py-3">
                                    @php
                                        $courseImage = '';
                                        if(!empty($course->course_image)){
                                            $courseImage = "{$urlServer}courses/$course->code/image/$course->course_image";
                                        }else{
                                            $courseImage = "{$urlServer}template/modern/img/ph1.jpg";
                                        }
                                    @endphp
                                    <div class='card-header border-0 bg-white'>
                                        <div class="card-title d-flex justify-content-start align-items-start gap-2 mb-0">
                                            @if($course->visible == 1)
                                                <button type="button" class="btn btn-transparent p-0" data-bs-toggle="tooltip" data-bs-placement="bottom" title="{{trans('langRegCourse')}}">
                                                    <i class="fa-solid fa-square-pen Neutral-600-cl settings-icons"></i>
                                                </button>
                                            @endif
                                            @if($course->visible == 2)
                                                <button type="button" class="btn btn-transparent p-0" data-bs-toggle="tooltip" data-bs-placement="bottom" title="{{trans('langOpenCourse')}}">
                                                    <i class="fa-solid fa-lock-open Neutral-600-cl settings-icons"></i>
                                                </button>
                                            @endif
                                            @if($course->visible == 0)
                                                <button type="button" class="btn btn-transparent p-0" data-bs-toggle="tooltip" data-bs-placement="bottom" title="{{trans('langClosedCourse')}}">
                                                    <i class="fa-solid fa-lock Neutral-600-cl settings-icons"></i>
                                                </button>
                                            @endif
                                            @if($course->visible == 3)
                                                <button type="button" class="btn btn-transparent p-0" data-bs-toggle="tooltip" data-bs-placement="bottom" title="{{trans('langInactiveCourse')}}">
                                                    <i class="fa-solid fa-triangle-exclamation Neutral-600-cl settings-icons"></i>
                                                </button>
                                            @endif
                                            <a class='@if($course->visible == 3) InvisibleCourse @endif TextBold mt-1' href="{{ $urlServer }}courses/{{ $course->code }}/index.php">{{ q($course->title) }}</a>
                                        </div>
                                    </div>


                                    <div class="card-body pt-0">
                                        <img src="{{ $courseImage }}" class="card-img-top cardImgCourse rounded-0 @if($course->visible == 3) InvisibleCourse @endif" alt="course image">
                                        <div class="card-text mt-3">
                                            <p class="d-inline form-value @if($course->visible == 3) InvisibleCourse @endif mb-0 vsmall-text">{{ trans('langCode') }}:</p>
                                            &nbsp<p class="d-inline form-value @if($course->visible == 3) InvisibleCourse @endif vsmall-text">{{ q($course->public_code) }}</p>
                                        </div>
                                        <div class="card-text">
                                            <p class='d-inline form-value @if($course->visible == 3) InvisibleCourse @endif mb-0'>{{ trans('langTeacher') }}:</p>
                                            &nbsp<p class="d-inline form-value @if($course->visible == 3) InvisibleCourse @endif vsmall-text">{{ q($course->professor) }}</p>
                                        </div>

                                    </div>
                                    <div class='card-footer d-flex justify-content-center align-items-center bg-white border-0 mb-2'>
                                        <!-- check if uid is editor of course or student -->
                                        <!------------------------------------------------->
                                        @php $is_course_teacher = check_editor($uid,$course->course_id); @endphp
                                        <!------------------------------------------------->
                                        <!------------------------------------------------->
                                        @if($_SESSION['status'] == USER_TEACHER and $is_course_teacher and $course->status == 1)
                                            <a class='btn submitAdminBtn w-100 gap-1' href="{{$urlServer}}modules/course_info/index.php?from_home=true&amp;course={{$course->code}}">
                                                <i class="fa-solid fa-gear settings-icons"></i>
                                                {{ trans('langAdm') }}
                                            </a>
                                        @else
                                            @if (get_config('disable_student_unregister_cours') == 0)
                                                <button class='btn deleteAdminBtn w-100 gap-1' data-bs-toggle="modal" data-bs-target="#exampleModal{{$course->course_id}}" >
                                                    <i class="fa-solid fa-circle-xmark settings-icons"></i>
                                                    {{ trans('langUnregCourse') }}
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="modal fade" id="exampleModal{{$course->course_id}}" tabindex="-1" aria-labelledby="exampleModalLabel{{$course->course_id}}" aria-hidden="true">
                                <form method="post" action="{{$urlAppend}}main/unregcours.php?u={{ $_SESSION['uid'] }}&amp;cid={{$course->course_id}}">
                                    <div class="modal-dialog modal-md">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                
                                                <div class="modal-title" id="exampleModalLabel{{$course->course_id}}">
                                                    <div class='icon-delete'><i class='fa-regular fa-trash-can fa-xl Accent-200-cl'></i></div>
                                                    <h3 class='modal-title-default text-center text-center mb-0'>{{ trans('langUnCourse') }}</h3>
                                                </div>
                                            </div>
                                            <div class="modal-body text-center">
                                                {{ trans('langConfirmUnregCours') }}<strong class="text-capitalize"> {{$course->title}}</strong>;
                                                <input type='hidden' name='fromMyCoursesPage' value="1">
                                            </div>
                                            <div class="modal-footer d-flex justify-content-center align-items-center">
                                                <a class="btn cancelAdminBtn" href="" data-bs-dismiss="modal">{{trans('langCancel')}}</a>

                                                <button type='submit' class="btn deleteAdminBtn" name="doit">{{trans('langDelete')}}</a>

                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            @php
                                if($countCards == 6 and $temp_pages < count($myCourses)){
                                    $pagesPag++;
                                    $countCards = 0;
                                }
                                $countCards++;
                                $allCourses++;
                            @endphp

                        @endforeach
                    </div>


                    <input type='hidden' id='KeyallCourse' value='{{ $allCourses }}'>
                    <input type='hidden' id='KeypagesCourse' value='{{ $pagesPag }}'>

                    <div class='col-12 d-flex justify-content-center Borders p-0 overflow-auto bg-white solidPanel mt-4'>
                        <nav aria-label='Page navigation example w-100'>
                            <ul class='pagination mycourses-pagination w-100 mb-0'>
                                <li class='page-item page-item-previous'>
                                    <a class='page-link bg-white' href='#'><span class='fa-solid fa-chevron-left'></span></a>
                                </li>
                                @if($pagesPag >=12 )
                                    @for($i=1; $i<=$pagesPag; $i++)

                                        @if($i>=1 && $i<=5)
                                            @if($i==1)
                                                <li id='KeypageCenter{{$i}}' class='page-item page-item-pages'>
                                                    <a id='Keypage{{$i}}' class='page-link' href='#'>{{$i}}</a>
                                                </li>

                                                <li id='KeystartLi' class='page-item page-item-pages d-flex justify-content-center align-items-end d-none'>
                                                    <a>...</a>
                                                </li>
                                            @else
                                                @if($i<$pagesPag)
                                                    <li id='KeypageCenter{{$i}}' class='page-item page-item-pages'>
                                                        <a id='Keypage{{$i}}' class='page-link' href='#'>{{$i}}</a>
                                                    </li>
                                                @endif
                                            @endif
                                        @endif

                                        @if($i>=6 && $i<=$pagesPag-1)
                                            <li id='KeypageCenter{{$i}}' class='page-item page-item-pages d-none'>
                                                <a id='Keypage{{$i}}' class='page-link' href='#'>{{$i}}</a>
                                            </li>

                                            @if($i==$pagesPag-1)
                                                <li id='KeycloseLi' class='page-item page-item-pages d-flex justify-content-center align-items-end d-block'>
                                                    <a>...</a>
                                                </li>
                                            @endif
                                        @endif

                                        @if($i==$pagesPag)
                                            <li id='KeypageCenter{{$i}}' class='page-item page-item-pages'>
                                                <a id='Keypage{{$i}}' class='page-link' href='#'>{{$i}}</a>
                                            </li>
                                        @endif
                                    @endfor

                                @else
                                    @for($i=1; $i<=$pagesPag; $i++)
                                        <li id='KeypageCenter{{$i}}' class='page-item page-item-pages'>
                                            <a id='Keypage{{$i}}' class='page-link' href='#'>{{$i}}</a>
                                        </li>
                                    @endfor
                                @endif

                                <li class='page-item page-item-next'>
                                    <a class='page-link bg-white' href='#'><span class='fa-solid fa-chevron-right'></span></a>
                                </li>
                            </ul>
                        </nav>
                    </div>


                </div>
            @else
                <div class='col-12'>
                    <div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>{{ trans('langNoCourses') }}</span></div>
                </div>
            @endif













