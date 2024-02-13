@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
<div class='container-fluid py-lg-0'>
        <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

            <div id="background-cheat-leftnav" class="col_sidebar_active d-flex justify-content-start align-items-strech ps-lg-0 pe-lg-0">
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block ContentLeftNav">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                </div>
            </div>

            <div class="col_maincontent_active">

                <div class="row">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                            @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                        </div>
                    </div>

                    @include('layouts.partials.legend_view')

                    @if (count($course_description) > 0)
                        @foreach ($course_description as $data)
                                <div class='panel panel-action-btn-default'>
                                    <div class='panel-heading'>
                                        <div class='panel-title'>{!! q($data->title) !!}</div>
                                    </div>
                                    <div class='panel-body'>
                                        {!! standard_text_escape($data->comments) !!}
                                    </div>
                                </div>
                        @endforeach
                    @else
                        <div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>{{ trans('langThisCourseDescriptionIsEmpty') }}</span></div>
                    @endif
                </div>
            </div>
        </div>
    
</div>
</div>

@endsection