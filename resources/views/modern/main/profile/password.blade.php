@extends('layouts.default')

@push('head_scripts')
<script type='text/javascript' src='{{ $urlAppend }}js/pwstrength.js'></script>
<script type='text/javascript'>
    
    var lang = {
        pwStrengthTooShort: "{{ js_escape(trans('langPwStrengTooShort')) }}",
        pwStrengthWeak: "{{ js_escape(trans('langPwStrengthWeak')) }}",
        pwStrengthGood: "{{ js_escape(trans('langPwStrengthGood')) }}", 
        pwStrengthStrong: "{{ js_escape(trans('langPwStrengthStrong')) }}"
    }
    
    $(document).ready(function() {
        $('#password_form').keyup(function() {
            $('#result').html(checkStrength($('#password_form').val()))
        });
    });

</script>
@endpush

@section('content')
<div class="pb-3 pt-3">

    <div class="container-fluid main-container">

        <div class="row">

            <div id="background-cheat-leftnav" class="col-xl-2 col-lg-3 col-md-0 col-sm-0 col-0 justify-content-center col_sidebar_active"> 
                <div class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block">
                    @if($course_code)
                        @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                    @else
                        @include('layouts.partials.sidebarAdmin')
                    @endif 
                </div>
            </div>

            <div class="col-xl-10 col-lg-9 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active col_maincontent_active_ProfileUser">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-2 pt-5 pb-5">

                    <nav class="navbar navbar-expand-lg navrbar_menu_btn">
                        <button type="button" id="menu-btn" class="d-none d-sm-block d-sm-none d-md-block d-md-none d-lg-block btn btn-primary menu_btn_button">
                            <i class="fas fa-align-left"></i>
                            <span></span>
                        </button>
                        <a class="btn btn-primary d-lg-none mr-auto" type="button" data-bs-toggle="offcanvas" href="#collapseTools" role="button" aria-controls="collapseTools" style="margin-top:-10px;">
                            <i class="fas fa-tools"></i>
                        </a>
                    </nav>

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    <div class="offcanvas offcanvas-start d-lg-none mr-auto" tabindex="-1" id="collapseTools" aria-labelledby="offcanvasExampleLabel">
                        <div class="offcanvas-header">
                            <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                        </div>
                        <div class="offcanvas-body">
                            @if($course_code)
                                @include('layouts.partials.sidebar',['is_editor' => $is_editor])
                            @else
                                @include('layouts.partials.sidebarAdmin')
                            @endif
                        </div>
                    </div>

                    {!! $action_bar !!}

                    @if(Session::has('message'))
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
                        <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            {{ Session::get('message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </p>
                    </div>
                    @endif

                    <div class='col-12'>
                    <div class='form-wrapper shadow-sm p-3 rounded'>
                        <form class='form-horizontal' role='form' method='post' action='{{ $passUrl }}'>
                            <fieldset>
                                <div class="row p-2"></div>

                                <div class='form-group{{ $old_pass_error ? " has-error" : "" }}'>
                                    <label for='old_pass' class='col-sm-6 control-label-notes'>{{ trans('langOldPass') }}: </label>
                                    <div class='col-sm-12'>
                                        <input type='password' class='form-control' id='old_pass' name='old_pass' value='{{ $old_pass }}' autocomplete='off'>
                                        <span class='help-block'>{{ $old_pass_error }}</span>
                                    </div>
                                </div>

                                <div class="row p-2"></div>

                                <div class='form-group{{ $password_form_error ? " has-error" : "" }}'>
                                    <label for='password_form' class='col-sm-6 control-label-notes'>{{ trans('langNewPass1') }}: </label>
                                    <div class='col-sm-12'>
                                        <input type='password' class='form-control' id='password_form' name='password_form' value='{{ $password_form }}' autocomplete='off'>
                                        <span class='help-block'>{{ $password_form_error }}</span>
                                    </div>
                                    <div class='col-sm-12 text-center padding-thin'>
                                        <span id='result'></span>
                                    </div>
                                </div>

                                <div class="row p-2"></div>

                                <div class='form-group{{ $password_form1_error ? " has-error" : "" }}'>
                                    <label for='password_form1' class='col-sm-6 control-label-notes'>{{ trans('langNewPass2') }}: </label>
                                    <div class='col-sm-12'>
                                        <input type='password' class='form-control' id='password_form1' name='password_form1' value='{{ $password_form1 }}' autocomplete='off'>
                                        <span class='help-block'>{{ $password_form1_error }}</span>
                                    </div>
                                </div>

                                <div class="row p-2"></div>

                                {!! showSecondFactorChallenge() !!}

                                <div class="row p-2"></div>

                                <div class='form-group'>
                                    <div class='col-sm-offset-2 col-sm-8'>
                                        <input type='submit' class='btn btn-primary' name='submit' value='{{ trans('langModify') }}'>
                                        <a href='display_profile.php' class='btn btn-secondary'>{{ trans('langCancel') }}</a>
                                    </div>
                                </div>
                            </fieldset>
                            {!! generate_csrf_token_form_field() !!}
                        </form>
                    </div></div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection