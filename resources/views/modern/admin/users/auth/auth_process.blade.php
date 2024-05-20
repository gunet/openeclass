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

                    <form class='form-horizontal' name='authmenu' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}'>
                    <fieldset>
                        <input type='hidden' name='auth' value='{{ intval($auth) }}'>
                        @switch ($auth)
                            @case (1)
                                @include('admin.users.auth.methods.eclass')
                            @break
                            @case(2)
                                @include('admin.users.auth.methods.pop3')
                                @include('admin.users.auth.methods.eclass')
                            @break
                            @case(3)
                                @include('admin.users.auth.methods.imap')
                                @include('admin.users.auth.methods.eclass')
                            @break
                            @case(4)
                                @include('admin.users.auth.methods.ldap')
                                @include('admin.users.auth.methods.eclass')
                            @break
                            @case(5)
                                @include('admin.users.auth.methods.db')
                                @include('admin.users.auth.methods.eclass')
                            @break
                            @case(6)
                                @include('admin.users.auth.methods.shib')
                                @include('admin.users.auth.methods.eclass')
                            @break
                            @case(7)
                                @include('admin.users.auth.methods.cas')
                                @include('admin.users.auth.methods.eclass')
                            @break
                            @case(8)
                            @case(9)
                            @case(10)
                            @case(11)
                            @case(12)
                            @case(13)
                            @case(14)
                                @include('admin.users.auth.methods.hybrid')
                            @break
                            @case(15)
                                @include('admin.users.auth.methods.oauth2')
                            @break
                        @endswitch
                        {!! showSecondFactorChallenge() !!}

                        <div class='form-group mt-5'>
                            <div class='col-12 d-flex justify-content-end align-items-center'>
                                <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langModify') }}'>
                                <a class='btn cancelAdminBtn ms-1' href='auth.php'>{{ trans('langCancel') }}</a>
                            </div>
                        </div>
                    </fieldset>
                    {!! generate_csrf_token_form_field() !!}
                    </form>
                </div> </div>
                <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
                    <img class='form-image-modules' src='{!! get_form_image() !!}' alt='form-image'>
                </div>
            </div>
    </div>
</div>
@endsection
