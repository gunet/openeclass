<?php
$require_current_course = TRUE;

$langFiles = 'group';
$require_help = TRUE;
$helpTopic = 'Group';
//include ('../../include/init.php');
include '../../include/baseTheme.php';
$nameTools = $langGroupProperties;
$navigation[]= array ("url"=>"group.php", "name"=> $langGroupManagement);
//begin_page();

$dbname = $_SESSION['dbname'];
$tool_content = "";
$tool_content .= <<<tCont

<form method="post" action="group.php">
<table>
<thead>
<tr> 
<th>
$langGroupProperties
</th>
</tr>
</thead>
<tbody>
<tr>
<td>

tCont;
$resultProperties=db_query("SELECT id, self_registration, private, forum, document 
			FROM group_properties WHERE id=1", $dbname);
while ($myProperties = mysql_fetch_array($resultProperties))
{
	if($myProperties['self_registration'])
	{
		$tool_content .=  "<input type=checkbox name=\"self_registration\" value=1 checked>";
	}
	else 
	{
		$tool_content .=  "<input type=checkbox name=\"self_registration\" value=1>";
	}
	$tool_content .=  "$langGroupAllowStudentRegistration</td></tr>
		<tr >
		<td class=\"category\">
		$langGroupTools
		</td>
		</tr>
		<tr>
		<td >";
	if($myProperties['forum'])
	{
		$tool_content .=  "<input type=checkbox name=\"forum\" value=1 checked>";
	}
	else 
	{
		$tool_content .=  "<input type=checkbox name=\"forum\" value=1>";
	}
	$tool_content .=  "$langGroupForum :";
	if($myProperties['private'])
	{
		$tool_content .=  "<input type=radio name=\"private\" value=1 checked>
		&nbsp;$langPrivate&nbsp;
		<input type=radio name=\"private\" value=0>
		&nbsp;$langPublic";
	}
	else 
	{
		$tool_content .=  "<input type=radio name=\"private\" value=1>
		&nbsp;$langPrivate&nbsp;
		<input type=radio name=\"private\" value=0 checked>
		&nbsp;$langPublic";
	}
	$tool_content .=  "</td></tr><tr><td>";
	if($myProperties['document'])
	{
		$tool_content .=  "<input type=checkbox name=\"document\" value=1 checked>";
	}
	else 
	{
		$tool_content .=  "<input type=checkbox name=\"document\" value=1>";
	}
	$tool_content .=  "$langGroupDocument"; 
}

$tool_content .= <<<tCont2
	</td></tr>
</tbody>
	</table>
	<br>
	<input type="submit" name="properties" value="$langValidate">

</form>


tCont2;

draw($tool_content, 2, 'group');

?>