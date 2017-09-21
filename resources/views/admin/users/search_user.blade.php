@extends('layouts.default')

@section('content')
    {!! isset($action_bar) ?  $action_bar : '' !!}
    <div class='form-wrapper'>
        <form class='form-horizontal' role='form' action='listusers.php' method='get' name='user_search'>
        <fieldset>
            <div class='form-group'>
                <label for='uname' class='col-sm-2 control-label'>{{ trans('langUsername') }}:</label>
                <div class='col-sm-10'>
                    <input class='form-control' type='text' name='uname' id='uname' value='{{ $uname }}'>
                </div>
            </div>
            <div class='form-group'>
                <label for='fname' class='col-sm-2 control-label'>{{ trans('langName') }}:</label>
                <div class='col-sm-10'>
                    <input class='form-control' type='text' name='fname' id='fname' value='{{ $fname }}'>
                </div>
            </div>
            <div class='form-group'>
                <label for='lname' class='col-sm-2 control-label'>{{ trans('langSurname') }}:</label>
                <div class='col-sm-10'>
                    <input class='form-control' type='text' name='lname' id='lname' value='{{ $lname }}'>
                </div>
            </div>
            <div class='form-group'>
                <label for='email' class='col-sm-2 control-label'>{{ trans('langEmail') }}:</label>
                <div class='col-sm-10'>
                    <input class='form-control' type='text' name='email' id='email' value='{{ $email }}'>
                </div>
            </div>  
            <div class='form-group'>
                <label for='am' class='col-sm-2 control-label'>{{ trans('langAm') }}:</label>
                <div class='col-sm-10'>
                    <input class='form-control' type='text' name='am' id='am' value='{{ $am }}'>
                </div>
            </div>
            <div class='form-group'>
                <label class='col-sm-2 control-label'>{{ trans('langUserType') }}:</label>
                <div class='col-sm-10'>
                    {!! selection($usertype_data, 'user_type', 0, 'class="form-control"') !!}
                </div>
            </div>
            <div class='form-group'>
                <label class='col-sm-2 control-label'>{{ trans('langAuthMethod') }}:</label>
                <div class='col-sm-10'>
                    {!! selection($authtype_data, 'auth_type', 0, 'class="form-control"') !!}
                </div>
            </div>
            <div class='form-group'>
                <label class='col-sm-2 control-label'>{{ trans('langRegistrationDate') }}:</label>
                <div class='col-sm-5'>
                    {!! selection(['1' => trans('langAfter'), '2' => trans('langBefore')], 'reg_flag', $reg_flag, 'class="form-control"') !!}
                </div>
                <div class='col-sm-5'>       
                    <input class='form-control' name='user_registered_at' id='id_user_registered_at' type='text' value='{{ $user_registered_at }}' placeholder='{{ trans('langRegistrationDate') }}'>
                </div>   
            </div>
            <div class='form-group'>
	        	<label class='col-sm-2 control-label'>{{ trans('langExpirationDate') }}:</label>
    	    	<div class='col-sm-10'>
        	    	<input class='form-control' name='user_expires_until' id='id_user_expires_until' type='text' value='{{ $user_expires_until }}' data-date-format='dd-mm-yyyy' placeholder='{{ trans('langUntil') }}'>
        		</div>
        	</div>
            <div class='form-group'>
                <label class='col-sm-2 control-label'>{{ trans('langEmailVerified') }}:</label>
                <div class='col-sm-10'>
                    {!! selection($verified_mail_data, 'verified_mail', $verified_mail, 'class="form-control"') !!}
                </div>
            </div>
            <div class='form-group'>
                <label for='dialog-set-value' class='col-sm-2 control-label'>{{ trans('langFaculty') }}:</label>
                <div class='col-sm-10'>
                    {!! $html !!}
                </div>
            </div>
            <div class='form-group'>
                <label for='search_type' class='col-sm-2 control-label'>{{ trans('langSearchFor') }}:</label>
                <div class='col-sm-10'>
                    <select class='form-control' name='search_type' id='search_type'>
                      <option value='exact'>{{ trans('langSearchExact') }}</option>
                      <option value='begin'>{{ trans('langSearchStartsWith') }}</option>
                      <option value='contains' selected>{{ trans('langSearchSubstring') }}</option>
                    </select>
                </div>
            </div>
            <div class='form-group'>
                <div class='col-sm-10 col-sm-offset-2'>
                    <div class='checkbox'>
                      <label>
                        <input type='checkbox' name='search' value='inactive'{{ $inactive_checked ? " checked" : "" }}>
                        {{ trans('langInactiveUsers') }}
                      </label>
                    </div> 
                </div>
            </div>    
            <div class='form-group'>
                <div class='col-sm-10 col-sm-offset-2'>
                    <input class='btn btn-primary' type='submit' value='{{ trans('langSearch') }}'>
                    <a class='btn btn-default' href='index.php'>{{ trans('langCancel') }}</a>
                </div>
            </div>
        </fieldset>
        </form>
    </div>
@endsection