                
                
                <div class='col-12'>
                   <div class='alert alert-info'><i class='fa-solid fa-circle-info fa-lg'></i><span>{{ ucfirst(trans('langAuthenticateVia')) }} {{ $auth_data['auth_name'] }}</span></div>
                </div>

                <div class='form-group mt-3'>
                    <label for='hybridauth_id_key' class='col-sm-12 control-label-notes'>{{ ucfirst($auth_data['auth_name']) }} Id/Key:</label>
                    <div class='col-sm-12'>
                        <input class='form-control' name='hybridauth_id_key' id='hybridauth_id_key' type='text' value='{{ isset($auth_data['key']) ? $auth_data['key'] : '' }}'>
                    </div>
                </div> 

             

                <div class='form-group mt-3'>
                    <label for='hybridauth_secret' class='col-sm-12 control-label-notes'>{{ ucfirst($auth_data['auth_name']) }} Secret:</label>
                    <div class='col-sm-12'>
                        <input class='form-control' name='hybridauth_secret' id='hybridauth_secret' type='text' value='{{ isset($auth_data['secret']) ? $auth_data['secret'] : '' }}'>
                    </div>
                </div> 

    

                <div class='form-group mt-3'>
                    <label for='auth_instructions' class='col-sm-12 control-label-notes'>{{ trans('langInstructionsAuth') }}:</label>
                    <div class='col-sm-12'>
                        <textarea class='form-control' name='hybridauth_instructions' id='hybridauth_instructions' rows='10'>{{ $auth_data['auth_instructions'] }}</textarea>
                    </div>
                </div>