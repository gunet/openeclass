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

                    @if($breadcrumbs && count($breadcrumbs)>2)
                    <div class='row p-2'></div>
                    <div class="float-start">
                        <p class='control-label-notes'>{!! $breadcrumbs[1]['bread_text'] !!}</p>
                        <small class='text-secondary'>{!! $breadcrumbs[count($breadcrumbs)-1]['bread_text'] !!}</small>
                    </div>
                    <div class='row p-2'></div>
                    @endif

                    {!! isset($action_bar) ?  $action_bar : '' !!}

                    <div class="overflow-auto">
                        <table id='search_results_table' class='announcements_table display'>
                            <thead class='notes_thead'>
                                <tr>
                                <th class='text-white' width='150'>{{ trans('langSurname') }}</th>
                                <th class='text-white' width='100'>{{ trans('langName') }}</th>
                                <th class='text-white' width='170'>{{ trans('langUsername') }}</th>
                                <th class='text-white'>{{ trans('langEmail') }}</th>
                                <th class='text-white'>{{ trans('langProperty') }}</th>
                                <th width='130' class='text-white centertext-center'>{!! icon('fa-gears') !!}</th>
                                </tr>
                            </thead>
                            <!-- DO NOT DELETE THESE EMPTY COLUMNS -->
                            <tfoot>
                                <tr>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                </tr>
                            </tfoot>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                    <div align='right' style='margin-top: 60px; margin-bottom:10px;'>
                        <!--Edit all function-->
                        <form action='multiedituser.php' method='post'> 
                        <!--redirect all request vars towards delete all action-->
                        @foreach ($_REQUEST as $key => $value)
                            <input type='hidden' name='{{ $key }}' value='{{ $value }}'>
                        @endforeach
                        @if (isset($_GET['department']) && $_GET['department'] && is_numeric($_GET['department'])) {
                            <input class='btn btn-primary' type='submit' name='move_submit' value='{{ trans('langChangeDepartment') }}'>
                        @endif
                        <input class='btn btn-primary' type='submit' name='dellall_submit' value='{{ trans('langDelList') }}'>
                        <input class='btn btn-primary' type='submit' name='activate_submit' value='{{ trans('langAddSixMonths') }}'>
                        {!! generate_csrf_token_form_field() !!}
                        </form>
                    </div>   
                </div>
            </div>
        </div>
    </div>
</div>         
@endsection