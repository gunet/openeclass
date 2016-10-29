@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    <div class="alert alert-info">
        {{ trans('langOldStatsLoginsExpl', get_config('actions_expire_interval')) }}
    </div>
    <!--C3 plot-->
    <div class='row plotscontainer'>
        <div id='userlogins_container' class='col-lg-12'>
            {!! plot_placeholder("old_stats", trans('langLoginUser')) !!}
        </div>
    </div>   
    <div class="form-wrapper">
        <form class="form-horizontal" role="form" method="post">
        <div class='input-append date form-group' data-date='{{ $user_date_start }}' data-date-format='dd-mm-yyyy'>
            <label class='col-sm-2 control-label' for='user_date_start'>{{ trans('langStartDate') }}:</label>
            <div class='col-xs-10 col-sm-9'>               
                <input class='form-control' name='user_date_start' id='user_date_start' type='text' value = '{{ $user_date_start }}'>
            </div>
            <div class='col-xs-2 col-sm-1'>
                <span class='add-on'><i class='fa fa-times'></i></span>
                <span class='add-on'><i class='fa fa-calendar'></i></span>
            </div>
        </div>       
        <div class='input-append date form-group' data-date='{{ $user_date_end }}' data-date-format='dd-mm-yyyy'>
            <label class='col-sm-2 control-label' for='user_date_end'>{{ trans('langEndDate') }}:</label>
            <div class='col-xs-10 col-sm-9'>
                <input class='form-control' id='user_date_end' name='user_date_end' type='text' value='{{ $user_date_end }}'>
            </div>
            <div class='col-xs-2 col-sm-1'>
                <span class='add-on'><i class='fa fa-times'></i></span>
                <span class='add-on'><i class='fa fa-calendar'></i></span>
            </div>
        </div>
        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">    
                <input class="btn btn-primary" type="submit" name="btnUsage" value="{{ trans('langSubmit') }}">
            </div>
        </div>
        </form>
    </div>
@endsection