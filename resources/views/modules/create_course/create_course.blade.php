@extends('layouts.default')

@section('content')

{!! $action_bar !!}

@if (deps_valid)
    <div class='text-center alert alert-danger'>
        {{ trans('langCreateCourseNotAllowedNode') }}
    </div>
    <p class='pull-right'>
        <a class='btn btn-default' href='{{ $_SERVER['SCRIPT_NAME'] }}'>{{ trans('langBack') }}</a>
    </p>
@else
    <div class='alert alert-success'>
        <b>{{ trans('langJustCreated') }} :</b> {{ $title }}<br>
        <span class='smaller'>{{ trans('langEnterMetadata') }}</span>
    </div>
@endif

@endsection
