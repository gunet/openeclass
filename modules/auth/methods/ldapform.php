<?php

/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2014  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ======================================================================== */


if (!function_exists('ldap_connect')) { // Is php5-ldap really there?
    Session::Messages($langLdapNotWork, 'alert-danger');
}

if (empty($auth_data['auth_settings'])) {
    $auth_data['ldap'] = '';
    $auth_data['ldaphost'] = '';
    $auth_data['ldap_base'] = '';
    $auth_data['ldapbind_dn'] = '';
    $auth_data['ldapbind_pw'] = '';
    $auth_data['ldap_login_attr'] = 'uid';
    $auth_data['ldap_login_attr2'] = '';
    $auth_data['ldap_studentid'] = '';
    $auth_data['ldap_firstname_attr'] = '';
    $auth_data['ldap_surname_attr'] = '';
    $auth_data['ldap_mail_attr'] = 'mail';
}

if (!isset($auth_data['ldap_studentid'])) {
    $auth_data['ldap_studentid'] = '';
}

if (!isset($auth_data['ldap_mail_attr'])) {
    $auth_data['ldap_mail_attr'] = '';
}

$tool_content .= "
    <div class='form-group'>
        <label for='ldaphost' class='col-sm-2 control-label'>$langldap_host_url:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='ldaphost' id='ldaphost' type='text' value='" . q($auth_data['ldaphost']) . "'>
        </div>
    </div>     
    <div class='form-group'>
        <label for='ldap_base' class='col-sm-2 control-label'>$langldap_base:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='ldap_base' id='ldap_base' type='text' value='" . q($auth_data['ldap_base']) . "'>
        </div>
    </div>  
    <div class='form-group'>
        <label for='ldapbind_dn' class='col-sm-2 control-label'>$langldap_bind_dn:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='ldapbind_dn' id='ldapbind_dn' type='text' value='" . q($auth_data['ldapbind_dn']) . "'>
        </div>
    </div>      
    <div class='form-group'>
        <label for='ldapbind_pw' class='col-sm-2 control-label'>$langldap_bind_pw:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='ldapbind_pw' id='ldapbind_pw' type='password' value='" . q($auth_data['ldapbind_pw']) . "' autocomplete='off'>
        </div>
    </div>  
    <div class='form-group'>
        <label for='ldap_login_attr' class='col-sm-2 control-label'>$langldap_login_attr:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='ldap_login_attr' id='ldap_login_attr' type='text' value='" . q($auth_data['ldap_login_attr']) . "'>
        </div>
    </div>  
    <div class='form-group'>
        <label for='ldap_login_attr2' class='col-sm-2 control-label'>$langldap_login_attr2:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='ldap_login_attr2' id='ldap_login_attr2' type='text' value='" . q($auth_data['ldap_login_attr2']) . "'>
        </div>
    </div>
    <div class='form-group'>
        <label for='ldap_login_attr' class='col-sm-2 control-label'>$langldap_mail_attr:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='ldap_mail_attr' id='ldap_mail_attr' type='text' value='" . q($auth_data['ldap_mail_attr']) . "'>
        </div>
    </div>
    <div class='form-group'>
        <label for='ldap_firstname_attr' class='col-sm-2 control-label'>$langldapfirstnameattr:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='ldap_firstname_attr' id='ldap_firstname_attr' type='text' value='" . q($auth_data['ldap_firstname_attr']) . "'>
        </div>
    </div>
    <div class='form-group'>
        <label for='ldap_surname_attr' class='col-sm-2 control-label'>$langldapsurnameattr:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='ldap_surname_attr' id='ldap_surname_attr' type='text' value='" . q($auth_data['ldap_surname_attr']) . "'>
        </div>
    </div>
    <div class='form-group'>
        <label for='ldap_studentid' class='col-sm-2 control-label'>$langldap_id_attr:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='ldap_studentid' id='ldap_studentid' type='text' value='" . q($auth_data['ldap_studentid']) . "'>
        </div>
    </div>" .
    eclass_auth_form($auth_data['auth_title'], $auth_data['auth_instructions']);

