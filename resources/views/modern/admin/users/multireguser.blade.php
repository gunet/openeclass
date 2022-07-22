@extends('layouts.default')

@section('content')

<div class="pb-3 pt-3">

    <div class="container-fluid main-container">

        <div class="row">

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
                    
                    <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                        <div class='alert alert-info'>{!! trans('langMultiRegUserInfo') !!}</div>
                    </div>


                    {!! isset($action_bar) ?  $action_bar : '' !!}
                    
                     <div class='col-xxl-12 col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12'>
                        <div class='form-wrapper shadow-sm p-3 mt-5 rounded'>
                        
                        <form class='form-horizontal' role='form' method='post' action='{{ $_SERVER['SCRIPT_NAME'] }}' onsubmit='return validateNodePickerForm();' >
                        <fieldset>        
                        <div class='form-group'>
                            <label for='fields' class='col-sm-6 control-label-notes'>{{ trans('langMultiRegFields') }}:</label>
                            <div class='col-sm-2'>
                                <input class='form-control' id='fields' type='text' name='fields' value='first last id email phone'>
                            </div>
                        </div>
                        <div class='row p-2'></div>
                        <div class='form-group'>
                            <label for='user_info' class='col-sm-6 control-label-notes'>{{ trans('langUsersData') }}:</label>
                            <div class='col-sm-12'>
                                <textarea class='auth_input form-control' name='user_info' id='user_info' rows='10'></textarea>
                            </div>
                        </div>
                        <div class='row p-2'></div>
                        <div class='form-group'>
                            <label for='type' class='col-sm-6 control-label-notes'>{{ trans('langMultiRegType') }}:</label>
                            <div class='col-sm-12'>
                                <select class='form-select' name='type' id='type'>
                                    <option value='stud'>{{ trans('langsOfStudents') }}</option>
                                    <option value='prof'>{{ trans('langOfTeachers') }}</option>
                                </select>
                            </div>
                        </div>
                        @if (!$eclass_method_unique)
                        <div class='row p-2'></div>
                            <div class='form-group'>
                                <label for='passsword' class='col-sm-6 control-label-notes'>{{ trans('langMethods') }}</label>
                                <div class='col-sm-12'>
                                    {!! selection($auth_m, "auth_methods_form", '', "class='form-control'") !!}
                                </div>
                            </div>
                        @endif
                        <div class='row p-2'></div>
                        <div class='form-group'>
                            <label for='prefix' class='col-sm-6 control-label-notes'>{{ trans('langMultiRegPrefix') }}:</label>
                            <div class='col-sm-12'>
                                <input class='form-control' type='text' name='prefix' id='prefix' value='user'>
                            </div>
                        </div>
                        <div class='row p-2'></div>
                        <div class='form-group'>
                            <label class='col-sm-6 control-label-notes'>{{ trans('langFaculty') }}:</label>
                            <div class='col-sm-12'>
                                {!! $html !!}
                            </div>
                        </div>
                        <div class='row p-2'></div>
                        <div class='form-group'>
                            <label for='am' class='col-sm-6 control-label-notes'>{{ trans('langAm') }}:</label>
                            <div class='col-sm-12'>
                                <input class='form-control' type='text' name='am' id='am'>
                            </div>
                        </div>
                        <div class='row p-2'></div>
                        <div class='form-group'>
                            <label for='lang' class='col-sm-6 control-label-notes'>{{ trans('langLanguage') }}:</label>
                            <div class='col-sm-12'>{!! lang_select_options('lang', 'class="form-control"') !!}</div>
                        </div>
                        <div class='row p-2'></div>
                        <div class='form-group'>
                        <label for='email_public' class='col-sm-6 control-label-notes'>{{ trans('langEmail') }}</label>
                            <div class='col-sm-12'>{!! selection($access_options, 'email_public', ACCESS_PROFS, 'class="form-control"') !!}</div>
                        </div>
                        <div class='row p-2'></div>
                        <div class='form-group'>
                        <label for='am_public' class='col-sm-6 control-label-notes'>{{ trans('langAm') }}</label>
                            <div class='col-sm-12'>{!! selection($access_options, 'am_public', ACCESS_PROFS, 'class="form-control"') !!}</div>
                        </div>
                        <div class='row p-2'></div>
                        <div class='form-group'>
                        <label for='phone_public' class='col-sm-6 control-label-notes'>{{ trans('langPhone') }}</label>
                            <div class='col-sm-12'>{!! selection($access_options, 'phone_public', ACCESS_PROFS, 'class="form-control"') !!}</div>
                        </div>
                        <div class='row p-2'></div>
                        <div class='form-group'>
                        <label for='send_mail' class='col-sm-6 control-label-notes'>{{ trans('langInfoMail') }}</label>
                            <div class='col-sm-12'>
                                <div class='checkbox'>
                                    <label>
                                        <input name='send_mail' id='send_mail' type='checkbox'> {{ trans('langMultiRegSendMail') }}
                                    </label>
                                </div>            
                            </div>
                        </div>
                        <div class='row p-2'></div>
                        <div class='form-group'>
                            {!! showSecondFactorChallenge() !!}
                            <div class='col-sm-9 col-sm-offset-3'>
                                <input class='btn btn-primary' type='submit' name='submit' value='{{ trans('langSubmit') }}'>
                                <a class='btn btn-secondary' href='index.php'>{{ trans('langCancel') }}</a>
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