@extends('layouts.default')

@section('content')

<div class="pb-lg-3 pt-lg-3 pb-0 pt-0">

    <div class="container-fluid main-container">

        <div class="row rowMedium">

            <div class="col-12 justify-content-center col_maincontent_active_Homepage">
                    
                <div class="row p-lg-5 p-md-5 ps-1 pe-1 pt-5 pb-5">

                    @include('layouts.common.breadcrumbs', ['breadcrumbs' => $breadcrumbs])

                    @include('layouts.partials.legend_view',['is_editor' => $is_editor, 'course_code' => $course_code])

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
                    

                    <div class='col-12'>
                        <div class='alert alert-info'>{{ trans('langMultiCourseInfo') }}</div>
                    </div>

                    {!! isset($action_bar) ?  $action_bar : '' !!}

                    <div class='col-lg-6 col-12 d-none d-md-none d-lg-block'>
                        <div class='col-12 h-100 left-form'></div>
                    </div>

                    <div class='col-lg-6 col-12'>
                        <div class='form-wrapper form-edit rounded'>
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
                                        <div class='col-12 d-flex justify-content-center align-items-center'>
                                            
                                              
                                                    <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langSubmit') }}'>
                                               
                                                    <a href='index.php' class='btn btn-outline-secondary cancelAdminBtn ms-1'>{{ trans('langCancel') }}</a>  
                                               
                                            
                                        </div>
                                    </div>
                                </fieldset>
                            {!! generate_csrf_token_form_field() !!}
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection