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


$require_admin = TRUE;
require_once '../../include/baseTheme.php';

$navigation[] = array('url' => 'index.php', 'name' => $langAdmin);
$toolName = $langAdminAn;
$pageName = $langAdminAn;

$ann_id = $_GET['ann_id'];

$tool_content = action_bar(array(
    array(
        'title' => $langBack,
        'url' => "adminannouncements.php",
        'icon' => 'fa-reply',
        'level' => 'primary')),false);

if(isset($ann_id)){
    $announcement = Database::get()->querySingle("SELECT * FROM admin_announcement WHERE `id`=?d", $ann_id);
    $tool_content .= "
                    
                        <div class='col-12'>
                            <div class='card panelCard card-default px-lg-4 py-lg-3'>
                                <div class='card-header border-0 d-flex justify-content-between align-items-center'>
                                    <h3>
                                            ".standard_text_escape($announcement->title)."
                                    </h3>
                                </div>
                                <div class='card-body'>
                                    <div class='single_announcement'>
                                        <div class='announcement-main'>
                                            ".standard_text_escape($announcement->body)."
                                        </div>
                                    </div>
                                </div>
                                <div class='card-footer border-0 d-flex justify-content-start align-items-center'>
                                    <div class='announcement-date small-text'>
                                        " . format_locale_date(strtotime($announcement->date)) . "
                                    </div>
                                </div>
                            </div>
                        </div>
                    ";
}

draw($tool_content, 3, null, $head_content);
