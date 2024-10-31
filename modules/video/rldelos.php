<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

$require_admin = true;
require_once '../../include/baseTheme.php';
require_once 'modules/admin/extconfig/externals.php';
require_once 'modules/admin/extconfig/opendelosapp.php';
require_once 'delos_functions.php';
require_once 'include/lib/curlutil.class.php';


//$authUrl = getDelosRLoginURL();
$authUrl = getDelosRLoginCASURL();
$authUrl .= "?token=" . getDelosSignedToken();

// construct http headers
$headers = array(
//    'Accept: application/json',
//    'Content-Type: application/json',
    'X-CustomToken: ' . getDelosSignedToken(),
    'Referer: http://' . getDelosLmsURL()
);


$rlhtml = CurlUtil::httpGetRequest($authUrl, $headers);
$tool_content = str_replace("/opendelos-default/", "http://delos-dev.gunet.gr/opendelos-default/", $rlhtml);

//$head_content = "";
//$tool_content = "hello world!";
//$tool_content = $rlhtml;
//draw_popup();

echo $tool_content;
