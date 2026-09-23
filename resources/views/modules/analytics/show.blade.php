@extends('layouts.default')

@section('content')

<div class='{{ $container }} module-container py-lg-0'>
    <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">
        <aside class='aside-sidebar'>@include('layouts.partials.left_menu')</aside>
        <main id="main" class="col-12 main-maincontent col_maincontent_active">

            <div class="row">

                @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools">
                    <div class="offcanvas-header">
                        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="{{ trans('langClose') }}"></button>
                    </div>
                    <div class="offcanvas-body">
                        @include('layouts.partials.sidebar', ['is_editor' => $is_editor])
                    </div>
                </div>

                @include('layouts.partials.legend_view')

                {!! isset($action_bar) ? $action_bar : '' !!}

                @include('layouts.partials.show_alert')

                <!-- Analytics Details Card -->
                <div class="col-12 mb-4">
                    <div class="card panelCard card-default px-lg-4 py-lg-3">
                        <div class="card-header border-0">
                            <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
                                <h2 class="text-heading-h3 mb-0 d-flex align-items-center flex-wrap gap-2">
                                    <span>{{ $analytics->title }}</span>
                                    @if ($analytics->active)
                                        <span class="badge bg-success">{{ trans('langActive') }}</span>
                                    @else
                                        <span class="badge bg-danger">{{ trans('langInactive') }}</span>
                                    @endif
                                </h2>
                                @if ($is_editor)
                                    <div>
                                        <a href="{{ $urlAppend }}modules/analytics/index.php?course={{ $course_code }}&amp;analytics_id={{ $analytics->id }}&amp;edit_analytics=1" class="btn submitAdminBtn" title="{{ trans('langModify') }}" data-bs-toggle="tooltip">
                                            <i class="fa-solid fa-gear"></i>
                                        </a>
                                    </div>
                                @endif
                            </div>
                            @if ($analytics->description)
                                <p class="text-muted mb-0 mt-2">{{ $analytics->description }}</p>
                            @endif
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-3 col-sm-6">
                                    <div class="p-3 bg-light rounded-3">
                                        <small class="text-muted d-block mb-1"><i class="fa-solid fa-calendar-days me-1"></i> {{ trans('langPeriod') }}</small>
                                        <span class="fw-bold">{{ $analytics->period_name }}</span>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="p-3 bg-light rounded-3">
                                        <small class="text-muted d-block mb-1"><i class="fa-solid fa-play me-1"></i> {{ trans('langStartDate') }}</small>
                                        <span class="fw-bold">{{ $analytics->start_date_formatted ?: '-' }}</span>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="p-3 bg-light rounded-3">
                                        <small class="text-muted d-block mb-1"><i class="fa-solid fa-flag-checkered me-1"></i> {{ trans('langEndDate') }}</small>
                                        <span class="fw-bold">{{ $analytics->end_date_formatted ?: '-' }}</span>
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="p-3 bg-light rounded-3">
                                        <small class="text-muted d-block mb-1"><i class="fa-solid fa-clock me-1"></i> {{ trans('langCreationDate') }}</small>
                                        <span class="fw-bold">{{ $analytics->created_formatted ?: '-' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Criteria & Elements Section -->
                <div class="col-12">
                    <div class="card panelCard card-default px-lg-4 py-lg-3">
                        <div class="card-header border-0 d-flex justify-content-between align-items-center">
                            <h3 class="text-heading-h3 mb-0">
                                <i class="fa-solid fa-list-check me-2"></i> {{ trans('langAnalyticsElements') }}
                            </h3>
                            <span class="badge bg-secondary">{{ count($elements) }}</span>
                        </div>
                        <div class="card-body">
                            @if (count($elements) == 0)
                                <div class="text-center text-muted py-4">
                                    <i class="fa-solid fa-folder-open fa-2x mb-2 d-block"></i>
                                    {{ trans('langAnalyticsNoElements') }}
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table-default">
                                        <thead>
                                            <tr class="list-header">
                                                <th>{{ trans('langType') }}</th>
                                                <th class="text-center">{{ trans('langAnalyticsCriticalLevel') }}</th>
                                                <th class="text-center">{{ trans('langAnalyticsAdvancedLevel') }}</th>
                                                <th class="text-center">{{ trans('langAnalyticsWeight') }}</th>
                                                @if ($is_editor)
                                                    <th class="text-end" aria-label="{{ trans('langSettingSelect') }}"><i class="fa-solid fa-gears"></i></th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($elements as $element)
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <i class="{{ $element['icon'] }} text-primary fs-5"></i>
                                                            <div>
                                                                <span class="fw-bold">{{ $element['title'] }}</span>
{{--                                                                @if (!empty($element['resource_info']))--}}
{{--                                                                    <span class="text-muted small">{{ $element['resource_info'] }}</span>--}}
{{--                                                                @endif--}}
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2">
                                                            {{ trans('langAnalyticsMinValue') }}: <strong>{{ $element['min_value'] }}</strong> &mdash; 
                                                            {{ trans('langAnalyticsMaxValue') }}: <strong>{{ $element['lower_threshold'] }}</strong>
                                                        </span>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                                                            {{ trans('langAnalyticsMinValue') }}: <strong>{{ $element['upper_threshold'] }}</strong> &mdash; 
                                                            {{ trans('langAnalyticsMaxValue') }}: <strong>{{ $element['max_value'] }}</strong>
                                                        </span>
                                                    </td>
                                                    <td class="text-center fw-bold fs-6">
                                                        <span class="badge bg-primary px-3 py-2">{{ $element['weight'] }}</span>
                                                    </td>
                                                    @if ($is_editor)
                                                        <td class="text-end">
                                                            <a href="{{ $urlAppend }}modules/analytics/index.php?course={{ $course_code }}&amp;analytics_id={{ $analytics->id }}&amp;analytics_element_id={{ $element['id'] }}&amp;edit_analytics_element=true" class="btn submitAdminBtn" title="{{ trans('langModify') }}" data-bs-toggle="tooltip">
                                                                <i class="fa-solid fa-gear"></i>
                                                            </a>
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>
</div>

@endsection
