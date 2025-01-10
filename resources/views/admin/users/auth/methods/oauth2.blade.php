

<div class='form-group'>
        <label for='apiBaseUrl' class='col-sm-12 control-label-notes'>API Base URL:</label>
        <div class='col-sm-12'>
            <input class='form-control' name='apiBaseUrl' id='apiBaseUrl' type='text' value="{{ isset($auth_data['apiBaseUrl']) ? q($auth_data['apiBaseUrl']) : '' }}" placeholder='https://sso.example.com/oauth2.0/'>
        </div>
    </div>
    <div class='form-group mt-4'>
        <label for='apiID' class='col-sm-12 control-label-notes'>Application ID:</label>
        <div class='col-sm-12'>
            <input class='form-control' name='apiID' id='apiID' type='text' value="{{ isset($auth_data['id']) ? q($auth_data['id']) : '' }}">
        </div>
    </div>
    <div class='form-group mt-4'>
        <label for='apiSecret' class='col-sm-12 control-label-notes'>Application Secret:</label>
        <div class='col-sm-12'>
            <input class='form-control' name='apiSecret' id='apiSecret' type='text' value="{{ isset($auth_data['secret']) ? q($auth_data['secret']) : '' }}">
        </div>
    </div>
    <div class='form-group mt-4'>
        <label for='authorizePath' class='col-sm-12 control-label-notes'>Authorize Path:</label>
        <div class='col-sm-12'>
            <input class='form-control' name='authorizePath' id='authorizePath' type='text' value="{{ isset($auth_data['authorizePath']) ? q($auth_data['authorizePath']) : '' }}">
        </div>
    </div>
    <div class='form-group mt-4'>
        <label for='profileMethod' class='col-sm-12 control-label-notes'>Profile Get Method:</label>
        <div class='col-sm-12'>
            <input class='form-control' name='profileMethod' id='profileMethod' type='text' value="{{ isset($auth_data['profileMethod']) ? q($auth_data['profileMethod']) : '' }}">
        </div>
    </div>
    <div class='form-group mt-4'>
        <label for='accessTokenPath' class='col-sm-12 control-label-notes'>Access Token Path:</label>
        <div class='col-sm-12'>
            <input class='form-control' name='accessTokenPath' id='accessTokenPath' type='text' value="{{ isset($auth_data['accessTokenPath']) ? q($auth_data['accessTokenPath']) : '' }}">
        </div>
    </div>
    <div class='form-group mt-4'>
        <label for='casusermailattr' class='col-sm-12 control-label-notes'>{{ trans('langSSOMailAttr') }}:</label>
        <div class='col-sm-12'>
            <input class='form-control' name='casusermailattr' id='casusermailattr' type='text' value="{{ isset($auth_data['casusermailattr']) ? q($auth_data['casusermailattr']) : '' }}">
        </div>
    </div>
    <div class='form-group mt-4'>
        <label for='casuserfirstattr' class='col-sm-12 control-label-notes'>{{ trans('langSSOGivenNameAttr') }}:</label>
        <div class='col-sm-12'>
            <input class='form-control' name='casuserfirstattr' id='casuserfirstattr' type='text' value="{{ isset($auth_data['casuserfirstattr']) ? q($auth_data['casuserfirstattr']) : '' }}">
        </div>
    </div>
    <div class='form-group mt-4'>
        <label for='casuserlastattr' class='col-sm-12 control-label-notes'>{{ trans('langSSOSurnameAttr') }}:</label>
        <div class='col-sm-12'>
            <input class='form-control' name='casuserlastattr' id='casuserlastattr' type='text' value="{{ isset($auth_data['casuserlastattr']) ? q($auth_data['casuserlastattr']) : '' }}">
        </div>
    </div>
    <div class='form-group mt-4'>
        <label for='casuserstudentid' class='col-sm-12 control-label-notes'>{{ trans('langSSOStudentIDAttr') }}:</label>
        <div class='col-sm-12'>
            <input class='form-control' name='casuserstudentid' id='casuserstudentid' type='text' value="{{ isset($auth_data['casuserstudentid']) ? q($auth_data['casuserstudentid']) : '' }}">
        </div>
    </div>

    <div class='form-group mt-4'>
        <label for='auth_title' class='col-sm-12 control-label-notes'>{{ trans('langAuthTitle') }}:</label>
        <div class='col-sm-12'>
            <input class='form-control' name='auth_title' id='auth_title' type='text' value="{{ isset($auth_title) ? q($auth_title) : '' }}">
        </div>
    </div>
    <div class='form-group mt-4'>
        <label for='auth_instructions' class='col-sm-12 control-label-notes'>{{ trans('langInstructionsAuth') }}:</label>
        <div class='col-sm-12'>
            <textarea class='form-control' name='auth_instructions' id='auth_instructions' rows='10'>{{ isset($auth_instructions) ? q($auth_instructions) : '' }}</textarea>
        </div>
    </div>