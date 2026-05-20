@extends('layouts.default')

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

                    {!! $action_bar !!}

                    @include('layouts.partials.show_alert')

                    {{-- 
                        @if (!isset($_GET['update']))
                        <div class='col-12'>
                            <a class='btn submitAdminBtnDefault link-Update-Percentage gap-1 mb-3' href='{{ $urlAppend }}modules/session/update_percentage.php?course={{ $course_code }}&update_percentage=true'>
                                <i class='fa-solid fa-arrow-rotate-right'></i>
                                {{ trans('langUpdatePercentage') }}
                            </a>

                            <div class='d-flex align-items-start gap-2 show-calculation-message d-none mb-3'>
                                <div class='spinner-border text-warning' role='status' style='width:20px; height:20px;'>
                                    <span class='visually-hidden'></span>
                                </div>
                                {{ trans('langPlsWait') }}
                            </div>
                        </div>
                        @endif
                    --}}
                        

                </div>
            </div>

        </div>

    </div>
</div>



@endsection
