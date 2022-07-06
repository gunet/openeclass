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
                    
                <div class="row p-5">

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
                        <small class='text-secondary'>{!! $breadcrumbs[2]['bread_text'] !!}</small>
                    </div>
                    <div class='row p-2'></div>
                    @endif
    
                    {!! isset($action_bar) ?  $action_bar : '' !!}

                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                        <div class='form-wrapper shadow-lg p-3 mb-5 bg-body rounded bg-primary'>
                            <form role='form' class='form-horizontal' action='{{ $_SERVER['SCRIPT_NAME'] }}?c={{ $course->code }}' method='post' onsubmit='return validateNodePickerForm();'>
                                <fieldset>
                                    <div class='form-group mt-3'>
                                        <label for='Faculty' class='col-sm-6 control-label-notes'>{{ trans('langFaculty') }}:</label>
                                        <div class='col-sm-12'>
                                            {!! $node_picker !!}
                                        </div>
                                    </div>
                                    <div class='form-group mt-3'>
                                        <label for='fcode' class='col-sm-6 control-label-notes'>{{ trans('langCode') }}</label>
                                        <div class='col-sm-12'>
                                            <input type='text' class='form-control' name='fcode' id='fcode' value='{{ $course->code }}'>
                                        </div>
                                    </div>
                                    <div class='form-group mt-3'>
                                        <label for='title' class='col-sm-6 control-label-notes'>{{ trans('langCourseTitle') }}:</label>
                                        <div class='col-sm-12'>
                                            <input type='text' class='form-control' name='title' id='title' value='{{ $course->title }}'>
                                        </div>
                                    </div>
                                    <div class='form-group mt-3'>
                                        <label for='titulary' class='col-sm-6 control-label-notes'>{{ trans('langTeachers') }}:</label>
                                        <div class='col-sm-12'>
                                            <input type='text' class='form-control' name='titulary' id='titulary' value='{{ $course->prof_name }}'>
                                        </div>
                                    </div>
                                    {!! showSecondFactorChallenge() !!}
                                    {!! generate_csrf_token_form_field() !!}    
                                    <div class='form-group mt-3'>
                                        <div class='col-sm-10 col-sm-offset-4'>
                                            <input class='btn btn-primary' type='submit' name='submit' value='{{ trans('langModify') }}'>
                                        </div>
                                    </div>
                                </fieldset>
                            </form>
                        </div>  
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>              
@endsection