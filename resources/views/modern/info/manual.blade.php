@extends('layouts.default')

@section('content')

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active_Homepage">
                
                <div class="row p-lg-5 p-md-5 ps-1 pe-2 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-3'>
                        <div class='text-start text-secondary'>{{trans('langEclass')}} - {{trans('langManuals')}}</div>
                        {!! $action_bar !!}
                    </div>

                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-3'>
                        <div class='list-group'>
                            <li class='list-group-item list-header control-label-notes'>{{ $general_tutorials['title'] }}</li>
                            @foreach ($general_tutorials['links'] as $gt)
                                <a href='{{ $gt['url'] }}' target='_blank' class='mainpage list-group-item'>{!! $gt['desc'] !!}</a>
                            @endforeach
                        </div>
                    </div>

                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-3'>
                        <div class='list-group'>
                            <li class='list-group-item list-header control-label-notes'>{{ $teacher_tutorials['title'] }}</li>
                            @foreach ($teacher_tutorials['links'] as $tt)
                                <a href='{{ $tt['url'] }}' target='_blank' class='mainpage list-group-item'>{!! $tt['desc'] !!}</a>
                            @endforeach
                        </div>
                    </div>

                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-3'>
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
</div>

@endsection
