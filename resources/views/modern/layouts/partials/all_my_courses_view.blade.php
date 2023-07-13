
    <div class="col-12 col_maincontent_active_Homepage">

        <div class="row">

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
                                <div class="card h-100 card{{ $pagesPag }} Borders">
                                    @php 
                                        $courseImage = ''; 
                                        if(!empty($course->course_image)){
                                            $courseImage = "{$urlServer}courses/$course->code/image/$course->course_image";
                                        }else{
                                            $courseImage = "{$urlServer}template/modern/img/ph1.jpg";
                                        }
                                    @endphp 
                                    <img src="{{ $courseImage }}" class="card-img-top cardImgCourse @if($course->visible == 3) InvisibleCourse @endif" alt="course image">
                                    <div class="card-body">
                                        <div class="card-title d-flex justify-content-between align-items-start">
                                            <a class='@if($course->visible == 3) InvisibleCourse @endif TextSemiBold pe-2 fs-6' href="{{ $urlServer }}courses/{{ $course->code }}/index.php">{{ q($course->title) }}</a>
                                            @if($course->visible == 1) 
                                                <button type="button" class="btn btn-transparent fs-6 p-0" data-bs-toggle="tooltip" data-bs-placement="bottom" title="{{trans('langRegCourse')}}">
                                                    <span class='fa fa-lock text-secondary'></span>
                                                </button>
                                            @endif
                                            @if($course->visible == 2) 
                                                <button type="button" class="btn btn-transparent fs-6 p-0" data-bs-toggle="tooltip" data-bs-placement="bottom" title="{{trans('langOpenCourse')}}">
                                                    <span class='fa fa-unlock text-success'></span>
                                                </button>
                                            @endif
                                            @if($course->visible == 0) 
                                                <button type="button" class="btn btn-transparent fs-6 p-0" data-bs-toggle="tooltip" data-bs-placement="bottom" title="{{trans('langClosedCourse')}}">
                                                    <span class='fa fa-lock orangeText'></span>
                                                </button>
                                            @endif
                                            @if($course->visible == 3) 
                                                <button type="button" class="btn btn-transparent fs-6 p-0" data-bs-toggle="tooltip" data-bs-placement="bottom" title="{{trans('langInactiveCourse')}}">
                                                    <span class='fa fa-exclamation-triangle text-danger InvisibleCourse'></span>
                                                </button>
                                            @endif
                                        </div>
                                        <p class="card-text">
                                            <p class='card-title fw-bold mb-0 fs-6 @if($course->visible == 3) InvisibleCourse @endif mb-0'>{{ trans('langCode') }}</p>
                                            <p class="text-secondary @if($course->visible == 3) InvisibleCourse @endif small-text">{{ q($course->public_code) }}</p>
                                        </p>
                                        <p class="card-text">
                                            <p class='card-title fw-bold mb-0 fs-6 @if($course->visible == 3) InvisibleCourse @endif mb-0'>{{ trans('langTeacher') }}</p>
                                            <p class="text-secondary @if($course->visible == 3) InvisibleCourse @endif small-text">{{ q($course->professor) }}</p>
                                        </p>
                                        
                                    </div>
                                    <div class='card-footer d-flex justify-content-center align-items-center bg-white border-0 mb-2'>
                                        <!-- check if uid is editor of course or student -->
                                        <!------------------------------------------------->
                                        @php $is_course_teacher = check_editor($uid,$course->course_id); @endphp 
                                        <!------------------------------------------------->
                                        <!------------------------------------------------->
                                        @if($_SESSION['status'] == USER_TEACHER and $is_course_teacher and $course->status == 1)
                                            <a class='btn submitAdminBtn' href="{{$urlServer}}modules/course_info/index.php?from_home=true&amp;course={{$course->code}}">
                                                {{trans('langAdm') }}
                                            </a>
                                        @else
                                            <button class='btn deleteAdminBtn' data-bs-toggle="modal" data-bs-target="#exampleModal{{$course->course_id}}" >
                                                {{ trans('langUnregCourse') }}
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="modal fade" id="exampleModal{{$course->course_id}}" tabindex="-1" aria-labelledby="exampleModalLabel{{$course->course_id}}" aria-hidden="true">
                                <form method="post" action="{{$urlAppend}}main/unregcours.php?u={{ $_SESSION['uid'] }}&amp;cid={{$course->course_id}}">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h4 class="modal-title" id="exampleModalLabel{{$course->course_id}}">{{ trans('langUnCourse') }}</h4>
                                                <button type="button" class="close" data-bs-dismiss="modal">
                                                    <span class='fa-solid fa-xmark fa-lg Neutral-700-cl' aria-hidden='true'></span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                {{ trans('langConfirmUnregCours') }}<strong class="text-capitalize orangeText"> {{$course->title}}</strong>;
                                                <input type='hidden' name='fromMyCoursesPage' value="1">
                                            </div>
                                            <div class="modal-footer">
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

        </div>
    </div>








    


