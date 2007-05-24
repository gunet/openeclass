<?
/*
      +----------------------------------------------------------------------+
      | GUnet eClass 1.7                                                    |
      | Asychronous Teleteaching Platform                                    |
      +----------------------------------------------------------------------+
      | Copyright (c) 2003-2007  GUnet                                       |
      +----------------------------------------------------------------------+
      |                                                                      |
      | GUnet eClass 1.7 is an open platform distributed in the hope that   |
      | it will be useful (without any warranty), under the terms of the     |
      | GNU License (General Public License) as published by the Free        |
      | Software Foundation. The full license can be read in "license.txt".  |
      |                                                                      |
      | Main Developers Group: Costas Tsibanis <k.tsibanis@noc.uoa.gr>       |
      |                        Yannis Exidaridis <jexi@noc.uoa.gr>           |
      |                        Alexandros Diamantidis <adia@noc.uoa.gr>      |
      |                        Tilemachos Raptis <traptis@noc.uoa.gr>        |
      |                                                                      |
      | For a full list of contributors, see "credits.txt".                  |
      |                                                                      |
      +----------------------------------------------------------------------+
      | Contact address: Asynchronous Teleteaching Group (eclass@gunet.gr),  |
      |                  Network Operations Center, University of Athens,    |
      |                  Panepistimiopolis Ilissia, 15784, Athens, Greece    |
      +----------------------------------------------------------------------+
*/
$langFiles = array('registration','gunet');
include '../../include/baseTheme.php';
include '../../include/sendMail.inc.php';

if (@$usercomment != "" AND $name != "" AND $surname != "" AND $username != "" 
		AND $userphone != "" AND $usermail != "")  {

$nameTools = $langUserRequest;
$tool_content = "";

$MailErrorMessage = $langMailErrorMessage;

$tool_content .= "<table cellpadding='3' cellspacing='0' border='0' width='100%'>";
$tool_content .= "<tr valign='top'>";

// ------------------- Update table prof_request ------------------------------

$upd=db_query("INSERT INTO prof_request(profname,profsurname,profuname,profemail,proftmima,profcomm,status,date_open,comment,statut) VALUES('$name','$surname','$username','$usermail','$department','$userphone','1',NOW(),'$usercomment','5')");

//----------------------------- Email Message --------------------------
    $MailMessage = $mailbody1 . $mailbody2 . "$name $surname\n\n" .
			$mailbody3 . $mailbody4 . $mailbody5 . "$mailbody8\n\n" .
			"$langDepartment: $department\n$profcomment: $usercomment\n" .
			"$profuname : $username\n$profemail : $usermail\n" .
			"$contactphone : $userphone\n\n\n$logo\n\n";

	if (!send_mail($gunet, $emailhelpdesk, '', $emailhelpdesk, $mailsubject2, $MailMessage, $charset)) {
		$tool_content .= "<table border='0' align='center' cellpadding='0' cellspacing='0'>";
    $tool_content .= "<tr><td><div class='labeltext'>$MailErrorMessage<div class=alert1><a href=\"mailto:$emailhelpdesk\" class=mainpage>$emailhelpdesk</a>.</div></div><br>";
	}

$tool_content .= "<td width='75%' class=td_main>";
$tool_content .= "<div class='Subsystem_Label'>$nameTools</div>\n";

//  User Message 
	$tool_content .= "<table border='0' align='center' height=300 cellpadding='0' cellspacing='0'>";
	$tool_content .= "<tr><td valign=top><div class='td_main'>		       
                <br>$langDearUser!<br><br>$success<br><br>$infoprof<br><br>				
	        $click <a href=\"$urlServer\" class=mainpage>$here</a> $backpage
                </div><br></td>
         </tr></table>";
				$tool_content .= "</td></tr></table>";
				draw($tool_content, 0, 'auth');
    	  exit();

} else {

$tool_content = "";
$nameTools = $langUserRequest;

if (isset($Add) and (empty($usercomment) or empty($name) 
		or empty($surname) or empty($username) or empty($userphone) or empty($usermail))) {
				$tool_content .= "<br><div align='center' class='td_main'>$langFieldsMissing<br>
			  <a href='$_SERVER[PHP_SELF]' class=mainpage>$langTryAgain</a></div>";
	      $tool_content .= "</td></tr></table>";
				draw($tool_content, 0, 'auth');
    	  exit;
      }

if (!isset($close_user_registration) or $close_user_registration == FALSE) {
			$tool_content .= "<div class='td_main'>$langForbidden</div></td></tr></table>";
			draw($tool_content, 0, 'auth');
			exit;
			}

$tool_content .= "<table width='95%' align='center'><tbody><thead><tr><td>
           <form action='$_SERVER[PHP_SELF]' method='post'>
           <table border='0' align='center' cellpadding='3' cellspacing='0' class=td_main>
	   			<tr><td colspan='2'><small>$langInfoStudReq</small></td></tr>
			  	 <tr>
	  		  <th class='labeltext'>$langName</th>
           <td><input type='text' name='name' value='".@$name."' class='auth_input'><small>&nbsp;(*)</small></td>
           </tr>
	   <tr valign='top'>
	    <th class='labeltext'>$langSurname</th>
	    <td><input type='text' name='surname' value='".@$surname."' class='auth_input'>
			<small>&nbsp;(*)</small></td>
		  </tr>
		  <tr>
	    <th class='labeltext'>$langphone</th>
	    <td><input type='text' name='userphone' value='".@$userphone."' class='auth_input'>
			<small>&nbsp;(*)</small></td>
		  </tr>
		  <tr>
	    <th class='labeltext'>$profuname <small>$langUserNotice</small></th>
	    <td><input type='text' name='username' size='20' maxlength='20' value='".@$username."' class='auth_input'>
			<small>&nbsp;(*)</small></td>
		  </tr>
	  <tr><th class='labeltext'>$profemail</th>
     <td><input type='text' name='usermail' value='".@$usermail."' class='auth_input'>
			<small>&nbsp;(*)</small></td>
      </tr>	
	  <tr>
  	 <th class='labeltext'>$langComments<br><span class='explanationtext'>$profreason</th>
     <td><textarea name='usercomment' COLS='30' ROWS='4' WRAP='SOFT' class='auth_input'>".@$usercomment."</textarea>
	    <small>&nbsp;(*)</small></td>
	  </tr>
	  <tr>
   <th class='labeltext'>$langDepartment&nbsp;:</th>
   <td><select name='department' class='auth_input'>";

    $deps=mysql_query("SELECT name FROM faculte order by name");
    while ($dep = mysql_fetch_array($deps)) {
           $tool_content .= "\n<option value='$dep[0]'>$dep[0]</option>\n";
    }

	 $tool_content .= "</select></td>
	  </tr>				
	  <tr>
	    <td>&nbsp;</td>
	    <td><input type='submit' class='ButtonSubmit' name='Add' value='$langSend'>&nbsp;&nbsp;&nbsp;&nbsp;
			<small>$langRequiredFields</small></td>
	  </tr></thead></tbody>
	  </table>
     </form>
     </td></tr></table>"; 
}   // end of else if

draw($tool_content, 0, 'auth');
?>
