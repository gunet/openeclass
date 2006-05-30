<?
$langFiles = 'registration';
$path2add=2;
include '../include/baseTheme.php';

$nameTools = 'Αναβάθμιση';


// Initialise $tool_content
$tool_content = "";
// Main body
//====================

$tool_content .= "<form method=\"post\" action=\"upgrade.php\">
<table width=\"99%\"><caption>Πληροφορίες Αναβάθμισης</caption><tbody>
<tr><td valign=\"top\" width=\"3%\" nowrap><b>ΒΗΜΑ 1:</b></td>
<td><br>Διαβάστε τις οδηγίες αναβάθμισης του e-Class κάνοντας κλικ <b><a href=\"UPGRADE.txt\" target=\"blank\">ΕΔΩ</a></b>.</td></tr>
<tr><td valign=\"top\" width=\"3%\" nowrap><b>ΒΗΜΑ 2:</b></td>
<td><br>Δείτε τις αλλαγές - βελτιώσεις της καινούργιας έκδοσης του e-Class κάνοντας κλικ <b><a href=\"CHANGES.txt\" target=\"blank\">ΕΔΩ</a></b>.</td></tr>
<tr><td valign=\"top\" width=\"3%\" nowrap><b>ΒΗΜΑ 3:</b></td>
<td><br>Για να προχωρήσετε στην αναβάθμιση της βάσης δεδομένων,
	δώστε το όνομα χρήστη και το συνθηματικό του διαχειριστή της πλατφόρμας:</p>
	<p align=\"left\">
	$langUsername : <input type=\"text\" name=\"login\" size=\"20\"><br><br>
	$langPass :   <input type=\"password\" name=\"password\" size=\"20\"><br><br>
	<input type=\"submit\" name=\"submit_upgrade\" value=\"Αναβάθμιση Βάσης Δεδομένων\"></td></tr>
</tbody></table></form>";

$tool_content .= "<br><center><p><a href=\"../index.php\">Επιστροφή</a></p></center>";

draw($tool_content,0);

?>