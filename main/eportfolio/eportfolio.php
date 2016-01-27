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


$require_login = false;

include '../../include/baseTheme.php';
include 'main/eportfolio/eportfolio_functions.php';

$toolName = $langMyePortfolio;

$userdata = array();

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $pageName = $langUserePortfolio;
} else {
    $id = $uid;
}

$userdata = Database::get()->querySingle("SELECT surname, givenname, username, has_icon
                                          FROM user WHERE id = ?d", $id);

if ($userdata) {
    if ($uid == $id) {
        $tool_content .= 
            action_bar(array(
                array('title' => $langEditePortfolio,
                    'url' => "edit_eportfolio.php",
                    'icon' => 'fa-edit',
                    'level' => 'primary-label')
                ));    
    }
    
    $tool_content .= "
        <div class='row'>
            <div class='col-sm-12'>
                <div class='panel panel-default'>
                <div class='panel-body'>
                    <div id='pers_info' class='row'>
                        <div class='col-xs-12 col-sm-2'>
                            <div id='profile-avatar'>" . profile_image($id, IMAGESIZE_LARGE, 'img-responsive img-circle') . "</div>
                        </div>
                        <div class='col-xs-12 col-sm-10 profile-pers-info'>
                            <div class='row profile-pers-info-name'>
                                <div class='col-xs-12'>
                                    <div>" . q("$userdata->givenname $userdata->surname") . "</div>
                                    <div class='not_visible'>(".q($userdata->username).")</div>
                                </div>
                            </div>
                        </div>";

    $tool_content .= render_eportfolio_fields_content($id);
    $tool_content .= "</div>
            </div>
        </div>
    </div>
</div>";
}
if ($uid == $id) {
    draw($tool_content, 1);
} else {
    draw($tool_content, 2);
}
