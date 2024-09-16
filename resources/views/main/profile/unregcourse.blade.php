@extends('layouts.default')
@section('content')

    <div class="col-12 main-section">
        <div class='{{ $container }} main-container'>
            <div class="row m-auto">

                @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                @include('layouts.partials.legend_view')

                {!! $action_bar !!}

                @include('layouts.partials.show_alert') 

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
