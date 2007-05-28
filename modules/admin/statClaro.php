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
    statClaro.php
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
$langStat4Claroline = "Στατιστικά πλατφόρμας";
$nameTools = $langPlatformGenStats;
$navigation[] = array("url" => "index.php", "name" => $langAdmin);
// Initialise $tool_content
$tool_content = "";

/*****************************************************************************
        MAIN BODY
******************************************************************************/
$tool_content .=  "<a href='statClaro.php'>".$langPlatformGenStats."</a> <br> ".
                "<a href='platformStats.php?first='>".$langVisitsStats."</a> <br> ".
             "<a href='visitsCourseStats.php?first='>".$langVisitsCourseStats."</a> <br> ".
              "<a href='oldStats.php'>".$langOldStats."</a> <br> ".
               "<a href='monthlyReport.php'>".$langMonthlyReport."</a>".
          "<p>&nbsp</p>";

          
// Constract some tables with statistical information
$tool_content .= "
<br>
<table width=\"99%\" border=\"0\" align=center cellspacing='0' cellpadding='1' style=\"border: 1px solid silver\">
<tr><th class='stat1'>".$langNbLogin."</th></tr>
<tbody>
";

$tool_content .= "
<tr>
  <td class='stat2'>".$langSince." ".list_1Result("select loginout.when from loginout order by loginout.when limit 1 ").": <b>".list_1Result("select count(*) from loginout where loginout.action ='LOGIN' ")."</b></td>
</tr>
<tr>
  <td class='stat2'>".$langLast30Days.": <b>".list_1Result("select count(*) from loginout where action ='LOGIN' and (loginout.when > DATE_SUB(CURDATE(),INTERVAL 30 DAY))")."</b></td>
</tr>
<tr>
  <td class='stat2'>".$langLast7Days.": <b>".list_1Result("select count(*) from loginout where action ='LOGIN' and (loginout.when > DATE_SUB(CURDATE(),INTERVAL 7 DAY))")."</b></td>
</tr>
<tr>
  <td class='stat2'>".$langToday.": <b>".list_1Result("select count(*) from loginout where action ='LOGIN' and (loginout.when > curdate())")."</b></td>
</tr>
";
$tool_content .="
</tbody>
</table>
<br>";

$tool_content .= "
<br>
<table width=\"99%\" border=\"0\" align=center cellspacing='0' cellpadding='1' style=\"border: 1px solid silver\">
<tr><th class='stat1'>$langNbUsers</th></tr>
<tbody>
";

$tool_content .= "
<tr>
  <td class='stat2'>".$langNbProf.": <b>".list_1Result("select count(*) from user where statut = 1;")."</b></td>
</tr>
<tr>
  <td class='stat2'>".$langNbStudents.": <b>".list_1Result("select count(*) from user where statut = 5;")."</b></td>
</tr>
<tr>
  <td class='stat2'>$langNbVisitors: <b>".list_1Result("select count(*) from user where statut = 10;")."</b></td>
</tr>
";

$tool_content .="
</tbody>
</table>
<br>";

$tool_content .= "
<br>
<table width=\"99%\" border=\"0\" align=center cellspacing='0' cellpadding='1' style=\"border: 1px solid silver\">
<tr><th class='stat1'>$langOthers</th></tr>
<tbody>
";

$tool_content .= "
<tr>
  <td class='stat2'>".$langNbCourses.": <b>".list_1Result("select count(*) from cours;")."</b></td>
</tr>
<tr>
  <td class='stat2'>".$langNbAnnoucement.": <b>".list_1Result("select count(*) from annonces;")."</b></td>
</tr>
";

$tool_content .="
</tbody>
</table>
<br>";

$tool_content .= "
<br>
<table width=\"99%\" border=\"0\" align=center cellspacing='0' cellpadding='1' style=\"border: 1px solid silver\">
<tr><th class='stat1'>$langCoursesPerDept</th></tr>
<tbody>
";

$tool_content .= "
<tr><td class='stat2'>".tablize(list_ManyResult("select DISTINCT faculte, count(*) from cours Group by faculte "), $language)."</td></tr>
";

$tool_content .="
</tbody>
</table>
<br>
";

$tool_content .= "
<br>
<table width=\"99%\" border=\"0\" align=center cellspacing='0' cellpadding='1' style=\"border: 1px solid silver\">
<tr><th class='stat1'>$langCoursesPerLang</th></tr>
<tbody>
";

$tool_content .= "
<tr><td class='stat2'>".tablize(list_ManyResult("select DISTINCT languageCourse, count(*) from cours Group by languageCourse "), $language)."</td></tr>
";

$tool_content .="
</tbody>
</table>
<br>
";

$tool_content .= "
<br>
<table width=\"99%\" border=\"0\" align=center cellspacing='0' cellpadding='1' style=\"border: 1px solid silver\">
<tr><th class='stat1'>$langCoursesPerVis</th></tr>
<tbody>
";

$tool_content .= "
<tr><td class='stat2'>
".tablize(list_ManyResult("select DISTINCT visible, count(*) 
from cours Group by visible "), $language)."
</td></tr>
";

$tool_content .="
</tbody>
</table>
<br>
";


$tool_content .= "
<br>
<table width=\"99%\" border=\"0\" align=center cellspacing='0' cellpadding='1' style=\"border: 1px solid silver\">
<tr><th class='stat1'>$langCoursesPerType</th></tr>
<tbody>
";

$tool_content .= "
<tr><td class='stat2'>
".tablize(list_ManyResult("select DISTINCT type, 
count(*) from cours Group by type "), $language)."
</td></tr>
";

$tool_content .="
</tbody>
</table>
<br>
";


$tool_content .= "
<br>
<table width=\"99%\" border=\"0\" align=center cellspacing='0' cellpadding='1' style=\"border: 1px solid silver\">
<tr><th class='stat1'>$langUsersPerCourse</th></tr>
<tbody>
";

$tool_content .= "
<tr><td class='stat2'>
".tablize(list_ManyResult("select cours.intitule, count(user_id)
from cours_user, cours where cours.code=cours_user.code_cours Group by code_cours order by code_cours"), $language)."
</td></tr>
";

$tool_content .="
</tbody>
</table>
<br>
";


$tool_content .= "<font color=\"#FF0000\">$langErrors</font>";

$tool_content .= "
<br><br>
<table width=\"99%\" border=\"0\" align=center cellspacing='0' cellpadding='1' style=\"border: 1px solid silver\">
<tr><th class='stat1'>$langMultEnrol</th></tr>
<tbody>
";

$tool_content .= "
<tr><td class='stat2'>";

$sqlLoginDouble = "select DISTINCT username , count(*) as nb from user group by username HAVING nb > 1  order by nb desc ";
$loginDouble = list_ManyResult($sqlLoginDouble);
$tool_content .= $sqlLoginDouble;
if (count($loginDouble) > 0) { 	
	$tool_content .= "<br>";
	$tool_content .= error_message($langError);
 	$tool_content .= "<br>";
	$tool_content .= tablize($loginDouble, $language);
} else { 
	$tool_content .= ok_message($langOk);
}
$tool_content .= "</td></tr>
";

$tool_content .="
</tbody>
</table>
<br>
";


$tool_content .= "
<table width=\"99%\" border=\"0\" align=center cellspacing='0' cellpadding='1' style=\"border: 1px solid silver\">
<tr><th class='stat1'>$langMultEmail</th></tr>
<tbody>
";

$tool_content .= "
<tr><td class='stat2'>";

$sqlLoginDouble = "select DISTINCT email, count(*) as nb from user group by email HAVING nb > 1  order by nb desc";
$loginDouble = list_ManyResult($sqlLoginDouble);
$tool_content .= $sqlLoginDouble;
if (count($loginDouble) > 0) { 	
	$tool_content .= "<br>";
	$tool_content .= error_message($langError);
 	$tool_content .= "<br>";
	$tool_content .= tablize($loginDouble, $language);
} else { 
	$tool_content .= ok_message($langOk);
}
$tool_content .= "</td></tr>
";

$tool_content .="
</tbody>
</table>
<br>
";


$tool_content .= "
<table width=\"99%\" border=\"0\" align=center cellspacing='0' cellpadding='1' style=\"border: 1px solid silver\">
<tr><th class='stat1'>$langMultLoginPass</th></tr>
<tbody>
";

$tool_content .= "
<tr><td class='stat2'>";

$sqlLoginDouble = "select DISTINCT CONCAT(username, \" -- \", password) as paire, count(*) as nb from user group by paire HAVING nb > 1   order by nb desc";
$loginDouble = list_ManyResult($sqlLoginDouble);
$tool_content .= $sqlLoginDouble;
if (count($loginDouble) > 0) { 	
	$tool_content .= "<br>";
	$tool_content .= error_message($langError);
 	$tool_content .= "<br>";
	$tool_content .= tablize($loginDouble, $language);
} else { 
	$tool_content .= ok_message($langOk);
}
$tool_content .= "</td></tr>
";

$tool_content .="
</tbody>
</table>
<br>
";


// Display link back to index.php
$tool_content .= "<br><center><p><a href=\"index.php\">".$langReturn."</a></p></center>";

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
				case '0': $key = $langHiddens; break;
				case '1'; $key = $langVis_enrols; break;
				case '2': $key = $langVisibles; break;
				case '5': $key = 'Φοιτητές'; break;
				case '10': $key = 'Επισκέπτες'; break;
				case 'pre': $key = $langPre; break;
				case 'post': $key = $langPost; break;
				case 'other': $key = '¶λλο'; break;
				case 'english': $key = $langEnglish; break;
				case 'greek': $key = $langGreek; break;
			}
			if (strpos($key, 'Statut :10')) $key = substr_replace($key, 'Επισκέπτες', strlen($key)-10);
			if (strpos($key, 'Statut :1')) $key = substr_replace($key, 'Καθηγητές', strlen($key)-9);
			if (strpos($key, 'Statut :5')) $key = substr_replace($key, 'Φοιτητές', strlen($key)-9);
			$ret .= "<td bgcolor=\"#e6e6e6\">".$key."</td>";
			$ret .= "<td bgcolor=\"#f5f5f5\"><strong>".$laValeur."</strong></td>";
			$ret .= "</tr>";
		}
	$ret .= "</table>";
	}
	return $ret;
}

function ok_message($mess) {
	return " <b><span style=\"color: #00FF00\">".$mess."</span></b>";
}

function error_message($mess) {
	return " <b><span style=\"color: #FF0000\">".$mess."</span></b>";
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

	$res = mysql_query($sql ,$db);
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
