@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} main-container'>

        <div class="row m-auto">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view')

                    @if(isset($action_bar))
                        {!! $action_bar !!}
                    @else
                        <div class='mt-4'></div>
                    @endif

                    @include('layouts.partials.show_alert') 

                    <div class='col-lg-6 col-12'>
                        <div class='alert alert-danger'>
                            <i class='fa-solid fa-circle-xmark fa-lg'></i>
                            <span>
                                {{ trans('langCourseDelConfirm2') }}
                                <em>{{ course_id_to_title($course_id) }}</em>
                                <br><br>
                                <i>{{ trans('langNoticeDel') }}</i>
                                <br>
                            </span>
                        </div>
                        <div class='col-12 d-flex justify-content-end align-items-center flex-wrap gap-2'>
                            <a class='btn cancelAdminBtn' href='listcours.php'>
                                {{ trans('langCancel') }}
                            </a>
                            <a class='btn deleteAdminBtn' href='{{ $_SERVER['SCRIPT_NAME'] }}?c={{ $course_id }}&amp;delete=yes&amp;{{ generate_csrf_token_link_parameter() }}' {!! $asktotp !!}>
                                {{ trans('langDelete') }}
                            </a>
                        </div>
                    </div>
                    <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
                        <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
                    </div>
                
        </div>
    </div>
</div>
@endsection
