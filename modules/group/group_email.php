<?php  

$require_current_course = TRUE;

$langFiles = 'group';
$require_help = TRUE;
$helpTopic = 'Group';
//include ('../../include/init.php');
include '../../include/baseTheme.php';
$nameTools = $langGroupMail;
$navigation[]= array ("url"=>"group.php", "name"=> $langGroupSpace, 
	"url"=>"group_space.php?userGroupId=$userGroupId", "name"=>$langGroupSpace);

//begin_page();

include('../include/sendMail.inc.php');

$tool_content = "";
//$local_style = " body,p,td {font-family: Arial, Helvetica, sans-serif; font-size: 10pt}
//		.select {border-color:blue;border-width : 3px;}
//	.box {  width: 200px} 
//";

$currentCourse=$dbname;

if ($is_adminOfCourse)  {

if (isset($submit)) {
	$sql=mysql_query("SELECT user FROM user_group WHERE team = '$userGroupId'");
	while ($userid = mysql_fetch_array($sql)) {
		mysql_select_db($mysqlMainDb);
		$m = mysql_fetch_array(mysql_query("SELECT DISTINCT email FROM user where user_id='$userid[0]'"));
		mysql_select_db($currentCourse);
		$prof = mysql_fetch_array(mysql_query("SELECT username, user_email FROM users WHERE user_id='1'"));
		$emailsubject = $intitule." - ".$subject;
		$emailbody = "$body_mail\n\n$prof[username]\n$langProfLesson\n";
		if (!send_mail($prof['username'], $prof['user_email'],
				'', $m[0], $emailsubject, $emailbody, $charset)) {
			$tool_content .= "<h4>$langMailError</h4>";
		}
	}
	$tool_content .= "<h4>$langEmailSuccess</h4>";
	$tool_content .= "&nbsp;";
	$tool_content .= "<p><a href=\"group.php\">$langBack</a></p>";
} else {

$tool_content .= <<<tCont
<h4>$langTypeMessage</h4>

<form action="group_email.php" method="post">
<table>
	<thead>
		<tr>
			<th>$langMailSubject</th>
			<td><input type="text" name="subject" value="$subject" size="69"></input></td>
		</tr>
	</thead>
</table>
<br>
<table>
	<thead>
		<tr>
			<th>$langMailBody</th>
		</tr>
		<tr>
			<td><textarea name="body_mail" rows="10" cols="60"></textarea></td>
		</tr>
	</thead>
</table>

<br>
<input type="submit" name="submit" value="$langSend"></input>
</form>


tCont;
	}

 }	// if prof
 
 draw($tool_content, 2, 'group');
?>
