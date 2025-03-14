@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} main-container'>
        <div class="row m-auto">
                <div class='col-12'>
                    <h1>{!! $toolName !!}</h1>
                </div>

                @include('layouts.partials.show_alert') 

                @if ($user_registration)
                    @if (!in_array($auth, $authmethods) || !$alt_auth_stud_reg)
                        <div class='col-12'>
                            <div class='alert alert-info'>
                                <i class='fa-solid fa-circle-xmark fa-lg'></i><span>{{ trans('langCannotRegister') }}</span>
                            </div>
                        </div>
                    @else
                        <div class='col-12 mt-4'>
                            <div class='row row-cols-1 row-cols-lg-2 g-lg-5 g-4'>
                                <div class='col-lg-6 col-12'>
                                    <div class='form-wrapper form-edit px-0 border-0'>
                                        <form class='form-horizontal' role='form' method='post' action='altsearch.php'>
                                                @if($auth_instructions)<h4>{{ $auth_instructions }}</h4>@endif
                                                <div class='row'>
                                                    <div class='col-12 px-3'>
                                                        <div class='form-group mt-4'>
                                                            <label for='UserName' class='col-sm-12 control-label-notes'>{{ trans('langUsername') }} <span class='asterisk Accent-200-cl'>(*)</span></label>
                                                            <div class='col-sm-12'>
                                                                <input id='UserName' class='form-control' type='text' size='30' maxlength='30' placeholder="{{ trans('langUserNotice') }}" name='uname' autocomplete='off' {{ $set_uname }}>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class='row'>
                                                    <div class='col-12 px-3'>
                                                        <div class='form-group mt-4'>
                                                            <label for='Pass' class='col-sm-12 control-label-notes'>{{ trans('langPass') }} <span class='asterisk Accent-200-cl'>(*)</span></label>
                                                            <div class='col-sm-12'>
                                                                <input id='Pass' class='form-control' type='password' size='30' maxlength='30' name='passwd' autocomplete='off' placeholder='{{ trans('langPass') }}'>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <input type='hidden' name='auth' value='{{ $auth }}'>

                                                <div class='row'>
                                                    <div class='col-12 px-3'>
                                                        <div class='form-group mt-5'>
                                                            {!! $form_buttons !!}
                                                        </div>
                                                    </div>
                                                </div>
                                        </form>
                                    </div>
                                </div>
                                <div class='col-lg-6 col-12 d-none d-lg-block'>
                                    <img class='form-image-modules form-image-registration' src='{!! get_registration_form_image() !!}' alt='{{ trans('langRegistration') }}'>
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
@endsection
