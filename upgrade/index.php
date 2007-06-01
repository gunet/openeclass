<?
$langFiles = 'registration';
$path2add=2;
include '../include/baseTheme.php';

$nameTools = 'Αναβάθμιση των βάσεων δεδομένων του eClass';

// Initialise $tool_content
$tool_content = "";

// Main body
$tool_content .= "<table cellpadding='8' cellspacing='0' width='90%' align='center'>
  <tr>
  <td colspan='2' align='justify' style='border: 1px solid #FFFFFF;'>
  <center><b><u>ΠΡΟΣΟΧΗ</u>!</b></center><br>
  Το πρόγραμμα αναβάθμισης θα τροποποιήσει το αρχείο ρυθμίσεων <em>config.php</em>. 
	Επομένως πριν προχωρήσετε στην αναβάθμιση βεβαιωθείτε ότι ο web server μπορεί να έχει πρόσβαση στο <em>config.php</em>.
  </td></tr>
   <tr><td colspan='2' style='border: 1px solid #FFFFFF;' class='labeltext'>
   Επίσης για λόγους ασφαλείας βεβαιωθείτε ότι έχετε κρατήσει αντίγραφα ασφαλείας των βάσεων δεδομένων.</td></tr>
  <tr><td colspan='2' align='justify' style='border: 1px solid #FFFFFF;'>
    Για να δείτε τις αλλαγές-βελτιώσεις της καινούριας έκδοσης του eClass κάντε κλικ <a href='CHANGES.txt'>εδώ</a>.
  </td></tr>
  <tr><td colspan='2' align='justify' style='border: 1px solid #FFFFFF;'>Για να προχωρήσετε στην αναβάθμιση της βάσης δεδομένων, δώστε το όνομα χρήστη και το συνθηματικό του διαχειριστή της πλατφόρμας:</td></tr>
   <tr><td style='border: 1px solid #FFFFFF;'>
    <form method='post' action='upgrade.php'>
     <table width=70% align=center>
    <tr><td style=\"border: 1px solid #FFFFFF;\">
     <FIELDSET>
     <LEGEND><b>Στοιχεία Εισόδου</b></LEGEND>
			<table cellpadding='1' cellspacing='2' width='99%'>
      <tr>
     <th style='text-align: left; background: #E6EDF5; color: #4F76A3; font-size: 90%'> $langUsername :</th>
   <td style=\"border: 1px solid #FFFFFF;\">&nbsp;<input class='auth_input_admin' style='width:150px; heigth:20px;' type='text' name='login' size='20'></td>
    </tr>
    <tr>
    <th style='text-align: left; background: #E6EDF5; color: #4F76A3; font-size: 90%'>$langPass :</th>
    <td style=\"border: 1px solid #FFFFFF;\">&nbsp;<input class='auth_input_admin' type='password' style='width:150px; heigth:20px;' name='password' size='20'></td>
    </tr>
     <tr>
     <td colspan='2' style=\"border: 1px solid #FFFFFF;\" align='center'>
					<input type='submit' name='submit_upgrade' value='Αναβάθμιση Βάσης Δεδομένων'></td>
     </tr>
     </table>
    </FIELDSET>
     </td></tr></table>";

    if (isset($from_admin)) {
            $tool_content .= "<input type='hidden' name='from_admin' value='$from_admin'>";
     }
                
     $tool_content .= "</form></td></tr><tr><td style=\"border: 1px solid #FFFFFF;\" colspan=2>";
    
     if (isset($from_admin)) {
        $tool_content .= "<p align=right><a href='../modules/admin/index.php' class=mainpage>Επιστροφή στη σελίδα διαχείρισης</a></p>";
          }
     else 
					$tool_content .= "&nbsp;";
 
     $tool_content .= "</td></tr></table>";

draw($tool_content,0);
?>
