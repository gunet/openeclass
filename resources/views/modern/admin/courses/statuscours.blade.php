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
                          <form role='form' class='form-horizontal' action="{{ $_SERVER['SCRIPT_NAME'] }}?c={{$course->code}}" method='post'>                
                              <div class='form-group'>
                                      <label for='localize' class='col-sm-6 control-label-notes'>{{ trans('langAvailableTypes') }}:</label>
                                  
                                      
                                      <div class='row mt-3'>
                                          <div class='col-1'>
                                            <input class='mt-2' id='courseopen' type='radio' name='formvisible' value='2'{!! $course->visible == 2 ? ' checked': '' !!}>
                                          </div>
                                          <div class='col-1'>
                                            {!! course_access_icon(COURSE_OPEN) !!}
                                          </div>     
                                          <div class='col-10'> 
                                            <span class='d-inline'>{{ trans('langPublic') }}</span>
                                          </div>
                                      </div>
                                      
                                      <div class='row mt-3'>
                                        <div class='col-1'>
                                          <input id='coursewithregistration' type='radio' name='formvisible' value='1'{!! $course->visible == 1 ? ' checked': '' !!}>
                                        </div>
                                        <div class='col-1'>
                                          {!! course_access_icon(COURSE_REGISTRATION) !!}
                                        </div>
                                        <div class='col-10'> 
                                          <span class='d-inline'>{{ trans('langPrivOpen') }}</span>
                                        </div> 
                            
                                      </div>


                                      <div class='row mt-3'>
                                        <div class='col-1'>
                                          <input id='courseclose' type='radio' name='formvisible' value='0'{!! $course->visible == 0 ? ' checked': '' !!}>
                                        </div> 
                                        <div class='col-1'>
                                          {!! course_access_icon(COURSE_CLOSED) !!}
                                        </div>
                                        <div class='col-10'>
                                          <span class='d-inline'>
                                              {{ trans('langClosedCourseShort') }}
                                          </span>
                                        </div>
                                      </div>


                                      <div class='row mt-3'>
                                        <div class='col-1'>
                                          <input id='courseinactive' type='radio' name='formvisible' value='3'{!! $course->visible == 3 ? ' checked': '' !!}>
                                        </div> 
                                        <div class='col-1'>
                                          {!!  course_access_icon(COURSE_INACTIVE) !!}
                                        </div>
                                        <div class='col-10'>
                                          <span class='help-block'>
                                              {{ trans('langInactiveCourse') }}
                                          </span>
                                        </div>
                                      </div>                   
                                 
                              </div>
                              <div class='form-group mt-3'>
                                  <div class='col-sm-10 col-sm-offset-2'>
                                      <input class='btn btn-primary' type='submit' name='submit' value='{{ trans('langModify') }}'>
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