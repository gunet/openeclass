@extends('layouts.default')

@push('head_styles')
<link href="{{ $urlAppend }}js/jstree3/themes/proton/style.min.css" type='text/css' rel='stylesheet'>
@endpush

@push('head_scripts')
<script type='text/javascript' src='{{ $urlAppend }}js/jstree3/jstree.min.js'></script>
<script type='text/javascript' src='{{ $urlAppend }}js/tools.js'></script>
@endpush

@section('content')

@if (isset($_POST['create_restored_course']))
    {!! $new_action_bar !!}
    @if (!empty($restore_users))
        <div class='alert alert-info'>
            {!! $restore_users !!}
        </div>
    @endif
    <div class='alert alert-info'>
        {{ trans('langCopyFiles') }} {{ $coursedir}}
    </div>
@else
    {!! $action_bar !!}
    <div class='alert alert-info'>{{ trans('langInfo1') }} <br> {{ trans('langInfo2') }}</div>
    <div class='row'>
    <div class='col-md-12'>
    <div class='form-wrapper' >
        <form class='form-horizontal' role='form' action='{{ $formAction }}' method='post' onsubmit='return validateNodePickerForm();' >

        <div class='form-group'>
            <label for='course_code' class='col-sm-3 control-label'>{{ trans('langCourseCode') }}:</label>
            <div class='col-sm-9'>
                <input type='text' class='form-control' id='course_code' name='course_code' value='{{ $code }}'>
            </div>
        </div>
        <div class='form-group'>
            <label for='course_code' class='col-sm-3 control-label'>{{ trans('langLanguage') }}:</label>
            <div class='col-sm-9'>
                {!! $lang_selection !!}
            </div>
        </div>
        <div class='form-group'>
            <label for='course_title' class='col-sm-3 control-label'>{{ trans('langTitle') }}:</label>
            <div class='col-sm-9'>
                <input class='form-control' type='text' id='course_title' name='course_title' value='{{ $title }}'>
            </div>
        </div>

        <div class='form-group'>
            <label class='col-sm-3 control-label'>{{ trans('langCourseDescription') }}:</label>
            <div class='col-sm-9'>
                {!! $rich_text_editor !!}
            </div>
        </div>
        <div class='form-group'>
            <label class='col-sm-3 control-label'>{{ trans('langFaculty') }}:</label>
            <div class='col-sm-9'>
                {!! $course_node_picker !!} <br>{{ trans('langOldValue') }}: <i>{{ $old_faculty }}</i>
            </div>
        </div>
        <div class='form-group'>
            <label class='col-sm-3 control-label'>{{ trans('langCourseVis') }}:</label>
            <div class='col-sm-9'>
                {!! $visibility_select !!}
            </div>
        </div>
        <div class='form-group'>
            <label for='course_prof' class='col-sm-3 control-label'>{{ trans('langTeacher') }}:</label>
            <div class='col-sm-9'>
                <input class='form-control' type='text' id='course_prof' name='course_prof' value='{{ $prof }}' size='50' />
            </div>
        </div>
        <div class='form-group'>
            <label class='col-sm-3 control-label'>{{ trans('langUsersWillAdd') }}:</label>
            <div class='col-sm-9'>
                <input type='radio' name='add_users' value='all' id='add_users_all' checked='checked'>
               {{ trans('langAll') }}<br>
               <input type='radio' name='add_users' value='prof' id='add_users_prof'>
               {{ trans('langsTeachers') }}<br>
               <input type='radio' name='add_users' value='none' id='add_users_none'>
               {{ trans('langNone') }}
            </div>
        </div>
        <div class='form-group'>
            <label class='col-sm-3 control-label'>{{ trans('langMultiRegType') }}:</label>
            <div class='col-sm-9'>
                <input type='checkbox' name='create_users' value='1' id='create_users' checked='checked'>
            </div>
        </div>
        <div class='form-group'>
            <div class='col-sm-offset-3 col-sm-9'>
                <input class='btn btn-primary' type='submit' name='create_restored_course' value='{{ trans('langOk') }}'>
                <input type='hidden' name='restoreThis' value='" . q($_POST['restoreThis']) . "' />
            </div>
        </div>
        {!! generate_csrf_token_form_field() !!}
        </form>
    </div>
    </div>
    </div>
@endif
@endsection



