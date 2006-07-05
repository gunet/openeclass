<?php
/**=============================================================================
           GUnet e-Class 2.0
        E-learning and Course Management Program
================================================================================
           Copyright(c) 2003-2006  Greek Universities Network - GUnet
        Α full copyright notice can be read in "/info/copyright.txt".

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
$langFiles = array('admin');
// Include baseTheme
include '../../include/baseTheme.php';
// Check if user is administrator and if yes continue
// Othewise exit with appropriate message
@include "check_admin.inc";
// Define $nameTools
$langStat4Claroline = "Στατιστικά πλατφόρμας";
$nameTools = $langStat4Claroline;
// Initialise $tool_content
$tool_content = "";

/*****************************************************************************
        MAIN BODY
******************************************************************************/

// Constract some tables with statistical information
$tool_content .= "<table width=\"99%\"><caption>".$langNbLogin."</caption><tbody>";
$tool_content .= "
<tr><td>
<li>
Από ".list_1Result("select loginout.when from loginout order by loginout.when limit 1 ").": <b>".list_1Result("select count(*) from loginout where loginout.action ='LOGIN' ")."</b></li>
<li>
".$langLast30Days.": <b>".list_1Result("select count(*) from loginout where action ='LOGIN' and (loginout.when > DATE_SUB(CURDATE(),INTERVAL 30 DAY))")."</b></li>
<li>
".$langLast7Days.": <b>".list_1Result("select count(*) from loginout where action ='LOGIN' and (loginout.when > DATE_SUB(CURDATE(),INTERVAL 7 DAY))")."</b></li>
<li>
".$langToday.": <b>".list_1Result("select count(*) from loginout where action ='LOGIN' and (loginout.when > curdate())")."</b></li>
</td></tr>
";
$tool_content .="</tbody></table>";

$tool_content .= "<table width=\"99%\"><caption>Αριθμός Χρηστών</caption><tbody>";
$tool_content .= "
<tr><td>
<li>".$langNbProf.": <b>".list_1Result("select count(*) from user where statut = 1;")."</b></li>
<li>".$langNbStudents.": <b>".list_1Result("select count(*) from user where statut = 5;")."</b></li>
<li>Αριθμός επισκέπτων: <b>".list_1Result("select count(*) from user where statut = 10;")."</b></li>
</td></tr>
";
$tool_content .="</tbody></table>";

$tool_content .= "<table width=\"99%\"><caption>Διάφορα Σύνολα</caption><tbody>";
$tool_content .= "
<tr><td>
<li>Αριθμός μαθημάτων: <b>".list_1Result("select count(*) from cours;")."</b></li>
<li>".$langNbAnnoucement.": <b>".list_1Result("select count(*) from annonces;")."</b></li>
</td></tr>
";
$tool_content .="</tbody></table>";

$tool_content .= "<table width=\"99%\"><caption>Αριθμός μαθημάτων ανά τμήμα</caption><tbody>";
$tool_content .= "
<tr><td>
".tablize(list_ManyResult("select DISTINCT faculte, count(*) from cours Group by faculte "))."
</td></tr>
";
$tool_content .="</tbody></table>";

$tool_content .= "<table width=\"99%\"><caption>Αριθμός μαθημάτων ανά γλώσσα</caption><tbody>";
$tool_content .= "
<tr><td>
".tablize(list_ManyResult("select DISTINCT languageCourse, count(*) from cours Group by languageCourse "))."
</td></tr>
";
$tool_content .="</tbody></table>";

$tool_content .= "<table width=\"99%\"><caption>Αριθμός μαθημάτων ανά κατάσταση ορατότητας</caption><tbody>";
$tool_content .= "
<tr><td>
".tablize(list_ManyResult("select DISTINCT visible, count(*) 
from cours Group by visible "))."
</td></tr>
";
$tool_content .="</tbody></table>";

$tool_content .= "<table width=\"99%\"><caption>Αριθμός μαθημάτων ανά τύπο μαθημάτων</caption><tbody>";
$tool_content .= "
<tr><td>
".tablize(list_ManyResult("select DISTINCT type, 
count(*) from cours Group by type "))."
</td></tr>
";
$tool_content .="</tbody></table>";

$tool_content .= "<table width=\"99%\"><caption>Αριθμός εγγραφών ανά μάθημα</caption><tbody>";
$tool_content .= "
<tr><td>
".tablize(list_ManyResult("select CONCAT(code_cours,\" Statut :\",statut), count(user_id) 
from cours_user Group by code_cours, statut order by code_cours"))."
</td></tr>
";
$tool_content .="</tbody></table>";

$tool_content .= "<font size=\"+2\" color=\"#FF0000\">Σφάλματα:</font>";

$tool_content .= "<table width=\"99%\"><caption>Πολλαπλές εγγραφές χρηστών</caption><tbody>";
$tool_content .= "
<tr><td>";

$sqlLoginDouble = "select DISTINCT username , count(*) as nb from user group by username HAVING nb > 1  order by nb desc ";
$loginDouble = list_ManyResult($sqlLoginDouble);
$tool_content .= $sqlLoginDouble;
if (count($loginDouble) > 0) { 	
	$tool_content .= "<br>";
	$tool_content .= error_message();
 	$tool_content .= "<br>";
	$tool_content .= tablize($loginDouble);
} else { 
	$tool_content .= ok_message();
}
$tool_content .= "</td></tr>
";
$tool_content .="</tbody></table>";

$tool_content .= "<table width=\"99%\"><caption>Πολλαπλές εμφανίσεις διευθύνσεων e-mail</caption><tbody>";
$tool_content .= "
<tr><td>";

$sqlLoginDouble = "select DISTINCT email, count(*) as nb from user group by email HAVING nb > 1  order by nb desc";
$loginDouble = list_ManyResult($sqlLoginDouble);
$tool_content .= $sqlLoginDouble;
if (count($loginDouble) > 0) { 	
	$tool_content .= "<br>";
	$tool_content .= error_message();
 	$tool_content .= "<br>";
	$tool_content .= tablize($loginDouble);
} else { 
	$tool_content .= ok_message();
}
$tool_content .= "</td></tr>
";
$tool_content .="</tbody></table>";

$tool_content .= "<table width=\"99%\"><caption>Πολλαπλά ζεύγη LOGIN - PASS</caption><tbody>";
$tool_content .= "
<tr><td>";

$sqlLoginDouble = "select DISTINCT CONCAT(username, \" -- \", password) as paire, count(*) as nb from user group by paire HAVING nb > 1   order by nb desc";
$loginDouble = list_ManyResult($sqlLoginDouble);
$tool_content .= $sqlLoginDouble;
if (count($loginDouble) > 0) { 	
	$tool_content .= "<br>";
	$tool_content .= error_message();
 	$tool_content .= "<br>";
	$tool_content .= tablize($loginDouble);
} else { 
	$tool_content .= ok_message();
}
$tool_content .= "</td></tr>
";
$tool_content .="</tbody></table>";

// Display link back to index.php
$tool_content .= "<br><center><p><a href=\"index.php\">".$langReturn."</a></p></center>";

/**
 * output an <Table> with an array
 *
 * @return void
 * @param  array $tableau arrey to output
 * @desc output an <Table> with an array
 */
 
function tablize($tableau) { 
	$ret = "";
	if (is_array($tableau)) { 
		$ret .= "<table ";
		$ret .= "align=\"center\"  ";
    	$ret .= "bgcolor=\"#ffcccc\"  border=\"1\" ";
    	$ret .= "cellpadding=\"1\" cellspacing=\"0\" > ";
    	while ( list( $key, $laValeur ) = each($tableau)) { 
			$ret .= "<tr>"; 
			switch ($key) {
				case '0': $key = 'Κλειστά'; break;
				case '1'; $key = 'Ανοικτά με εγγραφή'; break;
				case '2': $key = 'Ανοικτά'; break;
				case '5': $key = 'Φοιτητές'; break;
				case '10': $key = 'Επισκέπτες'; break;
				case 'pre': $key = 'Προπτυχιακά'; break;
				case 'post': $key = 'Μεταπτυχιακά'; break;
				case 'other': $key = '¶λλο'; break;
				case 'english': $key = 'Αγγλικά'; break;
				case 'greek': $key = 'Ελληνικά'; break;
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

function ok_message() {  
	return " <b><span style=\"color: #00FF00\">Εντάξει!</span></b>";
}

function error_message() {
	return " <b><span style=\"color: #FF0000\">Προσοχή!</span></b>";
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