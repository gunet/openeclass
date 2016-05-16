@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    <div class='alert alert-info'>{{ trans('langMultiMoveCourseInfo') }}</div>
    <div class='form-wrapper'>        
        <form role='form' class='form-horizontal' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post' onsubmit='return validateNodePickerForm();'>
        <fieldset>
        <div class='form-group'>
            <label for='Faculty' class='col-sm-2 control-label'>{{ trans('langFaculty') }}:</label>
            <div class='col-sm-10'>    
            {!! $html !!}
            </div>
        </div>
        @foreach ($sql as $results)
            <input type='hidden' name='lessons[]' value='{{ $results->id }}'>
        @endforeach
        <div class='form-group'>
            <div class='col-sm-10 col-sm-offset-2'>
                <input class='btn btn-primary' type='submit' name='submit' value='{{ trans('langSubmit') }}' onclick='return confirmation("{{ trans('langConfirmMultiMoveCourses') }}");'>
                <a href='index.php' class='btn btn-default'>{{ trans('langCancel') }}</a>
            </div>
        </div>
        </fieldset>
        {!! generate_csrf_token_form_field() !!}
        </form>
    </div>
@endsection