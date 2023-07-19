@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
<div class='{{ $container }}'>
        <div class="row rowMargin">

                    <div class='col-12'>
                        <h1>{{ trans('langManuals') }}</h1>
                    </div>

                    <div class='col-12 mt-4 mb-4'>
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
