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
                        <div class="card-header border-0 p-0">
                            <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
                                <h2 class="text-heading-h3 mb-0 d-flex align-items-center flex-wrap gap-2">
                                    <span class="action-bar-title">{{ $analytics->title }}</span>
                                    @if ($analytics->active)
                                        <span class="badge bg-success">{{ trans('langActive') }}</span>
                                    @else
                                        <span class="badge bg-danger">{{ trans('langInactive') }}</span>
                                    @endif
                                </h2>
                                @if ($is_editor)
                                    <div>
                                        {!! $rule_action_button !!}
                                    </div>
                                @endif
                            </div>
                            @if ($analytics->description)
                                <p class="text-muted mb-0 mt-2">{{ $analytics->description }}</p>
                            @endif
                        </div>
                        <div class="card-body p-0 mt-3">
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
                    <div class="card panelCard border-0 shadow-sm rounded-4 px-lg-4 py-lg-3 bg-white mb-4">
                        <div class="card-header border-0 bg-transparent p-0 mb-3">
                            <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-3 p-2 d-flex align-items-center justify-content-center" style="background-color: #f1f5f9; color: #334155; width: 48px; height: 48px;">
                                        <i class="fa-solid fa-chart-column fa-xl"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-heading-h3 mb-0 fw-bold" style="color: #0f172a; font-size: 1rem;">
                                            {{ trans('langAnalyticsDifficultyLevel') }}
                                        </h3>
                                        <p class="text-muted small mb-0 mt-1" style="color: #64748b;">
                                            {{ trans('langAnalyticsDifficultyLevelInfo') }}
                                        </p>
                                    </div>
                                </div>
                                @if ($is_editor)
                                    {!! $add_element_button !!}
                                @endif
                            </div>
                        </div>

                        <div class="card-body p-0">
                            @if (count($elements) == 0)
                                <div class="text-center text-muted py-5">
                                    <i class="fa-solid fa-folder-open fa-3x mb-3 text-secondary d-block"></i>
                                    {{ trans('langAnalyticsNoElements') }}
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table align-middle border-0 mb-0" style="border-collapse: separate; border-spacing: 0;">
                                        <thead>
                                            <tr style="background-color: #f8fafc;">
                                                <th rowspan="2" class="align-middle border-0 ps-4 py-3 fw-bold" style="color: #475569; font-size: 0.95rem; width: 28%;">
                                                    {{ trans('langType') }}
                                                </th>
                                                <th colspan="2" class="text-center border-0 pt-3 pb-1 fw-bold" style="background-color: #fff7ed; color: #c2410c; font-size: 0.95rem; border-top-left-radius: 12px;">
                                                    <i class="fa-solid fa-arrow-down me-1"></i> {{ trans('langAnalyticsCriticalLevel') }}
                                                </th>
                                                <th colspan="2" class="text-center border-0 pt-3 pb-1 fw-bold" style="background-color: #f0fdf4; color: #15803d; font-size: 0.95rem; border-top-right-radius: 12px;">
                                                    <i class="fa-solid fa-arrow-up me-1"></i> {{ trans('langAnalyticsAdvancedLevel') }}
                                                </th>
                                                <th rowspan="2" class="text-center align-middle border-0 py-3 fw-bold" style="color: #475569; font-size: 0.95rem;">
                                                    {{ trans('langAnalyticsWeight') }}
                                                </th>
                                                @if ($is_editor)
                                                    <th rowspan="2" class="text-center align-middle border-0 py-3 pe-4 fw-bold" style="color: #475569; font-size: 0.95rem;">
                                                        {{ trans('langActions') }}
                                                    </th>
                                                @endif
                                            </tr>
                                            <tr style="background-color: #f8fafc;">
                                                <th class="text-center border-0 small font-normal pb-3 pt-1" style="background-color: #fff7ed; color: #9a3412; font-size: 0.8rem; border-bottom-left-radius: 12px;">
                                                    {{ trans('langFrom') }}
                                                </th>
                                                <th class="text-center border-0 small font-normal pb-3 pt-1" style="background-color: #fff7ed; color: #9a3412; font-size: 0.8rem;">
                                                    {{ trans('langTill2') }}
                                                </th>
                                                <th class="text-center border-0 small font-normal pb-3 pt-1" style="background-color: #f0fdf4; color: #166534; font-size: 0.8rem;">
                                                    {{ trans('langFrom') }}
                                                </th>
                                                <th class="text-center border-0 small font-normal pb-3 pt-1" style="background-color: #f0fdf4; color: #166534; font-size: 0.8rem; border-bottom-right-radius: 12px;">
                                                    {{ trans('langTill2') }}
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($elements as $element)
                                                <tr>
                                                    <!-- Type Column -->
                                                    <td class="p-2 ps-3" style="border-bottom: 1px solid #e2e8f0 !important;">
                                                        <div class="d-flex align-items-center gap-3">
                                                            <div class="rounded-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 46px; height: 46px; background-color: #eff6ff; color: #2563eb; font-size: 1.25rem;">
                                                                <i class="{{ $element['icon'] }}"></i>
                                                            </div>
                                                            <div>
                                                                <div class="fw-bold" style="color: #0f172a; font-size: 0.95rem;">{{ $element['title'] }}</div>
                                                            </div>
                                                        </div>
                                                    </td>

                                                    <!-- Critical/Lower Level (Min - Max) -->
                                                    <td colspan="2" class="text-center p-2" style="border-bottom: 1px solid #e2e8f0 !important;">
                                                        <div class="d-inline-flex align-items-center justify-content-center px-3 py-2 rounded-3" style="background-color: #fff7ed;">
                                                            <div class="rounded-3 px-3 py-1 fw-bold" style="background-color: #ffedd5; color: #c2410c; border: 1px solid #fed7aa; min-width: 48px; font-size: 1.05rem;">
                                                                {{ $element['min_value'] }}
                                                            </div>
                                                            <span class="mx-2 fw-medium" style="color: #94a3b8;">&mdash;</span>
                                                            <div class="rounded-3 px-3 py-1 fw-bold" style="background-color: #ffedd5; color: #c2410c; border: 1px solid #fed7aa; min-width: 48px; font-size: 1.05rem;">
                                                                {{ $element['lower_threshold'] }}
                                                            </div>
                                                        </div>
                                                    </td>

                                                    <!-- Advanced Level (Min - Max) -->
                                                    <td colspan="2" class="text-center p-2" style="border-bottom: 1px solid #e2e8f0 !important;">
                                                        <div class="d-inline-flex align-items-center justify-content-center px-3 py-2 rounded-3" style="background-color: #f0fdf4;">
                                                            <div class="rounded-3 px-3 py-1 fw-bold" style="background-color: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; min-width: 48px; font-size: 1.05rem;">
                                                                {{ $element['upper_threshold'] }}
                                                            </div>
                                                            <span class="mx-2 fw-medium" style="color: #94a3b8;">&mdash;</span>
                                                            <div class="rounded-3 px-3 py-1 fw-bold" style="background-color: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; min-width: 48px; font-size: 1.05rem;">
                                                                {{ $element['max_value'] }}
                                                            </div>
                                                        </div>
                                                    </td>

                                                    <!-- Weight -->
                                                    <td class="text-center p-2" style="border-bottom: 1px solid #e2e8f0 !important;">
                                                        <div class="d-inline-flex align-items-center justify-content-center rounded-3 fw-bold text-white shadow-sm" style="width: 42px; height: 42px; background-color: #0066ff; font-size: 1.15rem;">
                                                            {{ $element['weight'] }}
                                                        </div>
                                                    </td>

                                                    <!-- Actions -->
                                                    @if ($is_editor)
                                                        <td class="text-end p-2 pe-3" style="border-bottom: 1px solid #e2e8f0 !important;">
                                                            {!! $element['action_button'] !!}
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

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
    });
</script>

@endsection
