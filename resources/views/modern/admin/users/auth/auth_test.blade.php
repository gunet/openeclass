@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} main-container'>
            <div class="row m-auto">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view')

                    <div class='mt-4'></div>

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

                        <form class='form-horizontal' name='authmenu' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}'>
                            <input type='hidden' name='auth' value='{{ $auth }}'>
                            <fieldset>
                                <div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>{{ trans('langTestAccount') }} ({{ $auth_ids[$auth] }})</span></div>

                                <div class='form-group mt-4'>
                                    <label for='test_username' class='col-sm-12 control-label-notes'>{{ trans('langUsername') }}:</label>
                                    <div class='col-sm-12'>
                                        <input class='form-control' type='text' name='test_username' id='test_username' value='{{ canonicalize_whitespace($test_username) }}' autocomplete='off'>
                                    </div>
                                </div>



                                <div class='form-group mt-4'>
                                    <label for='test_password' class='col-sm-12 control-label-notes'>{{ trans('langPass') }}:</label>
                                    <div class='col-sm-12'>
                                        <input class='form-control' type='password' name='test_password' id='test_password' value='{{ $test_password }}' autocomplete='off'>
                                    </div>
                                </div>



                                <div class='form-group mt-5'>
                                    <div class='col-12 d-flex justify-content-end align-items-center gap-2'>
                                       <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langConnTest') }}'>
                                        <a class='btn cancelAdminBtn' href='auth.php'>{{ trans('langCancel') }}</a>
                                    </div>
                                </div>
                            </fieldset>
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
