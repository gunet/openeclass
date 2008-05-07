<?
$langFiles = 'registration';
$path2add=2;
include '../include/baseTheme.php';

$nameTools = 'Αναβάθμιση των βάσεων δεδομένων του eClass';

$max_execution_time = trim(ini_get('max_execution_time'));

// Initialise $tool_content
$tool_content = "";

// Main body
$tool_content .= "
<div class='warntitle'>ΠΡΟΣΟΧΗ!</div>
<p>Το πρόγραμμα αναβάθμισης θα τροποποιήσει το αρχείο ρυθμίσεων <em>config.php</em>. 
   Επομένως πριν προχωρήσετε στην αναβάθμιση βεβαιωθείτε ότι ο web server
   μπορεί να έχει πρόσβαση στο <em>config.php</em>. Για λόγους ασφαλείας, οι
   τωρινές ρυθμίσεις του <em>config.php</em> θα κρατηθούν στο αρχείο
   <em>config_backup.php</em>.</p>
<p>Επίσης για λόγους ασφαλείας βεβαιωθείτε ότι έχετε κρατήσει αντίγραφα ασφαλείας των βάσεων δεδομένων.</p>";
if (intval($max_execution_time) < 300) {
	$tool_content .= "<hr><p>ΠΡΟΣΟΧΗ! Για να ολοκληρωθεί η διαδικασία της αναβάθμισης βεβαιωθείτε ότι η μεταβλητή <em>max_execution_time</em> που ορίζεται στο <em>php.ini</em> είναι μεγαλύτερη από 300 (= 5 λεπτά). Αλλάξτε την τιμή της και ξαναξεκινήστε την διαδικασία αναβάθμισης<hr>";
	draw($tool_content, 0);
}
$tool_content .= "<p>Για να δείτε τις αλλαγές-βελτιώσεις της καινούριας έκδοσης του eClass κάντε
   κλικ <a href='CHANGES.txt'>εδώ</a>. Αν δεν το έχετε κάνει ήδη, παρακαλούμε
   διαβάσετε και ακολουθήστε τις <a href='upgrade.html'>οδηγίες αναβάθμισης</a>
   πριν προχωρήσετε στο παρακάτω βήμα.</p>
<p>Για να προχωρήσετε στην αναβάθμιση της βάσης δεδομένων, δώστε το όνομα
   χρήστη και το συνθηματικό του διαχειριστή της πλατφόρμας:</p>
<form method='post' action='upgrade.php'>
<table width='70%' align='center'>
<tr><td style='border: 1px solid #FFFFFF;'>
<fieldset><legend><b>Στοιχεία Εισόδου</b></legend>
<table cellpadding='1' cellspacing='2' width='99%'>
<tr><th style='text-align: left; background: #E6EDF5; color: #4F76A3; font-size: 90%'>$langUsername :</th>
    <td style=\"border: 1px solid #FFFFFF;\">&nbsp;<input class='auth_input_admin' style='width:150px; heigth:20px;' type='text' name='login' size='20'></td>
</tr>
<tr><th style='text-align: left; background: #E6EDF5; color: #4F76A3; font-size: 90%'>$langPass :</th>
    <td style=\"border: 1px solid #FFFFFF;\">&nbsp;<input class='auth_input_admin' type='password' style='width:150px; heigth:20px;' name='password' size='20'></td>
</tr>
<tr><td colspan='2' style=\"border: 1px solid #FFFFFF;\" align='center'>
    <input type='submit' name='submit_upgrade' value='Αναβάθμιση Βάσης Δεδομένων'></td>
</tr>
</table>
</fieldset>
</td></tr></table>";

if (isset($from_admin)) {
        $tool_content .= "<input type='hidden' name='from_admin' value='$from_admin'>";
}

$tool_content .= "</form></td></tr><tr><td style=\"border: 1px solid #FFFFFF;\" colspan=2>";

if (isset($from_admin)) {
        $tool_content .= "<p align=right><a href='../modules/admin/index.php' class=mainpage>Επιστροφή στη σελίδα διαχείρισης</a></p>";
} else {
        $tool_content .= "&nbsp;";
}
 

draw($tool_content, 0);
?>
