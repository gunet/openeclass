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
<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active_Homepage">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-2 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    {!! $action_bar !!}

                    @if(Session::has('message'))
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
                        <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach
                            @else
                                {!! Session::get('message') !!}
                            @endif
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </p>
                    </div>
                    @endif

                    <div class='col-lg-6 col-12 d-none d-md-none d-lg-block'>
                        <div class='col-12 h-100 left-form'></div>
                    </div>
                    <div class='col-lg-6 col-12'>
                    <div class='form-wrapper shadow-sm p-3 rounded'>
                        <form class='form-horizontal' role='form' method='post' action='{{ $passUrl }}'>
                            <fieldset>

                                <div class='form-group{{ $old_pass_error ? " has-error" : "" }} mt-3'>
                                    <label for='old_pass' class='col-sm-12 control-label-notes'>{{ trans('langOldPass') }} </label>
                                    <div class='col-sm-12'>
                                        <input type='password' class='form-control' placeholder="{{ trans('langOldPass') }}..." id='old_pass' name='old_pass' value='{{ $old_pass }}' autocomplete='off'>
                                        <span class='help-block'>{{ $old_pass_error }}</span>
                                    </div>
                                </div>


                                <div class='form-group{{ $password_form_error ? " has-error" : "" }} mt-3'>
                                    <label for='password_form' class='col-sm-12 control-label-notes'>{{ trans('langNewPass1') }} </label>
                                    <div class='col-sm-12'>
                                        <input type='password' class='form-control' placeholder="{{ trans('langNewPass1') }}..." id='password_form' name='password_form' value='{{ $password_form }}' autocomplete='off'>
                                        <span class='help-block'>{{ $password_form_error }}</span>
                                    </div>
                                    <div class='col-sm-12 text-center padding-thin'>
                                        <span id='result'></span>
                                    </div>
                                </div>

                                <div class='form-group{{ $password_form1_error ? " has-error" : "" }} mt-3'>
                                    <label for='password_form1' class='col-sm-12 control-label-notes'>{{ trans('langNewPass2') }} </label>
                                    <div class='col-sm-12'>
                                        <input type='password' class='form-control' placeholder="{{ trans('langNewPass2') }}..." id='password_form1' name='password_form1' value='{{ $password_form1 }}' autocomplete='off'>
                                        <span class='help-block'>{{ $password_form1_error }}</span>
                                    </div>
                                </div>

                                <div class="mt-3"></div>

                                {!! showSecondFactorChallenge() !!}

                                <div class='form-group mt-3'>
                                    <div class='col-12'>
                                        <div class='row'>
                                            <div class='col-6'>
                                               <input type='submit' class='btn btn-primary submitAdminBtn w-100' name='submit' value='{{ trans('langModify') }}'>
                                            </div>
                                            <div class='col-6'>
                                                <a href='display_profile.php' class='btn btn-secondary cancelAdminBtn w-100'>{{ trans('langCancel') }}</a>
                                            </div>
                                        </div>
                                       
                                        
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