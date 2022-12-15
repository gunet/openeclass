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

$require_help = true;
$guest_allowed = true;

include '../../include/baseTheme.php';

$pageName = $langAnnouncements;

$isaid = isset($_GET['aid']);
$announceArr = Database::get()->queryArray($isaid ? "SELECT `date`, `title` , `body` FROM `admin_announcement` WHERE id = ?d" :
                "SELECT `date`, `title` , `body` FROM `admin_announcement`
	        WHERE `visible` = 1 AND lang = ?s ORDER BY `date` DESC", $isaid ? intval($_GET['aid']) : $language);

$numOfAnnouncements = count($announceArr);
$tool_content .= action_bar(array(
                                array('title' => $langBack,
                                      'url' => $urlServer,
                                      'icon' => 'fa-reply',
                                      'level' => 'primary-label',
                                      'button-class' => 'btn-secondary')
                            ),false);
if ($numOfAnnouncements > 0) {
    $tool_content .= "<div class='col-sm-12 mt-3'><div class='panel panel-default bg-white'><div class='panel-body bg-white'>";
    for ($i = 0; $i < $numOfAnnouncements; $i++) {
        $tool_content .= "<div class='single_announcement'><div class='announcement-title control-label-notes'>" . q($announceArr[$i]->title) . "</div><hr>
		<div class='announcement-main mt-3'>" . standard_text_escape($announceArr[$i]->body) . "</div>
        <div class='announcement-date info-date text-end'>- " . format_locale_date(strtotime($announceArr[$i]->date)) . " -</div>
		</div>";
    }
    $tool_content .= "</div></div></div>";
}
draw($tool_content, 0);
