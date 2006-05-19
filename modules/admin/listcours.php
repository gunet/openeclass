<?php
$langFiles = array('admin','gunet');
include '../../include/baseTheme.php';
@include "check_admin.inc";
$nameTools = "Λίστα Μαθημάτων / Ενέργειες";

// Initialise $tool_content
$tool_content = "";
// Main body

// Manage order of display list
if (isset($ord)) {
	switch ($ord) {
		case "s":
			$order = "b.statut"; break;
		case "n":
			$order = "a.nom"; break;
		case "p":
			$order = "a.prenom"; break;
		case "u":
			$order = "a.username"; break;
		default:
			$order = "b.statut"; break;
	}
} else {
	$order = "b.statut";
}

if (isset($c)) {
	$sql = mysql_query("
		SELECT a.nom, a.prenom, a.username, a.password, b.statut, a.user_id
		FROM user AS a LEFT JOIN cours_user AS b ON a.user_id = b.user_id
		WHERE b.code_cours='$c' ORDER BY $order");
	if (!$sql) {
		die("Unable to query database!");
	}
}

if (isset($search) && $search=="yes") {
	$searchurl = "&search=yes";
	if (isset($search_submit)) {
		$searchtitle = $formsearchtitle;
		session_register('searchtitle');
		$searchcode = $formsearchcode;
		session_register('searchcode');
		$searchtype = $formsearchtype;
		session_register('searchtype');
		$searchfaculte = $formsearchfaculte;
		session_register('searchfaculte');
	} else {
		$searchtitle = $_SESSION['searchtitle'];
		$searchcode = $_SESSION['searchcode'];
		$searchtype = $_SESSION['searchtype'];
		$searchfaculte = $_SESSION['searchfaculte'];
	}
	$searchcours=array();
	if(!empty($searchtitle)) {
		$searchcours[] = "intitule LIKE '".mysql_escape_string($searchtitle)."%'";
	}
	if(!empty($searchcode)) {
		$searchcours[] = "code LIKE '".mysql_escape_string($searchcode)."%'";
	}
	if ($searchtype!="-1") {
		$searchcours[] = "visible = '".mysql_escape_string($searchtype)."'";
	}
	if($searchfaculte!="0") {
		$searchcours[] = "faculte = '".mysql_escape_string($searchfaculte)."'";
	}
	$query=join(' AND ',$searchcours);
	if (!empty($query)) {
		$sql=mysql_query("SELECT faculte, code, intitule,titulaires,visible FROM cours WHERE $query ORDER BY faculte");
		$caption .= "Βρέθηκαν ".mysql_num_rows($sql)." μαθήματα";
	} else {
		$sql=mysql_query("SELECT faculte, code, intitule,titulaires,visible FROM cours ORDER BY faculte");
		$caption .= "Βρέθηκαν ".mysql_num_rows($sql)." μαθήματα";			
	}
} else {
	$a=mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM cours"));		
	$caption .= "Υπάρχουν ".$a[0]." μαθήματα";
	$sql = mysql_query("SELECT faculte, code, intitule,titulaires,visible FROM cours ORDER BY faculte");
}

	
// Construct cours list table
$tool_content .= "<table border=\"1\"><caption>".$caption."</caption>
<thead>
  <tr>
    <th scope=\"col\">Τμήμα</th>
    <th scope=\"col\">Κωδικός</th>
    <th scope=\"col\">Τίτλος (Διδάσκων)</th>
    <th scope=\"col\">Κατάσταση Μαθήματος</th>
    <th scope=\"col\">Χρήστες</th>
    <th scope=\"col\">Διαγραφή Μαθήματος</th>
    <th scope=\"col\">Ενέργειες</th>
  </tr>
</thead><tbody>\n";

for ($j = 0; $j < mysql_num_rows($sql); $j++) {
	$logs = mysql_fetch_array($sql);
	$tool_content .= "  <tr>\n";
	 for ($i = 0; $i < 2; $i++) {
	 	$tool_content .= "    <td width=\"500\">".htmlspecialchars($logs[$i])."</td>\n";
	}
	$tool_content .= "    <td width='500'>".htmlspecialchars($logs[2])." (".$logs[3].")</td>\n";
	switch ($logs[4]) {
	case 2:
		$tool_content .= "    <td>Ανοιχτό</td>\n";
		break;
	case 1:
		$tool_content .= "    <td>Απαιτείται Εγγραφή</td>\n";
		break;
	case 0:
		$tool_content .= "    <td>Κελιστό</td>\n";
		break;
	}	
	$tool_content .= "    <td><a href=\"listusers.php?c=".$logs[1]."\">Χρήστες</a></td>
    <td><a href=\"delcours.php?c=".$logs[1]."\">Διαγραφή</a></td>
    <td><a href=\"editcours.php?c=".$logs[1]."".$searchurl."\">Επεξεργασία</a></td>\n";
}

$tool_content .= "</tbody></table>\n";
  
// If a search is started display link to search page
if (isset($search) && $search=="yes") {
	$tool_content .= "<br><center><p><a href=\"searchcours.php\">Επιστροφή στην αναζήτηση</a></p></center>";
}
	
$tool_content .= "<br><center><p><a href=\"index.php\">Επιστροφή</a></p></center>";

draw($tool_content,3,'admin');
?>