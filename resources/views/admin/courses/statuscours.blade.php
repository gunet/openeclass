@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    <div class='form-wrapper'>
        <form role='form' class='form-horizontal' action="{{ $_SERVER['SCRIPT_NAME'] }}?c={{$course->code}}" method='post'>                
            <div class='form-group'>
                <label for='localize' class='col-sm-2 control-label'>{{ trans('langAvailableTypes') }}:</label>
                <div class='col-sm-10'>
                    <div class='radio'>
                      <label>
                        <input id='courseopen' type='radio' name='formvisible' value='2'{!! $course->visible == 2 ? ' checked': '' !!}>
                        <img src='{{ $themeimg }}/lock_open.png' alt='{{ trans('langOpenCourse') }}' title='{{ trans('langOpenCourse') }}' width='16'>&nbsp;{{ trans('langOpenCourse') }}
                        <span class='help-block'>
                            <small>{{ trans('langPublic') }}</small>
                        </span>
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                        <input id='coursewithregistration' type='radio' name='formvisible' value='1'{!! $course->visible == 1 ? ' checked': '' !!}>
                        <img src='{{ $themeimg }}/lock_registration.png' alt='{{ trans("m['legrestricted']") }}' title='{{ trans("m['legrestricted']") }}' width='16'>&nbsp;{{ trans("m['legrestricted']") }}
                        <span class='help-block'>
                            <small>{{ trans('langPrivOpen') }}</small>
                        </span>
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                        <input id='courseclose' type='radio' name='formvisible' value='0'{!! $course->visible == 0 ? ' checked': '' !!}>
                        <img src='{{ $themeimg }}/lock_closed.png' alt='{{ trans('langClosedCourse') }}' title='{{ trans('langClosedCourse') }}' width='16'>&nbsp;{{ trans('langClosedCourse') }}
                        <span class='help-block'>
                            <small>{{ trans('langClosedCourseShort') }}</small>
                        </span>
                      </label>
                    </div>
                    <div class='radio'>
                      <label>
                        <input id='courseinactive' type='radio' name='formvisible' value='3'{!! $course->visible == 3 ? ' checked': '' !!}>
                        <img src='{{ $themeimg }}/lock_inactive.png' alt='{{ trans('langInactiveCourse') }}' title='{{ trans('langInactiveCourse') }}' width='16'>&nbsp;{{ trans('langInactiveCourse') }}
                        <span class='help-block'>
                            <small>{{ trans('langCourseInactiveShort') }}</small>
                        </span>
                      </label>
                    </div>                   
                </div>
            </div>
            <div class='form-group'>
                <div class='col-sm-10 col-sm-offset-2'>
                    <input class='btn btn-primary' type='submit' name='submit' value='{{ trans('langModify') }}'>
                </div>
            </div>
        </form>
    </div>
@endsection