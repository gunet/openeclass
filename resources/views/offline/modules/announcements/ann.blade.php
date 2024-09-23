@extends('layouts.default')

@section('content')
<div class="col-12 main-section">
<div class='container module-container py-lg-0'>
        <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

            @include('layouts.partials.left_menu')

            <div class="col_maincontent_active col_maincontent_active_module_content">

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

                    <div class="col-12">
                        <div class="card panelCard card-default px-lg-4 py-lg-3">
                            <div class="card-body">
                                <div class="single_announcement">
                                    <div class="announcement-title">
                                        {!! standard_text_escape($ann_title) !!}
                                    </div>
                                    <span class="announcement-date">
                                        {{ format_locale_date(strtotime($ann_date)) }}
                                    </span>
                                    <div class="announcement-main">
                                        <p>{!! $ann_body !!}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    
</div>
</div>

@endsection


