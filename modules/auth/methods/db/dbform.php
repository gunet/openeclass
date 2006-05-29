<?php
$dbdata = $auth_data;

if(!empty($dbdata))
{
    $dbsettings = $dbdata['auth_settings'];
    $dbinstructions = $dbdata['auth_instructions'];
    // $dbhost = str_replace("imaphost=","",$imapsettings);
    $edb = explode("|",$dbsettings);
    //dbhost
    $dbhost = str_replace("dbhost=","",$edb[0]);
    //dbname
    $dbname = str_replace("dbname=","",$edb[1]);
    //dbuser
    $dbuser = str_replace("dbuser=","",$edb[2]);
    // dbpass
    $dbpass = str_replace("dbpass=","",$edb[3]);
    //dbtable
    $dbtable = str_replace("dbtable=","",$edb[4]);
    //dbfielduser
    $dbfielduser = str_replace("dbfielduser=","",$edb[5]);
    //dbfieldpass
    $dbfieldpass = str_replace("dbfieldpass=","",$edb[6]);
}
else
{
    $dbsettings = $dbdata['auth_settings'];
    $dbinstructions = $dbdata['auth_instructions'];
    $dbhost = $dbsettings;
}



$tool_content .= "<table border=\"0\">
<tr valign=\"top\">
    <td align=\"right\">dbhost:</td>
    <td>
        <input name=\"dbhost\" type=\"text\" size=\"30\" value=\"$dbhost\" />
    </td>
    <td>
	auth_dbhost
    </td>
</tr>";
/*
<tr valign=\"top\">
    <td align=\"right\">auth_dbtype:</td>
    <td>";
    $dbtypes_settings = array("mysql","oracle","mssql","odbc");
    $dbtypes = array();
    foreach($dbtypes_settings as $v)
    {
	$dbtypes[$v] = $v;
    }
    $tool_content .= selection3($dbtypes,"dbtype","oracle");
$tool_content .= "
    </td>
    <td>
    auth_dbtype
    </td>
</tr>
*/
$tool_content .= "<tr valign=\"top\">
    <td align=\"right\">auth_dbname:</td>
    <td>
    <input name=\"dbname\" type=\"text\" size=\"30\" value=\"$dbname\" />
    </td>
    <td>
	auth_dbname
    </td>
</tr>

<tr valign=\"top\">
    <td align=\"right\">auth_dbuser:</td>
    <td>
    <input name=\"dbuser\" type=\"text\" size=\"30\" value=\"$dbuser\" />
    </td>
    <td>
	dbuser
    </td>
</tr>

<tr valign=\"top\">
    <td align=\"right\">auth_dbpass:</td>
    <td>
    <input name=\"dbpass\" type=\"text\" size=\"30\" value=\"$dbpass\" />
    </td>
    <td>
	auth_dbpass
    </td>
</tr>

<tr valign=\"top\">
    <td align=\"right\">auth_dbtable:</td>
    <td>
    <input name=\"dbtable\" type=\"text\" size=\"30\" value=\"$dbtable\" />
    </td>
    <td>
	auth_dbtable
    </td>
</tr>

<tr valign=\"top\">
    <td align=\"right\">auth_dbfielduser:</td>
    <td>
    <input name=\"dbfielduser\" type=\"text\" size=\"30\" value=\"$dbfielduser\" />
    </td>
    <td>
	auth_dbfielduser
    </td>
</tr>

<tr valign=\"top\">
    <td align=\"right\">auth_dbfieldpass:</td>
    <td>
    <input name=\"dbfieldpass\" type=\"text\" size=\"30\" value=\"$dbfieldpass\" />
    </td>
    <td>
	auth_dbfieldpass
    </td>
</tr>

<tr valign=\"top\">
    <td align=\"right\">instructions:</td>
    <td>
	<textarea name=\"dbinstructions\" cols=\"30\" rows=\"10\" wrap=\"virtual\">".$dbinstructions."</textarea> 
    </td>
    <td> 
	Here you can provide instructions for your users, so they know which username and password they should be using. The text you enter here will appear on the login page. 
	<br />If you leave this blank then no instructions will be printed.
    </td>
</tr>
</table>";



/*
include 'DB.php';

// Connect to the database
$dbh = DB::connect("$db_type://$db_user:$db_pass@$db_host/$db_name");

// Send a SELECT query to the database
$sth = $dbh->query('SELECT * FROM auth');
echo "<br><br>";
// Check if any rows were returned
if ($sth->numRows()) 
{
    print "<table>"; 
    print "<tr><th>Ice Cream Flavor</th><th>Price per Serving</th><th>Calories per Serving</th></tr>";
    // Retrieve each row 
    while ($row = $sth->fetchRow()) 
    {
	// And print out the elements in the row
	echo "$row[0]---$row[1]---$row[2]---$row[3]<br>";
    }
    //print "</table>";
} 
else 
{
    echo "No results";
} 


function auth_user_login ($username, $password) 
{
    // Returns true if the username and password work and false if they don't
    $sql = "SELECT user_id FROM user WHERE username='".$username."' AND password='".$password."'";
    $result = db_query($sql);
    if(mysql_num_rows($result)==1)
    {
        return true;
    }
    else
    {
	return false;
    }
}
*/

?>