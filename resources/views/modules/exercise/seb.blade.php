@extends('layouts.default')

@section('content')
    <div class="col-12 main-section">
        <div class='{{ $container }} module-container py-lg-0'>
            <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

                @include('layouts.partials.left_menu')

                <div class="col_maincontent_active">
                    <div class="row">
                        @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                        @include('layouts.partials.legend_view')

                        @include('layouts.partials.show_alert')
                        <div class='col-12 d-flex justify-content-center align-items-center gap-2'>
                            <h4>
                                {{ trans('langSEBInfo1') }}
                            </h4>
                        </div>
                        <div class="alert alert-info">
                            {{ trans('langSEBInfo2') }}
                        </div>
                        <form class='form-horizontal' role='form' method='post' action='{{ preg_replace('/https/', 'sebs', $urlServer) }}modules/exercise/launch_seb.php?course={{ $course_code }}&exerciseId={{ $eid }}'>
                            <div class='form-group mt-5'>
                                <div class='col-12 d-flex justify-content-center align-items-center gap-2'>
                                    <input class='btn submitAdminBtn' type='submit' name='LaunchSeb' value='{{ trans('langLaunchSafeExamBrowser') }}'>
                                </div>
                            </div>
                            <div class='form-group mt-5'>
                                <div class='col-12 d-flex justify-content-center align-items-center gap-2'>
                                    <a class='btn successAdminBtn' href="https://safeexambrowser.org/download_en.html" target="_blank">{{ trans('langDownloadSafeExamBrowser') }}</a>
                                    <a class='btn cancelAdminBtn' href='{{ $urlServer }}/modules/exercise/index.php?course={{ $course_code }}'>{{ trans('langBack') }}</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
