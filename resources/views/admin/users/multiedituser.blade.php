@extends('layouts.default')

@section('content')
    <main id="main" class="col-12 main-section">
        <div class='{{ $container }} main-container'>
            <div class="row m-auto">

                @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                @include('layouts.partials.legend_view')

                @if(isset($action_bar))
                    {!! $action_bar !!}
                @else
                    <div class='mt-4'></div>
                @endif

                @include('layouts.partials.show_alert')

                <div class='col-12'>
                    <div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>
                            @if (isset($_POST['activate_submit']))
                                {{ trans('langActivateUserInfo') }}
                            @elseif (isset($_POST['move_submit']))
                                {!! sprintf(trans('langMoveUserInfo'), '<strong>' . q($currentDepartment) . '</strong>'); !!}
                            @else
                                {{ trans('langMultiDelUserInfo') }}
                            @endif
                        </span>
                    </div>
                </div>

                <div class='col-lg-6 col-12'>
                    <div class='form-wrapper form-edit border-0 px-0'>
                        <form role='form' class='form-horizontal' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}'>
                        <fieldset>
                            <legend class='mb-0' aria-label="{{ trans('langForm') }}"></legend>

                            @if (isset($_POST['activate_submit']))
                                <div class='form-group mt-3'>
                                    <label class='col-sm-12 control-label-notes' for='months-id'>
                                        {{ trans('langActivateMonths') }}:
                                    </label>
                                    <div class='col-sm-12'>
                                        <input name='months' id='months-id' class='form-control' type='number' min='1' step='1' value='6'>
                                    </div>
                                </div>
                            @elseif (isset($_POST['move_submit']))
                                <input type='hidden' name='old_dep' value='{{ $dep }}'>
                                <div class='form-group mt-3'>
                                    <label class='col-sm-12 control-label-notes' for='dialog-set-value'>
                                        {{ trans('langFaculty') }}:
                                    </label>
                                    <div class='col-sm-12'>
                                        {!! $html !!}
                                    </div>
                                </div>
                            @else
                                <input type='hidden' name='delete' value='true'>
                            @endif

                            <div class='form-group mt-4'>
                                <label for='user_names' class='col-sm-12 control-label-notes'>
                                    {{ trans('langMultiDelUserData') }}:
                                </label>
                                <div class='col-sm-12'>
                                <textarea id='user_names' class='auth_input form-control' name='user_names' rows='30'>@foreach ($users as $user){{ $user->username . "\n" }}@endforeach</textarea>
                                </div>
                            </div>

                            <div class='form-group mt-5'>
                                <div class='col-12 d-flex justify-content-end align-items-center gap-2'>
                                    <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langSubmit') }}'
                                           @if (!(isset($_POST['activate_submit']) || isset($_POST['move_submit'])))
                                               onclick="return confirmation('{{ trans('langMultiDelUserConfirm') }}')"
                                          @endif
                                    >
                                    <a href='index.php' class='btn cancelAdminBtn'>{{ trans('langCancel') }}</a>
                                </div>
                            </div>

                        </fieldset>
                            {!! generate_csrf_token_form_field() !!}
                        </form>
                    </div>
                </div>
                <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
                    <img class='form-image-modules' src='{!! get_form_image() !!}' alt="{{ trans('langImgFormsDes') }}">
                </div>
            </div>
        </div>
    </main>
@endsection