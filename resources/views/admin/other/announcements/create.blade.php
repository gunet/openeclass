@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    <div class='form-wrapper'>
    <form role='form' class='form-horizontal' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}'>
        @if (isset($announcement))
            <input type='hidden' name='id' value='{{ $announcement->id }}'>
        @endif    
        <div class='form-group{{ Session::hasError('title') ? " has-error" : "" }}'>
            <label for='title' class='col-sm-2 control-label'>{{ trans('langTitle') }}:</label>
            <div class='col-sm-10'>
                <input class='form-control' type='text' name='title' value='{{ isset($announcement) ? $announcement->title : "" }}'>
                {!! Session::getError('title', "<span class='help-block'>:message</span>") !!}
            </div>
        </div>
        <div class='form-group'>
            <label for='newContent' class='col-sm-2 control-label'>{{ trans('langAnnouncement') }}:</label>
            <div class='col-sm-10'>{!! $newContentTextarea !!}</div>
        </div>
        <div class='form-group'>
            <label class='col-sm-2 control-label'>{{ trans('langLanguage') }}:</label>    
            <div class='col-sm-10'>
                {!! lang_select_options('lang_admin_ann', "class='form-control'", isset($announcement) ? $announcement->lang : false) !!}
            </div>
            <small class='text-right'>
                <span class='help-block'>{{ trans('langTipLangAdminAnn') }}</span>
            </small>
        </div>
        <div class='form-group'>
            <label for='startdate' class='col-sm-2 control-label'>{{ trans('langStartDate') }} :</label>
            <div class='col-sm-10'>
                <div class='input-group'>
                    <span class='input-group-addon'>
                        <input type='checkbox' name='startdate_active'{{ $start_checkbox }}>
                    </span>
                    <input class='form-control' name='startdate' id='startdate' type='text' value='{{ $startdate }}' disabled>
                </div>
            </div>
        </div>
        <div class='form-group'>
            <label for='enddate' class='col-sm-2 control-label'>{{ trans('langEndDate') }} :</label>
            <div class='col-sm-10'>
                <div class='input-group'>
                    <span class='input-group-addon'>
                        <input type='checkbox' name='enddate_active'{{ $end_checkbox }} disabled>
                    </span>
                    <input class='form-control' name='enddate' id='enddate' type='text' value='{{ $enddate }}' disabled>
                </div>
            </div>
        </div>
        <div class='form-group'>
            <div class='col-sm-10 col-sm-offset-2'>
                <div class='checkbox'>
                    <label>
                        <input type='checkbox' name='show_public'{{ $checked_public }}> {{ trans('showall') }}
                    </label>
                </div>
            </div>
        </div>
        <div class='form-group'>
            <div class='col-sm-offset-2 col-sm-10'>
                <input id='submitAnnouncement' class='btn btn-primary' type='submit' name='submitAnnouncement' value='{{ trans('langSubmit') }}'>
            </div>
        </div>
        {!! generate_csrf_token_form_field() !!}
        </form>
    </div>
@endsection