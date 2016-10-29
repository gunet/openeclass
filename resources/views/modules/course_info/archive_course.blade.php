@extends('layouts.default')

@section('content')

<div class='alert alert-info'>
    <ol>
        <li>{{ trans('langBUCourseDataOfMainBase') }} {{ $course_code }}</li>
        <li>{{ trans('langBackupOfDataBase') }} {{ $course_code }}</li>
    </ol>
</div>
<div class='alert alert-success'>
    {{ trans('langBackupSuccesfull') }}
</div>

{!! $action_bar !!}
  
@endsection