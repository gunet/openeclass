@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
<div class='{{ $container }} main-container'>
        <div class="row m-auto">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view')

                    @isset($action_bar)
                      {!! $action_bar !!}
                    @endisset
                    
                    @include('layouts.partials.show_alert') 

                 
                  <div class='col-lg-6 col-12'>
                    <div class='form-wrapper form-edit border-0 px-0'>
                      <form class='form-horizontal' role='form' action='modules_default.php' method='post'>
                        @if(!get_config('show_always_collaboration'))
                          <div class='row m-auto mb-4'>
                            <div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>
                                {{ trans('langDefaultModulesHelp') }}</span>
                            </div>
                            @foreach ($modules as $mid => $minfo)
                              <div class='col-md-6 col-12'>
                                <div class='form-group mt-3'>
                                  <div class='col-12 checkbox'>
                                      <label class='label-container @if(in_array($mid, $disabled)) d-none @endif'>
                                        <input type='checkbox' name='module[{{ $mid }}]' value='1'
                                          @if (in_array($mid, $default)) checked @endif
                                          @if (in_array($mid, $disabled)) disabled @endif>
                                          <span class='checkmark'></span>
                                          <div class='mt-0 me-0'>{!! icon($minfo['image']) !!}</div>
                                          {{ $minfo['title'] }}
                                      </label>
                                  </div>
                                </div>
                              </div>
                            @endforeach
                          </div>
                        @endif

                        @if(get_config('show_collaboration'))
                          <div class='row m-auto'>
                              <div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>
                                {{ trans('langDefaultModulesCollaborationHelp') }}</span>
                            </div> 
                            @foreach ($modules_collaborations as $mid => $minfo)
                              <div class='col-md-6 col-12'>
                                <div class='form-group mt-3'>
                                  <div class='col-12 checkbox'>
                                      <label class='label-container @if(in_array($mid, $disabledCollaboration)) d-none @endif'>
                                        <input type='checkbox' name='moduleCollaboration[{{ $mid }}]' value='1'
                                          @if (in_array($mid, $defaultCollaboration)) checked @endif
                                          @if (in_array($mid, $disabledCollaboration)) disabled @endif>
                                          <span class='checkmark'></span>
                                          <div class='mt-0 me-0'>{!! icon($minfo['image']) !!}</div>
                                          {{ $minfo['title'] }}
                                      </label>
                                  </div>
                                </div>
                              </div>
                            @endforeach
                          </div>
                        @endif
                        
                        {!! showSecondFactorChallenge() !!}

                        <div class='form-group mt-5'>
                          <div class='col-12 d-flex justify-content-end align-items-center'>
                            <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langSubmitChanges') }}'>
                          </div>
                        </div>

                        {!! generate_csrf_token_form_field() !!}

                      </form>
                    </div>
                  </div>
                  <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
                    <img class='form-image-modules' src='{!! get_form_image() !!}' alt='form-image'>
                  </div>
                
        </div>
</div>
</div>
@endsection
