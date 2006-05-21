<?
$langFiles = array('registration','gunet');
//include ('../../include/init.php');
include '../../include/baseTheme.php';

$tool_content .= "";
if (@$usercomment != "" AND $name != "" AND $surname != "" AND $username != ""
AND $userphone != "" AND $usermail != "")  {

	$nameTools = $reqregprof;
	$MailErrorMessage = $langMailErrorMessage;
	include('../../include/sendMail.inc.php');

	//	begin_page();

	// ------------------- Update table prof_request ------------------------------
	mysql_select_db($mysqlMainDb,$db);
	$upd=mysql_query("INSERT INTO prof_request(profname,profsurname,profuname,profemail,proftmima,profcomm,status,date_open,comment)
	VALUES('$name','$surname','$username','$usermail','$department','$userphone','1',NOW(),'$usercomment')");

	//----------------------------- Email Message --------------------------
	$MailMessage = $mailbody1 . $mailbody2 . "$name $surname\n\n" .
	$mailbody3 . $mailbody4 . $mailbody5 . "$mailbody6\n\n" .
	"$langDepartment: $department\n$profcomment: $usercomment\n" .
	"$profuname : $username\n$profemail : $usermail\n" .
	"$contactphone : $userphone\n\n\n$logo\n\n";

	if (!send_mail($gunet, $emailhelpdesk, '', $emailhelpdesk, $mailsubject, $MailMessage, $charset)) {
		$tool_content .= "<table width=\"99%\">";
		$tool_content .=  "<tbody><tr><td class=\"caution\">
                
		$MailErrorMessage
		<a href=\"mailto:$emailhelpdesk\">$emailhelpdesk</a>.
	       
                
		</td></tr></tbody>
		</table><br>";
		//		exit();
	}

	//------------------------------------User Message ----------------------------------------
	$tool_content .= "<table  width=\"99%\"><tbody";
	$tool_content .= "<tr class=\"odd\"><td>
				       
                        $dearprof<br><br>$success<br><br>$infoprof<br><br>				
			$click <a href=\"$urlServer\">$here</a> $backpage
                        
                       
                </td>
        </tr>
        </tbody>
	</table>";
	// --------------------------------------------------------------------------------------

	//	$tool_content .=  "<body bgcolor='white'>";

} else {

	$nameTools = $reqregprof;
//	begin_page();

//$tool_content .= "
//<table  width=\"99%\">
//	<tr>
//	<td>";

if (isset($Add) and (empty($usercomment) or empty($name) or empty($surname) or empty($username) or empty($userphone) or empty($usermail))) {
	$tool_content .=  "<table width='99%'><tbody>";
	$tool_content .=  "<tr><td>$langFieldsMissing</td></tr>";
	$tool_content .= "<tr><td></td></tr>";
	$tool_content .= "<tr><td>
               $langFillAgain <a href='$_SERVER[PHP_SELF]'>$langFillAgainLink</a>.<br><br><br></td></tr></tbody></table>";
	exit;// call draw?
}
$frmAction = $_SERVER["PHP_SELF"];
$tool_content .= "
	<form action=\"$frmAction\" method=\"post\">
	<table width=\"99%\">
	<thead>
	<tr >
	<th width=\"200\">
	
	$profname
	
	</th>
        <td>
        <input type=\"text\" name=\"name\" value=\"$name\">
	(*)
        </td>
        </tr>
	<tr >
	<th width=\"200\">
	
	$profsname
	
	</th>
	<td>
	<input type=\"text\" name=\"surname\" value=\"$surname\">
	(*)
	</td>
	</tr>
	<tr>
	<th  width=\"200\">
	
	$profphone
	
	</th>
	<td>
	<input type=\"text\" name=\"userphone\" value=\"$userphone\">
	(*)
	</td>
	</tr>
	<tr>
	<th  width=\"200\">
	
	$profuname
	</th>
	<td>
	<input type=\"text\" name=\"username\" size=\"20\" maxlength=\"20\" value=\"$username\">(*)$langUserNotice
	</td>
	</tr>
	
	<tr>
        <th  width=\"200\">
       
        profemail
       
        </th>
        <td>
        <input type=\"text\" name=\"usermail\" value=\"$usermail\">	(*)
        </td>
        </tr>	
	<tr >
        <th width=\"200\">
        
      $profcomment $profreason
	
       	</th>
        <td>
        <textarea name=\"usercomment\" COLS=\"35\" ROWS=\"4\" WRAP=\"SOFT\">$usercomment</textarea>(*)
        </td>
        </tr>
	<tr>
        <th width=\"200\">
        
        $langDepartment
        
        </th>
        <td>
        <select name=\"department\">
";
$deps=mysql_query("SELECT name FROM faculte order by id");
while ($dep = mysql_fetch_array($deps)) {
	$tool_content .=  "<option value=\"$dep[0]\">$dep[0]</option>\n";
}
$tool_content .= "
        </select>
        </td>
        </tr></thead>				
	</table><br>
	<input type=\"submit\" name=\"Add\" value=\"$langSend\">
       
	
</form>
	<p>$langRequiredFields</p>
";
}// end of else if

draw($tool_content, 0);
?>
<!--</body>
</html>
-->