                <div class='form-group'>
                    <label for='pop3host' class='col-sm-2 control-label'>{{ trans('langpop3host') }}:</label>
                    <div class='col-sm-10'>
                        <input class='form-control' name='pop3host' id='pop3host' type='text' value='{{ isset($auth_data['pop3host']) ? $auth_data['pop3host'] : '' }}'>
                    </div>
                </div>
                <div class='form-group'>
                    <label for='pop3port' class='col-sm-2 control-label'>{{ trans('langpop3port') }}:</label>
                    <div class='col-sm-10'>
                        <input type='text' class='form-control' value='110' name='pop3port' id='pop3port' disabled>
                    </div>
                </div>