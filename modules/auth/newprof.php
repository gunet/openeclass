<?
$langFiles = array('registration', 'admin', 'gunet');
include '../../include/baseTheme.php';
@include "check_admin.inc";
$nameTools = "Εγγραφή Καθηγητή";

// Initialise $tool_content
$tool_content = "";
// Main body

/* Check for LDAP server entries */
//$ldap_entries = mysql_fetch_array(mysql_query("SELECT * FROM institution"));
//if ($ldap_entries['ldapserver'] <> NULL) 
//	$navigation[]= array ("url"=>"newprof_info.php", "name"=> $regprof);

$tool_content .= "	<form action=\"newprof_second.php\" method=\"post\">
	<table width=\"99%\"><caption>Εισαγωγή Στοιχείων Καθηγητή</caption><tbody>
	<tr valign=\"top\" bgcolor=\"".$color2."\">
	<td width=\"3%\" nowrap><b>".$langSurname."</b></td>
	<td><input type=\"text\" name=\"nom_form\" value=\"".@$ps."\" >&nbsp;(*)</td>
	</tr>
	<tr bgcolor=\"".$color2."\">
	<td width=\"3%\" nowrap><b>".$langName."</b></td>
	<td>
	<input type=\"text\" name=\"prenom_form\" value=\"".@$pn."\">&nbsp;(*)</td>
	</tr>
	<tr bgcolor=\"".$color2."\">
	<td width=\"3%\" nowrap><b>".$langUsername."</b></td>
	<td><input type=\"text\" name=\"uname\" value=\"".@$pu."\">&nbsp;(*)</td>
	</tr>
	<tr bgcolor=\"".$color2."\">
	<td width=\"3%\" nowrap><b>".$langPass."&nbsp;:</b></td>
	<td><input type=\"text\" name=\"password\" value=\"".create_pass(5)."\"></td>
	</tr>
	<tr bgcolor=\"".$color2."\">
	<td width=\"3%\" nowrap><b>".$langEmail."</b></td>
	<td><input type=\"text\" name=\"email_form\" value=\"".@$pe."\">&nbsp;(*)</b></td>
	</tr>
	<tr bgcolor=\"".$color2."\">
	<td width=\"3%\" nowrap><b>".$langDepartment."&nbsp;</b></td>
	<td>";

	$dep = array();
        $deps=db_query("SELECT name FROM faculte order by id");
	while ($n = mysql_fetch_array($deps))
		$dep[$n[0]] = $n['name'];  

	if (isset($pt))
		selection ($dep, 'department', $pt);
	else 
		selection ($dep, 'department');

$tool_content .= "
	</td>
	</tr>
	<tr><td colspan=\"2\">".$langRequiredFields."</td></tr>
	<tr><td>&nbsp;</td>
	<td><input type=\"submit\" name=\"submit\" value=\"".$langOk."\" ></td>
	</tr>
</tbody></table></form>
";

$tool_content .= "<center><p><a href=\"../admin/index.php\">Επιστροφή</p></center>";

draw($tool_content,3,'admin');

// creating passwords automatically
function create_pass($length) {
	$res = "";
	$PASSCHARS="abcdefghijklmnopqrstuvwxyz023456789";
	$PASSL = 35;
	srand ((double) microtime() * 1000000);
	for ($i = 1; $i<=$length ; $i++ ) {
		$res .= $PASSCHARS[rand(0,$PASSL-1)];
	}
	return $res;
}
?>