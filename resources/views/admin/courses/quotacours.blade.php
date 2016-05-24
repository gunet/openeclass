@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    <div class='form-wrapper'>
        <form role='form' class='form-horizontal' action='{{ $_SERVER['SCRIPT_NAME'] }}?c={{ $course->code }}' method='post'>
        <fieldset>                    
            <div class='alert alert-info'>
                {{ trans('langTheCourse') }} <b>{{ $course->title }}</b> {{ trans('langMaxQuota') }}
            </div>
            <div class='form-group'>
                <label class='col-sm-4 control-label'>{{ trans('langLegend') }} {{ trans('langDoc') }}:</label>
                    <div class='col-sm-6'><input type='text' name='dq' value='{{ $dq }}' size='4' maxlength='4'> MB</div>
            </div>
            <div class='form-group'>
                <label class='col-sm-4 control-label'>{{ trans('langLegend') }} {{ trans('langVideo') }}:</label>
                    <div class='col-sm-6'><input type='text' name='vq' value='{{ $vq }}' size='4' maxlength='4'> MB</div>
            </div>
            <div class='form-group'>
                <label class='col-sm-4 control-label'>{{ trans('langLegend') }} <b>{{ trans('langGroups') }}</b>:</label>
                <div class='col-sm-6'>
                    <input type='text' name='gq' value='{{ $gq }}' size='4' maxlength='4'> MB
                </div>
            </div>
            <div class='form-group'>
                <label class='col-sm-4 control-label'>{{ trans('langLegend') }} <b>{{ trans('langDropBox') }}</b>:</label>
                <div class='col-sm-6'>
                    <input type='text' name='drq' value='{{ $drq }}' size='4' maxlength='4'> MB
                </div>
            </div>
            <div class='form-group'>
                <div class='col-sm-10 col-sm-offset-4'>
                    <input class='btn btn-primary' type='submit' name='submit' value='{{ trans('langModify') }}'>
                </div>
            </div>
        </fieldset>
        {!! generate_csrf_token_form_field() !!}
        </form>
    </div>
@endsection