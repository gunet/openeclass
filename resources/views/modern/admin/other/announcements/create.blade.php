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

                    {!! isset($action_bar) ?  $action_bar : '' !!}

                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                        <div class='form-wrapper shadow-lg p-3 mb-5 bg-body rounded bg-primary'>
                            <form role='form' class='form-horizontal' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}'>
                                @if (isset($announcement))
                                    <input type='hidden' name='id' value='{{ $announcement->id }}'>
                                @endif    
                                <div class='mt-3 form-group{{ Session::hasError('title') ? " has-error" : "" }}'>
                                    <label for='title' class='col-sm-6 control-label-notes'>{{ trans('langTitle') }}:</label>
                                    <div class='col-sm-12'>
                                        <input class='form-control' type='text' name='title' value='{{ isset($announcement) ? $announcement->title : "" }}'>
                                        {!! Session::getError('title', "<span class='help-block'>:message</span>") !!}
                                    </div>
                                </div>
                                <div class='mt-3 form-group'>
                                    <label for='newContent' class='col-sm-6 control-label-notes'>{{ trans('langAnnouncement') }}:</label>
                                    <div class='col-sm-12'>{!! $newContentTextarea !!}</div>
                                </div>
                                <div class='mt-3 form-group'>
                                    <label class='col-sm-6 control-label-notes'>{{ trans('langLanguage') }}:</label>    
                                    <div class='col-sm-12'>
                                        {!! lang_select_options('lang_admin_ann', "class='form-control'", isset($announcement) ? $announcement->lang : false) !!}
                                    </div>
                                    <small class='text-right'>
                                        <span class='help-block'>{{ trans('langTipLangAdminAnn') }}</span>
                                    </small>
                                </div>
                                <div class='mt-3 form-group'>
                                    <label for='startdate' class='col-sm-6 control-label-notes'>{{ trans('langStartDate') }} :</label>
                                    <div class='col-sm-12'>
                                        <div class='input-group'>
                                            <span class='input-group-addon'>
                                                <input type='checkbox' name='startdate_active'{{ $start_checkbox }}>
                                            </span>
                                            <input class='form-control' name='startdate' id='startdate' type='text' value='{{ $startdate }}' disabled>
                                        </div>
                                    </div>
                                </div>
                                <div class='mt-3 form-group'>
                                    <label for='enddate' class='col-sm-6 control-label-notes'>{{ trans('langEndDate') }} :</label>
                                    <div class='col-sm-12'>
                                        <div class='input-group'>
                                            <span class='input-group-addon'>
                                                <input type='checkbox' name='enddate_active'{{ $end_checkbox }} disabled>
                                            </span>
                                            <input class='form-control' name='enddate' id='enddate' type='text' value='{{ $enddate }}' disabled>
                                        </div>
                                    </div>
                                </div>
                                <div class='mt-3 form-group'>
                                    <div class='col-sm-10 col-sm-offset-2'>
                                        <div class='checkbox'>
                                            <label>
                                                <input type='checkbox' name='show_public'{{ $checked_public }}> {{ trans('langVisible') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class='mt-3 form-group'>
                                    <div class='col-sm-offset-2 col-sm-10'>
                                        <input id='submitAnnouncement' class='btn btn-primary' type='submit' name='submitAnnouncement' value='{{ trans('langSubmit') }}'>
                                    </div>
                                </div>
                                {!! generate_csrf_token_form_field() !!}
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection