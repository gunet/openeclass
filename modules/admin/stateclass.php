<?php
/**=============================================================================
           GUnet e-Class 2.0
        E-learning and Course Management Program
================================================================================
           Copyright(c) 2003-2006  Greek Universities Network - GUnet
        A full copyright notice can be read in "/info/copyright.txt".

           Authors:    Costas Tsibanis <k.tsibanis@noc.uoa.gr>
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
==============================================================================*/

/**===========================================================================
    stateclass.php
    @last update: 05-07-2006 by Pitsiougas Vagelis
    @authors list: Karatzidis Stratos <kstratos@uom.gr>
               Pitsiougas Vagelis <vagpits@uom.gr>
==============================================================================
        @Description: Various Statistics

==============================================================================*/

/*****************************************************************************
        DEAL WITH LANGFILES, BASETHEME, OTHER INCLUDES AND NAMETOOLS
******************************************************************************/
// Set the langfiles needed
$langFiles = array('admin', 'usage');
// Check if user is administrator and if yes continue
// Othewise exit with appropriate message
$require_admin = TRUE;
// Include baseTheme
include '../../include/baseTheme.php';

// Define $nameTools
$nameTools = $langStat4eClass;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);

// Initialise $tool_content
$tool_content = "";

/*****************************************************************************
        general statistics
******************************************************************************/
$tool_content .=  "<a href='stateclass.php'>".$langPlatformGenStats."</a> <br> ".
                "<a href='platformStats.php?first='>".$langVisitsStats."</a> <br> ".
             "<a href='visitsCourseStats.php?first='>".$langVisitsCourseStats."</a> <br> ".
              "<a href='oldStats.php'>".$langOldStats."</a> <br> ".
               "<a href='monthlyReport.php'>".$langMonthlyReport."</a>";

$tool_content .= "<table width=99% border='0' height=316 cellspacing='0' align=center cellpadding='0'>\n";
$tool_content .= "<tr>\n";
$tool_content .= "<td valign=top>
 <table width='96%' align='center' class='admin'>
   <tr>
     <td valign=top>
     <table width=90% align=center border=0 cellspacing='0' cellpadding='0'>
     <tr><td valign=top width=49%>
 <table border='0' width=100% align=center cellspacing='0' cellpadding='1' style='border: 1px solid $table_border'>
    <tr><td class='stat1' colspan='3'>$langNbLogin</td></tr>
   <tr>
  <td class='stat2'>$langFrom ".list_1Result("select loginout.when from loginout order by loginout.when limit 1").": </td>
	<td class='stat2' align=right width='25%'><b>".
list_1Result("select count(*) from loginout where loginout.action ='LOGIN'")."</b></td></tr>
 <tr><td class='stat2'>$langLast30Days :</td><td class='stat2' align=right><b>
".list_1Result("select count(*) from loginout where action ='LOGIN' and (loginout.when > DATE_SUB(CURDATE(),INTERVAL 30 DAY))")."</b></td></tr>
<tr><td class='stat2'>$langLast7Days :</td><td class='stat2' align=right><b>
".list_1Result("select count(*) from loginout where action ='LOGIN' and (loginout.when > DATE_SUB(CURDATE(),INTERVAL 7 DAY))")."</b></td>
 </tr>
<tr><td class='stat2'>$langToday :</td><td class='stat2' align=right><b>
".list_1Result("select count(*) from loginout where action ='LOGIN' and (loginout.when > curdate())")."</b></td> </tr>
</table>
</td><td width=2%>&nbsp;</td>";

$tool_content .= "<td valign=top width=49%><table border='0' width=100% align=center cellspacing='0' cellpadding='1' style='border: 1px solid $table_border'>
 <tr><td class='stat1' colspan='3'>$langNumUsers</td></tr>
 <tr>
 <td class='stat2'>$langNbProf :</td><td class='stat2' align=right width='25%'>
<b>".list_1Result("select count(*) from user where statut = 1;")."</b></td>
   </tr>
  <tr>
  <td class='stat2'>$langNbStudents :</td><td class='stat2' align=right><b>
".list_1Result("select count(*) from user where statut = 5;")."</b></td></tr>
 <tr><td class='stat2'>$langNumGuest :</td><td class='stat2' align=right>
<b>".list_1Result("select count(*) from user where statut = 10;")."</b></td></tr>
  <tr><td class='stat2' colspan=3>&nbsp;</td></tr></table>
  </td></tr></table>";

          
// Constract some tables with statistical information
$tool_content .= "<table border='0' width=90% align=center cellspacing='0' cellpadding='1' style='border: 1px solid $table_border'>
 <tr><td class='stat1' colspan='2'>$langStatCour</td>
 </tr>
   <tr><td class='stat2'>&nbsp;</td></tr>
     <tr>
      <td class='stat2' valign=top>
        <table width=100% align=center border=0 cellspacing='0' cellpadding='0'>
        <tr><td valign=top width=49% align=center>
     <table border='0' width='75%' border=1 align=center cellspacing='0' cellpadding='1' style='border: 1px solid $table_border'>
      <tr><td  style='background: #E6EDF5; color: #4F76A3; font-size: 90%'>$langCourses :</td></tr>
     <tr>
      <td class='stat2'>$langNumCourses : <b>".list_1Result("select count(*) from cours;")."</b></td>
    </tr></table>

      <br>
    <table border='0' width='75%' border=1 align=center cellspacing='0' cellpadding='1' style='border: 1px solid $table_border'>
     <tr><td  style='background: #E6EDF5; color: #4F76A3; font-size: 90%'>$langNumEachCourse :</td></tr>
    <tr>
   <td class='stat2'>".tablize(list_ManyResult("select DISTINCT faculte, count(*) from cours Group by faculte"), $language)."</td>
       </tr>
       </table>";

$tool_content .= "<br><table border='0' align=center width='75%' cellspacing='0' cellpadding='1' style='border: 1px solid  $table_border'>
    <tr><td style='background: #E6EDF5; color: #4F76A3; font-size: 90%'>$langNumEachLang :</td></tr>
  <tr>
  <td class='stat2'>".tablize(list_ManyResult("select DISTINCT languageCourse, count(*) from cours Group by languageCourse "), $language)."</td></tr></table>
 <br>

  <table border='0' align=center width='75%' cellspacing='0' cellpadding='1' style='border: 1px solid $table_border'>
   <tr>
   <td  style='background: #E6EDF5; color: #4F76A3; font-size: 90%'>$langNunEachAccess :</td>
    </tr>
      <tr>
  <td class='stat2'>".tablize(list_ManyResult("select DISTINCT visible, count(*) from cours GROUP BY visible "), $language)."</td>
        </tr>
        </table>

        <br>

  <table border='0' align=center width='75%' cellspacing='0' cellpadding='1' style='border: 1px solid $table_border'>
        <tr>
          <td style='background: #E6EDF5; color: #4F76A3; font-size: 90%'>$langNumEachCat :</td>
        </tr>
        <tr>
 <td class='stat2'>".tablize(list_ManyResult("select DISTINCT type, count(*) from cours GROUP BY type"),$language)."</td></tr>
        </table>";


$tool_content .= "<table width='75%' border=1 align=center cellspacing='0' cellpadding='1' style='border: 1px solid $table_border'>
   <tr><td style='background: #E6EDF5; color: #4F76A3; font-size: 90%'>$langAnnouncements :</td></tr>
   <tr>
  <td class='stat2'>$langNbAnnoucement :<b>".list_1Result("select count(*) from annonces;")."</b></td>
        </tr>
        </table>
        <br>
				</td>
        <td valign=top width=49%>

 <table border='0' align=center width='75%' cellspacing='0' cellpadding='1' style='border: 1px solid  $table_border'>
        <tr>
          <td style='background: #E6EDF5; color: #4F76A3; font-size: 90%' colspan='2'>$langNumEachRec :</td>
        </tr>
        <tr>
 <td class='stat2'>".tablize(list_ManyResult("select CONCAT(code_cours,\" Statut :\",statut), count(user_id) 
				from cours_user GROUP BY code_cours, statut order by code_cours"), $language)."</td>
        </tr></table>
</td></tr></table>";


$tool_content .= " </td></tr>
<tr><td class='stat2'>&nbsp;</td></tr></table>";

$tool_content .= "<table width=90% align=center cellspacing='0' cellpadding='1' style='border: 1px solid $table_border' > 
	<tr><td class=stat1 width=80%><b>$langAlert !</b></td></tr>
    <tr><td>
 <table border='0' width=100% align=center cellspacing='0' cellpadding='1'  style='border: 1px solid $table_border'>";

$sqlLoginDouble = "select DISTINCT username, count(*) as nb from user group by username HAVING nb > 1 order by nb desc";
$loginDouble = list_ManyResult($sqlLoginDouble);

$tool_content .= "<tr><td class=stat2 width=80%><strong>$langMultipleUsers :</strong><br>
<span class='explanationtext'>(".$sqlLoginDouble.")</span></td>
<td class=color2 align=center><strong>$langResult</strong></td>
    </tr>
    <tr><td class=stat2>";

if (count($loginDouble) > 0) {
        $tool_content .= tablize($loginDouble, $language);
      $tool_content .=  "</td><td class=color2 align=center>".error_message()." ";
} else {
        $tool_content .= "</td><td class=color2 align=center>".ok_message()." ";
}
$tool_content .= "</td></tr></table></td></tr>";


$sqlLoginDouble = "select DISTINCT email, count(*) as nb from user group by email HAVING nb > 1  order by nb desc";
$loginDouble = list_ManyResult($sqlLoginDouble);

$tool_content .= "<tr><td><table border='0' width=100% align=center cellspacing='0' cellpadding='1' style='border: 1px solid $table_border'>
     <tr>
       <td class='stat2' width=80%><strong>$langMultipleAddr e-mail:</strong>
       <br><span class='explanationtext'>(".$sqlLoginDouble.")</span></td>
       <td class=color2 align=center><strong>$langResult</strong></td>
     </tr>
     <tr>
       <td class=stat2>";

if (count($loginDouble) > 0) {
 $tool_content .= tablize($loginDouble, $language);
 $tool_content .= "</td><td class=color2 align=center>";
 $tool_content .=  error_message();
}
else
{
 $tool_content .=  "</td><td class=color2 align=center>";
 $tool_content .=  ok_message();
}
$tool_content .= "</td></tr></table>";
$tool_content .=  "</td></tr>";

$sqlLoginDouble = "select DISTINCT CONCAT(username, \" -- \", password) as paire, count(*) as nb from user group by paire 
					HAVING nb > 1 order by nb desc";
$loginDouble = list_ManyResult($sqlLoginDouble);

$tool_content .= "<tr><td>
 <table border='0' width=100% align=center cellspacing='0' cellpadding='1' style='border: 1px solid $table_border'>
   <tr>
    <td class='stat2' width=80%><strong>$langMultiplePairs LOGIN - PASS</strong>
     <br><span class='explanationtext'>(".$sqlLoginDouble.")</td>
      <td class='color2' align=center><strong>$langResult</strong></td>
    </tr>
    <tr><td class=stat2>";


if (count($loginDouble) > 0) {
$tool_content .=  tablize($loginDouble, $language);
$tool_content .= "</td><td class=color2 align=center>";
$tool_content .= error_message();
} else {
$tool_content .= "</td><td class=color2 align=center>";
$tool_content .= ok_message();
}
$tool_content .= "</tr></table></td></tr></table>";
$tool_content .= "</td></tr>
   <tr><td colspan=2><p align=right><a href='index.php' class=mainpage>$langBackAdmin&nbsp;</a></p>";
$tool_content .= "</td></tr></table></td></tr></table>";


/**
 * output an <Table> with an array
 *
 * @return void
 * @param  array $tableau arrey to output
 * @desc output an <Table> with an array
 */
 
function tablize($tableau, $lang) {
    if ($lang) {
        include "../lang/".$lang."/usage.inc.php";
    }
    $ret = "";
	if (is_array($tableau)) { 
		$ret .= "<table ";
		$ret .= "align=\"center\"  ";
    	$ret .= "bgcolor=\"#ffcccc\"  border=\"1\" ";
    	$ret .= "cellpadding=\"1\" cellspacing=\"0\" > ";
    	while ( list( $key, $laValeur ) = each($tableau)) { 
			$ret .= "<tr>"; 
			switch ($key) {
				case '0': $key = $langClosed; break;
				case '1'; $key = $langTypesRegistration; break;
				case '2': $key = $langOpen; break;
				case '5': $key = $langStudents; break;
				case '10': $key = $langGuest; break;
				case 'pre': $key = $langPre; break;
				case 'post': $key = $langPost; break;
				case 'other': $key = $langOther; break;
				case 'english': $key = $langEnglish; break;
				case 'greek': $key = $langGreek; break;
			}
			if (strpos($key, 'Statut :10')) $key = substr_replace($key, $langGuest, strlen($key)-10);
			if (strpos($key, 'Statut :1')) $key = substr_replace($key, $langProf, strlen($key)-9);
			if (strpos($key, 'Statut :5')) $key = substr_replace($key, $langStudents, strlen($key)-9);
			$ret .= "<td bgcolor=\"#e6e6e6\" style=\"font-size: 90%\">".$key."</td>";
			$ret .= "<td bgcolor=\"#f5f5f5\"><strong>".$laValeur."</strong></td>";
			$ret .= "</tr>";
		}
	$ret .= "</table>";
	}
	return $ret;
}

function ok_message() {

	global $langNotExist;

	return " <b><span style=\"color: #00FF00\">$langNotExist</span></b>";
}

function error_message() {
	global $langExist;

	return " <b><span style=\"color: #FF0000\">$langExist</span></b>";
} 


function list_1Result($sql) {
	global $db;

	$res = mysql_query($sql ,$db);
	$res = mysql_fetch_array($res);
	return $res[0];
}

function list_ManyResult($sql) { 
	global $db;
	$resu=array();

	$res =db_query($sql ,$db);
	while ($resA = mysql_fetch_array($res))
	{ 
		$resu[$resA[0]]=$resA[1];
	}
	return $resu;
}


/*****************************************************************************
        DISPLAY HTML
******************************************************************************/
// Call draw function to display the HTML
// $tool_content: the content to display
// 3: display administrator menu
// admin: use tool.css from admin folder
draw($tool_content,3,'admin');
?>
