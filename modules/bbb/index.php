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

/**
 * @file chat.php
 * @brief Main script for chat module
 */
$require_current_course = TRUE;
$require_login = TRUE;
$require_help = TRUE;
$helpTopic = 'bbb';

require_once '../../include/baseTheme.php';

/* * ** The following is added for statistics purposes ** */
require_once 'include/action.php';
$action = new action();
$action->record(MODULE_ID_BBB);
/* * *********************************** */

$nameTools = $langBBB;

// guest user not allowed
if (check_guest()) {
    $tool_content .= "<p class='caution'>$langNoGuest</p>";
    draw($tool_content, 2, 'conference');
}

$head_content = '';

if ($is_editor) {
    $tool_content .= "
        <div id='operations_container'>
          <ul id='opslist'>
            <li><a href='messageList.php?course=$course_code&amp;reset=true' target='messageList' class=small_tools>$langWash</a></li>
            <li><a href='messageList.php?course=$course_code&amp;store=true' target='messageList' class=small_tools>$langSave</a></li>
          </ul>
        </div>";
}

$tool_content .= "";

add_units_navigation(TRUE);
draw($tool_content, 2, null, $head_content);
