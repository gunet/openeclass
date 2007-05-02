<?
$langFiles = 'about';
$path2add=2;
include '../include/baseTheme.php';
$nameTools = $langInfo;
$tool_content ="";

$tool_content .= "<div style='text-align: justify; padding: 15px; font-size:10pt;'>$langIntro<br><br>$langAboutText:&nbsp;<b>$langEClass $langEclassVersion</b>&nbsp;&nbsp;<a href='http://portal.eclass.gunet.gr/' title='Portal eClass' target='_blank' border=0><img src='../images/about.gif' width=16 height=16 align=absbottom border=0></a><br><br>";

$a=mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM cours"));
$a1=mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM cours WHERE visible='2'"));
$a2=mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM cours WHERE visible='1'"));
$a3=mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM cours WHERE visible='0'"));

$tool_content .= "$langAboutCourses <b>$a[0]</b> $langCourses<br>";
$tool_content .= "<blockquote>
        <img src='../images/arrow_red.gif'>&nbsp;&nbsp;<i><b>$a1[0]</b> $langOpen,<br>
        <img src='../images/arrow_red.gif'>&nbsp;&nbsp;<b>$a2[0]</b> $langSemiopen,<br>
        <img src='../images/arrow_red.gif'>&nbsp;&nbsp;<b>$a3[0]</b> $langClosed </i><br>
       </blockquote>";

$e=mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM user"));
$b=mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM user where statut='1'"));
$c=mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM user where statut='5'"));
$d=mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM user where statut='10'"));
$tool_content .= "$langAboutUsers <b>$e[0]</b> $langUsers";

$tool_content .= "<blockquote>
          <img src='../images/arrow_red.gif'>&nbsp;&nbsp;<i><b>$b[0]</b> $langProf, <br>
          <img src='../images/arrow_red.gif'>&nbsp;&nbsp;<b>$c[0]</b> $langStud $langAnd<br>
          <img src='../images/arrow_red.gif'>&nbsp;&nbsp;<b>$d[0]</b> $langGuest </i>
          </blockquote></div>";

$tool_content .= "<div style='text-align: justify; padding-left: 15px; font-size:10pt;'>$langSupportUser <i> $administratorName $administratorSurname </i></div>";
draw($tool_content, 0);
?>
