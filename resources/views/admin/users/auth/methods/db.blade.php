                
                
                
                <div class='form-group'>
                    <label for='dbhost' class='col-sm-12 control-label-notes'>{{ trans('langdbhost') }}:</label>
                    <div class='col-sm-12'>
                        <input class='form-control' name='dbhost' id='dbhost' type='text' value='{{ isset($auth_data['dbhost']) ? $auth_data['dbhost'] : '' }}'>
                    </div>
                </div>

              

                <div class='form-group mt-3'>
                    <label for='dbname' class='col-sm-12 control-label-notes'>{{ trans('langdbname') }}:</label>
                    <div class='col-sm-12'>
                        <input class='form-control' name='dbname' id='dbname' type='text' value='{{ isset($auth_data['dbname']) ? $auth_data['dbname'] : '' }}'>
                    </div>
                </div>

                

                <div class='form-group mt-3'>
                    <label for='dbuser' class='col-sm-12 control-label-notes'>{{ trans('langdbuser') }}:</label>
                    <div class='col-sm-12'>
                        <input class='form-control' name='dbuser' id='dbuser' type='text' value='{{ isset($auth_data['dbuser']) ? $auth_data['dbuser'] : '' }}' autocomplete='off'>
                    </div>
                </div>

               

                <div class='form-group mt-3'>
                    <label for='dbpass' class='col-sm-12 control-label-notes'>{{ trans('langdbpass') }}:</label>
                    <div class='col-sm-12'>
                        <input class='form-control' name='dbpass' id='dbpass' type='password' value='{{ isset($auth_data['dbpass']) ? $auth_data['dbpass'] : '' }}' autocomplete='off'>
                    </div>
                </div>

                

                <div class='form-group mt-3'>
                    <label for='dbtable' class='col-sm-12 control-label-notes'>{{ trans('langdbtable') }}:</label>
                    <div class='col-sm-12'>
                        <input class='form-control' name='dbtable' id='dbtable' type='text' value='{{ isset($auth_data['dbtable']) ? $auth_data['dbtable'] : '' }}'>
                    </div>
                </div>

               

                <div class='form-group mt-3'>
                    <label for='dbfielduser' class='col-sm-12 control-label-notes'>{{ trans('langdbfielduser') }}:</label>
                    <div class='col-sm-12'>
                        <input class='form-control' name='dbfielduser' id='dbfielduser' type='text' value='{{ isset($auth_data['dbfielduser']) ? $auth_data['dbfielduser'] : '' }}'>
                    </div>
                </div>

              

                <div class='form-group mt-3'>
                    <label for='dbfieldpass' class='col-sm-12 control-label-notes'>{{ trans('langdbfieldpass') }}:</label>
                    <div class='col-sm-12'>
                        <input class='form-control' name='dbfieldpass' id='dbfieldpass' type='text' value='{{ isset($auth_data['dbfieldpass']) ? $auth_data['dbfieldpass'] : '' }}'>
                    </div>
                </div>

             

                <div class='form-group mt-3'>
                    <label for='dbpassencr' class='col-sm-12 control-label-notes'>{{ trans('langdbpassencr') }}:</label>
                    <div class='col-sm-12'>
                        {!! selection(
                            [
                                'none' => 'Plain Text',
                                'md5' => 'MD5',
                                'ehasher' => 'Eclass Hasher'
                            ], 
                            'dbpassencr', isset($auth_data['dbpassencr']) ? $auth_data['dbpassencr'] : 'none') !!}
                    </div>
                </div>