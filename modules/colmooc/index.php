<?php

/* ========================================================================
 * Open eClass 3.6
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2017  Greek Universities Network - GUnet
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

  ============================================================================
  @Description: Main script for the work tool
  ============================================================================
 */

$require_current_course = TRUE;
$require_login = TRUE;
$require_help = TRUE;
$helpTopic = 'colmooc';

require_once '../../include/baseTheme.php';
require_once 'functions.php';

$toolName = $langColmooc;

if ($is_editor) {
    if (isset($_GET['sync'])) {
        $pageName = "Synchronization";
        $navigation[] = array('url' => "$_SERVER[SCRIPT_NAME]?course=$course_code", 'name' => $langColmooc);
        $tool_content .= action_bar(array(
            array('title' => $langBack,
                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code",
                'icon' => 'fa-reply',
                'level' => 'primary-label')));

        do_sync();
    } else {
        $tool_content .= action_bar(array(
                array('title' => "Synchronize",
                    'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;sync=1",
                    'icon' => 'fa-plus-circle',
                    'button-class' => 'btn-success',
                    'level' => 'primary-label',
                    'show' => 1)));
    }
}

if (!isset($_GET['sync'])) {
    $tool_content .= "hello world";
}

add_units_navigation(TRUE);
draw($tool_content, 2, null, $head_content);