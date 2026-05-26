@push('bottom_scripts')
<script>
  $(function () {
    $('#uid_attr_is_username').on('change', function () {
      if ($(this).is(':checked')) {
        $('#UsernamePrefixField').slideUp('fast');
      } else {
        $('#UsernamePrefixField').removeClass('d-none').slideDown('fast');
      }
    });
  });
</script>
@endpush

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
        <label for='uid_attr' class='col-sm-12 control-label-notes'>{{ trans('langSSOUsernameAttr') }}:</label>
        <div class='col-sm-12'>
            <input class='form-control' name='uid_attr' id='uid_attr' type='text' value="{{ isset($auth_data['uid_attr']) ? q($auth_data['uid_attr']) : '' }}">
        </div>
        <div class='col-sm-12 mt-2'>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="uid_attr_is_username" value="1" id="uid_attr_is_username" @if ($auth_data['uid_attr_is_username']) checked @endif>
                <label class="form-check-label" for="uid_attr_is_username">
                    {{ trans('langSSOUidIsUsername') }}
                </label>
            </div>
        </div>
    </div>

    <div class='form-group mt-4 {{ $auth_data['uid_attr_is_username']? 'd-none': '' }}' id='UsernamePrefixField'>
        <label for='UsernamePrefix' class='col-sm-12 control-label-notes'>{{ trans('langMultiRegPrefix') }}:</label>
        <div class='col-sm-12'>
            <input class='form-control' name='username_prefix' id='UsernamePrefix' type='text' value="{{ isset($auth_data['username_prefix']) ? q($auth_data['username_prefix']) : '' }}">
            {{ trans('langSSOUsernameHelp') }}
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

    <div class='form-group mt-4'>
        <label for='auth_instructions' class='col-sm-12 control-label-notes'>{{ trans('langInstructionsAuth') }}:</label>
        <div class='col-sm-12'>
            <textarea class='form-control' name='auth_instructions' id='auth_instructions' rows='10'>{{ isset($auth_instructions) ? q($auth_instructions) : '' }}</textarea>
        </div>
    </div>
