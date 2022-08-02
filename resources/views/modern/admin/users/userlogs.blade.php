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

                    {!! $users_login_data !!}



                    {!! isset($action_bar) ?  $action_bar : '' !!}
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                        <div class='form-wrapper shadow-sm p-3 mt-5 rounded'>
                            
                            <form class='form-horizontal' role='form' method='get' action='{{ $_SERVER['SCRIPT_NAME'] }}'>  
                                <input type="hidden" name="u" value="{{ $u }}">
                                <div class='form-group mt-3' data-date='{{ $user_date_start }}' data-date-format='dd-mm-yyyy'>
                                    <label class='col-sm-6 control-label-notes'>{{ trans('langStartDate') }}:</label>
                                    <div class='col-sm-12'>               
                                        <input class='form-control' name='user_date_start' id='user_date_start' type='text' value = '{{ $user_date_start }}'>
                                    </div>
                                </div>
                                <div class='form-group mt-3' data-date= '{{ $user_date_end }}' data-date-format='dd-mm-yyyy'>
                                    <label class='col-sm-6 control-label-notes'>{{ trans('langEndDate') }}:</label>
                                    <div class='col-sm-12'>
                                        <input class='form-control' name='user_date_end' id='user_date_start' type='text' value= '{{ $user_date_end }}'>
                                    </div>
                                </div>
                                <div class='form-group mt-3'>  
                                    <label class='col-sm-6 control-label-notes'>{{ trans('langLogTypes') }}:</label>
                                    <div class='col-sm-12'>{!! selection($log_types, 'logtype', $logtype, "class='form-control'") !!}</div>
                                </div>
                                <div class="form-group mt-3">
                                    <label class="col-sm-6 control-label-notes">{{ trans('langCourse') }}:</label>
                                    <div class="col-sm-12">{!! selection($cours_opts, 'u_course_id', $u_course_id, "class='form-control'") !!}</div>
                                </div>
                                <div class="form-group mt-3">
                                    <label class="col-sm-6 control-label-notes">{{ trans('langLogModules') }}:</label>
                                    <div class="col-sm-12">{!! selection($module_names, 'u_module_id', '', "class='form-control'") !!}</div>
                                </div>
                                <div class="form-group mt-3">
                                    <div class="col-sm-10 col-sm-offset-9">
                                        <input class="btn btn-primary" type="submit" name="submit" value="{{ trans('langSubmit') }}">
                                        <a class="btn btn-secondary" href="listusers.php" data-placement="bottom" data-toggle="tooltip" title="" data-original-title="{{ trans('langBack') }}" >
                                            <span class="fa fa-reply space-after-icon"></span><span class="hidden-xs">{{ trans('langBack') }}</span>
                                        </a>
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