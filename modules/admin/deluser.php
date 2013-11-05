<?php
/* ========================================================================
 * Open eClass 2.6
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


$require_usermanage_user = TRUE;
include '../../include/baseTheme.php';
require_once 'admin.inc.php';
$nameTools = $langUnregUser;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);

$u = isset($_GET['u']) ? intval($_GET['u']) : false;
if (check_admin($u) and (!(isset($_SESSION['is_admin'])))) {            
        header('Location: ' . $urlServer);
}
$doit = isset($_GET['doit']);

$u_account  = $u ? q(uid_to_username($u)) : '';
$u_realname = $u ? q(uid_to_name($u))     : '';

if (!$doit) {
    if ($u_account) {
        $tool_content .= "<p class='title1'>$langConfirmDelete</p>
            <div class='alert1'>$langConfirmDeleteQuestion1 <em>$u_realname ($u_account)</em><br/>
            $langConfirmDeleteQuestion3
            </div>
            <p class='eclass_button'><a href='$_SERVER[SCRIPT_NAME]?u=$u&amp;doit=yes'>$langDelete</a></p>";
    } else {
        $tool_content .= "<p>$langErrorDelete</p>";
    }    
    $tool_content .= "<div class='right'><a href='index.php'>$langBackAdmin</a></div><br/>";

} else {
        $success = deleteUser($u);
        
        if ($success === true) {
            $tool_content .= "<p>$langUserWithId $u $langWasDeleted.</p>\n";
        } else {
            $tool_content .= "<p>$langErrorDelete</p>";
        }   
    $tool_content .= "<div class='right'><a href='index.php'>$langBackAdmin</a></div><br/>";
}
draw($tool_content,3);
