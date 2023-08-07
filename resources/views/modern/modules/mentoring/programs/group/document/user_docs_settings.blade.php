
    <div class='col-12'>
        <div class='form-wrapper form-edit mb-3 rounded shadow-sm p-3'>
            <form action='{{ $base_url }}' method='post' class='form-horizontal' role='form'>

                {!! $group_hidden_input !!}

                <p class='text-start TextSemiBold fs-6'>{{ trans('langManageDocs')}}<small class='small-text'>{{ trans('langCreateEditDelDoc')}}</small>?</p>
                <div class='form-group mt-0 d-inline-flex'>
                    <div class='checkbox'>
                        <label>
                            <input type='checkbox' id='settingsDocYes' name='settingsDocYes' value="1" {!! $setting_value == 1 ? 'checked' : '' !!}>{{ trans('langYes') }}
                            <input class='ms-3' id='settingsDocNo' type='checkbox' name='settingsDocNo' value="0" {!! $setting_value == 0 ? 'checked' : '' !!}>{{ trans('langNo')}}
                        </label>
                    </div>     
                </div>
               

                <div class='form-group mt-3'>
                    <div class='col-12 d-flex justify-content-start align-items-center'>
                        <button class='btn submitAdminBtn' type='submit'>{{ trans('langCreate') }}</button>
                        <a class='btn cancelAdminBtn ms-1' href='{{ $backUrl }}'>{{ trans('langCancel') }}</a>
                    </div>
                </div>
                {!! generate_csrf_token_form_field() !!}
            </form>
        </div>
    </div>

