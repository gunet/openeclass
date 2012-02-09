<?php
/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
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


if (isset($_REQUEST['token']))
{
    $require_mlogin = true;
    require_once ('../../include/minit.php');
    
    if (isset($_REQUEST['logout']))
    {
        require_once ('../../include/CAS/CAS.php');
        require_once ('../../modules/auth/auth.inc.php');

        if (isset($_SESSION['uid']))
            db_query("INSERT INTO loginout (loginout.id_user, loginout.ip, loginout.when, loginout.action)
                                    VALUES ($_SESSION[uid], '$_SERVER[REMOTE_ADDR]', NOW(), 'LOGOUT')");

        if (isset($_SESSION['cas_uname'])) // if we are CAS user
            define('CAS', true);

        foreach(array_keys($_SESSION) as $key)
            unset($_SESSION[$key]);

        session_destroy();

        if (defined('CAS')) {
            $cas = get_auth_settings(7);
            if (isset($cas['cas_ssout']) and intval($cas['cas_ssout']) === 1) {
                phpCAS::client(SAML_VERSION_1_1, $cas['cas_host'], intval($cas['cas_port']), $cas['cas_context'], FALSE);
                phpCAS::logoutWithRedirectService($urlServer);
            }
        }

        echo RESPONSE_OK;
        exit();
    }
    
    if (isset($_REQUEST['redirect']))
    {
        header('Location: '. urldecode($_REQUEST['redirect']));
        exit();
    }
    
    echo $ret = (isset($_SESSION['uid'])) ? RESPONSE_OK : RESPONSE_EXPIRED;
    exit();
}


if (isset($_REQUEST['uname']) && isset($_REQUEST['pass']))
{
    require_once ('../../include/minit.php');
    require_once ('../../include/CAS/CAS.php');
    require_once ('../../modules/auth/auth.inc.php');
    
    $uname = autounquote(canonicalize_whitespace($_REQUEST['uname']));
    $pass = autounquote($_REQUEST['pass']);
    
    foreach(array_keys($_SESSION) as $key)
        unset($_SESSION[$key]);
    $_SESSION['user_perso_active'] = false;
    
    $sqlLogin = "SELECT user_id, nom, username, password, prenom, statut, email, perso, lang, verified_mail
                   FROM user 
                  WHERE username COLLATE utf8_bin = " . quote($uname);
    $result = db_query($sqlLogin);
    
    while ($myrow = mysql_fetch_assoc($result)) 
        $ok = login($myrow, $uname, $pass);
    
    if (isset($_SESSION['uid']) && $ok == 1) {
        db_query("INSERT INTO loginout (loginout.id_user, loginout.ip, loginout.when, loginout.action)
                                VALUES ($_SESSION[uid], '$_SERVER[REMOTE_ADDR]', NOW(), 'LOGIN')");
        
        if ($GLOBALS['persoIsActive'] and $GLOBALS['userPerso'] == 'no')
            $_SESSION['user_perso_active'] = true;
        $_SESSION['mobile'] = true;

        echo session_id();
    } else
        echo RESPONSE_FAILED;

    exit();
}
