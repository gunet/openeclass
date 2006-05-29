<?
$langFiles = array('registration','gunet');
include ('../../include/init.php');

if (@$usercomment != "" AND $name != "" AND $surname != "" AND $username != "" AND $userphone != "" AND $usermail != "")  
{
	$nameTools = $reqregprof;
	$MailErrorMessage = $langMailErrorMessage;
	include('../../include/sendMail.inc.php');
	
	begin_page();

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

	if (!send_mail($gunet, $emailhelpdesk, '', $emailhelpdesk, $mailsubject, $MailMessage, $charset)) 
	{
		echo("<table border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\" width=\"$mainInterfaceWidth\">");
        	echo "<tr bgcolor=$color2><td>
                <font size=\"2\" face=\"arial, helvetica\">
		<br><br>$MailErrorMessage
		<a href=\"mailto:$emailhelpdesk\">$emailhelpdesk</a>.
	        </font>
                <br><br><br>
		</td></tr>
		</table>";
		exit();
	}

	//------------------------------------User Message ----------------------------------------
		echo("<table border=\"0\" align=\"center\" cellpadding=\"0\" cellspacing=\"0\" width=\"$mainInterfaceWidth\">");
		echo("<tr bgcolor=$color2><td>
				<font size=\"2\" face=\"arial, helvetica\">		       
	                        <br><br>$dearprof<br><br>$success<br><br>$infoprof<br><br>				
				$click <a href=\"$urlServer\">$here</a> $backpage
	                        </font>
	                        <br><br><br>
	                </td>
	        </tr>
		</table>");
	// --------------------------------------------------------------------------------------

	echo "<body bgcolor='white'>";

} 
else 
{
	$tool_content .= "<br />No data provided. Cannot proceed<br>";
}








/*


	$nameTools = $reqregprof;
	begin_page();

	?>
	<table border="0" align="center" cellpadding="0" cellspacing="0" width="<?= $mainInterfaceWidth?>">
		<tr>
		<td>
	<?
	if (isset($Add) and (empty($usercomment) or empty($name) or empty($surname) or empty($username) or empty($userphone) or empty($usermail))) {
	                echo "<table cellpadding='3' cellspacing='0' border='0' width='100%'>";
	                echo "<tr bgcolor=$color2><td><font size='2' face='arial, helvetica'>$langFieldsMissing</font></td></tr>";
	                echo "<tr bgcolor=$color2><td></td></tr>";
	                echo "<tr bgcolor=$color2><td>
	                <font size='2' face='arial, helvetica'>$langFillAgain <a href='$_SERVER[PHP_SELF]'>$langFillAgainLink</a>.</font><br><br><br></td></tr></table>";
	                exit;
	        }
	?>
	<form action="<?= $_SERVER['PHP_SELF'] ?>" method="post">
	<table cellpadding="3" cellspacing="0" border="0" width="100%">
	<tr bgcolor="<?= $color2;?>">
	<td>
	<font size="2" face="arial, helvetica">
	<?= $profname ?>
	</font>
	</td>
        <td>
        <input type="text" name="name" value="<?echo @$name ?>">
	<font size="1">&nbsp;(*)</font>
        </td>
        </tr>
	<tr valign="top" bgcolor="<?= $color2 ?>">
	<td>
	<font size="2" face="arial, helvetica">
	<?= $profsname?>
	</font>
	</td>
	<td>
	<input type="text" name="surname" value="<?echo @$surname?>">
	<font size="1">&nbsp;(*)</font>
	</td>
	</tr>
	<tr bgcolor="<?= $color2;?>">
	<td>
	<font size="2" face="arial, helvetica">
	<?= $profphone?>
	</font>
	</td>
	<td>
	<input type="text" name="userphone" value="<?echo @$userphone ?>">
	<font size="1">&nbsp;(*)</font>
	</td>
	</tr>
	<tr bgcolor="<?= $color2;?>">
	<td>
	<font size="2" face="arial, helvetica">
	<?= $profuname?>
	</font>
	</td>
	<td>
	<input type="text" name="username" size="20" maxlength="20" value="<?echo @$username ?>"><font size="1">&nbsp;&nbsp;(*)</font>
	</td>
	</tr>
	<tr bgcolor="<?= $color2;?>"><td>&nbsp;</td><td><font size="1"><?= $langUserNotice ?></font></td></tr>
	<tr bgcolor="<?= $color2;?>">
        <td>
        <font size="2" face="arial, helvetica">
        <?= $profemail ?>
        </font>
        </td>
        <td>
        <input type="text" name="usermail" value="<?echo @$usermail ?>">
	<font size="1">&nbsp;(*)</font>
        </td>
        </tr>	
	<tr bgcolor="<?= $color2;?>">
        <td>
        <font size="2" face="arial, helvetica">
        <?= $profcomment ?>
        </font><br><font size="1" face="arial, helvetica">
	<?= $profreason ?>
	</font>
       	</td>
        <td>
        <textarea name="usercomment" COLS="35" ROWS="4" WRAP="SOFT"><?echo @$usercomment; ?></textarea>
	<font size="1">&nbsp;(*)</font>
        </td>
        </tr>
	<tr bgcolor="<?= $color2;?>">
        <td>
        <font size="2" face="arial, helvetica">
        <?= $langDepartment;?>&nbsp;:
        </font>
        </td>
        <td>
        <select name="department">
<?
        $deps=mysql_query("SELECT name FROM faculte order by id");
        while ($dep = mysql_fetch_array($deps)) {
                  echo "<option value=\"$dep[0]\">$dep[0]</option>\n";
        }
?>
        </select>
        </td>
        </tr>				
	<tr bgcolor="<?= $color2;?>" >
        <td>&nbsp;</td>
        <td>
	<input type="submit" name="Add" value="<?= $langSend ?>">
        </td>
        </tr>
	</table>
	</form>
		</td>
		</tr>
	<tr><td  align='right'><font size="1"><?= $langRequiredFields ?></font></td></tr>
	</table>
	<?
}   // end of else if
?>
</body>
</html>
*/

draw($tool_content,1);

?>