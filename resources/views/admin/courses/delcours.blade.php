@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    
    <div class='alert alert-danger'>
        {{ trans('langCourseDelConfirm2') }} 
        <em>{{ course_id_to_title($course_id) }}</em>
        <br><br>
        <i>{{ trans('langNoticeDel') }}</i>
        <br>
    </div>    
    <ul class='list-group'>
        <li class='list-group-item'>
            <a href='{{ $_SERVER['SCRIPT_NAME'] }}?c={{ getIndirectReference($course_id) }}&amp;delete=yes&amp;{{ generate_csrf_token_link_parameter() }}' {!! $asktotp !!}>
               <b>{{ trans('langYes') }}</b>
            </a>
        </li>
        <li class='list-group-item'>
            <a href='listcours.php'>
                <b>{{ trans('langNo') }}</b>
            </a>
        </li>
    </ul>
@endsection