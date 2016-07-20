@extends('layouts.default')

@section('content')

    {!! $action_bar !!}
    <div class='list-group'>
        <li class='list-group-item list-header'>{{ $general_tutorials['title'] }}</li>
        @foreach ($general_tutorials['links'] as $gt)
            <a href='{{ $gt['url'] }}' target='_blank' class='mainpage list-group-item'>{!! $gt['desc'] !!}</a>
        @endforeach
    </div>
    <div class='list-group'>
        <li class='list-group-item list-header'>{{ $teacher_tutorials['title'] }}</li>
        @foreach ($teacher_tutorials['links'] as $tt)
            <a href='{{ $tt['url'] }}' target='_blank' class='mainpage list-group-item'>{!! $tt['desc'] !!}</a>
        @endforeach
    </div>
    <div class='list-group'>
        <li class='list-group-item list-header'>{{ $student_tutorials['title'] }}</li>
        @foreach ($student_tutorials['links'] as $st)
            <a href='{{ $st['url'] }}' target='_blank' class='mainpage list-group-item'>{!! $st['desc'] !!}</a>
        @endforeach
    </div>

@endsection
