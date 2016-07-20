            <div class='form-group'>
                <label for='ldaphost' class='col-sm-2 control-label'>{{ trans('langldap_host_url') }}:</label>
                <div class='col-sm-10'>
                    <input class='form-control' name='ldaphost' id='ldaphost' type='text' value='{{ isset($auth_data['ldaphost']) ? $auth_data['ldaphost'] : '' }}'>
                </div>
            </div>     
            <div class='form-group'>
                <label for='ldap_base' class='col-sm-2 control-label'>{{ trans('langldap_base') }}:</label>
                <div class='col-sm-10'>
                    <input class='form-control' name='ldap_base' id='ldap_base' type='text' value='{{ isset($auth_data['ldap_base']) ? $auth_data['ldap_base'] : '' }}'>
                </div>
            </div>  
            <div class='form-group'>
                <label for='ldapbind_dn' class='col-sm-2 control-label'>{{ trans('langldap_bind_dn') }}:</label>
                <div class='col-sm-10'>
                    <input class='form-control' name='ldapbind_dn' id='ldapbind_dn' type='text' value='{{ isset($auth_data['ldapbind_dn']) ? $auth_data['ldapbind_dn'] : '' }}'>
                </div>
            </div>      
            <div class='form-group'>
                <label for='ldapbind_pw' class='col-sm-2 control-label'>{{ trans('langldap_bind_pw') }}:</label>
                <div class='col-sm-10'>
                    <input class='form-control' name='ldapbind_pw' id='ldapbind_pw' type='password' value='{{ isset($auth_data['ldapbind_pw']) ? $auth_data['ldapbind_pw'] : '' }}' autocomplete='off'>
                </div>
            </div>  
            <div class='form-group'>
                <label for='ldap_login_attr' class='col-sm-2 control-label'>{{ trans('langldap_login_attr') }}:</label>
                <div class='col-sm-10'>
                    <input class='form-control' name='ldap_login_attr' id='ldap_login_attr' type='text' value='{{ isset($auth_data['ldap_login_attr']) ? $auth_data['ldap_login_attr'] : 'uid' }}'>
                </div>
            </div>  
            <div class='form-group'>
                <label for='ldap_login_attr2' class='col-sm-2 control-label'>{{ trans('langldap_login_attr2') }}:</label>
                <div class='col-sm-10'>
                    <input class='form-control' name='ldap_login_attr2' id='ldap_login_attr2' type='text' value='{{ isset($auth_data['ldap_login_attr2']) ? $auth_data['ldap_login_attr2'] : '' }}'>
                </div>
            </div>
            <div class='form-group'>
                <label for='ldap_login_attr' class='col-sm-2 control-label'>{{ trans('langldap_mail_attr') }}:</label>
                <div class='col-sm-10'>
                    <input class='form-control' name='ldap_mail_attr' id='ldap_mail_attr' type='text' value='{{ isset($auth_data['ldap_mail_attr']) ? $auth_data['ldap_mail_attr'] : '' }}'>
                </div>
            </div>  
            <div class='form-group'>
                <label for='ldap_studentid' class='col-sm-2 control-label'>{{ trans('langldap_id_attr') }}:</label>
                <div class='col-sm-10'>
                    <input class='form-control' name='ldap_studentid' id='ldap_studentid' type='text' value='{{ isset($auth_data['ldap_studentid']) ? $auth_data['ldap_studentid'] : '' }}'>
                </div>
            </div>