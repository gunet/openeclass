@extends('layouts.default')

@section('content')


<div class="col-12 main-section">
<div class='{{ $container }}  @if($course_code) module-container py-lg-0 @else main-container @endif'>
        <div class="@if($course_code) course-wrapper d-lg-flex align-items-lg-strech w-100 @else row m-auto @endif">

            @if($course_code)
                <div id="background-cheat-leftnav" class="col_sidebar_active d-flex justify-content-start align-items-strech ps-lg-0 pe-lg-0">
                    <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block ContentLeftNav">
                        @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                    </div>
                </div>
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
                                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                            </div>
                            <div class="offcanvas-body">
                                @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                            </div>
                        </div>
                        @endif

                        @include('layouts.partials.legend_view')

                        {!! $backButton !!}

                        @if(Session::has('message'))
                        <div class='col-12 all-alerts'>
                            <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                                @php
                                    $alert_type = '';
                                    if(Session::get('alert-class', 'alert-info') == 'alert-success'){
                                        $alert_type = "<i class='fa-solid fa-circle-check fa-lg'></i>";
                                    }elseif(Session::get('alert-class', 'alert-info') == 'alert-info'){
                                        $alert_type = "<i class='fa-solid fa-circle-info fa-lg'></i>";
                                    }elseif(Session::get('alert-class', 'alert-info') == 'alert-warning'){
                                        $alert_type = "<i class='fa-solid fa-triangle-exclamation fa-lg'></i>";
                                    }else{
                                        $alert_type = "<i class='fa-solid fa-circle-xmark fa-lg'></i>";
                                    }
                                @endphp

                                @if(is_array(Session::get('message')))
                                    @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                    {!! $alert_type !!}<span>
                                    @foreach($messageArray as $message)
                                        {!! $message !!}
                                    @endforeach</span>
                                @else
                                    {!! $alert_type !!}<span>{!! Session::get('message') !!}</span>
                                @endif

                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        </div>
                        @endif

                        <div class='col-12'>
                            <div class='card panelCard px-lg-4 py-lg-3 h-100'>
                                <div class='card-body'>
                                    <form class='form-horizontal' role='form'>
                                        <div class='form-group'>
                                            <label class='col-sm-12 control-label-notes'>{{ trans('langQuotaUsed') }}</label>
                                            <div class='col-sm-8'>
                                                <p class='form-control-static'>{!! $used !!}</p>
                                            </div>
                                        </div>

                                        <div class='form-group mt-4'>
                                            <label class='col-sm-12 control-label-notes'>{{ trans('langQuotaPercentage') }}</label>
                                            <div class='col-sm-9'>
                                                <div class='progress-circle-bar' role='progressbar' aria-valuenow={{$diskUsedPercentage}} aria-valuemin='0' aria-valuemax='100' style='--value: {{$diskUsedPercentage}}; --size: 6rem;'></div>
                                            </div>
                                        </div>

                                        <div class='form-group mt-4'>
                                            <label class='col-sm-12 control-label-notes'>{{ trans('langQuotaTotal') }}</label>
                                            <div class='col-sm-8'>
                                                <p class='form-control-static'>{!! $quota !!}</p>
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
</div>
@endsection

