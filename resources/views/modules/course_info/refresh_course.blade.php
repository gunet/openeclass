@extends('layouts.default')

@push('head_styles')
<link href="{{ $urlAppend }}js/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" type='text/css' rel='stylesheet'>
@endpush

@push('head_scripts')
<script type='text/javascript' src='{{ $urlAppend }}js/tools.js'></script>
<script type='text/javascript' src='{{ $urlAppend }}js/bootstrap-datepicker/js/bootstrap-datepicker.min.js'></script>
<script type='text/javascript' src='{{ $urlAppend }}js/bootstrap-datepicker/locales/bootstrap-datepicker.{{ $language }}.min.js'></script>

<script type='text/javascript'>    
$(function() {
    $('#reg_date').datepicker({
            format: 'dd-mm-yyyy',
            language: '{{ $language }}',
            autoclose: true
        });
});
</script>

@endpush

@section('content')

{!! $action_bar !!}

    <div class='form-wrapper'>
        @if (!isset($_GET['from_user']))
            <div class='alert alert-info'>
                {{ trans('langRefreshInfo') }} {{ trans('langRefreshInfo_A') }}
            </div>
            <form class='form-horizontal' role='form' action='{{ $form_url }}' method='post'>
        @else
            <form class='form-horizontal' role='form' action='{{ $form_url_from_user }}' method='post'>
        @endif
    <fieldset>
        <div class='form-group'>
            <label for='delusers' class='col-sm-2 control-label'>{{ trans('langUsers') }}</label>
            <div class='col-sm-4 checkbox'>
                <label><input type='checkbox' name='delusers'>{{ trans('langUserDelCourse') }}:</label>
            </div>
            <div class='col-sm-3'>
                {!! $selection_date !!}
            </div>
            <div class='col-sm-3'>
                <input type='text' name='reg_date' id='reg_date' value='{{ $date_format }}'>
            </div>                
        </div>
    @if (!isset($_GET['from_user']))
        <div class='form-group'>
            <label for='delannounces' class='col-sm-2 control-label'>{{ trans('langAnnouncements') }}</label>
            <div class='col-sm-10 checkbox'>
                <label><input type='checkbox' name='delannounces'>{{ trans('langAnnouncesDel') }}</label>
            </div>
        </div>
        <div class='form-group'>
          <label for='delagenda' class='col-sm-2 control-label'>{{ trans('langAgenda') }}</label>
          <div class='col-sm-10 checkbox'>
              <label><input type='checkbox' name='delagenda'>{{ trans('langAgendaDel') }}</label>
          </div>
        </div>
        <div class='form-group'>
          <label for='hideworks' class='col-sm-2 control-label'>{{ trans('langWorks') }}</label>
            <div class='col-sm-10 checkbox'>
                <label><input type='checkbox' name='hideworks'>{{ trans('langHideWork') }}</label>
              </div>
            <div class='col-sm-offset-2 col-sm-10 checkbox'>
                <label><input type='checkbox' name='delworkssubs'>{{ trans('langDelAllWorkSubs') }}</label>
            </div>
        </div>
        <div class='form-group'>
          <label for='purgeexercises' class='col-sm-2 control-label'>{{ trans('langExercises') }}</label>
          <div class='col-sm-10 checkbox'>
              <label><input type='checkbox' name='purgeexercises'>{{ trans('langPurgeExercisesResults') }}</label>
          </div>
        </div>
        <div class='form-group'>
          <label for='clearstats' class='col-sm-2 control-label'>{{ trans('langUsage') }}</label>
          <div class='col-sm-10 checkbox'>
              <label><input type='checkbox' name='clearstats'>{{ trans('langClearStats') }}</label>
          </div>
        </div>
        <div class='form-group'>
          <label for='delblogposts' class='col-sm-2 control-label'>{{ trans('langBlog') }}</label>
          <div class='col-sm-10 checkbox'>
              <label><input type='checkbox' name='delblogposts'> {{ trans('langDelBlogPosts') }}</label>
          </div>
        </div>
        <div class='form-group'>
          <label for='delwallposts' class='col-sm-2 control-label'>{{ trans('langWall') }}</label>
          <div class='col-sm-10 checkbox'>
              <label><input type='checkbox' name='delwallposts'>{{ trans('langDelWallPosts') }}</label>
          </div>
        </div>
    @endif
        {{ showSecondFactorChallenge() }}
    <div class='col-sm-offset-2 col-sm-10'>
        <input class='btn btn-primary' type='submit' value='{{ trans('langSubmitActions') }}' name='submit'>
    </div>
    </fieldset>
    {!! generate_csrf_token_form_field() !!}
    </form>
    </div>    

@endsection