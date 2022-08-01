@extends('layouts.default')

@section('content')

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 justify-content-center col_maincontent_active_Homepage">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    @if(Session::has('message'))
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-5'>
                        <p class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            {{ Session::get('message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </p>
                    </div>
                    @endif

                    {!! isset($action_bar) ?  $action_bar : '' !!}
                    @if (isset($sub))
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                        <div class='form-wrapper shadow-sm p-3 mt-5 rounded'>
                        
                            <form class='form-horizontal' role='form' name='mail_verification_change' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}'>
                        <fieldset>		
                            <div class='row p-2'></div>
                                <div class='form-group'>
                                    <label class='col-sm-6 control-label-notes'>{{ trans('langChangeTo') }}:</label>
                                    <div class='col-sm-12'>
                                        {!! selection($mail_ver_data, "new_mail_ver", $sub, "class='form-control'") !!}
                                    </div>
                        </div>
                                {!! showSecondFactorChallenge() !!}
                                <div class='row p-2'></div>
                        <div class='col-sm-offset-2 col-sm-10'>
                                    <input class='btn btn-primary' type='submit' name='submit' value='{{ trans('langEdit') }}'>
                                </div>
                        <input type='hidden' name='old_mail_ver' value='{{ $sub }}'>		
                        </fieldset>
                                {!! generate_csrf_token_form_field() !!}
                            </form>
                        </div></div>
                    @endif    
                    @include('admin.users.mail_ver_settings.messages')
                </div>
            </div>
        </div>
    </div>
</div>
@endsection