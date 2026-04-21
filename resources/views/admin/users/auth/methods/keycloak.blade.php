

<div class='form-group'>
        <label for='apiBaseUrl' class='col-sm-12 control-label-notes'>Provider Base URL:</label>
        <div class='col-sm-12'>
            <input class='form-control' name='apiBaseUrl' id='apiBaseUrl' type='text' value="{{ isset($auth_data['apiBaseUrl']) ? q($auth_data['apiBaseUrl']) : '' }}" placeholder='https://sso.example.com'>
        </div>
    </div>
    <div class='form-group mt-4'>
        <label for='apiID' class='col-sm-12 control-label-notes'>Realm:</label>
        <div class='col-sm-12'>
            <input class='form-control' name='realm' id='realm' type='text' value="{{ isset($auth_data['realm']) ? q($auth_data['realm']) : '' }}">
        </div>
    </div>
    <div class='form-group mt-4'>
        <label for='apiID' class='col-sm-12 control-label-notes'>Client ID:</label>
        <div class='col-sm-12'>
            <input class='form-control' name='apiID' id='apiID' type='text' value="{{ isset($auth_data['id']) ? q($auth_data['id']) : '' }}">
        </div>
    </div>
    <div class='form-group mt-4'>
        <label for='apiSecret' class='col-sm-12 control-label-notes'>Client Secret:</label>
        <div class='col-sm-12'>
            <input class='form-control' name='apiSecret' id='apiSecret' type='text' value="{{ isset($auth_data['secret']) ? q($auth_data['secret']) : '' }}">
        </div>
    </div>
    <div class='form-group mt-4'>
        <label for='userstudentid' class='col-sm-12 control-label-notes'>{{ trans('langSSOStudentIDAttr') }}:</label>
        <div class='col-sm-12'>
            <input class='form-control' name='userstudentid' id='userstudentid' type='text' value="{{ isset($auth_data['userstudentid']) ? q($auth_data['userstudentid']) : '' }}">
        </div>
    </div>

    <div class='form-group mt-4'>
        <label for='auth_title' class='col-sm-12 control-label-notes'>{{ trans('langAuthTitle') }}:</label>
        <div class='col-sm-12'>
            <input class='form-control' name='auth_title' id='auth_title' type='text' value="{{ isset($auth_title) ? q($auth_title) : '' }}">
        </div>
    </div>
	 <div class='form-group mt-3 altauth'>
        <label for='altauth' class='col-sm-12 control-label-notes'>{{ trans('langcas_altauth') }}:</label>
        <div class='col-sm-12'>
        {!! selection(
            [
                0 => '-',
                1 => 'eClass',
                2 => 'POP3',
                3 => 'IMAP',
                4 => 'LDAP',
                5 => 'External DB'
            ],
            'altauth', isset($auth_data['altauth']) ? $auth_data['altauth'] : 0, 'class="form-control"') !!}
        </div>
    </div>
    <div class='form-group mt-4'>
        <label for='auth_instructions' class='col-sm-12 control-label-notes'>{{ trans('langInstructionsAuth') }}:</label>
        <div class='col-sm-12'>
            <textarea class='form-control' name='auth_instructions' id='auth_instructions' rows='10'>{{ isset($auth_instructions) ? q($auth_instructions) : '' }}</textarea>
        </div>
    </div>
