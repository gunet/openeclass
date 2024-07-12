@extends('layouts.default')

@section('content')


<div class="col-12 main-section">
<div class='{{ $container }} module-container py-lg-0'>
    <div class="course-wrapper d-lg-flex align-items-lg-strech w-100">

                <div id="background-cheat-leftnav" class="col_sidebar_active d-flex justify-content-start align-items-strech ps-lg-0 pe-lg-0"> 
                    <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block ContentLeftNav">
                        @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                    </div>
                </div>

                <div class="col_maincontent_active">
                
                  <div class="row">

                        @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                        <div class="offcanvas offcanvas-start d-lg-none" tabindex="-1" id="collapseTools">
                            <div class="offcanvas-header">
                                <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                            </div>
                            <div class="offcanvas-body">
                                @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                            </div>
                        </div>
                                
                        @include('layouts.partials.legend_view')

                        @include('layouts.partials.show_alert') 

                        <div class='d-lg-flex gap-4 mt-4'>
                                        <div class='flex-grow-1'>
                        <div class='form-wrapper form-edit rounded'>
                          <form class='form-horizontal' role='form' action='{{ $base_url }}' method='post'>
                              <div class='form-group'>
                                  <div class='col-sm-12'>            
                                      <div class='checkbox'>
                                      <label class='label-container'>
                                          <input type='checkbox' name='index' value='yes'{{ $checked_index }}><span class='checkmark'></span> {{ trans('langGlossaryIndex') }}                               
                                        </label>
                                      </div>
                                  </div>
                              </div>

                              <div class='form-group mt-2'>
                                  <div class='col-sm-12'>            
                                      <div class='checkbox'>
                                      <label class='label-container'>
                                          <input type='checkbox' name='expand' value='yes'{{ $checked_expand }}><span class='checkmark'></span> {{ trans('langGlossaryExpand') }}                               
                                        </label>
                                      </div>
                                  </div>
                              </div>

                              <div class='form-group mt-4'>
                                  <div class='col-12 d-flex justify-content-start'>{!! $form_buttons !!}</div>
                              </div>   
                              {!! generate_csrf_token_form_field() !!}                
                          </form>
                        </div>
                        </div><div class='d-none d-lg-block'>
                            <img class='form-image-modules' src='{!! get_form_image() !!}' alt='form-image'>
                        </div>
                        </div>

                  </div>
                </div>
    </div>
  
</div>
</div>
@endsection

