<?php

//include '/eclass20/include/excel/DB.php';
include 'DB.php';

echo DB_OK;

$db_type = 'mysql';
$db_host = 'localhost';
$db_user = 'eclass';
$db_pass = 'eclass#1@1';
$db_name = 'eclass';

// Connect to the database
$dbh = DB::connect("$db_type://$db_user:$db_pass@$db_host/$db_name");

// Send a SELECT query to the database
$sth = $dbh->query('SELECT * FROM auth');
echo "<br><br>";
// Check if any rows were returned
if ($sth->numRows()) {
//     print "<table>"; 
//          print "<tr><th>Ice Cream Flavor</th><th>Price per Serving</th><th>Calories per Serving</th></tr>";
	       // Retrieve each row 
	            while ($row = $sth->fetchRow()) {
		               // And print out the elements in the row
			                  echo "$row[0]---$row[1]---$row[2]---$row[3]<br>";
					       }
					            //print "</table>";
						    } else {
						         echo "No results";
							 } 


?>
