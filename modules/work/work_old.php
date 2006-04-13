<?

/*
 +----------------------------------------------------------------------+
 | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
 | Copyright (c) 2003, 2004, 2005 GUNet                                 |
 +----------------------------------------------------------------------+
 | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
 |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
 |          Christophe Gesche <gesche@ipm.ucl.ac.be>                    |
 |                                                                      |
 | e-class changes by: Costas Tsibanis <costas@noc.uoa.gr>              |
 |                     Yannis Exidaridis <jexi@noc.uoa.gr>              |
 |                     Alexandros Diamantidis <adia@noc.uoa.gr>         |
 +----------------------------------------------------------------------+
 | Legacy work submissions display (available only to professor)        |
 | Used only if there are submissions uploaded from pre-1.2 e-Class     |
 +----------------------------------------------------------------------+
*/


$langFiles = "work";
$require_current_course = TRUE;
include('../../include/init.php');

$nameTools = $langWorksOld;
$navigation[] = array('url' => 'work.php', 'name' => $langWorks);
begin_page();
?>

<script>
function confirmation (name) {
        if (confirm("<?php echo $langDelWarn1 ?> «"+ name + "» - <?= $langDelSure ?>"))
                {return true;}
        else
                {return false;}
}
</script>

<?
if ($is_adminOfCourse)  {
	include ('../include/lib/fileManageLib.inc.php');
	if (isset($delete) ) {
		mysql_select_db($currentCourseID);
		$del=mysql_query("SELECT url FROM work WHERE id='$delete'");	
		$filename=mysql_fetch_row($del);
		if (mysql_num_rows($del) > 0) {
			$filetodelete=$webDir.$currentCourseID.$filename[0];
			@unlink($filetodelete);
			mysql_query("DELETE FROM work WHERE id='$delete'",$db);
		}
	}
} else {
	echo $langProfOnly; 
	exit;
}


if(!isset($id) || !$id) {
	$result = mysql_query("SELECT id, url, titre, description, auteurs, active, accepted 
				FROM work ORDER BY auteurs, titre",$db);
	$i = 0;
	echo "<table width=\"600\" cellpadding=\"5\" cellspacing=\"0\" border=\"0\">
	<tr><td colspan=\"2\"></td></tr>";
	while ($myrow = mysql_fetch_array($result)) {
		/*** convert the file name in a correct url ***/

		$myrow['url'] = rawurlencode($myrow['url']);
		($i%2 == 0) ? ($bgColor = $color2) : ($bgColor = $color1);
		if ($is_adminOfCourse)   {
			echo "<tr bgcolor=\"".$bgColor."\">
			<td width=\"30\" valign=\"top\">
			<a href=\"../../".$currentCourseID.urldecode($myrow['url'])."\"><img  alt=\"\" src=../image/travaux.png border=0></a>
			</td>
			<td  width=\"570\"  valign=\"top\">
			<font size=\"2\" face=\"arial, helvetica\">
			<a href=\"../../$currentCourseID".urldecode($myrow['url'])."\">".$myrow['titre']."</a>
			<br>".$myrow['auteurs']."<br>".$myrow['description']."</font>
			</td>
			<td><a href=\"$_SERVER[PHP_SELF]?delete=".$myrow['id']."\" onClick=\"return confirmation('".$myrow['titre']."');\">
			<img src=\"../document/img/supprimer.gif\" border=0></a></td>					
			</tr>";
		} else {
	 		echo "<tr bgcolor=\"".$bgColor."\">
                  	<td width=\"30\" valign=\"top\">
                        <a href=\"../../".$currentCourseID.urldecode($myrow[url])."\"><img  alt=\"\" src=../image/travaux.gif border=0></a>
                        </td>
                        <td  width=\"570\"  valign=\"top\">
                        <font size=\"2\" face=\"arial, helvetica\">
                        <a href=\"../../$currentCourseID".urldecode($myrow[url])."\">".$myrow[titre]."</a>
                        <br>".$myrow[auteurs]."<br>".$myrow[description]."</font></td></tr>";
		}
		$i++;
	}	// while
echo "</table>";
}
?>

</td></tr>
<tr><td colspan="2"><hr noshade size="1">

<?
end_page();
?>		
	
