<?
$colorLight     ="#F5F5F5";
$colorMedium    ="#004571";
$colorDark	="#000066";

echo "<table cellpadding=\"3\" border=0 width=\"100%\">
<tr><td colspan=\"5\" style='padding:0px;' align=\"center\" bgcolor=\"$colorMedium\">
<font face=\"Arial, Helvetica, sans-serif\" color=\"#FFFFFF\" size=\"2\">
<img src='$urlServer/images/gunet/banner.jpg'></td></tr>
<tr><td colspan=\"5\" align=\"left\" bgcolor=\"$colorMedium\">
	<font face=\"Arial, Helvetica, sans-serif\" color=\"#FFFFFF\" size=\"2\">";
if (isset($uid)) {
	echo "<span style='float: left'>$langUser : $prenom $nom</span>";
	echo "<a href='".$urlServer."index.php?logout=yes' style='float: right; color: white;'>$langLogout</a></td>";
}
else
	echo "<br></font></td></tr>";

if (isset($currentCourseID))
echo "<tr bgcolor=\"$colorLight\">
	<td colspan=\"5\"><b>
	<font face=\" Arial, Helvetica, sans-serif\" size=\"3\" color=\"$colorDark\">$intitule</font>
	<font color=\"#000066\" face=\"Arial, Helvetica, sans-serif\" size=\"2\">
	<br>$titulaires $code_cours</font></b>
	</td></tr>";


$lesson_info=@mysql_query("SELECT intitule, email FROM cours_user AS cu, cours AS c, user AS u 
	WHERE cu.code_cours='$code_cours' AND  cu.code_cours=c.code AND cu.user_id=u.user_id AND cu.tutor=1");
while ($info= @mysql_fetch_array($lesson_info)) {
	$prof_email=$info['email'];
	$lesson=$info['intitule'];
}
			
echo "<tr><td colspan=\"4\"><font face=\"Arial, Helvetica, sans-serif\" size=\"1\">
<a href=\"".$urlServer."index.php\" target=\"_top\">$siteName</a>";

if (isset($currentCourseID))
	echo "&nbsp;&gt;&nbsp;<a href=\"".$urlServer."courses/$currentCourseID/index.php\" target=\"_top\" >$intitule</a>";
if (isset($interbredcrump) && is_array($interbredcrump)) 
while (list(,$step) = each($interbredcrump) )
{
	echo "&nbsp;&gt;&nbsp;<a target=\"_top\" href=\"".$step["url"]."\" >".$step["name"]."</a>";
};

if (isset($nameTools)) echo "&nbsp;&gt;&nbsp;<b>$nameTools</b>";

echo "<br></font></td></tr></table>";                   // closed table

if (isset($db)) {
	if (isset($currentCourseID))	
		mysql_select_db("$currentCourseID",$db);
	else
		mysql_select_db("$mysqlMainDb",$db);
}



?>
