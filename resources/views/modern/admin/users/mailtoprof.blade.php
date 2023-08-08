@extends('layouts.default')

@section('content')


<div class="col-12 main-section">
<div class='{{ $container }}'>
        <div class="row rowMargin">

                    @if(!get_config('mentoring_always_active') and !get_config('mentoring_platform'))
                        @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])
                    @endif

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

                    @if(isset($action_bar))
                        {!! $action_bar !!}
                    @else
                        <div class='mt-4'></div>
                    @endif

                    
                    @if(Session::has('message'))
                    <div class='col-12 all-alerts'>
                        <div class="alert {{ Session::get('alert-class', 'alert-info') }} alert-dismissible fade show" role="alert">
                            @php 
                                $alert_type = '';
                                if(Session::get('alert-class', 'alert-info') == 'alert-success'){
                                    $alert_type = "<i class='fa-solid fa-circle-check fa-lg'></i>";
                                }elseif(Session::get('alert-class', 'alert-info') == 'alert-info'){
                                    $alert_type = "<i class='fa-solid fa-circle-info fa-lg'></i>";
                                }elseif(Session::get('alert-class', 'alert-info') == 'alert-warning'){
                                    $alert_type = "<i class='fa-solid fa-triangle-exclamation fa-lg'></i>";
                                }else{
                                    $alert_type = "<i class='fa-solid fa-circle-xmark fa-lg'></i>";
                                }
                            @endphp
                            
                            @if(is_array(Session::get('message')))
                                @php $messageArray = array(); $messageArray = Session::get('message'); @endphp
                                {!! $alert_type !!}<span>
                                @foreach($messageArray as $message)
                                    {!! $message !!}
                                @endforeach</span>
                            @else
                                {!! $alert_type !!}<span>{!! Session::get('message') !!}</span>
                            @endif
                            
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif

                   
                    
                    

                    <div class='col-lg-6 col-12'>
                        <div class='form-wrapper form-edit rounded'>
                        
                        <form class='form-horizontal' role='form' action='{{ $_SERVER['SCRIPT_NAME'] }}' method='post'>
                        <fieldset>
                            <div class='form-group'>
                                <label for='email_title' class='col-sm-12 control-label-notes'>{{ trans('langTitle') }}</label>
                                <div class='col-sm-12'>
                                    <input class='form-control' placeholder="{{ trans('langTitle') }}" type='text' name='email_title' value=''>
                                </div>
                            </div>
                           
                            <div class='form-group mt-4'>
                            <label for='body_mail' class='col-sm-12 control-label-notes'>{{ trans('typeyourmessage') }}</label>
                                <div class='col-sm-12'>
                                {!! $body_mail_rich_text !!}
                                </div>
                            </div>
                            
                            <div class='form-group mt-4'>
                                <label class='col-sm-12 control-label-notes'>{{ trans('langFaculty') }}</label>
                                <div class='col-sm-12'>
                                    {!! $buildusernode !!}
                                </div>
                            </div>
                          
                            <div class='form-group mt-4'>
                            <label for='sendTo' class='col-sm-12 control-label-notes mb-1'>{{ trans('langSendMessageTo') }}</label>
                                <div class='col-sm-12'>
                                    <div class='checkbox'>
                                        <label class='me-3' >
                                            <input type='checkbox' name='send_to_prof' value='1'>
                                            @if((get_config('mentoring_platform') and !get_config('mentoring_always_active')) or (!get_config('mentoring_platform')))
                                                {{ trans('langProfOnly') }}
                                            @else
                                                {{ trans('langProfTutorMentorOnly') }}
                                            @endif
                                        </label>
                                        <label>
                                            <input type='checkbox' name='send_to_users' value='1'>
                                            @if((get_config('mentoring_platform') and !get_config('mentoring_always_active')) or (!get_config('mentoring_platform')))
                                                {{ trans('langStudentsOnly') }}
                                            @else
                                                {{ trans('langMenteesOnly') }}
                                            @endif
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class='mt-3'></div>
                            {!! showSecondFactorChallenge() !!}
                            <div class='col-12 mt-5 d-flex justify-content-center align-items-center'>	
                            <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langSend') }}'>          
                            </div>	
                        </fieldset>
                        {!! generate_csrf_token_form_field() !!}
                        </form>
                    </div></div>
                    <div class='col-lg-6 col-12 d-none d-md-none d-lg-block'>
                        <div class='col-12 h-100 left-form'></div>
                    </div>
                
        </div>
</div>
</div>
@endsection