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
                        <div class='alert alert-info'>{!! trans('langMultiRegUserInfo') !!}</div>
                    </div>


                    {!! isset($action_bar) ?  $action_bar : '' !!}

                    <div class='col-lg-6 col-12 d-none d-md-none d-lg-block'>
                        <div class='col-12 h-100 left-form'></div>
                    </div>
                    
                     <div class='col-lg-6 col-12'>
                        <div class='form-wrapper form-edit rounded'>
                        
                        <form class='form-horizontal' role='form' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}' onsubmit='return validateNodePickerForm();' >
                        <fieldset>        
                        <div class='form-group'>
                            <label for='fields' class='col-sm-12 control-label-notes'>{{ trans('langMultiRegFields') }}</label>
                            <div class='col-sm-12'>
                                <input class='form-control' id='fields' type='text' name='fields' value='first last id email phone'>
                            </div>
                        </div>
                       
                        <div class='form-group mt-4'>
                            <label for='user_info' class='col-sm-12 control-label-notes'>{{ trans('langUsersData') }}</label>
                            <div class='col-sm-12'>
                                <textarea class='auth_input form-control' name='user_info' id='user_info' rows='10'></textarea>
                            </div>
                        </div>
                    
                        <div class='form-group mt-4'>
                            <label for='type' class='col-sm-12 control-label-notes'>{{ trans('langMultiRegType') }}</label>
                            <div class='col-sm-12'>
                                <select class='form-select' name='type' id='type'>
                                    <option value='stud'>{{ trans('langsOfStudents') }}</option>
                                    <option value='prof'>{{ trans('langOfTeachers') }}</option>
                                </select>
                            </div>
                        </div>
                        @if (!$eclass_method_unique)
                        
                            <div class='form-group mt-4'>
                                <label for='passsword' class='col-sm-12 control-label-notes'>{{ trans('langMethods') }}</label>
                                <div class='col-sm-12'>
                                    {!! selection($auth_m, "auth_methods_form", '', "class='form-control'") !!}
                                </div>
                            </div>
                        @endif
                      
                        <div class='form-group mt-4'>
                            <label for='prefix' class='col-sm-12 control-label-notes'>{{ trans('langMultiRegPrefix') }}</label>
                            <div class='col-sm-12'>
                                <input class='form-control' type='text' name='prefix' id='prefix' value='user'>
                            </div>
                        </div>
                      
                        <div class='form-group mt-4'>
                            <label class='col-sm-12 control-label-notes'>{{ trans('langFaculty') }}:</label>
                            <div class='col-sm-12'>
                                {!! $html !!}
                            </div>
                        </div>
                        
                        <div class='form-group mt-4'>
                            <label for='am' class='col-sm-12 control-label-notes'>{{ trans('langAm') }}</label>
                            <div class='col-sm-12'>
                                <input class='form-control' placeholder="{{ trans('langAm') }}..." type='text' name='am' id='am'>
                            </div>
                        </div>
                      
                        <div class='form-group mt-4'>
                            <label for='lang' class='col-sm-12 control-label-notes'>{{ trans('langLanguage') }}</label>
                            <div class='col-sm-12'>{!! lang_select_options('lang', 'class="form-control"') !!}</div>
                        </div>
                        
                        <div class='form-group mt-4'>
                        <label for='email_public' class='col-sm-12 control-label-notes'>{{ trans('langEmail') }}</label>
                            <div class='col-sm-12'>{!! selection($access_options, 'email_public', ACCESS_PROFS, 'class="form-control"') !!}</div>
                        </div>
                    
                        <div class='form-group mt-4'>
                        <label for='am_public' class='col-sm-12 control-label-notes'>{{ trans('langAm') }}</label>
                            <div class='col-sm-12'>{!! selection($access_options, 'am_public', ACCESS_PROFS, 'class="form-control"') !!}</div>
                        </div>
                    
                        <div class='form-group mt-4'>
                        <label for='phone_public' class='col-sm-12 control-label-notes'>{{ trans('langPhone') }}</label>
                            <div class='col-sm-12'>{!! selection($access_options, 'phone_public', ACCESS_PROFS, 'class="form-control"') !!}</div>
                        </div>
               
                        <div class='form-group mt-4'>
                        <label for='send_mail' class='col-sm-12 control-label-notes mb-1'>{{ trans('langInfoMail') }}</label>
                            <div class='col-sm-12'>
                                <div class='checkbox'>
                                    <label>
                                        <input name='send_mail' id='send_mail' type='checkbox'> {{ trans('langMultiRegSendMail') }}
                                    </label>
                                </div>            
                            </div>
                        </div>
                       
                        <div class='form-group mt-5'>
                            {!! showSecondFactorChallenge() !!}
                            <div class='col-12 d-flex justify-content-center align-items-center'>
                               
                                   
                                        <input class='btn submitAdminBtn' type='submit' name='submit' value='{{ trans('langSubmit') }}'>
                                 
                                        <a class='btn cancelAdminBtn ms-1' href='index.php'>{{ trans('langCancel') }}</a>
                                  
                               
                                
                                
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