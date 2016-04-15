                <div class='form-group'>
                    <label for='dbhost' class='col-sm-2 control-label'>{{ trans('langdbhost') }}:</label>
                    <div class='col-sm-10'>
                        <input class='form-control' name='dbhost' id='dbhost' type='text' value='{{ isset($auth_data['dbhost']) ? $auth_data['dbfieldpass'] : '' }}'>
                    </div>
                </div>
                <div class='form-group'>
                    <label for='dbname' class='col-sm-2 control-label'>{{ trans('langdbname') }}:</label>
                    <div class='col-sm-10'>
                        <input class='form-control' name='dbname' id='dbname' type='text' value='{{ isset($auth_data['dbname']) ? $auth_data['dbfieldpass'] : '' }}'>
                    </div>
                </div>
                <div class='form-group'>
                    <label for='dbuser' class='col-sm-2 control-label'>{{ trans('langdbuser') }}:</label>
                    <div class='col-sm-10'>
                        <input class='form-control' name='dbuser' id='dbuser' type='text' value='{{ isset($auth_data['dbuser']) ? $auth_data['dbfieldpass'] : '' }}' autocomplete='off'>
                    </div>
                </div>
                <div class='form-group'>
                    <label for='dbpass' class='col-sm-2 control-label'>{{ trans('langdbpass') }}:</label>
                    <div class='col-sm-10'>
                        <input class='form-control' name='dbpass' id='dbpass' type='password' value='{{ isset($auth_data['dbpass']) ? $auth_data['dbfieldpass'] : '' }}' autocomplete='off'>
                    </div>
                </div>
                <div class='form-group'>
                    <label for='dbtable' class='col-sm-2 control-label'>{{ trans('langdbtable') }}:</label>
                    <div class='col-sm-10'>
                        <input class='form-control' name='dbtable' id='dbtable' type='text' value='{{ isset($auth_data['dbtable']) ? $auth_data['dbfieldpass'] : '' }}'>
                    </div>
                </div>
                <div class='form-group'>
                    <label for='dbfielduser' class='col-sm-2 control-label'>{{ trans('langdbfielduser') }}:</label>
                    <div class='col-sm-10'>
                        <input class='form-control' name='dbfielduser' id='dbfielduser' type='text' value='{{ isset($auth_data['dbfielduser']) ? $auth_data['dbfieldpass'] : '' }}'>
                    </div>
                </div>
                <div class='form-group'>
                    <label for='dbfieldpass' class='col-sm-2 control-label'>{{ trans('langdbfieldpass') }}:</label>
                    <div class='col-sm-10'>
                        <input class='form-control' name='dbfieldpass' id='dbfieldpass' type='text' value='{{ isset($auth_data['dbfieldpass']) ? $auth_data['dbfieldpass'] : '' }}'>
                    </div>
                </div>
                <div class='form-group'>
                    <label for='dbpassencr' class='col-sm-2 control-label'>{{ trans('langdbpassencr') }}:</label>
                    <div class='col-sm-10'>
                        {!! selection(
                            [
                                'none' => 'Plain Text',
                                'md5' => 'MD5',
                                'ehasher' => 'Eclass Hasher'
                            ], 
                            'dbpassencr', isset($auth_data['dbpassencr']) ? $auth_data['dbpassencr'] : 'none') !!}
                    </div>
                </div>