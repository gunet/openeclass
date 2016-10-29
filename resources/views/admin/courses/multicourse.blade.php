@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    <div class='alert alert-info'>{{ trans('langMultiCourseInfo') }}</div>
    <div class='form-wrapper'>
        <form role='form' class='form-horizontal' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}' onsubmit="return validateNodePickerForm();">
        <fieldset>
        <div class='form-group'>
            <label for='title' class='col-sm-3 control-label'>{{ trans('langMultiCourseTitles') }}:</label>
            <div class='col-sm-9'>{!! text_area('courses', 20, 80, '') !!}</div>
        </div>
	<div class='form-group'>
            <label for='title' class='col-sm-3 control-label'>{{ trans('langFaculty') }}:</label>	  
            <div class='col-sm-9'>
                {!! $html !!}
            </div>
        </div>                
        <div class='form-group'>
            <label class='col-sm-offset-4 col-sm-8'>{{ trans('langConfidentiality') }}</label></div>
            <div class='form-group'>
                <label for='password' class='col-sm-3 control-label'>{{ trans('langOptPassword') }}</label>
                <div class='col-sm-9'>
                    <input id='coursepassword' class='form-control' type='text' name='password' id='password' autocomplete='off'>
                </div>
            </div>
        <div class='form-group'>
            <label for='Public' class='col-sm-3 control-label'>{{ trans('langOpenCourse') }}</label>
            <div class='col-sm-9 radio'>
                <label>
                    <input id='courseopen' type='radio' name='formvisible' value='2' checked> {{ trans('langPublic') }}
                </label>
            </div>
        </div>
        <div class='form-group'>
            <label for='PrivateOpen' class='col-sm-3 control-label'>{{ trans('langRegCourse') }}</label>	
            <div class='col-sm-9 radio'>
                <label>
                    <input id='coursewithregistration' type='radio' name='formvisible' value='1'> 
                        {{ trans('langPrivOpen') }}
                </label>
            </div>
        </div>
        <div class='form-group'>
            <label for='PrivateClosed' class='col-sm-3 control-label'>{{ trans('langClosedCourse') }}</label>
            <div class='col-sm-9 radio'>
                <label>
                    <input id='courseclose' type='radio' name='formvisible' value='0'> 
                        {{ trans('langClosedCourseShort') }}
                </label>
            </div>
       </div>
        <div class='form-group'>
             <label for='Inactive' class='col-sm-3 control-label'>{{ trans('langInactiveCourse') }}</label>
             <div class='col-sm-9 radio'>
                 <label>
                     <input id='courseinactive' type='radio' name='formvisible' value='3'> {{ trans('langCourseInactiveShort') }}
                 </label>
             </div>
         </div>
         <div class='form-group'>
          <label for='language' class='col-sm-3 control-label'>{{ trans('langLanguage') }}:</label>	  
           <div class='col-sm-9'>{!! lang_select_options('lang') !!}</div>
         </div>
        {!! showSecondFactorChallenge() !!}
        <div class='form-group'>
           <div class='col-sm-10 col-sm-offset-2'>
               <input class='btn btn-primary' type='submit' name='submit' value='{{ trans('langSubmit') }}'>
               <a href='index.php' class='btn btn-default'>{{ trans('langCancel') }}</a>    
           </div>
        </div>
        </fieldset>
        {!! generate_csrf_token_form_field() !!}
        </form>
    </div>
@endsection