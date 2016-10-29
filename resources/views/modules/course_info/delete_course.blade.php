@extends('layouts.default')

@section('content')

{!! $action_bar !!}

<div class='alert alert-danger'>
    {{ trans('langByDel_A') }} <b> {{ q($currentCourseName) }} {{ ($course_code) }} ;</b>
</div>
<div class='form-wrapper'>
    <form class='form-horizontal' role='form' method='post' action=' {{ $form_url }}'>
    {{ showSecondFactorChallenge() }}
    <div class='form-group'>
        <div class='col-sm-10 col-sm-offset-5'>
            <input class='btn btn-primary' type='submit' name='delete' value='{{ trans('langDelete') }}'>
        </div>
    </div>
    <span class='help-block'>
        <small>{{ trans('langByDel') }}</small>
    </span>
    {!! generate_csrf_token_form_field() !!}
   </form>
</div>

@endsection