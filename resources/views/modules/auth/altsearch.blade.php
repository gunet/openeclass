@extends('layouts.default')

@push('head_styles')
    <link href="{{ $urlAppend }}js/jstree3/themes/proton/style.min.css" type='text/css' rel='stylesheet'>
@endpush

@push('head_scripts')
    <script type='text/javascript' src='{{ $urlAppend }}js/jstree3/jstree.min.js'></script>
@endpush

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} main-container'>
        <div class="row m-auto">
            <div class='col-12'>
                <h1>{!! $toolName !!}</h1>
            </div>

            @include('layouts.partials.show_alert')

            @if (!$alt_auth_stud_reg || !$user_registration || !in_array($auth, get_auth_active_methods()))
                <div class='col-12 mt-4'>
                    <div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i>
                        <span>{{ trans('langStudentCannotRegister') }}</span>
                    </div>
                </div>
            @elseif (!isset($_POST['submit']))
                <div class='col-12 mt-4'>
                    <div class='row row-cols-1 row-cols-lg-2 g-lg-5 g-4'>
                        <div class='col-lg-6 col-12'>
                            <div class='form-wrapper form-edit rounded px-0 border-0'>
                                <form role='form' class='form-horizontal' action='altsearch.php' method='post'>

                                    <div class='col-lg-6 col-12 px-3'>
                                        <div class='form-group mt-lg-0 mt-4'>
                                            <label for='givenname_id' class='col-sm-12 control-label-notes'>{{ trans('langName') }}</label>
                                            <div for='givenname_id' class='col-sm-12${!! $givennameClass !!}'>{!! $givennameInput !!}</div>
                                        </div>
                                    </div>

                                    <div class='col-lg-6 col-12 px-3 pt-3'>
                                        <div class='form-group mt-lg-0 mt-4'>
                                            <label for='surname_id' class='col-sm-12 control-label-notes'>{{ trans('langSurname') }}</label>
                                            <div id='surname_id' class='col-sm-12{!! $surnameClass !!}'>{!! $surnameInput !!}</div>
                                        </div>
                                    </div>

                                    <div class='col-12 px-3 pt-4'>
                                        <div class='form-group mt-lg-0 mt-4'>
                                            <label for='email_id' class='col-sm-12 control-label-notes'>{{ trans('langEmail') }}</label>
                                            <div class='col-sm-12'>
                                                <div class='col-sm-12{!! $emailClass !!}'>{!! $emailInput !!}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class='col-lg-6 col-12 px-3 pt-4'>
                                        <div class='form-group mt-lg-0 mt-4'>
                                            <div class='col-sm-12 control-label-notes'>{{ trans('langAm') }}</div>
                                            <div class='col-sm-12{!! $amClass !!}'>{!! $amInput !!}</div>
                                        </div>
                                    </div>

                                    <div class='col-lg-6 col-12 px-3'>
                                        <div class='form-group mt-4'>
                                            <label for='UserPhone' class='col-sm-12 control-label-notes'>{{ trans('langPhone') }}</label>
                                            <div class='col-sm-12'>
                                                <input id='UserPhone' class='form-control' type='text' name='userphone' size='20' maxlength='20' placeholder='{{ trans('langOptional') }}'>
                                            </div>
                                        </div>
                                    </div>

                                    @if ($comment_required)
                                        <div class='col-12 px-3'>
                                            <div class='form-group mt-4'>
                                                <label for='UserComment' class='col-sm-6 control-label-notes'>{{ trans('langComments') }}</label>
                                                <div class='col-sm-12'>
                                                    <textarea id='UserComment' class='form-control' name='usercomment' cols='30' rows='4' placeholder='{{ trans('langReason') }}'></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <div class='col-12 px-3'>
                                        <div class='form-group mt-4'>
                                            <label for='dialog-set-value' class='col-sm-6 control-label-notes'>{{ trans('langFaculty') }}</label>
                                            <div class='col-sm-12'>
                                                {!! $buildusernode !!}
                                            </div>
                                        </div>
                                    </div>

                                    <div class='col-12 px-3'>
                                        <div class='form-group mt-1'>
                                            <label for='UserLang' class='col-sm-12 control-label-notes'>{{ trans('langLanguage') }}</label>
                                            <div class='col-sm-12'>
                                                {!! $lang_select_options !!}
                                            </div>
                                        </div>
                                    </div>

                                    <div class='form-group mt-5'>
                                        <div class='col-12 d-flex justify-content-center align-items-center'>
                                            <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langRegistration') }}'>
                                        </div>
                                    </div>

                                    @if (isset($_SESSION['shib_uname']))
                                        <input type='hidden' name='uname' value='{{ $_SESSION['shib_uname'] }}'>
                                    @else
                                        <input type='hidden' name='uname' value='{{ $_SESSION['was_validated']['uname'] }}'>
                                    @endif
                                    <input type='hidden' name='auth' value='{{ $auth }}'>

                                </form>
                            </div>
                        </div>
                        <div class='col-lg-6 col-12 d-none d-lg-block'>
                            <img class='form-image-modules form-image-registration' src='{!! get_registration_form_image() !!}' alt='{{ trans('langRegistration') }}'>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection

