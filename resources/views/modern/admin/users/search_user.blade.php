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

                    {!! isset($action_bar) ?  $action_bar : '' !!}

                    <div class='col-lg-6 col-12 d-none d-md-none d-lg-block'>
                        <div class='col-12 h-100 left-form'></div>
                    </div>
                    
                    <div class='col-lg-6 col-12'>
                        <div class='form-wrapper form-edit rounded'>
                        
                        <form class='form-horizontal' role='form' action='listusers.php' method='get' name='user_search'>
                        <fieldset>
                         
                            <div class='form-group'>
                                <label for='uname' class='col-sm-12 control-label-notes'>{{ trans('langUsername') }}</label>
                                <div class='col-sm-12'>
                                    <input class='form-control' placeholder="{{ trans('langUsername') }}" type='text' name='uname' id='uname' value='{{ $uname }}'>
                                </div>
                            </div>
                          
                            <div class='form-group mt-4'>
                                <label for='fname' class='col-sm-12 control-label-notes'>{{ trans('langName') }}</label>
                                <div class='col-sm-12'>
                                    <input class='form-control' placeholder="{{ trans('langName') }}" type='text' name='fname' id='fname' value='{{ $fname }}'>
                                </div>
                            </div>
                         
                            <div class='form-group mt-4'>
                                <label for='lname' class='col-sm-12 control-label-notes'>{{ trans('langSurname') }}</label>
                                <div class='col-sm-12'>
                                    <input class='form-control' placeholder="{{ trans('langSurname') }}" type='text' name='lname' id='lname' value='{{ $lname }}'>
                                </div>
                            </div>
                          
                            <div class='form-group mt-4'>
                                <label for='email' class='col-sm-12 control-label-notes'>{{ trans('langEmail') }}</label>
                                <div class='col-sm-12'>
                                    <input class='form-control' placeholder="{{ trans('langEmail') }}" type='text' name='email' id='email' value='{{ $email }}'>
                                </div>
                            </div>  
                     
                            <div class='form-group mt-4'>
                                <label for='am' class='col-sm-12 control-label-notes'>{{ trans('langAm') }}</label>
                                <div class='col-sm-12'>
                                    <input class='form-control' placeholder="{{ trans('langAm') }}" type='text' name='am' id='am' value='{{ $am }}'>
                                </div>
                            </div>
                      
                            <div class='form-group mt-4'>
                                <label class='col-sm-12 control-label-notes'>{{ trans('langUserType') }}</label>
                                <div class='col-sm-12'>
                                    {!! selection($usertype_data, 'user_type', 0, 'class="form-select"') !!}
                                </div>
                            </div>
                      
                            <div class='form-group mt-4'>
                                <label class='col-sm-12 control-label-notes'>{{ trans('langAuthMethod') }}</label>
                                <div class='col-sm-12'>
                                    {!! selection($authtype_data, 'auth_type', 0, 'class="form-select"') !!}
                                </div>
                            </div>
                   
                            <div class='form-group mt-4'>
                                <label class='col-sm-12 control-label-notes'>{{ trans('langRegistrationDate') }}</label>
                                    <div class='row'>
                                        <div class='col-6'>
                                            {!! selection(['1' => trans('langAfter'), '2' => trans('langBefore')], 'reg_flag', $reg_flag, 'class="form-select"') !!}
                                        </div>
                                        <div class='col-6'>       
                                            <input class='form-control' name='user_registered_at' id='id_user_registered_at' type='text' value='{{ $user_registered_at }}' placeholder='{{ trans('langRegistrationDate') }}'>
                                        </div> 
                                    </div> 
                                
                            </div>
                         
                            <div class='form-group mt-4'>
                                <label class='col-sm-12 control-label-notes'>{{ trans('langExpirationDate') }}</label>
                                <div class='col-sm-12'>
                                    <input class='form-control' name='user_expires_until' id='id_user_expires_until' type='text' value='{{ $user_expires_until }}' data-date-format='dd-mm-yyyy' placeholder='{{ trans('langUntil') }}'>
                                </div>
                            </div>
                          
                            <div class='form-group mt-4'>
                                <label class='col-sm-12 control-label-notes'>{{ trans('langEmailVerified') }}</label>
                                <div class='col-sm-12'>
                                    {!! selection($verified_mail_data, 'verified_mail', $verified_mail, 'class="form-select"') !!}
                                </div>
                            </div>
                      
                            <div class='form-group mt-4'>
                                <label for='dialog-set-value' class='col-sm-12 control-label-notes'>{{ trans('langFaculty') }}:</label>
                                <div class='col-sm-12'>
                                    {!! $html !!}
                                </div>
                            </div>
                           
                            <div class='form-group mt-4'>
                                <label for='search_type' class='col-sm-12 control-label-notes'>{{ trans('langSearchFor') }}</label>
                                <div class='col-sm-12'>
                                    <select class='form-select' name='search_type' id='search_type'>
                                    <option value='exact'>{{ trans('langSearchExact') }}</option>
                                    <option value='begin'>{{ trans('langSearchStartsWith') }}</option>
                                    <option value='contains' selected>{{ trans('langSearchSubstring') }}</option>
                                    </select>
                                </div>
                            </div>
                         
                            <div class='form-group mt-4'>
                                <div class='col-sm-10 col-sm-offset-2'>
                                    <div class='checkbox'>
                                    <label>
                                        <input type='checkbox' name='search' value='inactive'{{ $inactive_checked ? " checked" : "" }}>
                                        {{ trans('langInactiveUsers') }}
                                    </label>
                                    </div> 
                                </div>
                            </div>    
                       
                            <div class='form-group mt-5 d-flex justify-content-center align-items-center'>
                                <input class='btn submitAdminBtn' type='submit' value='{{ trans('langSearch') }}'>
                                <a class='btn btn-outline-secondary cancelAdminBtn ms-2' href='index.php'>{{ trans('langCancel') }}</a>  
                            </div>
                        </fieldset>
                        </form>
                    </div></div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection