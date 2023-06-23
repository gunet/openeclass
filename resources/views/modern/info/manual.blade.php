@extends('layouts.default')

@section('content')

<div class="col-12 basic-section p-xl-5 px-lg-3 py-lg-5">

        <div class="row rowMargin">

            <div class="col-12 col_maincontent_active_Homepage">
                
                <div class="row">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    {!! $action_bar !!}

                    <div class='col-12 mb-4'>
                        <div class='list-group'>
                            <li class='list-group-item list-header control-label-notes'>{{ $general_tutorials['title'] }}</li>
                            @foreach ($general_tutorials['links'] as $gt)
                                <a href='{{ $gt['url'] }}' target='_blank' class='mainpage list-group-item'>{!! $gt['desc'] !!}</a>
                            @endforeach
                        </div>
                    </div>

                    <div class='col-12 mb-4'>
                        <div class='list-group'>
                            <li class='list-group-item list-header control-label-notes'>{{ $teacher_tutorials['title'] }}</li>
                            @foreach ($teacher_tutorials['links'] as $tt)
                                <a href='{{ $tt['url'] }}' target='_blank' class='mainpage list-group-item'>{!! $tt['desc'] !!}</a>
                            @endforeach
                        </div>
                    </div>

                    <div class='col-12 mb-4'>
                        <div class='list-group'>
                            <li class='list-group-item list-header control-label-notes'>{{ $student_tutorials['title'] }}</li>
                            @foreach ($student_tutorials['links'] as $st)
                                <a href='{{ $st['url'] }}' target='_blank' class='mainpage list-group-item'>{!! $st['desc'] !!}</a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
   
</div>

@endsection
