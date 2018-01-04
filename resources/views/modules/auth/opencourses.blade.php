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
                dialog = $('<div class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modal-label" aria-hidden="true"><div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">{!! trans("langCancel") !!}</span></button><h4 class="modal-title" id="modal-label">{!! trans('langCourseMetadata') !!}</h4></div><div class="modal-body">body</div></div></div></div>');
            });
        </script>
    @endpush
@endif

@section('content')

    {!! $action_bar !!}

    @if (isset($buildRoots))
        {{ $buildRoots }}
    @endif

    <div class='row'>
        <div class='col-xs-12'>
            <ul class='list-group'>
                <li class='list-group-item list-header'>{!! trans('langFaculty') !!}: <strong>{!! $tree->getFullPath($fc, false, $_SERVER['SCRIPT_NAME'] . '?fc=') !!}</strong>
                {!! $childHTML !!}
            </ul>
        </div>
    </div>
@if (count($courses) > 0)
    <div class='row'>
        <div class='col-xs-12'>
            <div class='table-responsive'>
                <table class='table-default'>
                    <tr class='list-header'>
                        <th class='text-left'>{!! trans('langCourseCode') !!}</th>
                    @if (isset($isInOpenCoursesMode))
                        <th class='text-left' width='220'>{!! trans('langTeacher') !!}</th>
                        <th width='30'>{!! trans('langOpenCoursesLevel') !!}</th>
                    @else
                        <th class='text-left' width='220'>{!! trans('langTeacher') !!}</th>
                        <th width='30'>{!! trans('langType') !!}</th>
                    @endif
                    </tr>

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
                                <div class='pull-right'>
                                @if ($course_data[$mycourse->id]['userguest']->password === '')
                                        <form method='post' action='{{ $urlAppend }}'>
                                            <input type='hidden' name='uname' value='{{ $course_data[$mycourse->id]['userguest']->username }}'>
                                            <input type='hidden' name='pass' value=''>
                                            <input type='hidden' name='next' value='/courses/{{ $mycourse->k }}/'>
                                            <button class='btn btn-default' type='submit' title='{!! trans('langGuestLogin') !!}' name='submit' data-toggle='tooltip'><span class='fa fa-plane'></span></button>
                                        </form>
                                @else
                                        <a class='btn btn-default' role='button' href='{{ $urlAppend }}main/login_form.php?user={!! urlencode($course_data[$mycourse->id]['userguest']->username) !!}&amp;next=%2Fcourses%2F{{ $mycourse->k }}%2F' title='{!! trans('langGuestLogin') !!}' data-toggle='tooltip'>
                                        <span class='fa fa-plane'></span></a>
                                @endif
                                </div>
                            @endif
                        @endif
                        </td>
                        <td>
                            {!! $mycourse->t !!}
                        </td>
                        <td class='text-center'>
                        @if ($isInOpenCoursesMode)
                            {!! CourseXMLElement::getLevel($mycourse->level) !!}&nbsp;
                            <a href='javascript:showMetadata("{!! $mycourse->k !!}");'><img src='{{ $themeimg }}/lom.png'/></a>
                        @else
                            @foreach( $icons as $visible=>$image)
                                @if($visible == $mycourse->visible)
                                    {!! $image !!}
                                @endif
                            @endforeach
                        @endif
                        </td>
                    </tr>
                @endforeach
                </table>
            </div>
        </div>
    </div>
@else
    <div class='alert alert-warning text-center'>- {!! trans('langNoCourses') !!} -</div>
@endif

@endsection
    
    
