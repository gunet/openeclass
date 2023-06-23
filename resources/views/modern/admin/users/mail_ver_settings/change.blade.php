@extends('layouts.default')

@section('content')

<div class="col-12 basic-section p-xl-5 px-lg-3 py-lg-5">

        <div class="row rowMargin">

            <div class="col-12 col_maincontent_active_Homepage">
                    
                <div class="row">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    {!! isset($action_bar) ?  $action_bar : '' !!}

                    @if(Session::has('message'))
                    <div class='col-12 all-alerts'>
                        <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach
                            @else
                                {!! Session::get('message') !!}
                            @endif
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif

                    
                    @if (isset($sub))
                    <div class='col-lg-6 col-12 d-none d-md-none d-lg-block'>
                        <div class='col-12 h-100 left-form'></div>
                    </div>
                    <div class='col-lg-6 col-12'>
                        <div class='form-wrapper form-edit rounded'>
                        
                            <form class='form-horizontal' role='form' name='mail_verification_change' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}'>
                            <fieldset>		
                          
                                    <div class='form-group'>
                                        <label class='col-sm-12 control-label-notes'>{{ trans('langChangeTo') }}:</label>
                                        <div class='col-sm-12'>
                                            {!! selection($mail_ver_data, "new_mail_ver", $sub, "class='form-control'") !!}
                                        </div>
                                    </div>
                                    {!! showSecondFactorChallenge() !!}
                             
                                    <div class='col-12 mt-5 d-flex justify-content-center align-items-center'>
                                        <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langEdit') }}'>
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
@endsection