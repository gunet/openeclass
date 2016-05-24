@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    @if ($u_account && $c)
        <div class='alert alert-warning'>
            {{ trans('langConfirmDeleteQuestion1') }} 
            <em>{{ $u_realname }} ({{ $u_account }})</em>
            {{ trans('langConfirmDeleteQuestion2') }} 
            <em>{{ course_id_to_title($c) }}</em>
        </div>
        <div class='col-sm-offset-5'>
            <a class='btn btn-primary' href='{{ $_SERVER['SCRIPT_NAME'] }}?u={{ $u }}&amp;c={{ $c }}&amp;doit=yes'>{{ trans('langDelete') }}</a>
        </div>
    @else
        <div class='alert alert-danger'>{{ trans('langErrorUnreguser') }}</div>
    @endif              
@endsection