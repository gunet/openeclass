          
            
            <div class='form-group'>
                <label for='ldaphost' class='col-sm-12 control-label-notes'>{{ trans('langldap_host_url') }}</label>
                <div class='col-sm-12'>
                    <input class='form-control' placeholder="{{ trans('langldap_host_url') }}..." name='ldaphost' id='ldaphost' type='text' value='{{ isset($auth_data['ldaphost']) ? $auth_data['ldaphost'] : '' }}'>
                </div>
            </div>     

            

            <div class='form-group mt-3'>
                <label for='ldap_base' class='col-sm-12 control-label-notes'>{{ trans('langldap_base') }}</label>
                <div class='col-sm-12'>
                    <input class='form-control' placeholder="{{ trans('langldap_base') }}..." name='ldap_base' id='ldap_base' type='text' value='{{ isset($auth_data['ldap_base']) ? $auth_data['ldap_base'] : '' }}'>
                </div>
            </div>  


            <div class='form-group mt-3'>
                <label for='ldapbind_dn' class='col-sm-12 control-label-notes'>{{ trans('langldap_bind_dn') }}</label>
                <div class='col-sm-12'>
                    <input class='form-control' placeholder="{{ trans('langldap_bind_dn') }}..." name='ldapbind_dn' id='ldapbind_dn' type='text' value='{{ isset($auth_data['ldapbind_dn']) ? $auth_data['ldapbind_dn'] : '' }}'>
                </div>
            </div>     
            
          

            <div class='form-group mt-3'>
                <label for='ldapbind_pw' class='col-sm-12 control-label-notes'>{{ trans('langldap_bind_pw') }}</label>
                <div class='col-sm-12'>
                    <input class='form-control' placeholder="{{ trans('langldap_bind_pw') }}..." name='ldapbind_pw' id='ldapbind_pw' type='password' value='{{ isset($auth_data['ldapbind_pw']) ? $auth_data['ldapbind_pw'] : '' }}' autocomplete='off'>
                </div>
            </div>  

          

            <div class='form-group mt-3'>
                <label for='ldap_login_attr' class='col-sm-12 control-label-notes'>{{ trans('langldap_login_attr') }}</label>
                <div class='col-sm-12'>
                    <input class='form-control' placeholder="{{ trans('langldap_login_attr') }}..." name='ldap_login_attr' id='ldap_login_attr' type='text' value='{{ isset($auth_data['ldap_login_attr']) ? $auth_data['ldap_login_attr'] : 'uid' }}'>
                </div>
            </div>  

            

            <div class='form-group mt-3'>
                <label for='ldap_login_attr2' class='col-sm-12 control-label-notes'>{{ trans('langldap_login_attr2') }}</label>
                <div class='col-sm-12'>
                    <input class='form-control' placeholder="{{ trans('langldap_login_attr2') }}..." name='ldap_login_attr2' id='ldap_login_attr2' type='text' value='{{ isset($auth_data['ldap_login_attr2']) ? $auth_data['ldap_login_attr2'] : '' }}'>
                </div>
            </div>

        

            <div class='form-group mt-3'>
                <label for='ldap_login_attr' class='col-sm-12 control-label-notes'>{{ trans('langldap_mail_attr') }}</label>
                <div class='col-sm-12'>
                    <input class='form-control' placeholder="{{ trans('langldap_mail_attr') }}..." name='ldap_mail_attr' id='ldap_mail_attr' type='text' value='{{ isset($auth_data['ldap_mail_attr']) ? $auth_data['ldap_mail_attr'] : '' }}'>
                </div>
            </div>  

            <div class='form-group mt-3'>
                <label for='ldap_firstname_attr' class='col-sm-12 control-label-notes'>{{ trans('langldapfirstnameattr') }}</label>
                <div class='col-sm-12'>
                    <input class='form-control' name='ldap_firstname_attr' id='ldap_firstname_attr' type='text'  value='{{ isset($auth_data['ldap_firstname_attr']) ? $auth_data['ldap_firstname_attr'] : '' }}'>
                </div>
            </div>


            <div class='form-group mt-3'>
                <label for='ldap_surname_attr' class='col-sm-12 control-label-notes'>{{ trans('langldapsurnameattr') }}</label>
                <div class='col-sm-12'>
                    <input class='form-control' name='ldap_surname_attr' id='ldap_surname_attr' type='text' value='{{ isset($auth_data['ldap_surname_attr']) ? $auth_data['ldap_surname_attr'] : '' }}'>
                </div>
            </div>

            

            <div class='form-group mt-3'>
                <label for='ldap_studentid' class='col-sm-12 control-label-notes'>{{ trans('langldap_id_attr') }}</label>
                <div class='col-sm-12'>
                    <input class='form-control' placeholder="{{ trans('langldap_id_attr') }}..." name='ldap_studentid' id='ldap_studentid' type='text' value='{{ isset($auth_data['ldap_studentid']) ? $auth_data['ldap_studentid'] : '' }}'>
                </div>
            </div>