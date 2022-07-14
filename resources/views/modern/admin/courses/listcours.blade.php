@extends('layouts.default')

@section('content')

<div class="pb-3 pt-3">

    <div class="container-fluid main-container">

        <div class="row">

            <div id="background-cheat-leftnav" class="col-xl-2 col-lg-3 col-md-0 col-sm-0 col-0 justify-content-center col_sidebar_active"> 
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block">
                    @include('layouts.partials.sidebarAdmin')
                </div>
            </div>

            <div class="col-xl-10 col-lg-9 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active">
                    
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

                    <table id='course_results_table' class='announcements_table'>
                        <thead>
                            <tr class='notes_thead'>
                            <th class='text-white'>{{ trans('langCourseCode') }}</th>
                            <th class='text-white'>{{ trans('langGroupAccess') }}</th>
                            <th class='text-white'>{{ trans('langFaculty') }}</th>
                            <th class='text-white'>{!! icon('fa-cogs') !!}</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                    @if (isset($_GET['formsearchfaculte']) and $_GET['formsearchfaculte'] and is_numeric(getDirectReference($_GET['formsearchfaculte'])))
                        <div align='right' style='margin-top: 60px; margin-bottom:10px;'>
                            <form action='multieditcourse.php' method='post'>
                                <!--redirect all request vars towards action-->
                                @foreach ($_REQUEST as $key => $value)
                                    <input type='hidden' name='{!! q($key) !!}' value='{!! q($value) !!}'>
                                @endforeach

                                <input class='btn btn-primary' type='submit' name='move_submit' value='{{ trans('langChangeDepartment') }}'>
                                {!! generate_csrf_token_form_field() !!}
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
