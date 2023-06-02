@extends('layouts.default')

@section('content')

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div class="col-12 justify-content-center col_maincontent_active_Homepage">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    @if(Session::has('message'))
                    <div class='col-12 all-alerts'>
                        <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach
                            @else
                                {!! Session::get('message') !!}
                            @endif
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif

                    {!! $action_bar !!}

                    @if ($user_registration) 
                        @if (!in_array($auth, $authmethods)) 
                            <div class='col-12'><div class='alert alert-danger'>{{ trans('langCannotRegister') }}</div></div>
                        @elseif (!$_SESSION['u_prof'] and !$alt_auth_stud_reg)
                            <div class='col-12'><div class='alert alert-danger'>{{ trans('langCannotRegister') }}</div></div>
                        @elseif ($_SESSION['u_prof'] and !$alt_auth_prof_reg)
                            <div class='col-12'><div class='alert alert-danger'>{{ trans('langCannotRegister') }}</div></div>
                        @else
                        
                        <div class='col-lg-6 col-12 ms-auto me-auto'>
                            <div class='form-wrapper form-edit rounded'>
                                <form class='form-horizontal' role='form' method='post' action='altsearch.php'>
                                    <fieldset> {{ $auth_instructions }}
                                            <div class='form-group'>
                                                <label for='UserName' class='col-sm-12 control-label-notes'>{{ trans('langUsername') }}</label>
                                                <div class='col-sm-12'>
                                                    <input class='form-control' type='text' size='30' maxlength='30' placeholder="{{ trans('langUserNotice') }}" name='uname' autocomplete='off' {{ $set_uname }}>
                                                </div>
                                            </div>
                                            <div class='form-group mt-4'>
                                                <label for='Pass' class='col-sm-12 control-label-notes'>{{ trans('langPass') }}</label>
                                                <div class='col-sm-12'>
                                                    <input class='form-control' type='password' size='30' maxlength='30' name='passwd' autocomplete='off' placeholder='{{ trans('langPass') }}'>
                                                </div>
                                            </div>                    
                                        <input type='hidden' name='auth' value='{{ $auth }}'>
                                        <div class='form-group mt-5'>
                                            <div class='col-12 d-flex justify-content-center align-items-center'>
                                                {!! $form_buttons !!}
                                                @if (isset($_SESSION['prof']) and $_SESSION['prof']) 
                                                    <input type='hidden' name='p' value='1'>
                                                @endif
                                            </div>
                                        </div>
                                    </fieldset>
                                </form>
                            </div>   
                        </div> 
                        @endif
                    @else
                    <div class='col-12'>
                        <div class='alert alert-info'>{{ trans('langCannotRegister') }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
    
    
