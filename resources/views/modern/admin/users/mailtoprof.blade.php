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

                    {!! isset($action_bar) ?  $action_bar : '' !!}
                    
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                        <div class='form-wrapper shadow-sm p-3 mt-5 rounded'>
                        
                        <form class='form-horizontal' role='form' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
                        <fieldset>
                            <div class='form-group'>
                                <label for='email_title' class='col-sm-6 control-label-notes'>{{ trans('langTitle') }}</label>
                                <div class='col-sm-12'>
                                    <input class='form-control' type='text' name='email_title' value=''>
                                </div>
                            </div>
                            <div class='row p-2'></div>
                            <div class='form-group'>
                            <label for='body_mail' class='col-sm-6 control-label-notes'>{{ trans('typeyourmessage') }}</label>
                                <div class='col-sm-12'>
                                {!! $body_mail_rich_text !!}
                                </div>
                            </div>
                            <div class='row p-2'></div>
                            <div class='form-group'>
                                <label class='col-sm-6 control-label-notes'>{{ trans('langFaculty') }}:</label>
                                <div class='col-sm-12'>
                                    {!! $buildusernode !!}
                                </div>
                            </div>
                            <div class='row p-2'></div>
                            <div class='form-group'>
                            <label for='sendTo' class='col-sm-6 control-label-notes'>{{ trans('langSendMessageTo') }}</label>
                                <div class='col-sm-12'>
                                    <div class='checkbox'>
                                        <label>
                                            <input type='checkbox' name='send_to_prof' value='1'>{{ trans('langProfOnly') }}
                                        </label>
                                        <label>
                                            <input type='checkbox' name='send_to_users' value='1'>{{ trans('langStudentsOnly') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class='row p-2'></div>
                            {!! showSecondFactorChallenge() !!}
                            <div class='col-sm-offset-2 col-sm-10'>	
                            <input class='btn btn-primary' type='submit' name='submit' value='{{ trans('langSend') }}'>          
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