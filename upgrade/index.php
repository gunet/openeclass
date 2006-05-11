<?
$langFiles = 'registration';
include('../include/init.php');

$nameTools = 'Αναβάθμιση';

begin_page();
?>
	<table cellpadding="8" cellspacing="0" border="0" width="100%">
	<form method="post" action="upgrade.php">
	<tr><td colspan="2" bgcolor=<?= $color2 ?>>
	Για να δείτε τις αλλαγές-βελτιώσεις της καινούριας έκδοσης του e-Class κάντε κλικ <a href="CHANGES.txt" target=_blank>εδώ</a>.</td></tr>
	<tr><td colspan="2" bgcolor=<?= $color2 ?>>Για να προχωρήσετε στην αναβάθμιση της βάσης δεδομένων,
	δώστε το όνομα χρήστη και το συνθηματικό του διαχειριστή της πλατφόρμας:</td></tr>
	<tr><td bgcolor=<?= $color2 ?>><?= $langUsername ?>:</td><td bgcolor=<?= $color2 ?>>
	<input type="text" name="login" size="20"></td></tr>
	<tr><td bgcolor=<?= $color2 ?>><?= $langPass ?>:</td>
	<td bgcolor=<?= $color2 ?>><input type="password" name="password" size="20"></td></tr>
	<tr><td align="center" colspan="2" bgcolor=<?= $color2 ?>><input type="submit" name="submit" value="Αναβάθμιση Βάσης Δεδομένων"></td></tr>
	</table>
<?
end_page();
?>
