<?
$langFiles = 'about';
include '../include/init.php';
$nameTools = $langInfo;
begin_page();
?>
<tr><td><table cellpadding="3" cellspacing="0" border="0" width="100%">
<tr valign="top" bgcolor="<?= $color2 ?>">
<td><font size="2" face="arial, helvetica">
<?
echo "<p align=center>$langAboutText</p>";
echo "<p align=center><b>$siteName $langEclassVersion</b></p>";
echo "<hr width='80%'>";
$a=mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM cours"));
$a1=mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM cours WHERE visible='2'"));
$a2=mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM cours WHERE visible='1'"));
$a3=mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM cours WHERE visible='0'"));
echo "<p align=center>$langAboutCourses <b>$a[0]</b> $langCourses</p>";
echo "<p align=center>(<i><b>$a1[0]</b> $langOpen, <b>$a2[0]</b> $langSemiopen, <b>$a3[0]</b> $langClosed</i>)</p>"; 
echo "<hr width='80%'>";
$e=mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM user"));
$b=mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM user where statut='1'"));
$c=mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM user where statut='5'"));
$d=mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM user where statut='10'"));
echo "<p align=center>$langAboutUsers <b>$e[0]</b> $langUsers</p>";
echo "<p align=center>(<i><b>$b[0]</b> $langProf, <b>$c[0]</b> $langStud $langAnd <b>$d[0]</b> $langGuest</i>)</p>";

echo "</font><br>";
end_page();
?>
