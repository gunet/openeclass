<?
$langFiles = 'about';
//include '../include/init.php';
$path2add=2;
include '../include/baseTheme.php';
$nameTools = $langInfo;
//begin_page();
$tool_content ="";

$tool_content .= "<p>$langAboutText <b>$siteName $langEclassVersion</b></p>";
//$tool_content .= "<p></p>";
//$tool_content .= "<hr width='80%'>";
$a=mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM cours"));
$a1=mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM cours WHERE visible='2'"));
$a2=mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM cours WHERE visible='1'"));
$a3=mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM cours WHERE visible='0'"));

$tool_content .= "<p>$langAboutCourses <b>$a[0]</b> $langCourses";
$tool_content .= " (<i><b>$a1[0]</b> $langOpen, <b>$a2[0]</b> $langSemiopen, <b>$a3[0]</b> $langClosed</i>)</p>"; 
//$tool_content .= "<hr width='80%'>";
$e=mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM user"));
$b=mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM user where statut='1'"));
$c=mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM user where statut='5'"));
$d=mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM user where statut='10'"));
$tool_content .= "<p>$langAboutUsers <b>$e[0]</b> $langUsers";
$tool_content .= " (<i><b>$b[0]</b> $langProf, <b>$c[0]</b> $langStud $langAnd <b>$d[0]</b> $langGuest</i>)</p>";

//$tool_content .= "</font><br>";
//end_page();
draw($tool_content, 0);
?>
