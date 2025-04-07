@extends('layouts.default')

@if ($isInOpenCoursesMode)
    @push('head_styles')
        <link rel="stylesheet" type="text/css" href="{{ $urlAppend }}modules/course_metadata/course_metadata.css">
    @endpush
    @push('head_scripts')
        <script type="text/javascript">
            var dialog;
            var showMetadata = function(course) {
                $('.modal-body', dialog).load('anoninfo.php', {course: course}, function(response, status, xhr) {
                    if (status === "error") {
                        $('.modal-body', dialog).html("Sorry but there was an error, please try again");
                        //console.debug("jqxhr Request Failed, status: " + xhr.status + ", statusText: " + xhr.statusText);
                    }
                });
                dialog.modal('show');
            };

            $(document).ready(function() {
                dialog = $('<div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modal-label" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><div class="modal-title" id="modal-label">{!! trans('langCourseMetadata') !!}</div><button type="button" class="close" data-bs-dismiss="modal"></button></div><div class="modal-body">body</div></div></div></div>');
            });

        </script>
    @endpush
@endif

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} main-container'>
        <div class="row m-auto">

            @if(isset($_SESSION['uid']))
                @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])
            @endif

            @include('layouts.partials.show_alert')

            <div class="col-12 @if(isset($_SESSION['uid'])) mt-4 @endif">
                <h1>{{ $toolName }}</h1>
            </div>

            <div class='col-12 mt-4'>
                @if (isset($buildRoots))
                    {!! $buildRoots !!}
                @endif
                <div class='col-12'>
                    <ul class='list-group list-group-flush'>
                        <li class="list-group-item list-group-item-action d-flex justify-content-start align-items-center flex-wrap gap-2">
                            {!! $tree->getFullPath($fc, false, $_SERVER['SCRIPT_NAME'] . '?fc=') !!}
                        </li>
                        {!! $childHTML !!}
                    </ul>
                </div>
            </div>

            @if (count($courses) > 0)
                <div class='col-12 mt-4'>
                    <ul class='list-group list-group-flush'>
                        <li class='list-group-item list-group-item-action d-flex justify-content-between align-items-center'>
                            <div>{{ trans('langCourse') }}</div>
                            <div>{{ trans('langGroupAccess') }}</div>
                        </li>
                        @foreach($courses as $mycourse)
                            <li class="list-group-item element d-flex justify-content-between align-items-center gap-5">
                                <div class='d-flex justify-content-start align-items-start gap-3'>
                                    @if (isset($_SESSION['uid'])) {{-- logged in user --}}
                                        <div class="d-flex justify-content-start align-items-center gap-3">
                                            @if (isset($myCourses[$mycourse->id]))
                                                @if ($myCourses[$mycourse->id]->status != 1) {{-- display registered courses --}}
                                                    <label class='label-container' aria-label='{{ trans('langSelect') }}'>
                                                        <input type='checkbox' name='selectCourse[]' value='{{ $mycourse->id }}' checked='checked' @if ($mycourse->visible == COURSE_CLOSED) class='reg_closed' @endif @if (get_config('disable_student_unregister_cours')) 'disabled' @endif>
                                                        <span class='checkmark'></span>
                                                    </label>
                                                @else
                                                    <i class='fa-solid fa-user fa-lg mt-3'></i>
                                                @endif
                                            @else {{-- display unregistered courses--}}
                                                    <label class='label-container' aria-label='{{ trans('langSelect') }}'>
                                                        <input type='checkbox' name='selectCourse[]' value='{{ $mycourse->id }}'
                                                               @if ((($mycourse->visible == COURSE_REGISTRATION or $mycourse->visible == COURSE_OPEN)
                                                                        and setting_get(SETTING_FACULTY_USERS_REGISTRATION, $mycourse->id) == 1
                                                                        and !in_array($fc, $user_faculty_ids))
                                                                    or (!is_enabled_course_registration($_SESSION['uid']))
                                                                    or $mycourse->visible == COURSE_CLOSED)
                                                                   disabled
                                                                @endif>
                                                        <span class='checkmark'></span>
                                                    </label>
                                            @endif
                                            <input type='hidden' name='changeCourse[]' value='{{ $mycourse->id }}'>
                                        </div>
                                    @endif
                                    <div>
                                        <div class='d-flex justify-content-start align-items-start gap-3 flex-wrap'>
                                            <div>
                                                @if ($mycourse->visible == COURSE_OPEN or $unlock_all_courses or isset($myCourses[$mycourse->id])) {{-- open course or user is registered to it --}}
                                                    <a class='TextBold' href="../../courses/{!! urlencode($mycourse->k) !!}/">{!! $mycourse->i !!}</a>
                                                    &nbsp;<small>({!! $mycourse->c !!})</small>
                                                @else
                                                    <span @if (isset($_SESSION['uid'])) id='cid{{ $mycourse->id }}' @endif class='TextBold'>
                                                        {!! $mycourse->i !!}
                                                    </span>
                                                    &nbsp;<small>({!! $mycourse->c !!})</small>
                                                @endif
                                                <div>
                                                    <small class='vsmall-text TextRegular'>{{ $mycourse->t }}</small>
                                                    @if (isset($_SESSION['uid']))           {{--  user is logged in --}}
                                                        @if ($mycourse->visible == COURSE_CLOSED and !setting_get(SETTING_COURSE_USER_REQUESTS_DISABLE, $mycourse->id) and !isset($myCourses[$mycourse->id])) {{-- user is not registered --}}
                                                            <br><small><em>
                                                            <a class='text-decoration-underline' href='../contact/index.php?course_id={{ $mycourse->id }}'>
                                                                @if($mycourse->clb)
                                                                    {{ trans('langLabelCollabUserReques') }}
                                                                @else
                                                                    {{ trans('langLabelCourseUserRequest') }}
                                                                @endif
                                                            </a>
                                                            </em></small>
                                                        @endif
                                                        {{-- course is password protected --}}
                                                        @if (isset($myCourses[$mycourse->id]))
                                                            @if ($myCourses[$mycourse->id]->status != 1 and (!empty($mycourse->password)))
                                                                <span class='badge Warning-200-bg'>{{ trans('langPassword') }}</span>
                                                                <input class='form-control' type='password' name='pass{{ $mycourse->id }}' value='{{ $mycourse->password }}' autocomplete='off' />
                                                            @endif
                                                        @else
                                                            @if (!empty($mycourse->password) and ($mycourse->visible == COURSE_REGISTRATION or $mycourse->visible == COURSE_OPEN))
                                                                <span class='badge Warning-200-bg'>{{ trans('langPassword') }}</span>
                                                                <input class='form-control' type='password' name='pass{{ $mycourse->id }}' autocomplete='off' />
                                                            @endif
                                                        @endif
                                                        {{-- course has prerequisites --}}
                                                        {!! getCoursePrerequisites($mycourse->id) !!}
                                                    @endif
                                                </div>
                                            </div>
                                            @if ($displayGuestLoginLinks)
                                                @if ($course_data[$mycourse->id]['userguest'])
                                                    <div>
                                                        @if ($course_data[$mycourse->id]['userguest']->password === '')
                                                            <form method='post' action='{{ $urlAppend }}'>
                                                                <input type='hidden' name='uname' value='{{ $course_data[$mycourse->id]['userguest']->username }}'>
                                                                <input type='hidden' name='pass' value=''>
                                                                <input type='hidden' name='next' value='/courses/{{ $mycourse->k }}/'>
                                                                <button style='height:30px;' type='submit' title='{!! trans('langGuestLogin') !!}' name='submit' data-bs-toggle='tooltip' data-bs-placement='top' aria-label="{!! trans('langGuestLogin') !!}">
                                                                    <i class="fa-solid fa-right-to-bracket fa-lg"></i>
                                                                </button>
                                                            </form>
                                                        @else
                                                            <a role='button' href="{{ $urlAppend }}main/login_form.php?user={!! urlencode($course_data[$mycourse->id]['userguest']->username) !!}&amp;next=%2Fcourses%2F{{ $mycourse->k }}%2F" title='{!! trans('langGuestLogin') !!}' data-bs-placement='top' data-bs-toggle='tooltip' aria-label='{!! trans('langGuestLogin') !!}'>
                                                                <i class="fa-solid fa-right-to-bracket fa-lg"></i>
                                                            </a>
                                                        @endif
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </div>


                                <div class="d-flex justify-content-start align-items-center gap-3" style='min-width:65px;'>
                                    @if (!isset($_SESSION['uid']) and $mycourse->visible == COURSE_CLOSED)
                                        <div>
                                            &mdash;
                                        </div>
                                    @else
                                        <div>
                                            @if (!get_config('show_modal_openCourses'))
                                                <a href='{{ $urlAppend }}modules/auth/info_course.php?c={{ $mycourse->k }}' data-bs-toggle='tooltip' data-bs-placement='top' title="{{ trans('langPreview') }}" aria-label="{{ trans('langPreview') }}">
                                                    <i class="fa-solid fa-display"></i>
                                                </a>
                                            @else
                                                <button class="ClickCourse border-0 rounded-pill bg-transparent" id="{{ $mycourse->k }}" type="button" class="btn btn-secondary" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ trans('langPreview') }}" aria-label="{{ trans('langPreview') }}">
                                                    <i class='fa-solid fa-display'></i>
                                                </button>
                                                <div id="myModal{{ $mycourse->k }}" class="modal">
                                                    <div class="modal-content modal-content-opencourses px-lg-5 py-lg-5">
                                                        <div class='col-12 d-flex justify-content-between align-items-start modal-display'>
                                                            <div>
                                                                <div class='d-flex justify-content-start align-items-center gap-2 flex-wrap'>
                                                                    <h2 class='mb-0'>{{ $mycourse->i }}</h2>
                                                                    {!! course_access_icon($mycourse->visible) !!}
                                                                    @if($mycourse->cls > 0)
                                                                        {!! copyright_info($mycourse->id) !!}
                                                                    @endif
                                                                </div>
                                                                <div class='mt-2'>{{ $mycourse->c }}&nbsp; - &nbsp;{{ $mycourse->t }}</div>
                                                            </div>
                                                            <div>
                                                                <button type='button' class="close" aria-label="{{ trans('langClose') }}"></button>
                                                            </div>
                                                        </div>
                                                        <div class='course-content mt-4'>
                                                            <div class='col-12 d-flex justify-content-center align-items-start'>
                                                                @if($mycourse->img == NULL)
                                                                    <img class='openCourseImg' src="{{ $urlAppend }}resources/img/ph1.jpg" alt="{{ trans('langCourseImage') }}" /></a>
                                                                @else
                                                                    <img class='openCourseImg' src="{{ $urlAppend }}courses/{{ $mycourse->k }}/image/{{ $mycourse->img }}" alt="{{ trans('langCourseImage') }}" /></a>
                                                                @endif
                                                            </div>
                                                            <div class='col-12 openCourseDes mt-3 Neutral-900-cl pb-3'>
                                                                @if(empty($mycourse->de))
                                                                    @if($mycourse->clb)
                                                                        <p class='text-center'>{{ trans('langThisCollabDescriptionIsEmpty') }}</p>
                                                                    @else
                                                                        <p class='text-center'>{{ trans('langThisCourseDescriptionIsEmpty') }}</p>
                                                                    @endif
                                                                @else
                                                                    {!! $mycourse->de !!}
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                    <div>
                                        @if ($isInOpenCoursesMode)
                                            {!! CourseXMLElement::getLevel($mycourse->level) !!}&nbsp;
                                                <a href='javascript:showMetadata("{!! $mycourse->k !!}");' data-bs-toggle='tooltip' data-bs-original-title="{{ trans('langCourseMetadata') }}">
                                                    <img alt="{{ trans('langCourseMetadata') }}" src='{{ $urlAppend }}resources/icons/lom.png'/>
                                                </a>
                                        @else
                                            {!! course_access_icon($mycourse->visible) !!}
                                        @endif
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</div>

<script type="text/javascript">
    $(course_list_init);
    var urlAppend = '{{ $urlAppend }}';
    var lang = {
        unCourse: '{{ js_escape(trans('langUnCourse')) }}',
        cancel: '{{ js_escape(trans('langCancel')) }}',
        close: '{{ js_escape(trans('langClose')) }}',
        unregCourse: '{{ js_escape(trans('langDeleteUser')) }}',
        reregisterImpossible: '{{ js_escape(trans('langConfirmUnregCours')) }}',
        invalidCode: '{{ js_escape(trans('langWrongPassCourse')) }} ',
        prereqsNotComplete: '{{ js_escape(trans('langPrerequisitesNotComplete')) }}',
    };
    var courses = {!! json_encode($courses_list) !!};

    var idCourse = '';
    var btn = '';
    var modal = '';
    $(".ClickCourse").click(function() {
        idCourse = this.id;
        modal = document.getElementById("myModal"+idCourse);
        btn = document.getElementById(idCourse);
        modal.style.display = "block";
        $('[data-bs-toggle="tooltip"]').tooltip("hide");
        var $div = $('<div />').appendTo('body');
        $div.attr('class', 'modal-backdrop fade show');
    });

    $(".close").click(function() {
        modal.style.display = "none";
        $(".modal-backdrop").remove();
    });

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
            $(".modal-backdrop").remove();
        }
        $('[data-bs-toggle="tooltip"]').tooltip("hide");
    }

</script>

@endsection
