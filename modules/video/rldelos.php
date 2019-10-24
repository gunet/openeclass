<?php

/* ========================================================================
 * Open eClass
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
 * ========================================================================
 */

$require_admin = true;
require_once '../../include/baseTheme.php';
require_once 'modules/admin/extconfig/externals.php';
require_once 'modules/admin/extconfig/opendelosapp.php';
require_once 'inc/delos_functions.php';


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


$rlhtml = httpGetRequest($authUrl, $headers);
$tool_content = str_replace("/opendelos-default/", "http://delos-dev.gunet.gr/opendelos-default/", $rlhtml);

//$head_content = "";
//$tool_content = "hello world!";
//$tool_content = $rlhtml;
//draw_popup();

echo $tool_content;