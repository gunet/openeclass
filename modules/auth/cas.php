<?php
/* ========================================================================
 * Open eClass 2.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
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


/*
 *  	Authors:	Giannis Kapetanakis <bilias@edu.physics.uoc.gr>
 */

require_once '../../include/baseTheme.php';
require_once 'modules/auth/auth.inc.php';

$auth = 7;
cas_authenticate($auth);
if (phpCAS::checkAuthentication()) {
    $cas = get_auth_settings($auth);
    $_SESSION['cas_attributes'] = phpCAS::getAttributes();
    $attrs = get_cas_attrs($_SESSION['cas_attributes'], $cas);
    $_SESSION['cas_uname'] = phpCAS::getUser();

    if (!empty($_SESSION['cas_uname'])) {
        $_SESSION['uname'] = $_SESSION['cas_uname'];
    }
    if (!empty($attrs['surname'])) {
        $_SESSION['cas_surname'] = $attrs['surname'];
    }
    if (!empty($attrs['givenname'])) {
        $_SESSION['cas_givenname'] = $attrs['givenname'];
    }
    if (!empty($attrs['email'])) {
        $_SESSION['cas_email'] = $attrs['email'];
    }
    if (!empty($attrs['studentid'])) {
    	$_SESSION['cas_userstudentid'] = $attrs['studentid'];
    }
}

header("Location: $urlServer");
