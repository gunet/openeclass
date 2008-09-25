<?
/*===========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ===========================================================================
*	Copyright(c) 2003-2008  Greek Universities Network - GUnet
*	A full copyright notice can be read in "/info/copyright.txt".
*
*  	Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*				Yannis Exidaridis <jexi@noc.uoa.gr>
*				Alexandros Diamantidis <adia@noc.uoa.gr>
*
*	For a full list of contributors, see "credits.txt".
*
*	This program is a free software under the terms of the GNU
*	(General Public License) as published by the Free Software
*	Foundation. See the GNU License for more details.
*	The full license can be read in "license.txt".
*
*	Contact address: 	GUnet Asynchronous Teleteaching Group,
*						Network Operations Center, University of Athens,
*						Panepistimiopolis Ilissia, 15784, Athens, Greece
*						eMail: eclassadmin@gunet.gr
============================================================================*/

$require_login = TRUE;
include '../../include/baseTheme.php';

$nameTools = $langUnregCours;

$local_style = 'h3 { font-size: 10pt;} li { font-size: 10pt;} ';

$tool_content = "";
//$tool_content .= "<table width=100% border='0' height=316 cellspacing='0' align=center cellpadding='0'>\n";
//$tool_content .= "<tr><td valign=top>";

if (isset($_GET['cid']))
  $_SESSION['cid_tmp']=$cid;
if(!isset($_GET['cid']))
  $cid=$_SESSION['cid_tmp'];

if (!isset($doit) or $doit != "yes") {

  $tool_content .= "
    <table width=\"99%\">
    <tbody>
    <tr>
      <td class=\"caution_NoBorder\" height='60' colspan='3'>
        <p>$langConfirmUnregCours : <em>$cid</em>&nbsp;? </p>
      </td>
    </tr>
    <tr>
      <th rowspan='2' class='left' width='30%'>$l_confirm :</th>
      <td><a href='$_SERVER[PHP_SELF]?u=$uid&cid=$cid&doit=yes' class=mainpage>$langYes</a></td>
      <td align=\"left\" width='60%'><small>$langUnCourse</small></td>
    </tr>
    <tr>
      <td><a href='../../index.php' class=mainpage>$langNo</a></td>
      <td align=\"left\"><small>&nbsp;</small></td>
    </tr>
    </tbody>
    </table>";

} else {
if (isset($uid) and $uid==$_SESSION['uid']) {
            //$tool_content .= "<table cellpadding=3 height=320 cellspacing=0 border=0 width=100%>";
            //$tool_content .= "<br><tr valign=top>";
            //$tool_content .= "<td align=center valign=top class=td_main>";
            //$tool_content .= "<span class='labeltext'>";
            db_query("DELETE from cours_user WHERE code_cours='$cid' and user_id='$uid'");
                if (mysql_affected_rows() > 0) {
                        $tool_content .= "<p class='success_small'>$langCoursDelSuccess</p>";
                } else {
                        $tool_content .= "<p class='caution_small'>$langCoursError</p>";
                }
         }
        $tool_content .= "<br><br><div align=right><a href='../../index.php' class=mainpage>$langBack</a></div>";
		//$tool_content .= "</td></tr></table>";
}

//$tool_content .= "</td></tr></table>";
if (isset($_SESSION['uid'])) {
        draw($tool_content, 1);
} else {
        draw($tool_content, 0);
}
?>
