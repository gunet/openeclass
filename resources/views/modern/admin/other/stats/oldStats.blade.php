@extends('layouts.default')

@section('content')

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active_Homepage">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    @if(Session::has('message'))
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
                        <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                        {!! Session::get('message') !!}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </p>
                    </div>
                    @endif

                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                        <div class="alert alert-info">
                            {{ trans('langOldStatsLoginsExpl', get_config('actions_expire_interval')) }}
                        </div>
                    </div>

                    <!--C3 plot-->
                    <div class='row plotscontainer'>
                        <div id='userlogins_container' class='col-lg-12'>
                            {!! plot_placeholder("old_stats", trans('langLoginUser')) !!}
                        </div>
                    </div>   

                    {!! isset($action_bar) ?  $action_bar : '' !!}
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                        <div class="form-wrapper shadow-sm p-3 mt-5 rounded">
                            
                            <form class="form-horizontal" role="form" method="post">
                                <div class='input-append date form-group mt-3' data-date='{{ $user_date_start }}' data-date-format='dd-mm-yyyy'>
                                    <label class='col-sm-6 control-label-notes' for='user_date_start'>{{ trans('langStartDate') }}:</label>
                                    <div class='row'>
                                        <div class='col-10 col-sm-11'>               
                                            <input class='form-control' name='user_date_start' id='user_date_start' type='text' value = '{{ $user_date_start }}'>
                                        </div>
                                        <div class='col-2 col-sm-1'>
                                            <span class='add-on'><i class='fa fa-times'></i></span>
                                            <span class='add-on'><i class='fa fa-calendar'></i></span>
                                        </div>
                                    </div>
                                </div>       
                                <div class='input-append date form-group mt-3' data-date='{{ $user_date_end }}' data-date-format='dd-mm-yyyy'>
                                    <label class='col-sm-6 control-label-notes' for='user_date_end'>{{ trans('langEndDate') }}:</label>
                                    <div class='row'>
                                        <div class='col-10 col-sm-11'>
                                            <input class='form-control' id='user_date_end' name='user_date_end' type='text' value='{{ $user_date_end }}'>
                                        </div>
                                        <div class='col-2 col-sm-1'>
                                            <span class='add-on'><i class='fa fa-times'></i></span>
                                            <span class='add-on'><i class='fa fa-calendar'></i></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group mt-3">
                                    <div class="col-sm-offset-2 col-sm-10">    
                                        <input class="btn btn-primary" type="submit" name="btnUsage" value="{{ trans('langSubmit') }}">
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