<?PHP
//general_error.php

	$tool_content .=  "
	<table cellpadding='6' cellspacing='0' border='0' width='650' bgcolor='#E6E6E6'>
        <tr bgcolor='navy'>
        <td valign='top' align='center'>
        <font color='white' face='arial, helvetica'>Πλατφόρμα Ασύγχρονης Τηλεκπαίδευσης e-Class</font>
        </td></tr>
        <tr><td>&nbsp;</td></tr>
        <tr bgcolor='#E6E6E6'><td>
        <b>Η πλατφόρμα ασύγχρονης τηλεκπαίδευσης δεν λειτουργεί !</b>
        <p>Πιθανοί λόγοι:
        <ul><li>Υπάρχει πρόβλημα με την MySQL (επικοινωνήστε με το διαχειριστή του συστήματος).</li>
        <li>Υπάρχει πρόβλημα στις ρυθμίσεις του αρχείου <tt>config.php</tt></li></ul></p>
        </td>
        </tr>
        <tr bgcolor='#E6E6E6'>
        <td><p>Ένας πιθανός λόγος, επίσης, είναι ότι χρησιμοποιείτε την πλατφόρμα για πρώτη φορά.</p>
        Σε αυτή την περίπτωση κάντε κλίκ στον <a href=\"./install/\">Οδηγό Εγκατάστασης</a>
        για να ξεκινήσετε το πρόγραμμα εγκατάστασης.
        </td>
        </tr>
	</table>";
	exit();


?>