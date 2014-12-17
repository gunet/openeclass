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


$require_usermanage_user = TRUE;
include '../../include/baseTheme.php';
$pageName = $langUnregUser;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);

// get the incoming values and initialize them
$u = isset($_GET['u']) ? intval($_GET['u']) : false;
$doit = isset($_GET['doit']);

$u_account = $u ? q(uid_to_name($u, 'username')) : '';
$u_realname = $u ? q(uid_to_name($u)) : '';
$t = 0;

if (!$doit) {
    if ($u_account) {
        $tool_content .= "<p class='title1'>$langConfirmDelete</p>
            <div class='alert alert-warning'>$langConfirmDeleteQuestion1 <em>$u_realname ($u_account)</em><br/>
            $langConfirmDeleteQuestion3
            </div>
            <p class='eclass_button'><a href='$_SERVER[SCRIPT_NAME]?u=$u&amp;doit=yes'>$langDelete</a></p>";
    } else {
        $tool_content .= "<p>$langErrorDelete</p>";
    }    
} else {
    if ($u == 1) {
        $tool_content .= $langTryDeleteAdmin;
    } else {
        $success = deleteUser($u, true);
        if ($success === true) {
            $tool_content .= "<p>$langUserWithId $u $langWasDeleted.</p>";
        } else {
            $tool_content .= "<p>$langErrorDelete</p>";
        }
    }    
}

$tool_content .= "<div class='right'><a href='index.php'>$langBackAdmin</a></div><br/>";
draw($tool_content, 3);
