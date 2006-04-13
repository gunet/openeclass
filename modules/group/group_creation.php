<?

$require_current_course = TRUE;

$langFiles = 'group';
$require_help = TRUE;
$helpTopic = 'Group';
include ('../../include/init.php');


$nameTools = $langGroupCreation;
$navigation[]= array ("url"=>"group.php", "name"=> $langGroupManagement);

begin_page();
?>

<td valign="top"> 
<font size="2" face="arial, helvetica">
		</td>
	</tr>
	<tr> 
	  <td width="100%" colspan="2"> <font size="2" face="arial, helvetica">
	<form method="post" action="group.php">
	<table border="0" width="100%" cellspacing="0">
		<tbody>
		<tr> 
		<td valign="top" bgcolor="#000066" colspan="3">
		<b><font color="#ffffff"><?= $langGroupCreation ?></font></b>
		</td>
		</tr>
		<tr> 
		<td valign="top" bgcolor="<?= $color2?>">
		<b><nobr><br><?= $langCreate?>
		<input type="text" name="group_quantity" size="3" value="1">
		<?= $langNewGroups ?>
		</nobr>
		</b>
		<small><nobr><?= $langMax ?>
		<input type="text" name="group_max" size="3" value="8">
		<?= $langPlaces ?>
		</nobr></small>
		</td>
		<td valign="bottom" bgcolor="<?= $color2?>" width="14%">
		<input type="submit" value=<?= $langCreate ?> name="creation">
		</td>
		</tr>
		</tbody>
		</table>
		<p align="left"></p>
	</form>
		<p align="left"></p>
		</td>
		</tr>
		<tr><td width="100%" colspan="3"></td></tr>
	</tbody>
</table>
</body>
</html>
