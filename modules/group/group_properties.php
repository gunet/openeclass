<?  
$require_current_course = TRUE;

$langFiles = 'group';
$require_help = TRUE;
$helpTopic = 'Group';
include ('../../include/init.php');

$nameTools = $langGroupProperties;
$navigation[]= array ("url"=>"group.php", "name"=> $langGroupManagement);
begin_page();

?>
<tr> 
<td width="100%" colspan="2">
<font size="2" face="arial, helvetica">
<form method="post" action="group.php">
<table border="0" width="100%" cellspacing="0" cellpadding="4">
<tbody>
<tr> 
<td valign="top" bgcolor="#000066">
<font size="2" face="arial, helvetica">
<b><font color="#ffffff"><?= $langGroupProperties ?></font></b>
</td>
</tr>
<tr bgcolor="<?= $color2?>">
<td valign="top">

<?
$resultProperties=db_query("SELECT id, self_registration, private, forum, document 
			FROM group_properties WHERE id=1");
while ($myProperties = mysql_fetch_array($resultProperties))
{
	if($myProperties['self_registration'])
	{
		echo "<input type=checkbox name=\"self_registration\" value=1 checked>";
	}
	else 
	{
		echo "<input type=checkbox name=\"self_registration\" value=1>";
	}
	echo "$langGroupAllowStudentRegistration</td></tr>
		<tr bgcolor=\"$color2\">
		<td valign=top>
		<p><b>$langGroupTools</b></p>
		</td>
		</tr>
		<tr bgcolor=\"$color2\">
		<td valign=top>";
	if($myProperties['forum'])
	{
		echo "<input type=checkbox name=\"forum\" value=1 checked>";
	}
	else 
	{
		echo "<input type=checkbox name=\"forum\" value=1>";
	}
	echo "$langGroupForum&nbsp;&nbsp;&nbsp;";
	if($myProperties['private'])
	{
		echo "<input type=radio name=\"private\" value=1 checked>
		&nbsp;$langPrivate&nbsp;
		<input type=radio name=\"private\" value=0>
		&nbsp;$langPublic";
	}
	else 
	{
		echo "<input type=radio name=\"private\" value=1>
		&nbsp;$langPrivate&nbsp;
		<input type=radio name=\"private\" value=0 checked>
		&nbsp;$langPublic";
	}
	echo "</td></tr><tr bgcolor=\"$color2\"><td>";
	if($myProperties['document'])
	{
		echo "<input type=checkbox name=\"document\" value=1 checked>";
	}
	else 
	{
		echo "<input type=checkbox name=\"document\" value=1>";
	}
	echo "$langGroupDocument"; 
}

?>
	</td></tr>
	<tr bgcolor="<?= $color2?>">
	<td valign="top" align="right">
	<input type="submit" name="properties" value="<?= $langValidate ?>">
	</td>
	</tr>
	</tbody>
	</table>
</form>
	</td>
	</tr>
	<tr><td width="100%" colspan="3"></td></tr>
	</tbody>
</table>
</body>
</html>
