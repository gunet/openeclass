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

                    

                    @if(Session::has('message'))
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
                        <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            {{ Session::get('message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </p>
                    </div>
                    @endif

                    {!! isset($action_bar) ?  $action_bar : '' !!}
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                        <div class='form-wrapper shadow-sm p-3 mt-5 rounded'>
                            
                        <form role='form' class='form-horizontal' method='post' action='autoenroll.php'>
                            <input type='hidden' name='add' value='{{ $type }}'>
                            @if (isset($_GET['edit']))
                                <input type='hidden' name='id' value='{{ $_GET['edit'] }}'>
                            @endif           
                            <fieldset>
                                <div class='form-group'>
                                    <label class='col-sm-6 control-label-notes'>{{ trans('langStatus') }}:</label>   
                                    <div class='col-sm-12'>
                                        <p class='form-control-static'>{{ $type == USER_STUDENT ? trans('langStudents') : trans('langTeachers') }}</p>
                                    </div>
                                </div>
                                <div class='row p-2'></div>
                                <div class='form-group'>
                                    <label for='title' class='col-sm-6 control-label-notes'>{{ trans('langFaculty') }}:</label>   
                                    <div class='col-sm-12 form-control-static'>
                                        {!! $htmlTree !!}
                                    </div>
                                </div>
                                <div class='row p-2'></div>
                                <div class='form-group'>
                                    <label for='title' class='col-sm-6 control-label-notes'>{{ trans('langAutoEnrollCourse') }}:</label>   
                                    <div class='col-sm-12'>
                                        <input class='form-control' type='hidden' id='courses' name='courses' value=''>
                                    </div>
                                </div>
                                <div class='row p-2'></div>
                                <div class='form-group'>
                                    <label for='title' class='col-sm-6 control-label-notes'>{{ trans('langAutoEnrollDepartment') }}:</label>   
                                    <div class='col-sm-12 form-control-static'>                  
                                        <div id='nodCnt2'>
                                        @foreach ($deps as $key => $dep)
                                            <p id='nc_{{ $key }}'>
                                                <input type='hidden' name='rule_deps[]' value='{{ $dep }}'>
                                                {{ $tree->getFullPath(getDirectReference($dep)) }}
                                                &nbsp;
                                                <a href='#nodCnt2'>
                                                    <span class='fa fa-times' data-bs-toggle='tooltip' data-original-title='{{ trans('langNodeDel') }}' data-bs-placement='top' title='{{ trans('langNodeDel') }}'></span>
                                                </a>
                                            </p>
                                        @endforeach
                                        </div>
                                        <div>
                                            <p>
                                                <a id='ndAdd2' href='#add'>
                                                    <span class='fa fa-plus' data-bs-toggle='tooltip' data-bs-placement='top' title='{{ trans('langNodeAdd') }}'></span>
                                                </a>
                                            </p>
                                        </div>
                                        <div class='modal fade' id='treeCourseModal' tabindex='-1' role='dialog' aria-labelledby='treeModalLabel' aria-hidden='true'>
                                            <div class='modal-dialog'>
                                                <div class='modal-content'>
                                                    <div class='modal-header'>
                                                        <button type='button' class='close treeCourseModalClose'>
                                                            <span aria-hidden='true'>&times;</span>
                                                            <span class='sr-only'>{{ trans('langCancel') }}</span>
                                                        </button>
                                                        <h4 class='modal-title' id='treeCourseModalLabel'>{{ trans('langNodeAdd') }}</h4>
                                                    </div>
                                                    <div class='modal-body'>
                                                        <div id='js-tree-course'></div>
                                                    </div>
                                                    <div class='modal-footer'>
                                                        <button type='button' class='btn btn-secondary treeCourseModalClose'>{{ trans('langCancel') }}</button>
                                                        <button type='button' class='btn btn-primary' id='treeCourseModalSelect'>{{ trans('langSelect') }}</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>                    
                                    </div>
                                </div>
                                <div class='row p-2'></div>
                                <div class='form-group'>
                                    <div class='col-sm-12 checkbox'>
                                        <label>
                                            <input type='checkbox' name='apply' id='apply' value='1' checked='1'>
                                            {{ trans('langApplyRule') }}
                                        </label>
                                    </div>
                                </div>
                                <div class='row p-2'></div>
                                {!! showSecondFactorChallenge() !!}
                                <div class='form-group'>
                                    <div class='col-sm-10 col-sm-offset-2'>
                                        <input class='btn btn-primary' type='submit' name='submit' value='{{ trans('langSubmit') }}'>
                                        <a href='autoenroll.php' class='btn btn-secondary'>{{ trans('langCancel') }}</a>    
                                    </div>
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