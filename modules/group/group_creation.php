<?php
/*========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2008  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* =========================================================================*/
/*
 * Groups Component
 * 
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 * 
 * @abstract This module is responsible for the user groups of each lesson
 *
 */
$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Group';
$require_prof = true;

include '../../include/baseTheme.php';

if (!$is_adminOfCourse) {
	die("You are not professor for this lesson");
}

$nameTools = $langNewGroupCreate;
$navigation[]= array ("url"=>"group.php", "name"=> $langGroups);
$tool_content = "";

$tool_content = <<<tCont

    <form method="post" action="group.php">
    <table width="100%" align="left" class="FormData">
    <tbody>
    <tr>
      <th width="220">&nbsp;</th>
      <td><b>$langNewGroupCreateData</b></td>
    </tr>
    <tr> 
      <th class="left">$langNewGroups :</th>
      <td><input type="text" name="group_quantity" size="3" value="1" class="FormData_InputText"></td>
    </tr>
    <tr> 
      <th class="left">$langNewGroupMembers :</th>
      <td><input type="text" name="group_max" size="3" value="8" class="FormData_InputText">&nbsp;<small>$langMax $langPlaces</small></td>
    </tr>
    <tr>
      <th>&nbsp;</th>
      <td><input type="submit" value=$langCreate name="creation"></td>
    </tr>
    </tbody>
    </table>
    <br />
    </form>

tCont;

draw($tool_content, 2, 'group');

?>
