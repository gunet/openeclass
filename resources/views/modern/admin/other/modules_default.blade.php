@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
<div class='{{ $container }}'>
        <div class="row rowMargin">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    @isset($action_bar)
                      {!! $action_bar !!}
                    @endisset
                    
                    @if(Session::has('message'))
                    <div class='col-12 all-alerts'>
                        <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @php 
                                $alert_type = '';
                                if(Session::get('alert-class', 'alert-info') == 'alert-success'){
                                    $alert_type = "<i class='fa-solid fa-circle-check fa-lg'></i>";
                                }elseif(Session::get('alert-class', 'alert-info') == 'alert-info'){
                                    $alert_type = "<i class='fa-solid fa-circle-info fa-lg'></i>";
                                }elseif(Session::get('alert-class', 'alert-info') == 'alert-warning'){
                                    $alert_type = "<i class='fa-solid fa-triangle-exclamation fa-lg'></i>";
                                }else{
                                    $alert_type = "<i class='fa-solid fa-circle-xmark fa-lg'></i>";
                                }
                            @endphp
                            
                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                {!! $alert_type !!}<span>
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach</span>
                            @else
                                {!! $alert_type !!}<span>{!! Session::get('message') !!}</span>
                            @endif
                            
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif

                  <div class='col-12'>
                    <div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>
                        {{ trans('langDefaultModulesHelp') }}</span>
                    </div>
                  </div>
                  
                 

                  <div class='col-lg-6 col-12 d-none d-md-none d-lg-block'>
                      <div class='col-12 h-100 left-form'></div>
                  </div>

                  <div class='col-lg-6 col-12'>
                    <div class='form-wrapper form-edit rounded'>
                      
                      <form class='form-horizontal' role='form' action='modules_default.php' method='post'>
                      <div class='row'>
                        @foreach ($modules as $mid => $minfo)
                        <div class='col-md-6 col-12'>
                          <div class='form-group mt-3'>
                            <div class='col-12 checkbox'>
                                <label class='d-inline-flex align-items-top @if(in_array($mid, $disabled)) not_visible @endif'>
                                   <input type='checkbox' name='module[{{ $mid }}]' value='1'
                                    @if (in_array($mid, $default)) checked @endif
                                    @if (in_array($mid, $disabled)) disabled @endif>
                                    <div class='mt-0 me-1'>{!! icon($minfo['image']) !!}</div>
                                    {{ $minfo['title'] }}
                                </label>
                            </div>
                          </div>
                        </div>
                        @endforeach
                      </div>
                      <div class='mt-3'></div>
                      {!! showSecondFactorChallenge() !!}
                      <div class='form-group mt-5'>
                        <div class='col-12 d-flex justify-content-center align-items-center'>
                          <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langSubmitChanges') }}'>
                        </div>
                      </div>
                      {!! generate_csrf_token_form_field() !!}
                    </form>
                  </div>
                  </div>
                
        </div>
</div>
</div>
@endsection
