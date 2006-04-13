<?
$langFiles = array('admin','gunet','speedSubscribe');
include '../../include/init.php';
include 'check_admin.inc';

$nameTools = $langSpeedSubscribe;
$navigation[]= array ("url"=>"index.php", "name"=> $langAdmin);
begin_page();
if (isset($submit) && $submit == "$langSubscribe") {

	echo "<h4>$lang_subscribe_processing</h4>";

	$lesStatutDeCours["1"] = "Καθηγητής";
	$lesStatutDeCours["5"] = "Φοιτητής";
	while (list($key,$contenu)= @each($course)) {
		echo "<hr>";
		$sql = "INSERT INTO `cours_user` (`code_cours`, `user_id`, `statut`, `role`) 
			VALUES ('$contenu', '$uid', '1', 'Διαχειριστής')";
		$res =mysql_query($sql);
		if ($res)
			echo "<br>$langSuccess<br>";	
		elseif (mysql_errno() == 1062)
		{
			$sql2 = "SELECT `statut` sCours FROM `cours_user` 
				WHERE `code_cours` = '$contenu' AND `user_id`= '$uid'";
			$res2 =mysql_query($sql2);
			$lelienUserCours = mysql_fetch_array($res2);
			echo "<font color=\"red\">!!!<strong> $langAlreadySubscribe</strong> !!!</font>
				".$langAs." ".$lesStatutDeCours[$lelienUserCours["sCours"]]."<br>";
		}
	}
	echo "<br><br><a href=\"../..\">Επιστροφή στην αρχική σελίδα</a><hr color=\"blue\" noshade size=4>";
}
?>
<form name="speedSub" action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
<font size='2' face='arial, helvetica'>
<?
$sql = "SELECT cours_faculte.faculte f, cours.code k, cours.fake_code c, cours.intitule i, cours.titulaires t
		FROM cours_faculte, cours WHERE cours.code=cours_faculte.code 
		ORDER BY cours_faculte.faculte, cours.code";

$result=mysql_query($sql);	
while ($mycours = mysql_fetch_array($result)) { 
	if($mycours['f'] != @$facOnce)
	{ 
		echo "<hr noshade size=1><font color=\"navy\">$mycours[f]</font><br>";
	}
	$facOnce=$mycours['f'];
	if($mycours['k'] != @$codeOnce)
	{ 
		echo "<input type=checkbox name=course[] value=$mycours[k]>$mycours[c] $mycours[i] $mycours[t]<br>";
	}
	$codeOnce=$mycours['k'];
}
?>
<br><input type="submit" name="submit" value="<?= $langSubscribe ?>"><br><br>
</form>

