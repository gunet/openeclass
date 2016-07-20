                <div class='form-group'>
                    <label for='cas_host' class='col-sm-2 control-label'>{{ trans('langcas_host') }}:</label>
                    <div class='col-sm-10'>
                        <input class='form-control' name='cas_host' id='cas_host' type='text' value='{{ isset($auth_data['cas_host']) ? $auth_data['cas_host'] : '' }}'>
                    </div>
                </div>
                <div class='form-group'>
                    <label for='cas_port' class='col-sm-2 control-label'>{{ trans('langcas_port') }}:</label>
                    <div class='col-sm-10'>
                        <input class='form-control' name='cas_port' id='cas_port' type='text' value='{{ isset($auth_data['cas_port']) ? $auth_data['cas_port'] : '443' }}'>
                    </div>
                </div>    
                <div class='form-group'>
                    <label for='cas_context' class='col-sm-2 control-label'>{{ trans('langcas_context') }}:</label>
                    <div class='col-sm-10'>
                        <input class='form-control' name='cas_context' id='cas_context' type='text' value='{{ isset($auth_data['cas_context']) ? $auth_data['cas_context'] : '' }}'>
                    </div>
                </div> 
                <div class='form-group'>
                    <label for='cas_logout' class='col-sm-2 control-label'>{{ trans('langcas_logout') }}:</label>
                    <div class='col-sm-10'>
                        <input class='form-control' name='cas_logout' id='cas_logout' type='text' value='{{ isset($auth_data['cas_logout']) ? $auth_data['cas_logout'] : '' }}'>
                    </div>
                </div>
                <div class='form-group'>
                    <label for='cas_logout' class='col-sm-2 control-label'>{{ trans('langcas_ssout') }}:</label>
                    <div class='col-sm-10'>
                        {!! selection(
                            [
                                0 => trans("m['no']"), 
                                1 => trans("m['yes']")
                            ], 
                            'cas_ssout', isset($auth_data['cas_ssout']) ? $auth_data['cas_ssout'] : 0, 'class="form-control"') !!}
                    </div>
                </div>
                <div class='form-group'>
                    <label for='cas_cachain' class='col-sm-2 control-label'>{{ trans('langcas_cachain') }}:</label>
                    <div class='col-sm-10'>
                        <input class='form-control' name='cas_cachain' id='cas_cachain' type='text' value='{{ isset($auth_data['cas_cachain']) ? $auth_data['cas_cachain'] : '' }}'>
                    </div>
                </div>  
                <div class='form-group'>
                    <label for='casusermailattr' class='col-sm-2 control-label'>{{ trans('langcasusermailattr') }}:</label>
                    <div class='col-sm-10'>
                        <input class='form-control' name='casusermailattr' id='casusermailattr' type='text' value='{{ isset($auth_data['casusermailattr']) ? $auth_data['casusermailattr'] : 'mail' }}'>
                    </div>
                </div>       
                <div class='form-group'>
                    <label for='casuserfirstattr' class='col-sm-2 control-label'>{{ trans('langcasuserfirstattr') }}:</label>
                    <div class='col-sm-10'>
                        <input class='form-control' name='casuserfirstattr' id='casuserfirstattr' type='text' value='{{ isset($auth_data['casuserfirstattr']) ? $auth_data['casuserfirstattr'] : 'givenName' }}'>
                    </div>
                </div> 
                <div class='form-group'>
                    <label for='casuserlastattr' class='col-sm-2 control-label'>{{ trans('langcasuserlastattr') }}:</label>
                    <div class='col-sm-10'>
                        <input class='form-control' name='casuserlastattr' id='casuserlastattr' type='text' value='{{ isset($auth_data['casuserlastattr']) ? $auth_data['casuserlastattr'] : 'sn' }}'>
                    </div>
                </div>
                <div class='form-group'>
                    <label for='casuserstudentid' class='col-sm-2 control-label'>{{ trans('langcasuserstudentid') }}:</label>
                    <div class='col-sm-10'>
                        <input class='form-control' name='casuserstudentid' id='casuserstudentid' type='text' value='{{ isset($auth_data['casuserstudentid']) ? $auth_data['casuserstudentid'] : '' }}'>
                    </div>
                </div>
                <div class='form-group'>
                    <label for='cas_altauth' class='col-sm-2 control-label'>{{ trans('langcas_altauth') }}:</label>
                    <div class='col-sm-10'>
                        {!! selection(
                            [
                                0 => '-',
                                1 => 'eClass',
                                2 => 'POP3',
                                3 => 'IMAP',
                                4 => 'LDAP',
                                5 => 'External DB'
                            ], 
                            'cas_altauth', isset($auth_data['cas_altauth']) ? $auth_data['cas_altauth'] : 0, 'class="form-control"') !!}
                    </div>
                </div>