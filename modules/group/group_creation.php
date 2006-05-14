<?php

$require_current_course = TRUE;

$langFiles = 'group';
$require_help = TRUE;
$helpTopic = 'Group';
//include ('../../include/init.php');
include '../../include/baseTheme.php';

$nameTools = $langGroupCreation;
$navigation[]= array ("url"=>"group.php", "name"=> $langGroupManagement);

//begin_page();
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