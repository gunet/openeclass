@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
<div class='{{ $container }}'>
        <div class="row rowMargin">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    {!! $action_bar !!}

                    <div class='col-12 mb-4'>
                        <div class='list-group'>
                            <li class='list-group-item bgTheme text-white TextSemiBold'>{{ $general_tutorials['title'] }}</li>
                            @foreach ($general_tutorials['links'] as $gt)
                                <a href='{{ $gt['url'] }}' target='_blank' class='mainpage list-group-item'>{!! $gt['desc'] !!}</a>
                            @endforeach
                        </div>
                    </div>

                    <div class='col-12 mb-4'>
                        <div class='list-group'>
                            <li class='list-group-item bgTheme text-white TextSemiBold'>{{ $teacher_tutorials['title'] }}</li>
                            @foreach ($teacher_tutorials['links'] as $tt)
                                <a href='{{ $tt['url'] }}' target='_blank' class='mainpage list-group-item'>{!! $tt['desc'] !!}</a>
                            @endforeach
                        </div>
                    </div>

                    <div class='col-12 mb-4'>
                        <div class='list-group'>
                            <li class='list-group-item bgTheme text-white TextSemiBold'>{{ $student_tutorials['title'] }}</li>
                            @foreach ($student_tutorials['links'] as $st)
                                <a href='{{ $st['url'] }}' target='_blank' class='mainpage list-group-item'>{!! $st['desc'] !!}</a>
                            @endforeach
                        </div>
                    </div>
                
        </div>
   
</div>
</div>

@endsection
