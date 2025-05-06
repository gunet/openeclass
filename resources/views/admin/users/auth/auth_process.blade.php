@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} main-container'>
            <div class="row m-auto">

                @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                @include('layouts.partials.legend_view')

                <div class='mt-4'></div>

                @include('layouts.partials.show_alert')

                <div class='col-lg-6 col-12'>
                    <div class='form-wrapper form-edit border-0 px-0'>

                    <form class='form-horizontal' name='authmenu' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}'>
                    <fieldset>
                        <legend class='mb-0' aria-label="{{ trans('langForm') }}"></legend>
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
                            <div class='col-12 d-flex justify-content-end align-items-center gap-2'>
                                <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langSubmit') }}'>
                                <a class='btn cancelAdminBtn' href='auth.php'>{{ trans('langCancel') }}</a>
                            </div>
                        </div>
                    </fieldset>
                    {!! generate_csrf_token_form_field() !!}
                    </form>
                </div> </div>
                <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
                    <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
                </div>
            </div>
    </div>
</div>
@endsection
