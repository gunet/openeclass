
@extends('layouts.default')

@section('content')

<div class="pb-3 pt-3">

    <div class="container-fluid main-container">

        <div class="row">

            <div class="col-xl-2 col-lg-2 col-md-0 col-sm-0 col-0 justify-content-center col_sidebar_active"> 
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block">
                    @include('layouts.partials.sidebarAdmin')
                </div>
            </div>

            <div class="col-xl-10 col-lg-10 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    <nav class="navbar navbar-expand-lg navrbar_menu_btn">
                        <button type="button" id="menu-btn" class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block btn btn-primary menu_btn_button">
                            <i class="fas fa-align-left"></i>
                            <span></span>
                        </button>
                        
                    
                        <a class="btn btn-primary d-lg-none mr-auto" type="button" data-bs-toggle="offcanvas" href="#collapseTools" role="button" aria-controls="collapseTools" style="margin-top:-10px;">
                            <i class="fas fa-tools"></i>
                        </a>
                    </nav>

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    <div class="offcanvas offcanvas-start d-lg-none mr-auto" tabindex="-1" id="collapseTools" aria-labelledby="offcanvasExampleLabel">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                        @include('layouts.partials.sidebarAdmin')
                        </div>
                    </div>

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    {!! isset($action_bar) ?  $action_bar : '' !!}

                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                        <div class="form-wrapper shadow-lg p-3 mb-5 bg-body rounded bg-primary">
                            <form class="form-horizontal" role="form" method="post">
                                <div class="form-group mt-3">
                                    <div class="col-sm-12">
                                        <select name="selectedMonth" class="form-control">
                                            @for ($i = 0; $i < 12; $i++)
                                            <option value='{{ $option_date->modify( '-1 month' )->format('m Y') }}'>{{ trans("langMonths['".$option_date->format('m')."']").' '.$option_date->format('Y') }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <input class="btn btn-primary mt-2" type="submit" name="btnUsage" value="{{ trans('langSubmit') }}">
                                </div>
                            </form>
                        </div>
                    </div>
                    @if (isset($_POST['selectedMonth']))
                        @if (isset($monthly_data))
                            <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                                <div class='table-responsive mt-3'>
                                    <table class="announcements_table">
                                        <tbody>		
                                            <tr class='notes_thead'>
                                                <th colspan="2" class="text-center text-white">{{ trans('langReport') }}: {{ $msg_of_month }} {{ $y }}</th>
                                            </tr>
                                            <tr>
                                                <th class="left">{{ trans('langNbProf') }}: </th>
                                                <td>{{ $monthly_data->profesNum }}</td>
                                            </tr>
                                            <tr>
                                                <th class="left">{{ trans('langNbStudents') }}: </th>
                                                <td>{{ $monthly_data->studNum }}</td>
                                            </tr>
                                            <tr>
                                                <th class="left">{{ trans('langNbVisitors') }}: </th>
                                                <td>{{ $monthly_data->visitorsNum }}</td>
                                            </tr>
                                            <tr>
                                                <th class="left">{{ trans('langNbCourses') }}:  </th>
                                                <td>{{ $monthly_data->coursNum }}</td>
                                            </tr>
                                            <tr>
                                                <th class="left">{{ trans('langNbLogin') }}: </th>
                                                <td>{{ $monthly_data->logins }}</td>
                                            </tr>
                                            <tr>
                                                <td>{!! $monthly_data->details !!}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @else
                            <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                                <div class="alert alert-warning">
                                    {{ trans('langNoReport') }}: {{ $msg_of_month }} {{ $y }}
                                </div>
                            </div>
                        @endif
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
@endsection