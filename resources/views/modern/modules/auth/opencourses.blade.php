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
                dialog = $('<div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modal-label" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-bs-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">{!! trans("langCancel") !!}</span></button><h4 class="modal-title" id="modal-label">{!! trans('langCourseMetadata') !!}</h4></div><div class="modal-body">body</div></div></div></div>');
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

                    <div class="col-12 @if(isset($_SESSION['uid'])) mt-4 @endif">
                        <h1>{{ trans('langCourses') }}</h1>
                    </div>
                    <div class='col-12 mt-4'>
                        <div class='row row-cols-1 row-cols-lg-2 g-lg-5 g-4'>
                            <div class='col-12'>
                                <div class='col-12'>
                                    <div class="card border-card h-100 Borders border-0 bg-transparent">
                                        <div class="card-body p-0">


                                            @if (isset($buildRoots))
                                                {!! $buildRoots !!}
                                            @endif


                                            <div class='col-12 border-card mt-4 rounded-2'>
                                                <ul class='list-group list-group-flush list-group-default'>
                                                    <li class="list-group-item d-flex justify-content-start align-items-center flex-wrap gap-2 TextBold">
                                                        {!! $tree->getFullPath($fc, false, $_SERVER['SCRIPT_NAME'] . '?fc=') !!}
                                                    </li>
                                                    {!! $childHTML !!}
                                                </ul>
                                                
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>




                    @if (count($courses) > 0)
                        <div class='col-12 mt-5'>
                            <div class='table-responsive'>
                                <table class='table-default' id="myopencourses_table">
                                    <thead>
                                        <tr class='list-header'>
                                            <th>{!! trans('langCourseCode') !!}</th>
                                        @if (isset($isInOpenCoursesMode))
                                            <th>{!! trans('langTeacher') !!}</th>
                                            <th class='text-end'>{!! trans('langOpenCoursesLevel') !!}</th>
                                        @else
                                            <th>{!! trans('langTeacher') !!}</th>
                                            <th class='text-end'>{!! trans('langType') !!}</th>
                                        @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($courses as $mycourse)
                                            <tr>
                                                <td>
                                                    @if ($mycourse->visible == COURSE_OPEN)
                                                        <a class='TextBold' href="../../courses/{!! urlencode($mycourse->k) !!}/">{!! $mycourse->i !!}</a>&nbsp;<small>({!! $mycourse->c !!})</small>
                                                    @else
                                                        <span class='TextBold'>{!! $mycourse->i !!}</span>&nbsp;<small>({!! $mycourse->c !!})</small>
                                                    @endif
                                                    @if ($displayGuestLoginLinks)
                                                        @if ($course_data[$mycourse->id]['userguest'])
                                                            <div class='float-end ps-3'>
                                                            @if ($course_data[$mycourse->id]['userguest']->password === '')
                                                                    <form method='post' action='{{ $urlAppend }}'>
                                                                        <input type='hidden' name='uname' value='{{ $course_data[$mycourse->id]['userguest']->username }}'>
                                                                        <input type='hidden' name='pass' value=''>
                                                                        <input type='hidden' name='next' value='/courses/{{ $mycourse->k }}/'>
                                                                        <button type='submit' title='{!! trans('langGuestLogin') !!}' name='submit' data-bs-toggle='tooltip' data-bs-placement='top'><span class='fa fa-plane'></span></button>
                                                                    </form>
                                                            @else
                                                                    <a role='button' href='{{ $urlAppend }}main/login_form.php?user={!! urlencode($course_data[$mycourse->id]['userguest']->username) !!}&amp;next=%2Fcourses%2F{{ $mycourse->k }}%2F' title='{!! trans('langGuestLogin') !!}' data-bs-placement='top' data-bs-toggle='tooltip'>
                                                                        <span class='fa fa-plane'></span>
                                                                    </a>
                                                            @endif
                                                            </div>
                                                        @endif
                                                    @endif

                                                    @if(!get_config('show_modal_openCourses'))
                                                        <a href='{{ $urlAppend }}modules/auth/info_course.php?c={{ $mycourse->k }}' class='float-end pt-1' data-bs-toggle='tooltip' data-bs-placement='top' title="{{trans('langPreview')}}&nbsp;{{trans('langOfCourse')}}">
                                                            <i class="fa-solid fa-circle-info Primary-500-cl fa-lg"></i>
                                                        </a>
                                                    @endif

                                                    @if(get_config('show_modal_openCourses'))
                                                        <button class="ClickCourse border-0 rounded-pill bg-transparent float-end" id="{{$mycourse->k}}" type="button" class="btn btn-secondary" data-bs-toggle="tooltip" data-bs-placement="top" title="{{trans('langPreview')}}&nbsp;{{trans('langOfCourse')}}">
                                                            <i class='fa-solid fa-display Primary-500-cl'></i>
                                                        </button>

                                                        <!-- The Modal -->
                                                        <div id="myModal{{$mycourse->k}}" class="modal">

                                                            <!-- Modal content -->
                                                            <div class="modal-content modal-content-opencourses px-lg-5 py-lg-5">
                                                                <div class='col-12 d-flex justify-content-between align-items-start'>
                                                                    <div>
                                                                        <h2 class='d-flex justify-content-start align-items-start gap-3 TextBold mb-0'>
                                                                            <span class='settings-icons mt-1 Neutral-600-cl'>{!! course_access_icon($mycourse->visible) !!}</span>
                                                                            {{$mycourse->i}}
                                                                        </h2>
                                                                        <p class='course-professor-code'>{{$mycourse->c}}&nbsp; - &nbsp;{{$mycourse->t}}</p>
                                                                    </div>
                                                                    <div>
                                                                        <button type='button' class="close border-0 bg-default mt-2"><i class='fa-solid fa-xmark fa-lg Neutral-700-cl'></i></button>
                                                                    </div>
                                                                </div>

                                                                <div class='course-content mt-4'>
                                                                    <div class='col-12 d-flex justify-content-center align-items-start'>
                                                                        @if($mycourse->img == NULL)
                                                                            <img class='openCourseImg' src="{{ $urlAppend }}template/modern/img/ph1.jpg" alt="{{ $mycourse->img }}" /></a>
                                                                        @else
                                                                            <img class='openCourseImg' src="{{ $urlAppend }}courses/{{$mycourse->k}}/image/{{$mycourse->img}}" alt="{{ $mycourse->img }}" /></a>
                                                                        @endif
                                                                    </div>

                                                                    <div class='col-12 openCourseDes mt-3 blackBlueText pb-3'>
                                                                        @if(empty($mycourse->de))
                                                                            <p class='text-center'>{{ trans('langThisCourseDescriptionIsEmpty') }}</p>
                                                                        @else
                                                                            {!! $mycourse->de !!}
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    @endif

                                                </td>
                                                <td>
                                                    {!! $mycourse->t !!}
                                                </td>
                                                <td class='text-end'>
                                                @if ($isInOpenCoursesMode)
                                                    {!! CourseXMLElement::getLevel($mycourse->level) !!}&nbsp;
                                                    <a href='javascript:showMetadata("{!! $mycourse->k !!}");'><img src='{{ $themeimg }}/lom.png'/></a>
                                                @else
                                                    {!! course_access_icon($mycourse->visible) !!}
                                                @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

        </div>

    </div>
</div>

<script type="text/javascript">
    var idCourse = '';
    var btn = '';
    var modal = '';
    $(".ClickCourse").click(function() {
        // Get the btn id
        idCourse = this.id;

        // Get the modal
        modal = document.getElementById("myModal"+idCourse);

        // Get the button that opens the modal
        btn = document.getElementById(idCourse);

        // When the user clicks the button, open the modal
        modal.style.display = "block";

        $('[data-bs-toggle="tooltip"]').tooltip("hide");

        var $div = $('<div />').appendTo('body');
        $div.attr('class', 'modal-backdrop fade show');
    });

    $(".close").click(function() {
        modal.style.display = "none";
        $(".modal-backdrop").remove();
    });

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
            $(".modal-backdrop").remove();
        }
        $('[data-bs-toggle="tooltip"]').tooltip("hide");
    }

</script>

@endsection
