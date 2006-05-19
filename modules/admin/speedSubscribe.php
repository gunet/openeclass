<?php
$langFiles = array('admin','gunet','speedSubscribe');
include '../../include/baseTheme.php';
@include "check_admin.inc";
$nameTools = $langSpeedSubscribe;

// Initialise $tool_content
$tool_content = "";
// Main body


if (isset($submit) && $submit == "$langSubscribe") {

	$tool_content .= "<table width=\"99%\"><caption>".$lang_subscribe_processing."</caption></tbody>";

	$lesStatutDeCours["1"] = "Καθηγητής";
	$lesStatutDeCours["5"] = "Φοιτητής";
	while (list($key,$contenu)= @each($course)) {
		$sql = "INSERT INTO `cours_user` (`code_cours`, `user_id`, `statut`, `role`) 
			VALUES ('$contenu', '$uid', '1', 'Διαχειριστής')";
		$res =mysql_query($sql);
		if ($res)
			$tool_content .= "<tr><td>".$langSuccess."<td><tr>";	
		elseif (mysql_errno() == 1062)
		{
			$sql2 = "SELECT `statut` sCours FROM `cours_user` 
				WHERE `code_cours` = '$contenu' AND `user_id`= '$uid'";
			$res2 =mysql_query($sql2);
			$lelienUserCours = mysql_fetch_array($res2);
			$tool_content .= "<tr><td><b>".$langAlreadySubscribe."</b> ".$langAs." ".$lesStatutDeCours[$lelienUserCours["sCours"]]."</td></tr>";
		}
	}
	$tool_content .= "<tr><td><b>Ολοκληρώθηκε</b></td></tr></tbody></table><br>";
}

$tool_content .= "<form name=\"speedSub\" action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">";

$sql = "SELECT cours_faculte.faculte f, cours.code k, cours.fake_code c, cours.intitule i, cours.titulaires t
		FROM cours_faculte, cours WHERE cours.code=cours_faculte.code 
		ORDER BY cours_faculte.faculte, cours.code";

$result=mysql_query($sql);	
$firstfac = true;
while ($mycours = mysql_fetch_array($result)) { 
	if($mycours['f'] != @$facOnce)
	{
		if ($firstfac) {
			$tool_content .= "<table width=\"99%\"><caption>".$mycours[f]."</caption><tbody>";
			$firstfac = false;
		} else {
			$tool_content .= "</tbody></table><br><table width=\"99%\"><caption>".$mycours[f]."</caption><tbody>";
		}
	}
	$facOnce=$mycours['f'];
	if($mycours['k'] != @$codeOnce)
	{
		$tool_content .= "<tr><td><input type=checkbox name=course[] value=$mycours[k]>$mycours[c] $mycours[i] $mycours[t]</td></tr>";
	}
	$codeOnce=$mycours['k'];
}
if (!$firstfac) {
	$tool_content .= "</tbody></table>";
}

$tool_content .= "<br><p><input type=\"submit\" name=\"submit\" value=\"".$langSubscribe."\"></p></form>";

$tool_content .= "<br><center><p><a href=\"index.php\">Επιστροφή</a></p></center>";

draw($tool_content,3,'admin');
?>