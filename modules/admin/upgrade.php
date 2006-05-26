<?php
$langFiles = 'registration';
include '../../include/baseTheme.php';
@include "check_admin.inc";
$nameTools = 'Αναβάθμιση Βάσης Δεδομένων';

// Initialise $tool_content
$tool_content = "";
// Main body

$tool_content .= "<form method=\"post\" action=\"../../upgrade/upgrade.php\">
<table width=\"99%\"><caption>Πληροφορίες Αναβάθμισης</caption><tbody>
<tr><td valign=\"top\" width=\"3%\" nowrap><b>ΒΗΜΑ 1:</b></td>
<td><br>Διαβάστε τις οδηγίες αναβάθμισης του e-Class κάνοντας κλικ <b><a href=\"../../upgrade/UPGRADE.txt\" target=\"blank\">ΕΔΩ</a></b>.</td></tr>
<tr><td valign=\"top\" width=\"3%\" nowrap><b>ΒΗΜΑ 2:</b></td>
<td><br>Δείτε τις αλλαγές - βελτιώσεις της καινούργιας έκδοσης του e-Class κάνοντας κλικ <b><a href=\"../../upgrade/CHANGES.txt\" target=\"blank\">ΕΔΩ</a></b>.</td></tr>
<tr><td valign=\"top\" width=\"3%\" nowrap><b>ΒΗΜΑ 3:</b></td>
<td><br>Προχωρήστε στην αναβάθμιση της πλατφόρμας πατώντας το κουμπί<br><br><input type=\"submit\" name=\"submit\" value=\"Αναβάθμιση Βάσης Δεδομένων\"></td></tr>
</tbody></table></form>";

$tool_content .= "<br><center><p><a href=\"index.php\">Επιστροφή</a></p></center>";

draw($tool_content,3, 'admin');
?>