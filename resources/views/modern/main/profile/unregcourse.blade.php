@extends('layouts.default')
@section('content')

    <div class="col-12 main-section">
        <div class='{{ $container }} main-container'>
            <div class="row m-auto">

                @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                @include('layouts.partials.legend_view')

                {!! $action_bar !!}

                @if(Session::has('message'))
                    <div class='col-12 all-alerts'>
                        <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @php
                                $alert_type = '';
                                if(Session::get('alert-class', 'alert-info') == 'alert-success'){
                                    $alert_type = "<i class='fa-solid fa-circle-check fa-lg'></i>";
                                } elseif(Session::get('alert-class', 'alert-info') == 'alert-info'){
                                    $alert_type = "<i class='fa-solid fa-circle-info fa-lg'></i>";
                                } elseif(Session::get('alert-class', 'alert-info') == 'alert-warning'){
                                    $alert_type = "<i class='fa-solid fa-triangle-exclamation fa-lg'></i>";
                                } else {
                                    $alert_type = "<i class='fa-solid fa-circle-xmark fa-lg'></i>";
                                }
                            @endphp

                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                {!! $alert_type !!}<span>
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach</span>
                            @else
                                {!! $alert_type !!}<span>{!! Session::get('message') !!}</span>
                            @endif

                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                @endif

                <div class='col-12 mt-4'>
                    <div class='form-wrapper form-edit rounded mt-4 p-0 border-0'>
                        <form class='form-horizontal' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}?u={{ $_SESSION['uid'] }}&amp;cid={{ $cid }}'>
                            <div class='form-group'>
                                <div class='col-sm-12 form-label'>
                                    {{ trans('langConfirmUnregCours') }}: <strong> {{ $course_title }}</strong>
                                </div>
                            </div>
                            <div class='form-group mt-4 d-flex justify-content-start align-items-center gap-2'>
                                <button class='btn deleteAdminBtn' name='doit'> {{ trans('langUnCourse') }}</button>
                                <a href='{{$urlAppend }}main/portfolio.php' class='btn cancelAdminBtn'> {{ trans('langCancel') }}</a>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

@endsection
