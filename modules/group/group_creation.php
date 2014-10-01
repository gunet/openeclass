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

$nameTools = $langNewGroupCreate;
$navigation[] = array("url" => "index.php?course=$course_code", "name" => $langGroups);

$tool_content .= "<div id='operations_container'>
                    <ul id='opslist'>
                        <li><a href='index.php?course=$course_code'>$langBack</a></li>
                    </ul>
                </div>";
                
$tool_content .= " <form method='post' action='index.php?course=$course_code'>
    <fieldset>
    <legend>$langNewGroupCreateData</legend>
    <table width='99%' class='tbl'>
    <tr>
      <th width='160' class='left'>$langNewGroups:</th>
      <td><input type='text' name='group_quantity' size='3' value='1'></td>
    </tr>
    <tr>
      <th class='left'>$langNewGroupMembers:</th>
      <td><input type='text' name='group_max' size='3' value='8'>&nbsp;$langMax $langPlaces</td>
    </tr>
    <tr>
      <th>&nbsp;</th>
      <td><input type='submit' value='$langCreate' name='creation'></td>
    </tr>
    </table>
    </fieldset>
    </form>";

draw($tool_content, 2);
