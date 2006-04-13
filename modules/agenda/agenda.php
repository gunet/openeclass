<?
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.0 $Revision$                            |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      | $Id$               |
      +----------------------------------------------------------------------+
      |   This program is free software; you can redistribute it and/or      |
      |   modify it under the terms of the GNU General Public License        |
      |   as published by the Free Software Foundation; either version 2     |
      |   of the License, or (at your option) any later version.             |
      |                                                                      |
      |   This program is distributed in the hope that it will be useful,    |
      |   but WITHOUT ANY WARRANTY; without even the implied warranty of     |
      |   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the      |
      |   GNU General Public License for more details.                       |
      |                                                                      |
      |   You should have received a copy of the GNU General Public License  |
      |   along with this program; if not, write to the Free Software        |
      |   Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA          |
      |   02111-1307, USA. The GNU GPL license is also available through     |
      |   the world-wide-web at http://www.gnu.org/copyleft/gpl.html         |
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
      +----------------------------------------------------------------------+

*/

$langFiles = 'agenda';
$require_current_course = TRUE;
$require_help = TRUE;
$helpTopic = 'Agenda';

include('../../include/init.php');

include('../../include/lib/textLib.inc.php'); 

$dateNow = date("d-m-Y / H:i:s",time());
$nameTools = $langAgenda;
$local_style = '
	.month { font-weight : bold; color: #FFFFFF; background-color: #000066;
		padding-left: 15px; padding-right : 15px; }
	.content {position: relative; left: 25px; }';
mysql_select_db($dbname);
begin_page();

if ($language == 'greek')
        $lang_editor='gr';
else
        $lang_editor='en';

?>

<script type="text/javascript">
  _editor_url = '<?= $urlAppend ?>/include/htmlarea/';
  _css_url='<?= $urlAppend ?>/css/';
  _image_url='<?= $urlAppend ?>/include/htmlarea/images/';
  _editor_lang = '<?= $lang_editor ?>';
</script>
<script type="text/javascript" src='<?= $urlAppend ?>/include/htmlarea/htmlarea.js'></script>

<script type="text/javascript">
var editor = null;

function initEditor() {

  var config = new HTMLArea.Config();
  config.height = '180px';
  config.hideSomeButtons(" showhelp undo redo popupeditor ");

  editor = new HTMLArea("ta",config);

  // comment the following two lines to see how customization works
  editor.generate();
  return false;
}

</script>

<body onload="initEditor()">

<font size="2">
<?= $langDateNow ?>&nbsp;
<?= $dateNow ?>
<br><br></font>

<?

if ($is_adminOfCourse) {

	if (isset($submit)&&$submit) {	
		$date_selection = $fyear."-".$fmonth."-".$fday;	
		$hour = $fhour.":".$fminute.":00";
		if(isset($tout)&&$tout)
		{
			$sql="DELETE FROM agenda";
		}
		elseif(isset($id) && $id) {
			$sql = "UPDATE agenda
				SET titre='".trim($titre)."',
				contenu='".trim($contenu)."',
				day='$date_selection',
				hour='$hour',
				lasting='$lasting'
				WHERE id='$id'";
			unset($id);
			unset($contenu);
			unset($titre);
		}
			else
		{
	$sql = "INSERT INTO agenda (id, titre,contenu, day, hour, lasting) 
		VALUES (NULL, '".trim($titre)."','".trim($contenu)."', '$date_selection','$hour', '$lasting')";
			unset($id);
			unset($contenu);
			unset($titre);
		}	
		$result = mysql_query($sql);
	}
	elseif (isset($delete)&&$delete) {
		$sql = "DELETE FROM agenda WHERE id=$id";
		$result = mysql_query($sql);
	}      

	if (isset($id) && $id) {
		$sql = "SELECT id, titre, contenu, day, hour, lasting FROM agenda WHERE id=$id";
		$result= mysql_query($sql);
		$myrow = mysql_fetch_array($result);
		$id = $myrow["id"];
		$titre = $myrow["titre"];
		$contenu= $myrow["contenu"];
		$hourAncient=$myrow["hour"];
		$dayAncient=$myrow["day"];
		$lastingAncient=$myrow["lasting"];
	}
?>

<!-- Form -->

<form method="post" action="<?= $_SERVER['PHP_SELF'] ?>">
	<input type="hidden" name="id" value="<? if (isset($id)) echo $id ?>">
	<table>
	<tr>
	<td colspan="7">
	<font size="3" face="arial, helvetica"><?= $langAddEvent?></font>
	</td>
	</tr>
	<tr><td><?= $langDay; ?></td>
	<td><?= $langMonth; ?></td>
	<td><?= $langYear; ?></td>
	<td><?= $langHour; ?></td>
	<td><?= $langMinute; ?></td>
	<td><?= $langLasting ?></td>
	</tr>
<?
	$day	= date("d");
	$month	= date("m");
	$year	= date("Y");
	$hours	= date("H");
	$minutes= date("i");

	if (isset($hourAncient) && $hourAncient) {
		$hourAncient = split(":", $hourAncient);
		$hours=$hourAncient[0];
		$minutes=$hourAncient[1];
	}
	if (isset($dayAncient) && $dayAncient) {
		$dayAncient= split("-",  $dayAncient);
		$year=$dayAncient[0];
		$month=$dayAncient[1];
		$day=$dayAncient[2];
	}
?>
	<tr>
	<td>
	<select name="fday">
	<option value="<?=  $day ?>">[<?= $day ?>]</option>
	<? 
	for ($d=1; $d<=31; $d++) echo "<option value='$d'>$d</option>";
	?>
	</select>
	</td>
	<td>
	<select name="fmonth">
	<option value="<?= $month ?>">[<?= $langMonthNames['long'][($month-1)] ?>]</option>
	<? 
	for ($i=1; $i<=12; $i++)
		echo "<option value='$i'>".$langMonthNames['long'][$i-1]."</option>";
	?>
	</select>
	</td>
	<td>
	<select name="fyear">
	<option value="<?= $year ?>">[<?= $year ?>]</option>
	<? 
	$currentyear = date("Y");
	for ($y = $currentyear - 1; $y <= $currentyear + 2; $y++) {
		echo "<option value=\"$y\">$y</option>\n";
	} ?>
	</select>
	</td>
	<td>
	<select name="fhour">
	<option value="<?= $hours ?>">[<?= $hours ?>]</option>
	<option value="--">--</option>
	<? 
	for ($h=0; $h<=24; $h++) 
		echo "<option value='$h'>$h</option>";
	?>
	</select>
	</td>
	<td>
	<select name="fminute">
	<option value="<?= $minutes ?>">[<?= $minutes ?>]</option>
	<option value="--">--</option>
	<? 
	for ($m=0; $m<=55; $m=$m+5) echo "<option value='$m'>$m</option>";
	?>
	</select>
	</td>
	<td><input type="text" name="lasting" value="<?= @$myrow['lasting'] ?>" size="2"></td>
	</tr>
	<tr><td>&nbsp;</td><tr>
	<tr><td align="center" valign="top"><?= $langTitle ?> :</td></tr>
	<tr>
	<td colspan="6"><input type="text" size="60" name="titre" value="<?php  if (isset($titre)) echo $titre ?>"></td>
	</tr>
	<tr><td align="center"><?= $langDetail ?>:</td></tr>
	<tr>
	<td colspan="6"> 
	<textarea id='ta' name='contenu' value='<?= $contenu ?>' style='width:100%' rows='20' cols='80'><?= @$contenu 
?>
	</textarea>
	<br><div align="right"><input type="Submit" name="submit" value="<?= $langOk ?>"> </div>
	</td></tr>
</table>
</form>

<?
	}

/*---------------------------------------------
 *  End  of  prof only                         
 *-------------------------------------------*/

echo "<table width=\"$mainInterfaceWidth\" cellpadding=\"2\" cellspacing=\"0\" border=\"0\">";
echo "<tr><td colspan=\"2\" valign=\"top\"><div align=\"right\"><font size=\"-2\">";
$sens =" ASC";
if (isset($_GET["sens"]) && $_GET["sens"]=="d") 
{ 
	echo "<a href=\"".$_SERVER['PHP_SELF']."?sens=\" >$langOldToNew</a>";
	$sens=" DESC ";
}
else
{
	echo "<a href=\"".$_SERVER['PHP_SELF']."?sens=d\" >$langNewToOld</a>";
}
echo "</font></div></td></tr>";
$numLine=0;
$result = mysql_query("SELECT id, titre, contenu, day, hour, lasting FROM agenda ORDER BY day ".$sens.", hour ".$sens,$db);
$barreMois ="";
$nowBarShowed = FALSE;
while ($myrow = mysql_fetch_array($result)) 
{	
	$contenu = $myrow["contenu"];		
	$contenu = nl2br($contenu);
	$contenu = make_clickable($contenu);
	if (!$nowBarShowed)
	{
// Following order
		if (((strtotime($myrow["day"]." ".$myrow["hour"]) > time()) && ($sens==" ASC")) || 
			((strtotime($myrow["day"]." ".$myrow["hour"]) < time()) && ($sens==" DESC "))) {
			if ($barreMois!=date("m",time())) {
				$barreMois=date("m",time());
				echo "
				<tr>
					<td class=\"month\" colspan=\"2\" valign=\"top\">
						".$langCalendar."&nbsp;".ucfirst(claro_format_locale_date("%B %Y",time()))."
					</td>
				</tr>";
			}
			$nowBarShowed = TRUE;
			echo "<tr> 
					<td>
						<font color=\"#CC3300\">
							<b>".$dateNow." &nbsp;</b>
						</font>
					</td>
					<td align=\"right\" bgcolor=\"#CC3300\">
						<font color=\"#FFFFFF\">
							<b>&lt;&lt;&lt; ".$langNow." &nbsp;</b>
						</font>
					</td>
				</tr>";
		}
	}
	if ($barreMois!=date("m",strtotime($myrow["day"])))
	{
		$barreMois=date("m",strtotime($myrow["day"]));
		echo "<tr><td class=\"month\" colspan=\"2\" valign=\"top\">
			".$langCalendar."&nbsp;".ucfirst(claro_format_locale_date("%B %Y",strtotime($myrow["day"])))."
			</td></tr>";
	}
	echo "
<!-- Date -->
	";
	if($numLine%2 == 0) 	
		echo "<tr valign=\"top\" bgcolor=\"$color1\">";
	elseif($numLine%2 == 1)     	
		echo "<tr valign=\"top\" bgcolor=\"$color2\">";
    if ($is_adminOfCourse) 
		echo "<td valign=\"top\">";
	else
		echo "<td valign=\"top\" colspan=\"2\">";

	echo "<p>".ucfirst(claro_format_locale_date($dateFormatLong,strtotime($myrow["day"])))."
		/ $langHour: 
		".ucfirst(date("H:i",strtotime($myrow["hour"])))." ";

	if ($myrow["lasting"] !="") {
		if ($myrow["lasting"] == 1)
			$message = $langHour;
		else 
			$message = $langHours;
		echo "<br>".$langLasting.": ".$myrow["lasting"]." ".$message."";
	}

	echo "<br><span class=\"content\">
		<b>".$myrow["titre"]."</b>
		<br>$contenu</span></p></td>";
	if ($is_adminOfCourse) { 
		echo "<td align=\"right\" valign=\"top\">
			<a href=\"$_SERVER[PHP_SELF]?id=".$myrow["id"]."\">$langModify</a> &#151; 
			<a href=\"$_SERVER[PHP_SELF]?id=".$myrow[0]."&delete=yes\">$langDelete</a>
		</td>";
	}
	echo "</tr>";
	$numLine++;
} 	// while
echo "</table>";
?>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<hr noshade size="1">
			<div align="right">
			</div>
<?
	end_page();
?>

