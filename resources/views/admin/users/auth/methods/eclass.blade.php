                <div class='form-group'>
                    <label for='auth_title' class='col-sm-2 control-label'>{{ trans('langAuthTitle') }}:</label>
                    <div class='col-sm-10'>
                        <input class='form-control' name='auth_title' id='auth_title' type='text' value='{{ $auth_data['auth_title'] }}'>
                    </div>
                </div>
                <div class='form-group'>
                    <label for='auth_instructions' class='col-sm-2 control-label'>{{ trans('langInstructionsAuth') }}:</label>
                    <div class='col-sm-10'>
                        <textarea class='form-control' name='auth_instructions' id='auth_instructions' rows='10'>{{ $auth_data['auth_instructions'] }}</textarea>
                    </div>
                </div>  