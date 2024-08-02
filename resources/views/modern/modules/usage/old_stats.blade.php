@extends('layouts.default')

@push('head_styles')
    <link rel='stylesheet' type='text/css' href="{{ $urlAppend }}js/c3-0.4.10/c3.min.css">
@endpush
@push('head_scripts')
    <script type='text/javascript' src='{{ $urlAppend }}js/tools.js'></script>
    <script type='text/javascript' src='{{ $urlAppend }}js/d3/d3.min.js'></script>
    <script type='text/javascript' src='{{ $urlAppend }}js/c3-0.4.10/c3.min.js'></script>

    <script type='text/javascript'>
        var xMinVal = null;
        var xMaxVal = null;
        var xTicks = null;
        var interval = 30; //per month
        oldStatsChart = null;

        $(document).ready(function(){
            $('#user_date_start').datepicker({
                format: 'dd-mm-yyyy',
                pickerPosition: 'bottom-left',
                language: '{{ $language }}',
                autoclose: true
            });

            $('#user_date_end').datepicker({
                format: 'dd-mm-yyyy',
                pickerPosition: 'bottom-left',
                language: '{{ $language }}',
                autoclose: true
            });

            sdate = $('#user_date_start').datepicker('getDate');
            startdate = sdate.getFullYear()+'-'+(sdate.getMonth()+1)+'-'+sdate.getDate();
            edate = $('#user_date_end').datepicker('getDate');
            enddate = edate.getFullYear()+'-'+(edate.getMonth()+1)+'-'+edate.getDate();
            module = $('#u_module_id option:selected').val();
            refresh_oldstats_course_plot(startdate, enddate, {{ $course_id }}, module);
        });

        function refresh_oldstats_course_plot(startdate, enddate, course, module){
            xAxisTicksAdjust();
            $.getJSON('results.php',{t:'ocs', s:startdate, e:enddate, c:course, m:module},function(data){
                var options = {
                    data: {
                        json: data,
                        x: 'time',
                        xFormat: '%Y-%m-%d',
                        axes: {
                            hits: 'y',
                            duration: 'y2'
                        },
                        types:{
                            hits: 'bar',
                            duration: 'spline'
                        },
                        names:{
                            hits: '{{ trans('langVisits') }}',
                            duration: '{{ trans('langDuration') }}'
                        }
                    },
                    axis:{ x:
                            {
                                type:'timeseries',
                                tick:{
                                    format: '%m-%Y',
                                    values:xTicks, rotate:60
                                },
                                label: '{{ trans('langMonth') }}',
                                min: xMinVal
                            },
                        y:{
                            label: '{{ trans('langVisits') }}',
                            min: 0,
                            padding:{
                                top:0, bottom:0
                            }
                        },
                        y2: {
                            show: true,
                            label: '{{ trans('langHours') }}',
                            min: 0,
                            padding:{
                                top:0,
                                bottom:0
                            }
                        }
                    },
                    bar:{
                        width:{
                            ratio:0.3
                        }
                    },
                    bindto: '#old_stats'
                };
                c3.generate(options);
            });
        }

        function xAxisTicksAdjust()
        {
            var xmin = sdate;
            var xmax = edate;

            dayMilliseconds = 24*60*60*1000;
            diffInDays = (edate-sdate)/dayMilliseconds;
            xTicks = new Array();
            var tick = new Date(xmin);
            cur = xmin.getMonth();
            if(interval == 1){
                xMinVal = xmin.getFullYear()+'-'+(xmin.getMonth()+1)+'-'+tick.getDate();
                xMaxVal = xmax.getFullYear()+'-'+(xmax.getMonth()+1)+'-'+xmax.getDate();
                if(tick.getDate() == 1){
                    xTicks.push(xMinVal);
                }
                while(tick <= xmax)
                {
                    tick.setDate(tick.getDate() + 1);
                    tickval = tick.getFullYear()+'-'+(tick.getMonth()+1)+'-'+tick.getDate();
                    if(cur != tick.getMonth()){
                        xTicks.push(tickval);
                        cur = tick.getMonth();
                    }
                }
            }
            else if(interval == 7){
                xminMonday = new Date(xmin.getTime() - xmin.getUTCDay()*dayMilliseconds);
                xMinVal = xminMonday.getFullYear()+'-'+(xminMonday.getMonth()+1)+'-'+xminMonday.getDate();
                xmaxMonday = new Date(xmax.getTime() + (7-xmax.getUTCDay())*dayMilliseconds);
                xMaxVal = xmaxMonday.getFullYear()+'-'+(xmaxMonday.getMonth()+1)+'-'+xmaxMonday.getDate();
                xTicks.push(xMinVal);
                tick = new Date(xminMonday);
                i = 1;
                while(tick <= xmaxMonday)
                {
                    tick.setDate(tick.getDate() + 7);
                    tickval = tick.getFullYear()+'-'+(tick.getMonth()+1)+'-'+tick.getDate();
                    if(i % 2 == 0){
                        xTicks.push(tickval);
                    }
                    i++;

                }
            }
            else if(interval == 30){
                xMinVal = xmin.getFullYear()+'-'+(xmin.getMonth()+1)+'-15';
                xMaxVal = xmax.getFullYear()+'-'+(xmax.getMonth()+1)+'-15';
                xTicks.push(xMinVal);
                while(tick <= xmax)
                {
                    tick.setMonth(tick.getMonth() + 1);
                    tickval = tick.getFullYear()+'-'+(tick.getMonth()+1)+'-15';
                    xTicks.push(tickval);
                }
            }
            else if(interval == 365){
                xMinVal = xmin.getFullYear()+'-06-30';
                xMaxVal = xmax.getFullYear()+'-06-30';
                xTicks.push(xMinVal);
                while(tick <= xmax)
                {
                    tick.setFullYear(tick.getFullYear() + 1);
                    tickval = tick.getFullYear()+'-06-30';
                    xTicks.push(tickval);
                }
            }
        }
    </script>

@endpush



@section('content')
    <div class="col-12 main-section">
        <div class='{{ $container }} module-container py-lg-0'>
            <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

                @include('layouts.partials.left_menu')

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

                        <div id='operations_container'>
                            {!! $action_bar !!}
                        </div>

                        @include('layouts.partials.show_alert') 

                        <div class='col-12 plotscontainer mt-4'>
                            <div id='userlogins_container' class='col-lg-12'>
                                {!! $plot_placeholder !!}
                            </div>
                        </div>

                        <div class="d-lg-flex gap-4 mt-4">
                            <div class="flex-grow-1">
                                <div class="form-wrapper form-edit rounded">
                                    <form class="form-horizontal" role="form" method="post" action="{{ $_SERVER['SCRIPT_NAME'] }}?course={{ $course_code  }}">
                                        <div class='input-append date form-group mt-4' id='user_date_start' data-date='{{ $user_date_start }}' data-date-format='dd-mm-yyyy'>
                                            <label class='col-12 control-label-notes'>{{ trans('langStartDate') }}</label>
                                            <div class='input-group'>
                                                <span class='add-on input-group-text h-40px bg-input-default input-border-color border-end-0'><i class='fa-regular fa-calendar'></i></span>
                                                <input class='form-control mt-0 border-start-0' name='user_date_start' id='user_date_start' type='text' value='{{ $user_date_start }}'>
                                            </div>
                                        </div>

                                        <div class='input-append date form-group mt-4' id='user_date_end' data-date='{{ $user_date_end }}' data-date-format='dd-mm-yyyy'>
                                            <label class='col-12 control-label-notes'>{{ trans('langEndDate') }}</label>
                                            <div class='input-group'>
                                                <span class='add-on input-group-text h-40px bg-input-default input-border-color border-end-0'><i class='fa-regular fa-calendar'></i></span>
                                                <input class='form-control mt-0 border-start-0' name='user_date_end' type='text' value='{{ $user_date_end }}'>
                                            </div>
                                        </div>

                                        <div class="form-group mt-4">
                                            <label class="col-12 control-label-notes">{{ trans('langModule') }}</label>
                                            <div class="col-12">
                                                <select name="u_module_id" id="u_module_id" class="form-select">{!! $mod_opts !!}}</select>
                                            </div>
                                        </div>
                                        <div class="form-group mt-5">
                                            <div class="col-12 d-flex justify-content-end align-items-center">
                                                <input class="btn submitAdminBtn" type="submit" name="btnUsage" value="{{ trans('langSubmit') }}">
                                            </div>
                                        </div>

                                    </form>
                                </div>
                            </div>
                            <div class='d-none d-lg-block'>
                                <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
