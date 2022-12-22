@extends('layouts.default')

@section('content')

    @if (count($course_description) > 0)
        @foreach ($course_description as $data)
                <div class='panel panel-action-btn-default'>
                    <div class='panel-heading'>
                        <div class='panel-title'>{!! q($data->title) !!}</div>
                    </div>
                    <div class='panel-body'>
                       {!! standard_text_escape($data->comments) !!}
                    </div>
                </div>
        @endforeach
    @else
        <div class='alert alert-warning'>{{ trans('langThisCourseDescriptionIsEmpty') }}</div>
    @endif

@endsection