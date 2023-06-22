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

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div class="col-12 justify-content-center col_maincontent_active_Homepage">

                <div class="row p-xl-5 px-lg-0 py-lg-3 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    {!! $action_bar !!}

                    @if(Session::has('message'))
                    <div class='col-12 all-alerts'>
                        <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach
                            @else
                                {!! Session::get('message') !!}
                            @endif
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif

                    <div class='col-12'>
                        <div class="card h-100 Borders">
                            <div class="card-body">
                                @if (isset($buildRoots))
                                    {!! $buildRoots !!}
                                @endif
                                <div class='form-wrapper form-edit rounded border-0'>
                                    <p class='control-label-notes mb-2'>{!! trans('langFaculty') !!}:</p>
                                    <ul>
                                        <li class='px-0 border-0'> 
                                            <strong class='normalBlueText'>{!! $tree->getFullPath($fc, false, $_SERVER['SCRIPT_NAME'] . '?fc=') !!}</strong>
                                        </li>
                                    </ul>
                                    <div class='col-12'>
                                        {!! $childHTML !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    @if (count($courses) > 0)
                        <div class='col-12 mt-2'>
                            <div class='table-responsive'>
                                <table class='table-default' id="myopencourses_table">
                                    <thead>
                                        <tr class='list-header'>
                                            <th class='text-start'>{!! trans('langCourseCode') !!}</th>
                                        @if (isset($isInOpenCoursesMode))
                                            <th class='text-start' width='220'>{!! trans('langTeacher') !!}</th>
                                            <th class='text-start'width='30'>{!! trans('langOpenCoursesLevel') !!}</th>
                                        @else
                                            <th class='text-start' width='220'>{!! trans('langTeacher') !!}</th>
                                            <th width='30'>{!! trans('langType') !!}</th>
                                        @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($courses as $mycourse)
                                            <tr>
                                                <td>
                                                    @if ($mycourse->visible == COURSE_OPEN)
                                                        <a href="../../courses/{!! urlencode($mycourse->k) !!}/">{!! $mycourse->i !!}</a>&nbsp;<small>({!! $mycourse->c !!})</small>
                                                    @else
                                                        {!! $mycourse->i !!}&nbsp;<small>({!! $mycourse->c !!})</small>
                                                    @endif
                                                    @if ($displayGuestLoginLinks)
                                                        @if ($course_data[$mycourse->id]['userguest'])
                                                            <div class='float-end'>
                                                            @if ($course_data[$mycourse->id]['userguest']->password === '')
                                                                    <form method='post' action='{{ $urlAppend }}'>
                                                                        <input type='hidden' name='uname' value='{{ $course_data[$mycourse->id]['userguest']->username }}'>
                                                                        <input type='hidden' name='pass' value=''>
                                                                        <input type='hidden' name='next' value='/courses/{{ $mycourse->k }}/'>
                                                                        <button class='btn submitAdminBtn' type='submit' title='{!! trans('langGuestLogin') !!}' name='submit' data-toggle='tooltip'><span class='fa fa-plane'></span></button>
                                                                    </form>
                                                            @else
                                                                    <a class='btn submitAdminBtn' role='button' href='{{ $urlAppend }}main/login_form.php?user={!! urlencode($course_data[$mycourse->id]['userguest']->username) !!}&amp;next=%2Fcourses%2F{{ $mycourse->k }}%2F' title='{!! trans('langGuestLogin') !!}' data-toggle='tooltip'>
                                                                    <span class='fa fa-plane'></span></a>
                                                            @endif
                                                            </div>
                                                        @endif
                                                    @endif


                                                    <button class="ClickCourse border-0 rounded-pill bg-transparent" id="{{$mycourse->k}}" type="button" class="btn btn-secondary" data-bs-toggle="tooltip" data-bs-placement="top" title="{{trans('langPreview')}}&nbsp;{{trans('langOfCourse')}}">
                                                       <img class='ClickCourseModalImg' src='{{ $urlAppend }}template/modern/img/info_a.svg'>
                                                    </button>

                                                    <!-- The Modal -->
                                                    <div id="myModal{{$mycourse->k}}" class="modal">

                                                        <!-- Modal content -->
                                                        <div class="modal-content modal-content-opencourses overflow-auto px-lg-4 py-lg-3">
                                                            <div class='row'>
                                                                <div class='col-10'>
                                                                    <span class="courseInfoText TextExtraBold blackBlueText">{{$mycourse->i}}</span>
                                                                    <span class='courseInfoText TextMedium blackBlueText ms-1'>({{$mycourse->c}})</span>
                                                                </div>
                                                                <div class='col-2'>
                                                                    <button type='button' class="close btn-sm text-uppercase d-flex justify-content-center align-items-center float-end" style='font-size:30px;'>&times;</button>
                                                                </div>
                                                            </div>
                                                            
                                                            <hr class='hr-OpenCourses'>

                                                            <div class='row mb-3'>
                                                                <div class='col-9 d-flex justify-content-start align-items-start ps-4'>
                                                                    <p class='small-text TextRegular blackBlueText d-inline-flex align-items-center'>
                                                                        <span class='fa fa-user lightBlueText pe-2 pt-0'></span>
                                                                        <span class='blackBlueText'>{{$mycourse->t}}</span>
                                                                    </p>
                                                                </div>
                                                                <div class='col-3 d-flex justify-content-end align-items-center pe-4 blackBlueText'>
                                                                    {!! course_access_icon($mycourse->visible) !!}
                                                                    @if($mycourse->p == 1)
                                                                        <span class="fa fa-star textgreyColor ps-3" data-bs-toggle="tooltip" data-bs-placement="top" title="" data-bs-original-title="{{trans('langPopular')}} {{trans('langCourse')}}" aria-label="{{trans('langPopular')}} {{trans('langCourse')}}"></span>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        
                                                            
                                                            <div class='col-12 d-flex justify-content-center align-items-start ps-md-5 pe-md-5'>
                                                                @if($mycourse->img == NULL)
                                                                    <img class='openCourseImg' src="{{ $urlAppend }}template/modern/img/ph1.jpg" alt="{{ $mycourse->img }}" /></a>
                                                                @else
                                                                    <img class='openCourseImg' src="{{ $urlAppend }}courses/{{$mycourse->k}}/image/{{$mycourse->img}}" alt="{{ $mycourse->img }}" /></a>
                                                                @endif
                                                            </div>

                                                            <div class='col-12 openCourseDes mt-3 ps-md-5 pe-md-5 blackBlueText pb-3'>
                                                                @if(empty($mycourse->de))
                                                                    <p class='text-center'>{{ trans('langThisCourseDescriptionIsEmpty') }}</p>
                                                                @else
                                                                    {!! $mycourse->de !!}
                                                                @endif
                                                            </div>
                                                        </div>

                                                    </div>
                                                
                                                </td>
                                                <td>
                                                    {!! $mycourse->t !!}
                                                </td>
                                                <td class='text-center'>
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
    });

    $(".close").click(function() {
        modal.style.display = "none";
    });

    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
        $('[data-bs-toggle="tooltip"]').tooltip("hide");
    }

</script>

@endsection


