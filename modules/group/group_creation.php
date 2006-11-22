<?php
/**===========================================================================
*              GUnet e-Class 2.0
*       E-learning and Course Management Program
* ===========================================================================
*	Copyright(c) 2003-2006  Greek Universities Network - GUnet
*	A full copyright notice can be read in "/info/copyright.txt".
*
*  Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*				Yannis Exidaridis <jexi@noc.uoa.gr>
*				Alexandros Diamantidis <adia@noc.uoa.gr>
*
*	For a full list of contributors, see "credits.txt".
*
*	This program is a free software under the terms of the GNU
*	(General Public License) as published by the Free Software
*	Foundation. See the GNU License for more details.
*	The full license can be read in "license.txt".
*
*	Contact address: 	GUnet Asynchronous Teleteaching Group,
*						Network Operations Center, University of Athens,
*						Panepistimiopolis Ilissia, 15784, Athens, Greece
*						eMail: eclassadmin@gunet.gr
============================================================================*/
/**
 * Groups Component
 * 
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 * 
 * @abstract This module is responsible for the user groups of each lesson
 *
 */
$require_current_course = TRUE;

$langFiles = 'group';
$require_help = TRUE;
$helpTopic = 'Group';

include '../../include/baseTheme.php';

$nameTools = $langGroupCreation;
$navigation[]= array ("url"=>"group.php", "name"=> $langGroupManagement);

$tool_content = <<<tCont

	<form method="post" action="group.php">
	<table  >
		<thead>
		
		<tr> 
			<th>
				$langNewGroups
			</th>
			<td>
				<input type="text" name="group_quantity" size="3" value="1">
			</td>
		</tr>
		
		<tr> 
			<th>
				$langMax $langPlaces
			</th>
			<td>
				<input type="text" name="group_max" size="3" value="8">
			</td>
		</tr>
		
		</thead>
		</table>
		
		<br>
		<input type="submit" value=$langCreate name="creation">

	</form>

tCont;


draw($tool_content, 2, 'group');

?>