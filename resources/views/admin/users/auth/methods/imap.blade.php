                <div class='form-group'>
                    <label for='imaphost' class='col-sm-2 control-label'>{{ trans('langimaphost') }}:</label>
                    <div class='col-sm-10'>
                        <input class='form-control' name='imaphost' id='imaphost' type='text' value='{{ isset($auth_data['imaphost']) ? $auth_data['imaphost'] : ''}}'>
                    </div>
                </div>
                <div class='form-group'>
                    <label for='imaport' class='col-sm-2 control-label'>{{ trans('langimapport') }}:</label>
                    <div class='col-sm-10'>
                        <input class='form-control' name='imaport' id='imaport' type='text' value='143' disabled>
                    </div>
                </div>