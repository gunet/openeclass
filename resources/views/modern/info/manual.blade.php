@extends('layouts.default')

@section('content')

<div class="pb-3 pt-3">

    <div class="container-fluid main-container">

        <div class="row">

            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active">

                <div class="row p-lg-5 p-md-5 ps-1 pe-2 pt-5 pb-5">

                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-3'>
                        <nav class="navbar navbar-expand-lg navrbar_menu_btn">
                            <a type="button" id="getTopicButton" class="d-none d-sm-block d-md-none d-lg-block ms-2 btn btn-primary btn btn-primary" href="{{$urlAppend}}modules/help/help.php?language={{$language}}&topic={{$helpTopic}}&subtopic={{$helpSubTopic}}" style='margin-top:-10px'>
                                <i class="fas fa-question"></i>
                            </a>
                        </nav>
                        @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])
                    </div>

                    <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-3">
                        <legend class="float-none w-auto py-2 px-4 notes-legend"><span style="margin-left:-20px;"><i class="fa fa-file-video-o fa-fw"></i> {{trans('langManuals')}}</span></legend>
                        <div class="row p-2"></div>
                    </div>


                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-3'>
                        <div class='row'>
                            <div class='text-start text-secondary'>{{trans('langEclass')}} - {{trans('langManuals')}}</div>
                            {!! $action_bar !!}
                        </div>
                    </div>


                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                        <div class='list-group shadow-lg p-3 mb-5 bg-body rounded bg-primary'>
                            <li class='list-group-item list-header notes_thead text-white'>{{ $general_tutorials['title'] }}</li>
                            @foreach ($general_tutorials['links'] as $gt)
                                <a href='{{ $gt['url'] }}' target='_blank' class='mainpage list-group-item'>{!! $gt['desc'] !!}</a>
                            @endforeach
                        </div>
                    </div>

                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                        <div class='list-group shadow-lg p-3 mb-5 bg-body rounded bg-primary'>
                            <li class='list-group-item list-header notes_thead text-white'>{{ $teacher_tutorials['title'] }}</li>
                            @foreach ($teacher_tutorials['links'] as $tt)
                                <a href='{{ $tt['url'] }}' target='_blank' class='mainpage list-group-item'>{!! $tt['desc'] !!}</a>
                            @endforeach
                        </div>
                    </div>

                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                        <div class='list-group shadow-lg p-3 mb-5 bg-body rounded bg-primary'>
                            <li class='list-group-item list-header notes_thead text-white'>{{ $student_tutorials['title'] }}</li>
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
