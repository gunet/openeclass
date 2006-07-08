<?php
$colorLight     ="#F5F5F5";
$colorMedium    ="#004571";
$colorDark	="#000066";

$tool_content .= "<table cellpadding=\"3\" border=0 width=\"99%\">
<tr><td colspan=\"5\" style='padding:0px;' align=\"center\" bgcolor=\"$colorMedium\">
<font face=\"Arial, Helvetica, sans-serif\" color=\"#FFFFFF\" size=\"2\">
<img src='$urlServer/images/gunet/banner.jpg'></td></tr>
<tr><td colspan=\"5\" align=\"left\" bgcolor=\"$colorMedium\">
	<font face=\"Arial, Helvetica, sans-serif\" color=\"#FFFFFF\" size=\"2\">";
if (isset($uid)) {
	$tool_content .= "<span style='float: left'>$langUser : $prenom $nom</span>";
	$tool_content .= "<a href='".$urlServer."index.php?logout=yes' style='float: right; color: white;'>$langLogout</a></td>";
} else {
	$tool_conent .= "<br></font></td></tr>";
}

if (isset($currentCourseID))
$tool_content .= "<tr bgcolor=\"$colorLight\">
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
			
$tool_content .= "<tr><td colspan=\"4\"><font face=\"Arial, Helvetica, sans-serif\" size=\"1\"><a href=\"".$urlServer."index.php\" target=\"_top\">$siteName</a>";

if (isset($currentCourseID))
	$tool_content .= "&nbsp;&gt;&nbsp;<a href=\"".$urlServer."courses/$currentCourseID/index.php\" target=\"_top\" >$intitule</a>";
if (isset($interbredcrump) && is_array($interbredcrump)) 
while (list(,$step) = each($interbredcrump) )
{
	$tool_content .= "&nbsp;&gt;&nbsp;<a target=\"_top\" href=\"".$step["url"]."\" >".$step["name"]."</a>";
};

if (isset($nameTools)) $tool_content .= "&nbsp;&gt;&nbsp;<b>$nameTools</b>";

$tool_content .= "<br></font></td></tr></table>";                   // closed table

if (isset($db)) {
	if (isset($currentCourseID))	
		mysql_select_db("$currentCourseID",$db);
	else
		mysql_select_db("$mysqlMainDb",$db);
}

?>
