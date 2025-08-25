@extends('layouts.default')

@push('head_scripts')
<link type="text/css" rel="stylesheet" media="all" href="{{$urlServer}}js/h5p-standalone/styles/h5p.css" />
<script type="text/javascript" src="{{$urlServer}}h5p-standalone/main.bundle.js"></script>
@endpush

@section('content')

<div class="col-12 main-section">
<div class='{{ $container }} module-container py-lg-0'>
        <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

            @include('layouts.partials.left_menu')

            <div class="col_maincontent_active">

                      <div class="row">

                        @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                        <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools">
                            <div class="offcanvas-header">
                                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="{{ trans('langClose') }}"></button>
                            </div>
                            <div class="offcanvas-body">
                                @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                            </div>
                        </div>

                        @include('layouts.partials.legend_view')

                        @include('layouts.partials.show_alert')

                        {!! $action_bar !!}

                        <div class="col-12 mt-4">
                            <div id="h5p-container"></div>
                        </div>

                </div>
            </div>
        </div>

</div>
</div>

<script type='text/javascript'>
        $(document).ready(function() {
            const el = document.getElementById('h5p-container');
            const options = {
              h5pJsonPath:  '{{$workspaceUrl}}',
              librariesPath: '{{$workspaceLibs}}',
              frameJs: '{{$urlServer}}/js/h5p-standalone/frame.bundle.js',
              frameCss: '{{$urlServer}}/js/h5p-standalone/styles/h5p.css',
              frame: true,
              copyright: true,
              icon: true,
              fullScreen: true
            };
            new H5PStandalone.H5P(el, options);
        });
    </script>
@endsection
