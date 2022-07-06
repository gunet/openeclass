@extends('layouts.default')

@section('content')

<div class="pb-3 pt-3">

    <div class="container-fluid main-container">

        <div class="row">

            <div id="background-cheat-leftnav" class="col-xl-2 col-lg-2 col-md-0 col-sm-0 col-0 justify-content-center col_sidebar_active"> 
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

                    

                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                       <div class='alert alert-info'>{{ trans('langAskManyUsersToCourses') }}</div>
                    </div>


                    {!! isset($action_bar) ?  $action_bar : '' !!}
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                        <div class='form-wrapper shadow-sm p-3 mt-5 rounded'>
                        
                        <form role='form' class='form-horizontal' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}'>
                            <fieldset>
                            <h4 class='control-label-notes'>{{ trans('langUsersData') }}</h4>
                                <div class='form-group'>
                                    <div class='radio'>
                                    <label>
                                        <input type='radio' name='type' value='uname' checked>{{ trans('langUsername') }}
                                    </label>
                                    </div>
                                <div class='col-sm-12'>{!! text_area('user_info', 10, 30, '') !!}</div>
                            </div>
                            </fieldset>
                            <div class='row p-2'></div>
                            <fieldset>
                            <h4 class='control-label-notes'>{{ trans('langCourseCodes') }}</h4>
                            <div class='form-group'>
                                    <div class='col-sm-12'>{!! text_area('courses_codes', 10, 30, '') !!}</div>
                                </div>
                                {!! showSecondFactorChallenge() !!}
                                <div class='row p-2'></div>
                                <div class='col-sm-10 col-sm-offset-2'>
                                    <input class='btn btn-primary' type='submit' name='submit' value='{{ trans('langRegistration') }}'>
                                </div>
                            </fieldset>
                            {!! generate_csrf_token_form_field() !!}
                        </form>
                    </div></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection