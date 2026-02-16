@extends('layouts.default')

@section('content')

    <div class="col-12 main-section">
        <div class='{{ $container }} @if($course_code) module-container py-lg-0 @else main-container @endif'>
            <div class="@if($course_code) course-wrapper d-lg-flex align-items-lg-strech w-100 @else row m-auto @endif">

                @if($course_code)
                    @include('layouts.partials.left_menu')
                @endif

                @if($course_code)
                    <div class="col_maincontent_active">
                @else
                    <div class="col-12">
                @endif

                    <div class="row">

                        @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                        @if($course_code)
                            <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools">
                                <div class="offcanvas-header">
                                    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="{{ trans('langClose') }}"></button>
                                </div>
                                <div class="offcanvas-body">
                                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                                </div>
                            </div>
                        @endif

                        @include('layouts.partials.legend_view')

                        @include('layouts.partials.show_alert')

                        {!! $backButton !!}

                        <div class='col-12'>
                            <div class='form-wrapper form-edit p-3 mt-2 bg-body rounded'>
                                <form class='form-horizontal' role='form'>
                                    <div class='form-group'>
                                        <div class='col-sm-12 control-label-notes'>{{ trans('langQuotaUsed') }}:</div>
                                        <div class='col-sm-12'>
                                            <p class='form-control-static'>{{ $used }}</p>
                                        </div>
                                    </div>

                                    <div class='form-group mt-3'>
                                        <div class='col-sm-12 control-label-notes'>{{ trans('langQuotaPercentage') }}:</div>
                                        <div class='col-sm-12'>
                                            <div class="progress">
                                                <div class='progress-circle-bar' role="progressbar" style="min-width: 2em; width: {{$diskUsedPercentage}}%;" aria-valuenow="{{$diskUsedPercentage}}" aria-valuemin="0" aria-valuemax="100">{{$diskUsedPercentage}}%</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class='form-group mt-3'>
                                        <div class='col-sm-12 control-label-notes'>{{ trans('langQuotaTotal') }}:</div>
                                        <div class='col-sm-12'>
                                            <p class='form-control-static'>{{ $quota }}</p>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

