<?
$langFiles = 'registration';
include('../include/init.php');

$nameTools = 'Αναβάθμιση';

begin_page();
?>
	<form method="post" action="upgrade.php">
	<p align="left">Για να δείτε τις αλλαγές-βελτιώσεις της καινούριας έκδοσης 
	του e-Class κάντε κλικ <a href="CHANGES.txt" target=_blank>εδώ</a>.</p>
	<p align="left">Για να προχωρήσετε στην αναβάθμιση της βάσης δεδομένων,
	δώστε το όνομα χρήστη και το συνθηματικό του διαχειριστή της πλατφόρμας:</p>
	<p align="left">
	<?= $langUsername ?>: <input type="text" name="login" size="20"><br><br>
	<?= $langPass ?>:   <input type="password" name="password" size="20"><br><br>
	<input type="submit" name="submit" value="Αναβάθμιση Βάσης Δεδομένων">
	</p>
<?
end_page();
?>
