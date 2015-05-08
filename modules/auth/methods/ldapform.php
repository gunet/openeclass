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


if (!function_exists('ldap_connect')) { // Is php4-ldap really there?
    $tool_content .= "<div class='alert alert-danger'>$langLdapNotWork</div>";
}

$ldapdata = $auth_data;

if (!empty($ldapdata)) {
    $ldapsettings = $ldapdata['auth_settings'];
    $auth_instructions = $ldapdata['auth_instructions'];
    $auth_title = $ldapdata['auth_title'];
    if (!empty($ldapsettings)) {
        $ldap = explode('|', $ldapsettings);
        $ldaphost = str_replace('ldaphost=', '', $ldap[0]);
        $ldap_base = str_replace('ldap_base=', '', $ldap[1]);
        $ldapbind_dn = str_replace('ldapbind_dn=', '', $ldap[2]);
        $ldapbind_pw = str_replace('ldapbind_pw=', '', $ldap[3]);
        if (isset($ldap[4])) {
            $ldap_login_attr = str_replace('ldap_login_attr=', '', $ldap[4]);
        }
        if (empty($ldap_login_attr)) {
            $ldap_login_attr = 'uid';
        }
        if (isset($ldap[5])) {
            $ldap_login_attr2 = str_replace('ldap_login_attr2=', '', $ldap[5]);
        }
        if (isset($ldap[6])) {
            $ldap_id_attr = str_replace('ldap_studentid=', '', $ldap[6]);
        }
    } else {
        $ldaphost = $ldap_base = $ldapbind_dn = $ldapbind_pw = $ldap_login_attr2 = $ldap_id_attr = '';
        $ldap_login_attr = "uid";
    }
} else {
    $ldapsettings = $ldapdata['auth_settings'];
    $auth_instructions = $ldapdata['auth_instructions'];
    $auth_title = $ldapdata['auth_title'];
    $ldaphost = $ldapsettings;
}

$tool_content .= "
    <div class='form-group'>
        <label for='ldaphost' class='col-sm-2 control-label'>$langldap_host_url:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='ldaphost' id='ldaphost' type='text' value='" . q($ldaphost) . "'>
        </div>
    </div>     
    <div class='form-group'>
        <label for='ldap_base' class='col-sm-2 control-label'>$langldap_base:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='ldap_base' id='ldap_base' type='text' value='" . q($ldap_base) . "'>
        </div>
    </div>  
    <div class='form-group'>
        <label for='ldapbind_dn' class='col-sm-2 control-label'>$langldap_bind_dn:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='ldapbind_dn' id='ldapbind_dn' type='text' value='" . q($ldapbind_dn) . "'>
        </div>
    </div>      
    <div class='form-group'>
        <label for='ldapbind_pw' class='col-sm-2 control-label'>$langldap_bind_pw:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='ldapbind_pw' id='ldapbind_pw' type='password' value='" . q($ldapbind_pw) . "' autocomplete='off'>
        </div>
    </div>  
    <div class='form-group'>
        <label for='ldap_login_attr' class='col-sm-2 control-label'>$langldap_login_attr:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='ldap_login_attr' id='ldap_login_attr' type='text' value='" . q($ldap_login_attr) . "'>
        </div>
    </div>  
    <div class='form-group'>
        <label for='ldap_login_attr2' class='col-sm-2 control-label'>$langldap_login_attr2:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='ldap_login_attr2' id='ldap_login_attr2' type='text' value='" . q($ldap_login_attr2) . "'>
        </div>
    </div>
    <div class='form-group'>
        <label for='ldap_id_attr' class='col-sm-2 control-label'>$langldap_id_attr:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='ldap_id_attr' id='ldap_id_attr' type='text' value='" . q($ldap_id_attr) . "'>
        </div>
    </div>
    <div class='form-group'>
        <label for='auth_title' class='col-sm-2 control-label'>$langAuthTitle:</label>
        <div class='col-sm-10'>
            <input class='form-control' name='auth_title' id='auth_title' type='text' value='" . q($auth_title) . "'>
        </div>
    </div>
    <div class='form-group'>
        <label for='auth_instructions' class='col-sm-2 control-label'>$langInstructionsAuth:</label>
        <div class='col-sm-10'>
            <textarea class='form-control' name='auth_instructions' id='auth_instructions' rows='4'>" . q($auth_instructions) . "</textarea>
        </div>
    </div>";
