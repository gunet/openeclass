@extends('layouts.default')

@section('content')

<main id="main" class="col-12 main-section">
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

                        @if ($callback_url)
                            <div class='form-group mb-4'>
                                <label for='callbackUrl' class='col-sm-12 control-label-notes'>Callback / Redirect URL:</label>
                                <div class='col-sm-12'>
                                    <div class='input-group mt-1'>
                                        <input class='form-control mt-0' name='callbackUrl' id='callbackUrl' type='text' value="{{ $callback_url }}" readonly>
                                        <button class='btn btn-outline-secondary' type='button' id='copyCallbackUrl' title="{{ trans('langCopy') }}" data-bs-toggle="tooltip" data-bs-placement="top">
                                            <span class='fa fa-regular fa-copy'></span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif

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
                            @case(16)
                                @include('admin.users.auth.methods.keycloak')
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
</main>
@endsection

@push('bottom_scripts')
    <script>
        $(function() {
            $('#copyCallbackUrl').on('click', function() {
                var copyText = $('#callbackUrl').val();
                navigator.clipboard.writeText(copyText).then(function() {
                    var btn = $('#copyCallbackUrl');
                    var icon = btn.find('.fa-copy');

                    icon.removeClass('fa-regular fa-copy').addClass('fa-check text-success');

                    btn.attr('data-bs-original-title', "{{ trans('langCopiedSucc') }}").attr('title', "{{ trans('langCopiedSucc') }}");
                    btn.tooltip('show');
                    setTimeout(() => {
                        btn.tooltip('hide');
                    }, 2000);
                });
            });
        });
    </script>
@endpush
