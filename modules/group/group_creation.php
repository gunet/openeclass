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

/**
 * @file group_creation.php
 * @brief create users group
 */

$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Group';
$require_editor = true;

require_once '../../include/baseTheme.php';
$toolName = $langGroups;
$pageName = $langNewGroupCreate;
$navigation[] = array("url" => "index.php?course=$course_code", "name" => $langGroups);

$tool_content .= action_bar(array(
    array(
        'title' => $langBack,
        'level' => 'primary-label',
        'icon' => 'fa-reply',
        'url' => "index.php?course=$course_code"
    )
));
                
$tool_content .= " 
    <div class='form-wrapper'>
        <form class='form-horizontal' role='form' method='post' action='index.php?course=$course_code'>
        <fieldset>
        <div class='form-group'>
            <label for='group_quantity' class='col-sm-2 control-label'>$langNewGroups:</label>
            <div class='col-sm-10'>
              <input name='group_quantity' type='text' class='form-control' id='group_quantity' value='1' placeholder='$langNewGroups'>
            </div>
        </div>
        <div class='form-group'>
            <label for='group_max' class='col-sm-2 control-label'>$langNewGroupMembers:</label>
            <div class='col-sm-10'>
              <input name='group_max' type='text' class='form-control' id='group_max' value='8' placeholder='$langNewGroupMembers'> &nbsp;$langMax $langPlaces
            </div>
        </div>
        <div class='form-group'>
        <div class='col-sm-10 col-sm-offset-2'>
            <input class='btn btn-primary' type='submit' value='$langCreate' name='creation'>
            <a class='btn btn-default' href='index.php?course=$course_code'>$langCancel</a>
        </div>
        </div>
        </fieldset>
        </form>
    </div>";

draw($tool_content, 2);
