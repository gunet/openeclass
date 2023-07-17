@extends('layouts.default')

@section('content')

<div class="col-12 basic-section p-xl-5 px-lg-3 py-lg-5">

        <div class="row rowMargin">

            <div class="col-12 col_maincontent_active_Homepage">
                    
                <div class="row">

                    <div class='col-12'>
                        <h1>{!! $toolName !!}</h1>
                    </div>

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

                    
                    @if ($user_registration) 
                        @if (!in_array($auth, $authmethods)) 
                            <div class='col-12'><div class='alert alert-danger'><i class='fa-solid fa-circle-xmark fa-lg'></i><span>{{ trans('langCannotRegister') }}</span></div></div>
                        @elseif (!$_SESSION['u_prof'] and !$alt_auth_stud_reg)
                            <div class='col-12'><div class='alert alert-danger'><i class='fa-solid fa-circle-xmark fa-lg'></i><span>{{ trans('langCannotRegister') }}</span></div></div>
                        @elseif ($_SESSION['u_prof'] and !$alt_auth_prof_reg)
                            <div class='col-12'><div class='alert alert-danger'><i class='fa-solid fa-circle-xmark fa-lg'></i><span>{{ trans('langCannotRegister') }}</span></div></div>
                        @else

                            <div class='col-12 mt-4'>
                                <div class='row rowMargin row-cols-1 row-cols-lg-2 g-lg-5'>
                                    <div class='col-lg-6 col-12'>
                                        <div class='form-wrapper form-edit px-0 border-0'>
                                            <form class='form-horizontal' role='form' method='post' action='altsearch.php'>
                                                <fieldset> {{ $auth_instructions }}
                                                    <div class='row'>
                                                        <div class='col-12 px-lg-3 px-0'>
                                                            <div class='form-group'>
                                                                <label for='UserName' class='col-sm-12 control-label-notes'>{{ trans('langUsername') }}</label>
                                                                <div class='col-sm-12'>
                                                                    <input class='form-control' type='text' size='30' maxlength='30' placeholder="{{ trans('langUserNotice') }}" name='uname' autocomplete='off' {{ $set_uname }}>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class='row'>
                                                        <div class='col-12 px-lg-3 px-0'>
                                                            <div class='form-group mt-4'>
                                                                <label for='Pass' class='col-sm-12 control-label-notes'>{{ trans('langPass') }}</label>
                                                                <div class='col-sm-12'>
                                                                    <input class='form-control' type='password' size='30' maxlength='30' name='passwd' autocomplete='off' placeholder='{{ trans('langPass') }}'>
                                                                </div>
                                                            </div>   
                                                        </div>
                                                    </div>                 

                                                    <input type='hidden' name='auth' value='{{ $auth }}'>

                                                    <div class='row'>
                                                        <div class='col-12 px-lg-3 px-0'>
                                                            <div class='form-group mt-5'>
                                                                
                                                                    {!! $form_buttons !!}
                                                                    @if (isset($_SESSION['prof']) and $_SESSION['prof']) 
                                                                        <input type='hidden' name='p' value='1'>
                                                                    @endif
                                                                
                                                            </div>
                                                        </div>
                                                    </div>
                                                </fieldset>
                                            </form>
                                        </div>   
                                        
                                    </div>
                                    <div class='col-lg-6 col-12'>
                                        <img src='{{ $urlAppend }}template/modern/img/RegImg.png' />
                                    </div>
                                </div>
                            </div>
                        @endif
                    @else
                    <div class='col-12'>
                        <div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>{{ trans('langCannotRegister') }}</span></div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    
</div>
@endsection
    
    
