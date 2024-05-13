@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
  <div class='{{ $container }} main-container'>
        <div class="row m-auto">

            @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

            @include('layouts.partials.legend_view')

            @if(isset($action_bar))
                {!! $action_bar !!}
            @else
                <div class='mt-4'></div>
            @endif

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
                    
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
            @endif

                  
          <div class='col-lg-6 col-12'>
            <div class='form-wrapper form-edit border-0 px-0'>
              <form class='form-horizontal' role='form' action='modules.php' method='post'>
                @if(!get_config('show_always_collaboration'))
                  <div class='row m-auto mb-4'>
                    <div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>
                        {{ trans('langDisableModulesHelp') }}</span>
                    </div>
                    @foreach ($modules as $mid => $minfo)
                      <div class='col-md-6 col-12'>
                        <div class='form-group mt-3'>
                          <div class='col-12 checkbox'>
                            <label class='label-container'>
                              <input type='checkbox' name='moduleDisable[{{ $mid }}]' value='1'{{ in_array($mid, $disabled)? ' checked': '' }}>
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
                        {{ trans('langDisableCollaborationModulesHelp') }}</span>
                    </div>
                    @foreach ($modules_collaboration as $mid => $minfo)
                      <div class='col-md-6 col-12'>
                        <div class='form-group mt-3'>
                          <div class='col-12 checkbox'>
                            <label class='label-container'>
                              <input type='checkbox' name='moduleDisableCollaboration[{{ $mid }}]' value='1'{{ in_array($mid, $disabledCollaboration)? ' checked': '' }}>
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