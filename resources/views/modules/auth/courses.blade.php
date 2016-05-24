@extends('layouts.default')

@section('content')

    {!! $action_bar !!}
    @if (isset($roots))
        {{ $roots }}
    @endif
    <form action='{{ $form_action }}' method='post'>
        <ul class='list-group'>
            <li class='list-group-item list-header'>
                <a name='top'></a>{{ trans('langFaculty') }}: {!! $fc_fullpath !!}
                {!! $fc_courses !!}
            </li>
        </ul>
    </form>
    @if (isset($expanded_fc))
        {!! $expanded_fc !!}</br>
        <div align='right'>
            <input class='btn btn-primary' type='submit' name='submit' value='$langRegistration'>&nbsp;&nbsp;
        </div>
    @elseif ($childCount)
        <div class='alert alert-warning text-center'>- {{ trans('langNoCourses') }}-</div>
    @endif

    <script type='text/javascript'>$(course_list_init);
        var themeimg = '" . js_escape($themeimg) . "';
        var urlAppend = '".js_escape($urlAppend)."';
        var lang = {
            unCourse: '" . js_escape($langUnCourse) . "',
            cancel: '" . js_escape($langCancel) . "',
            close: '" . js_escape($langClose) . "',
            unregCourse: '" . js_escape($langUnregCourse) . "',
            reregisterImpossible: '" . js_escape("$langConfirmUnregCours $m[unsub]") . "',
            invalidCode: '" . js_escape($langInvalidCode) . "',
        };
        var courses = ".(json_encode($courses_list)).";
    </script>

@endsection
