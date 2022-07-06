@extends('layouts.default')


@section('content')


<div class="pb-3 pt-3">

    <div class="container-fluid main-container">

        <div class="row">

            <div class="col-xl-2 col-lg-2 col-md-0 col-sm-0 col-0 justify-content-center col_sidebar_active"> 
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block">
                    @include('layouts.partials.sidebar',['is_editor' => $is_editor])
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
                            @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                        </div>
                    </div>


                    <div class="col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                        <div class="row p-2"></div><div class="row p-2"></div>
                        <legend class="float-none w-auto py-2 px-4 notes-legend"><span class="pos_TitleCourse"><i class="fas fa-folder-open" aria-hidden="true"></i> {{$toolName}} @if($course_code)του μαθήματος <<strong>{{$currentCourseName}} <small>({{$course_code}})</small></strong>></span>@endif
                            <div class="manage-course-tools"style="float:right">
                                @if($is_editor == 1)
                                    @include('layouts.partials.manageCourse',[$urlAppend => $urlAppend,'coursePrivateCode' => $course_code])              
                                @endif
                            </div>
                        </legend>
                    </div>
               
                    @if($course_code)
                        <div class="row p-2"></div><div class="row p-2"></div>
                        <span class="control-label-notes ms-1">{{trans('langTeacher')}}: <small>{{course_id_to_prof($course_id)}}</small></span>
                        <div class="row p-2"></div><div class="row p-2"></div>
                    @endif

                        {!! isset($action_bar) ?  $action_bar : '' !!}<div class="row p-2"></div>

                        @if(Session::has('message'))
                        <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
                            <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                                {{ Session::get('message') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </p>
                        </div>
                        @endif

                        <div class='form-wrapper'>
                            <form class='form-horizontal' role='form' method='post' action='index.php?course={{ $course_code }}'>
                            <fieldset>                 
                                 <div class='row p-2'></div>              
                                <div class='form-group'>
                                    <label class='col-sm-6 control-label-notes'>{{ trans('langSocialBookmarksFunct') }}</label>
                                    <div class='col-sm-9'> 
                                        <div class='radio'>
                                            <label>
                                                <input type='radio' value='1' name='settings_radio'{{ $social_enabled ? " checked" : "" }}>{{ trans('langActivate') }}
                                            </label>
                                        </div>
                                        <div class='radio'>
                                            <label>
                                                <input type='radio' value='0' name='settings_radio' {{ $social_enabled ? "" : " checked" }}>{{ trans('langDeactivate') }}
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class='row p-2'></div>              
                                
                                <div class='form-group'>
                                    <div class='col-sm-9 col-sm-offset-3'>
                                        <input type='submit' class='btn btn-primary' name='submitSettings' value='{{ trans('langSubmit') }}' />
                                        <a href='index.php?course={{ $course_code }}' class='btn btn-secondary'>{{ trans('langCancel') }}</a>
                                    </div>
                                </div>
                            </fieldset>
                            {!! generate_csrf_token_form_field() !!}
                            </form>
                        </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection