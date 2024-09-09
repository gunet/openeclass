@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
<div class='container module-container py-lg-0'>
        <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

            @include('layouts.partials.left_menu')

            <div class="col_maincontent_active col_maincontent_active_module">

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

                    @if (count($course_description) > 0)
                        @foreach ($course_description as $data)
                            <div class='card panelCard px-lg-4 py-lg-3'>
                                <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                                    <h3>{!! q($data->title) !!}</h3>
                                </div>
                                <div class='card-body'>
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