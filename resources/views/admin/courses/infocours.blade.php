@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    <div class='form-wrapper'>
	<form role='form' class='form-horizontal' action='{{ $_SERVER['SCRIPT_NAME'] }}?c={{ $course->code }}' method='post' onsubmit='return validateNodePickerForm();'>
	<fieldset>
            <div class='form-group'>
                <label for='Faculty' class='col-sm-2 control-label'>{{ trans('langFaculty') }}:</label>
                <div class='col-sm-10'>
                    {!! $node_picker !!}
                </div>
            </div>
            <div class='form-group'>
                <label for='fcode' class='col-sm-2 control-label'>{{ trans('langCode') }}</label>
                <div class='col-sm-10'>
                    <input type='text' class='form-control' name='fcode' id='fcode' value='{{ $course->code }}'>
                </div>
            </div>
            <div class='form-group'>
                <label for='title' class='col-sm-2 control-label'>{{ trans('langCourseTitle') }}:</label>
                <div class='col-sm-10'>
                    <input type='text' class='form-control' name='title' id='title' value='{{ $course->title }}'>
                </div>
            </div>
            <div class='form-group'>
                <label for='titulary' class='col-sm-2 control-label'>{{ trans('langTeachers') }}:</label>
                <div class='col-sm-10'>
                    <input type='text' class='form-control' name='titulary' id='titulary' value='{{ $course->prof_name }}'>
                </div>
            </div>
            {!! showSecondFactorChallenge() !!}
            {!! generate_csrf_token_form_field() !!}    
            <div class='form-group'>
                <div class='col-sm-10 col-sm-offset-4'>
                    <input class='btn btn-primary' type='submit' name='submit' value='{{ trans('langModify') }}'>
                </div>
            </div>
        </fieldset>
	</form>
    </div>                
@endsection