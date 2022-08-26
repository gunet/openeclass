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


$require_admin = TRUE;
require_once '../../include/baseTheme.php';
require_once 'include/lib/textLib.inc.php';

$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$toolName = $langAdminAn;
$pageName = $langAdminAn;

$ann_id = $_GET['ann_id'];

$tool_content = action_bar(array(
    array(
        'title' => $langBack,
        'url' => "adminannouncements.php",
        'icon' => 'fa-reply',
        'level' => 'primary-label')),false);

if(isset($ann_id)){
    $announcement = Database::get()->querySingle("SELECT * FROM admin_announcement WHERE `id`=?d", $ann_id);
    $tool_content .= "
                    
                        <div class='col-12'>
                            <div class='panel panel-default'>
                                <div class='panel-heading'>
                                    <span class='control-label-notes'>
                                            ".standard_text_escape($announcement->title)."
                                    </span>
                                </div>
                                <div class='panel-body NoBorderBottom'>
                                    <div class='single_announcement'>
                                        <div class='announcement-main'>
                                            ".standard_text_escape($announcement->body)."
                                        </div>
                                    </div>
                                </div>
                                <div class='panel-footer'>
                                    <div class='text-end text-success'>
                                        " . format_locale_date(strtotime($announcement->date)) . "
                                    </div>
                                </div>
                            </div>
                        </div>
                    ";
}

draw($tool_content, 3, null, $head_content);
