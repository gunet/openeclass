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

//$tool_content .= "<table cellpadding='3' cellspacing='0' border='0' width='100%'>";
//$tool_content .= "<tr valign='top'>";

// ------------------- Update table prof_request ------------------------------

$upd=db_query("INSERT INTO prof_request(profname,profsurname,profuname,profemail,proftmima,profcomm,status,date_open,comment,statut) VALUES('$name','$surname','$username','$usermail','$department','$userphone','1',NOW(),'$usercomment','5')");

//----------------------------- Email Message --------------------------
    $MailMessage = $mailbody1 . $mailbody2 . "$name $surname\n\n" .
			$mailbody3 . $mailbody4 . $mailbody5 . "$mailbody8\n\n" .
			"$langDepartment: $department\n$langComments: $usercomment\n" .
			"$langProfUname : $username\n$langProfEmail : $usermail\n" .
			"$contactphone : $userphone\n\n\n$logo\n\n";

	if (!send_mail($gunet, $emailhelpdesk, '', $emailhelpdesk, $mailsubject2, $MailMessage, $charset)) {
		$tool_content .= "
  <table width=\"99%\">
  <tbody>
  <tr>
    <td class=\"caution\" height='60'>
    <p>$MailErrorMessage&nbsp; <a href=\"mailto:$emailhelpdesk\" class=mainpage>$emailhelpdesk</a>.</p>
    </td>
  </tr>
  </tbody>
  </table>
  <br><br/>";

	}


    //  User Message
	$tool_content .= "
  <table width=\"99%\">
  <tbody>
  <tr>
    <td class=\"success\" height='60'>
    <p>$langDearUser!<br/><br/>$success</p>
    </td>
  </tr>
  </tbody>
  </table>
  <p>
    <br/><br/>$infoprof<br/><br/>				
	$click <a href=\"$urlServer\" class=mainpage>$langHere</a> $langBackPage
  </p>
  <br><br/>
	";
     

  $tool_content .= "</td></tr></table>";
  draw($tool_content, 0);
  exit();

} else {

$tool_content = "";
$nameTools = $langUserRequest;

if (isset($Add) and (empty($usercomment) or empty($name) or empty($surname) or empty($username) or empty($userphone) or empty($usermail))) {
	$tool_content .= "
  <table width=\"99%\">
  <tbody>
  <tr>
    <td class=\"caution\" height='60'>
    <p>$langFieldsMissing</p>
    <p><a href=\"javascript:history.go(-1)\">".$langAgain."</a></p>
    </td>
  </tr>
  </tbody>
  </table>
  <br><br/>
  ";
  
  draw($tool_content, 0, 'auth');
  exit;
      }

if (!isset($close_user_registration) or $close_user_registration == FALSE) {
			$tool_content .= "<div class='td_main'>$langForbidden</div></td></tr></table>";
			draw($tool_content, 0, 'auth');
			exit;
			}

$tool_content .= "
    <p>$langInfoStudReq</p>
    <table width=\"99%\" align='left' class='FormData'>
    <thead>
    <tr>
      <td>
      <form action='$_SERVER[PHP_SELF]' method='post'>
      <table width=\"100%\">
       <tbody>
       <tr>
         <th class='left' width='20%'>$langName</th>
         <td width='10%'><input type='text' name='name' value='".@$name."' class='FormData_InputText' size=\"33\"></td>
         <td><small>(*)</small></td>
       </tr>
       <tr>
         <th class='left'>$langSurname</th>
         <td><input type='text' name='surname' value='".@$surname."' class='FormData_InputText' size=\"33\"></td>
         <td><small>(*)</small></td>
       </tr>
       <tr>
         <th class='left'>$langphone</th>
         <td><input type='text' name='userphone' value='".@$userphone."' class='FormData_InputText' size=\"33\"></td>
         <td><small>(*)</small></td>
       </tr>
       <tr>
         <th class='left'>$langProfUname</th>
         <td><input type='text' name='username' size=\"33\" maxlength='20' value='".@$username."' class='FormData_InputText'></td>
         <td><small>(*)&nbsp;$langUserNotice</small></td>
       </tr>
       <tr>
         <th class='left'>$langProfEmail</th>
         <td><input type='text' name='usermail' value='".@$usermail."' class='FormData_InputText' size=\"33\"></td>
         <td><small>(*)</small></td>
       </tr>	
       <tr>
         <th class='left'>$langComments<br><small>$profreason</small></th>
         <td>
         <textarea name='usercomment' COLS='30' ROWS='4' WRAP='SOFT'  class='FormData_InputText'>".@$usercomment."</textarea>
         </td>
         <td><small>(*)</small></td>
       </tr>
       <tr>
         <th class='left'>$langDepartment&nbsp;</th>
         <td><select name='department'>";

    $deps=mysql_query("SELECT name FROM faculte order by name");
    while ($dep = mysql_fetch_array($deps)) {
           $tool_content .= "
         <option value='$dep[0]'>$dep[0]</option>\n";
    }

	 $tool_content .= "
         </select></td>
       </tr>				
       <tr>
         <th class='left'>&nbsp;</th>
         <td><input type='submit' class='ButtonSubmit' name='Add' value='$langSubmitNew'></td>
         <td><small><p align='right'>$langRequiredFields</p></small></td>
       </tr>
       </tbody>
       </table>
       </form>
       </td>
     </tr>
     </thead>
     </table>"; 
}   // end of else if

draw($tool_content, 0);
?>
