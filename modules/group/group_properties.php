<?php

/* ========================================================================
 * Open eClass 2.4
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2011  Greek Universities Network - GUnet
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

/*
 * Groups Component
 *
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id: group_properties.php,v 1.24 2011-05-16 12:36:35 adia Exp $
 *
 * @abstract This module is responsible for the user groups of each lesson
 *
 */

$require_current_course = true;
$require_help = true;
$helpTopic = 'Group';
$require_editor = true;

require_once '../../include/baseTheme.php';
$nameTools = $langGroupProperties;
$navigation[] = array('url' => "index.php?course=$course_code", 'name' => $langGroupManagement);

require_once 'group_functions.php';
initialize_group_info();

$checked['self_reg'] = $self_reg ? ' checked="1"' : '';
$checked['multi_reg'] = $multi_reg ? ' checked="1"' : '';
$checked['has_forum'] = $has_forum ? ' checked="1"' : '';
$checked['documents'] = $documents ? ' checked="1"' : '';
$checked['private_forum_yes'] = $private_forum ? ' checked="1"' : '';
$checked['private_forum_no'] = $private_forum ? '' : ' checked="1"';
$checked['wiki'] = ($wiki==0) ? '' : ' checked="1"';

$tool_content .= "<div id='operations_container'>
                    <ul id='opslist'>
                        <li><a href='index.php?course=$course_code'>$langBack</a></li>
                    </ul>
                </div>";

$tool_content .= "
<form method='post' action='index.php?course=$course_code'>
    <fieldset>
    <legend>$langGroupProperties / $langTools</legend>
    <table width='100%' class='tbl'>
    <tr>
      <th class='left' width='180'>$langGroupStudentRegistrationType:</th>
      <td class='smaller'>
       <input type='checkbox' name='self_reg' value='1'$checked[self_reg] />&nbsp;$langGroupAllowStudentRegistration<br />
       <input type='checkbox' name='multi_reg' value='1'$checked[multi_reg] />&nbsp;$langGroupAllowMultipleRegistration<br />
       </td>
    </tr>
    <tr>
      <td>&nbsp;</td>
      <td>&nbsp;</td>
    </tr>
    <tr>
    <th class='left'>$langGroupForum:</th>
      <td>
        <input type='checkbox' name='has_forum' value='1'$checked[has_forum] />
      </td>
    </tr>
    <tr>
      <th class='left'>$langPrivate_1:</th>
      <td class='smaller'>
        <input type='radio' name='private_forum' value='1'$checked[private_forum_yes] />
                &nbsp;$langPrivate_2&nbsp;<br />
        <input type='radio' name='private_forum' value='0'$checked[private_forum_no] />
                &nbsp;$langPrivate_3
      </td>
    </tr>
    <tr>
      <th class='left'>$langDoc:</th>
      <td>
        <input type='checkbox' name='documents' value='1'$checked[documents] />
      </td>
    </tr>
    <tr>
      <th class='left'>$langWiki:</th>
      <td>
        <input type='checkbox' name='wiki' value='1'$checked[wiki] />
      </td>
    </tr>
    <tr>
      <th>&nbsp;</th>
      <td class='right'><input type='submit' name='properties' value='$langModify' /></td>
    </tr>
    </table>
    </fieldset>
    </form>";
draw($tool_content, 2);

