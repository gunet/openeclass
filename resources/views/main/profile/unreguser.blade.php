@extends('layouts.default')
@section('content')

    <div class="col-12 main-section">
        <div class='{{ $container }} main-container'>
            <div class="row m-auto">

                @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                @include('layouts.partials.legend_view')

                {!! $action_bar !!}

                @include('layouts.partials.show_alert')

                @if ($display_form)
                    <div class='col-12'>
                        <div class='form-wrapper form-edit rounded mt-4'>
                            <form class='form-horizontal' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}'>

                                <div class='form-group'>
                                    <div class='col-12 control-label-notes mb-4'>
                                        {{ trans('langConfirm') }}
                                    </div>
                                </div>

                                <div class='col-12 d-flex justify-content-center align-items-center flex-wrap gap-2'>
                                    <button class='btn deleteAdminBtn' name='doit'>
                                        {{ trans('langUnregUser') }}
                                    </button>

                                    <a class='btn cancelAdminBtn' href='{{ $urlAppend }}main/profile/display_profile.php'>
                                        {{ trans('langCancel') }}
                                    </a>
                                </div>
                                {!! generate_csrf_token_form_field() !!}
                            </form>
                        </div>
                    </div>
                @endif

                @if ($user_deleted)
                    <div class='col-sm-12'>
                        <div class='alert alert-success'>
                            <i class='fa-solid fa-circle-check fa-lg'></i>
                            <span>{{ trans('langDelSuccess') }}
                                <p class="mt-2">{{ trans('langThanks') }}</p>
                            </span>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>

@endsection
