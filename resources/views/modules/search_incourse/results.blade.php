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

                        <div id='operations_container'>
                            {!! $action_bar !!}
                        </div>

                        @include('layouts.partials.show_alert')

                        <div class='alert alert-info'>
                            <i class='fa-solid fa-circle-info fa-lg'></i>
                            <span>{{ trans('langDoSearch') }}:&nbsp;<label>  {{ $search_terms }} </label>
                                <br><small>{{ $results_count }} {{ trans('langResults2') }}</small>
                            </span>
                        </div>

                        @if (!$found)
                            <div class='alert alert-warning'>
                                <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                                <span>{{ trans('langNoResult') }}</span>
                            </div>
                        @else
                            {!! $search_results !!}
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

