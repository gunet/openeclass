@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
<div class='{{ $container }}'>
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
                            
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif


                    

                   
                    
                    <div class='col-lg-6 col-12'>
                      <div class='form-wrapper form-edit border-0 px-0'>
                        
                          <form class='form-horizontal' role='form' method='post' action='{{ $urlServer }}modules/admin/password.php'>
                            <fieldset>      
                              <input type='hidden' name='userid' value='{{ $_GET['userid'] }}'>
                              <div class='form-group'>
                              <label class='col-sm-12 control-label-notes'>{{ trans('langNewPass1') }}</label>
                                <div class='col-sm-12'>
                                    <input class='form-control' placeholder="{{ trans('langNewPass1') }}" type='password' size='40' name='password_form' value='' id='password' autocomplete='off'>
                                    &nbsp;
                                    <span id='result'></span>
                                </div>
                              </div>
                              <div class='form-group mt-4'>
                                <label class='col-sm-12 control-label-notes'>{{ trans('langNewPass2') }}</label>
                                <div class='col-sm-12'>
                                    <input class='form-control' placeholder="{{ trans('langNewPass2') }}" type='password' size='40' name='password_form1' value='' autocomplete='off'>
                                </div>
                              </div>
                              <div class='col-12 mt-5 d-flex justify-content-end align-items-center'>
                                {!! showSecondFactorChallenge() !!}
                               
                                   
                                        <input class='btn submitAdminBtn' type='submit' name='changePass' value='{{ trans('langModify') }}'>
                                   
                                         <a class='btn cancelAdminBtn ms-1' href='{{ $urlServer }}modules/admin/edituser.php?u={{ urlencode(getDirectReference($_REQUEST['userid'])) }}'>{{ trans('langCancel') }}</a>
                                    
                                
                                
                               
                              </div>      
                            </fieldset>
                            {!! generate_csrf_token_form_field() !!}    
                          </form>
                      </div>
                    </div>
                    <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
                    <img class='form-image-modules' src='{{$urlAppend}}template/modern/img/form-image.png' alt='form-image'>
                    </div>
                
        </div>
</div>
</div>            
@endsection