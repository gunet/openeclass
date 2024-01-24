@extends('layouts.default')

@section('content')

<div class="col-12 main-section">
    <div class='{{ $container }} main-container'>
        <div class="row m-auto">
            

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view')
                    
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
                            
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                    @endif
                    

                    <div class='col-12 mb-4'>
                        <div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>{{ trans('langMultiCourseInfo') }}</span></div>
                    </div>


                    

                    <div class='col-lg-6 col-12'>
                        <div class='form-wrapper form-edit border-0 px-0'>
                            <form role='form' class='form-horizontal' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}' onsubmit="return validateNodePickerForm();">
                                <fieldset>

                                    <div class='form-group'>
                                        <label for='title' class='col-sm-12 control-label-notes'>{{ trans('langMultiCourseTitles') }}</label>
                                        <div class='col-sm-12'>{!! text_area('courses', 20, 80, '') !!}</div>
                                    </div>

                                    <div class='form-group mt-4'>
                                        <label for='title' class='col-sm-12 control-label-notes'>{{ trans('langFaculty') }}</label>	  
                                        <div class='col-sm-12'>
                                            {!! $html !!}
                                        </div>
                                    </div>    

                                    <div class='form-group mt-4'>
                                        <label class='col-sm-12 control-label-notes'>{{ trans('langConfidentiality') }}</label>
                                        <div class='col-sm-12'>
                                            <input id='coursepassword' class='form-control' type='text' name='password' id='password' autocomplete='off'>
                                            <div class='help-block'>({{ trans('langOptPassword') }})</div>
                                        </div>
                                    </div>

                                    <div class='form-group mt-4'>
                                        <label for='Public' class='col-sm-12 control-label-notes mb-2'>{{ trans('langOpenCourse') }}</label>
                                        <div class='col-sm-12 radio'>
                                            <label>
                                                <input id='courseopen' type='radio' name='formvisible' value='2' checked> {{ trans('langPublic') }}
                                            </label>
                                        </div>
                                    </div>

                                    <div class='form-group mt-4'>
                                        <label for='PrivateOpen' class='col-sm-12 control-label-notes mb-2'>{{ trans('langRegCourse') }}</label>	
                                        <div class='col-sm-12 radio'>
                                            <label>
                                                <input id='coursewithregistration' type='radio' name='formvisible' value='1'> 
                                                {{ trans('langPrivOpen') }}
                                            </label>
                                        </div>
                                    </div>

                                    <div class='form-group mt-4'>
                                        <label for='PrivateClosed' class='col-sm-12 control-label-notes mb-2'>{{ trans('langClosedCourse') }}</label>
                                        <div class='col-sm-12 radio'>
                                            <label>
                                                <input id='courseclose' type='radio' name='formvisible' value='0'> 
                                                    {{ trans('langClosedCourseShort') }}
                                            </label>
                                        </div>
                                    </div>

                                    <div class='form-group mt-4'>
                                        <label for='Inactive' class='col-sm-12 control-label-notes mb-2'>{{ trans('langInactiveCourse') }}</label>
                                        <div class='col-sm-12 radio'>
                                            <label>
                                                <input id='courseinactive' type='radio' name='formvisible' value='3'> {{ trans('langCourseInactiveShort') }}
                                            </label>
                                        </div>
                                    </div>

                                    <div class='form-group mt-4'>
                                        <label for='language' class='col-sm-12 control-label-notes'>{{ trans('langLanguage') }}</label>	  
                                        <div class='col-sm-12'>{!! lang_select_options('lang') !!}</div>
                                    </div>
                                    
                                    {!! showSecondFactorChallenge() !!}
                                    <div class='form-group mt-5'>
                                        <div class='col-12 d-flex justify-content-end align-items-center'>
                                            <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langSubmit') }}'>
                                            <a href='index.php' class='btn cancelAdminBtn ms-1'>{{ trans('langCancel') }}</a>  
                                        </div>
                                    </div>
                                </fieldset>
                            {!! generate_csrf_token_form_field() !!}
                            </form>
                        </div>
                    </div>

                    <div class='col-lg-6 col-12 d-none d-md-none d-lg-block text-end'>
                    <img class='form-image-modules' src='{{$urlAppend}}template/modern/img/form-image.png' alt='form-image'>
                    </div>
                
        </div>
    </div>
</div>
@endsection