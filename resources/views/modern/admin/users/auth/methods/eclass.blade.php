                
                <div class='row p-2'></div>
                
                <div class='form-group'>
                    <label for='auth_title' class='col-sm-6 control-label-notes'>{{ trans('langAuthTitle') }}:</label>
                    <div class='col-sm-12'>
                        <input class='form-control' name='auth_title' id='auth_title' type='text' value='{{ $auth_data['auth_title'] }}'>
                    </div>
                </div>

                <div class='row p-2'></div>

                <div class='form-group'>
                    <label for='auth_instructions' class='col-sm-6 control-label-notes'>{{ trans('langInstructionsAuth') }}:</label>
                    <div class='col-sm-12'>
                        <textarea class='form-control' name='auth_instructions' id='auth_instructions' rows='10'>{{ $auth_data['auth_instructions'] }}</textarea>
                    </div>
                </div>  