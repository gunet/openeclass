<?
/*
=============================================================================
GUnet e-Class 2.0
E-learning and Course Management Program
================================================================================
Copyright(c) 2003-2006  Greek Universities Network - GUnet
A full copyright notice can be read in "/info/copyright.txt".

Authors:     Costas Tsibanis <k.tsibanis@noc.uoa.gr>
Yannis Exidaridis <jexi@noc.uoa.gr>
Alexandros Diamantidis <adia@noc.uoa.gr>

For a full list of contributors, see "credits.txt".

This program is a free software under the terms of the GNU
(General Public License) as published by the Free Software
Foundation. See the GNU License for more details.
The full license can be read in "license.txt".

Contact address: GUnet Asynchronous Teleteaching Group,
Network Operations Center, University of Athens,
Panepistimiopolis Ilissia, 15784, Athens, Greece
eMail: eclassadmin@gunet.gr
==============================================================================
*/

$require_login = TRUE;
$langFiles = 'unreguser';
include '../../include/baseTheme.php';

$nameTools = $langUnregCours;

$local_style = 'h3 { font-size: 10pt;} li { font-size: 10pt;} ';

$tool_content = "";
$tool_content .= "<table width=100% border='0' height=316 cellspacing='0' align=center cellpadding='0'>\n";
$tool_content .= "<tr><td valign=top>";

if (isset($_GET['cid']))
  $_SESSION['cid_tmp']=$cid;
if(!isset($_GET['cid']))
  $cid=$_SESSION['cid_tmp'];

if (!isset($doit) or $doit != "yes") {
  $tool_content .= "<br><table border=\"0\" width=80% align=center cellspacing='1' cellpadding='1'>
	      <tr><td class=kk colspan=2 align=center>$langConfirmUnregCours : <em>$cid</em></td></tr>
        <tr><td>&nbsp;</td></tr>
        <tr>
        <td class='DocData' style=\"border: 1px solid $table_border;\" width='50%' align=center onMouseOver=\"this.style.backgroundColor='#C0BD85'\"; onMouseOut=\"this.style.backgroundColor='transparent'\">
           $langYes:&nbsp;<a href='$_SERVER[PHP_SELF]?u=$uid&cid=$cid&doit=yes' class=mainpage>$langDelete</a>
           </td>
           <td class='DocData' style=\"border: 1px solid $table_border;\" align=center onMouseOver=\"this.style.backgroundColor='orange'\"; onMouseOut=\"this.style.backgroundColor='transparent'\">
           $langNo:&nbsp;<a href='../../index.php' class=mainpage>$langBack</a>
           </td></tr></table>";

} else {
if (isset($uid) and $uid==$_SESSION['uid']) {
            $tool_content .= "<table cellpadding=3 height=320 cellspacing=0 border=0 width=100%>";
            $tool_content .= "<br><tr valign=top>";
            $tool_content .= "<td align=center valign=top class=td_main>";
            $tool_content .= "<span class='labeltext'>";
            db_query("DELETE from cours_user WHERE code_cours='$cid' and user_id='$uid'");
                if (mysql_affected_rows() > 0) {
                        $tool_content .= "<p class=alert1>$langCoursDelSuccess</p>";
                } else {
                        $tool_content .= "<p class=alert1>$langCoursError</p>";
                }
         }
       $tool_content .= "<div align=right><a href='../../index.php' class=mainpage>$langBack</a></div><br><br>";
			 $tool_content .= "</td></tr></table>";
}

$tool_content .= "</td></tr></table>";
if (isset($_SESSION['uid'])) {
        draw($tool_content, 1);
} else {
        draw($tool_content, 0);
}
?>
