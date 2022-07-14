@extends('layouts.default')

@section('content')


<div class="pb-3 pt-3">
  <div class="container-fluid main-container">
    <div class="row">

                <div id="background-cheat-leftnav" class="col-xl-2 col-lg-3 col-md-0 col-sm-0 col-0 justify-content-center col_sidebar_active"> 
                    <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block">
                        @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                    </div>
                </div>

                <div class="col-xl-10 col-lg-9 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active">
                
                  <div class="row p-lg-5 p-md-5 ps-1 pe-2 pt-5 pb-5">

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
                                
                        @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])



                        @if(Session::has('message'))
                        <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
                            <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                                {{ Session::get('message') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </p>
                        </div>
                        @endif


                        <div class='col-12'>
                        <div class='form-wrapper shadow-sm p-3 mt-5 rounded'>
                          <form class='form-horizontal' role='form' action='{{ $base_url }}' method='post'>
                              <div class='form-group'>
                                  <div class='col-sm-12'>            
                                      <div class='checkbox'>
                                        <label>
                                          <input type='checkbox' name='index' value='yes'{{ $checked_index }}> {{ trans('langGlossaryIndex') }}                               
                                        </label>
                                      </div>
                                  </div>
                              </div>
                              <div class="row p-2"></div>
                              <div class='form-group'>
                                  <div class='col-sm-12'>            
                                      <div class='checkbox'>
                                        <label>
                                          <input type='checkbox' name='expand' value='yes'{{ $checked_expand }}> {{ trans('langGlossaryExpand') }}                               
                                        </label>
                                      </div>
                                  </div>
                              </div>
                              <div class="row p-2"></div>
                              <div class='form-group'>
                                  <div class='col-sm-12'>{!! $form_buttons !!}</div>
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

