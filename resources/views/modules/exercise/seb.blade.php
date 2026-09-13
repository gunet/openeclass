@extends('layouts.default')

@section('content')
    <div class='{{ $container }} module-container py-lg-0'>
        <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">
            <aside class='aside-sidebar'>
                @include('layouts.partials.left_menu')
            </aside>
            <main id="main" class="col-12 main-maincontent col_maincontent_active">
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

                    <div class='form-group mt-5'>
                        <div class='col-12 d-flex justify-content-center align-items-center gap-4'>
                            <a href="{{ $seb_launch_url }}" class="btn submitAdminBtnDefault">{{ trans('langLaunchSafeExamBrowser') }}</a>
                            <a class='btn successAdminBtn' href="https://safeexambrowser.org/download_en.html" target="_blank">{{ trans('langDownloadSafeExamBrowser') }}</a>
                        </div>
                    </div>

                    <div class='form-group mt-5'>
                        <div class='col-12 d-flex justify-content-center align-items-center gap-2'>
                            <a class='btn cancelAdminBtn' href='{{ $urlServer }}/modules/exercise/index.php?course={{ $course_code }}'>{{ trans('langBack') }}</a>
                        </div>
                    </div>

                </div>
            </main>
        </div>
    </div>
@endsection
