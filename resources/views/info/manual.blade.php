@extends('layouts.default')

@section('content')

    {!! $action_bar !!}
    <div class='list-group'>
        <a href='{{ $general_tutorials['detail_descr']['url'] }}' target='_blank' class='mainpage list-group-item'>{!! $general_tutorials['detail_descr']['desc'] !!}</a>
        <a href='{{ $general_tutorials['short_descr']['url'] }}' target='_blank' class='mainpage list-group-item'>{!! $general_tutorials['short_descr']['desc'] !!}</a>
        <a href='{{ $general_tutorials['mant']['url'] }}' target='_blank' class='mainpage list-group-item'>{!! $general_tutorials['mant']['desc'] !!}</a>
        <a href='{{ $general_tutorials['mans']['url'] }}' target='_blank' class='mainpage list-group-item'>{!! $general_tutorials['mans']['desc'] !!}</a>
    </div>
    <br><p class='tool_title'>{{ $teacher_tutorials['title'] }}</p>
    <div class='list-group'>
        <a href='{{ $teacher_tutorials['create_account']['url'] }}' target='_blank' class='mainpage list-group-item'>{!! $teacher_tutorials['create_account']['desc'] !!}</a>
        <a href='{{ $teacher_tutorials['create_course']['url'] }}' target='_blank' class='mainpage list-group-item'>{!! $teacher_tutorials['create_course']['desc'] !!}</a>
        <a href='{{ $teacher_tutorials['portfolio_management']['url'] }}' target='_blank' class='mainpage list-group-item'>{!! $teacher_tutorials['portfolio_management']['desc'] !!}</a>
        <a href='{{ $teacher_tutorials['course_management']['url'] }}' target='_blank' class='mainpage list-group-item'>{!! $teacher_tutorials['course_management']['desc'] !!}</a>
        <a href='{{ $teacher_tutorials['forum_management']['url'] }}' target='_blank' class='mainpage list-group-item'>{!! $teacher_tutorials['forum_management']['desc'] !!}</a>
        <a href='{{ $teacher_tutorials['group_management']['url'] }}' target='_blank' class='mainpage list-group-item'>{!! $teacher_tutorials['group_management']['desc'] !!}</a>
    </div>
    <br><p class='tool_title'>{{ $student_tutorials['title'] }}</p>
    <div class='list-group'>
        <a href='{{ $student_tutorials['register_course']['url'] }}' target='_blank' class='mainpage list-group-item'>{!! $student_tutorials['register_course']['desc'] !!}</a>
        <a href='{{ $student_tutorials['personal_portfolio']['url'] }}' target='_blank' class='mainpage list-group-item'>{!! $student_tutorials['personal_portfolio']['desc'] !!}</a>
        <a href='{{ $student_tutorials['ecourse']['url'] }}' target='_blank' class='mainpage list-group-item'>{!! $student_tutorials['ecourse']['desc'] !!}</a>
        <a href='{{ $student_tutorials['forum']['url'] }}' target='_blank' class='mainpage list-group-item'>{!! $student_tutorials['forum']['desc'] !!}</a>
    </div>

@endsection
