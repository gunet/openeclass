<?
include '../../include/baseTheme.php';
include '../../include/sendMail.inc.php';
require_once 'auth.inc.php';
$nameTools = $langReqRegProf;
$navigation[] = array("url"=>"registration.php", "name"=> $langAuthReg);

// Initialise $tool_content
$tool_content = "";

$auth = get_auth_id();

// display form
if (!isset($submit)) {

@$tool_content .= "	
<table width=\"99%\" class='FormData' align='left'>
<thead>
<tr>
<td>
<form action=\"$_SERVER[PHP_SELF]\" method=\"post\">

  <table width=\"100%\" align='left'>
  <tbody>
  <tr>
   <th class='left' width='20%'>$langSurname</th>
   <td width='10%'><input size='35' type=\"text\" name=\"nom_form\" value=\"$nom_form\" class='FormData_InputText'></td>
	<td>(*)</td>
  </tr>
  <tr>
    <th class='left'>$langName</th>
    <td><input size='35' type=\"text\" name=\"prenom_form\" value=\"$prenom_form\" class='FormData_InputText'></td>
	<td>(*)</td>
  </tr>
	<tr>
    <th class='left'>$langPhone</th>
    <td><input size='35' type=\"text\" name=\"userphone\" value=\"$userphone\" class='FormData_InputText'></td>
  <td>(*)</td>
  </tr>
  <tr>
    <th class='left'>$langUsername</th>
    <td><input size='35' type=\"text\" name=\"uname\" value=\"$uname\" class='FormData_InputText'></td>
	<td>(*) (**)</td>
  </tr>
  <tr>
    <th class='left'>$langPass</th>
    <td><input size='35' type=\"text\" name=\"password\" value=\"".create_pass(5)."\" class='FormData_InputText'></td>
	<td>(**)</td>
  </tr>
  <tr>
    <th class='left'>$langEmail</th>
    <td><input size='35' type=\"text\" name=\"email_form\" value=\"$email_form\" class='FormData_InputText'></td>
	<td>(*)</td>
  </tr>
  <tr>
    <th class='left'>$langComments</td>
    <td><textarea name=\"usercomment\" COLS=\"32\" ROWS=\"4\" WRAP=\"SOFT\" class='FormData_InputText'>".$usercomment."</textarea></td>
	<td>(*) $profreason</td>
  </tr>
  <tr>
    <th class='left'>".$langDepartment."</th>
    <td colspan='2'><select name=\"department\">";
        $deps=mysql_query("SELECT name FROM faculte order by id");
        while ($dep = mysql_fetch_array($deps)) 
        {
        	$tool_content .= "<option value=\"$dep[0]\">$dep[0]</option>\n";
        }
        $tool_content .= "</select>
    </td>
  </tr>	
  <tr>
    <th>&nbsp;</th>
    <td>
    <input type=\"submit\" name=\"submit\" value=\"".$langSubmitNew."\" >
    <input type=\"hidden\" name=\"auth\" value=\"1\" ></td>
		<td>
    <p align='right'>$langRequiredFields<br>$langStar2 . $langCharactersNotAllowed</p>
    </td>
  </tr>
  </tbody></table></form>
  </td></tr></table>
	<br>";

} else {

// registration
$registration_errors = array();

if ((strstr($password, "'")) or (strstr($password, '"')) or (strstr($password, '\\'))
    or (strstr($uname, "'")) or (strstr($uname, '"')) or (strstr($uname, '\\'))) {
					$registration_errors[] = $langCharactersNotAllowed;
		  }

    // check if there are empty fields
    if (empty($nom_form) or empty($prenom_form) or empty($userphone) or empty($password) 
				or empty($usercomment) or empty($uname) or (empty($email_form))) {
      $registration_errors[]=$langEmptyFields;
	   } 

if (count($registration_errors) == 0) {    // registration is ok
     $uname = escapeSimple($uname);  // escape the characters: simple and double quote
      // ------------------- Update table prof_request ------------------------------
      $username = $uname;
      $auth = $_POST['auth'];
      if($auth!=1)
      {
        switch($auth)
        {
          case '2': $password = "pop3";
            break;
          case '3': $password = "imap";
            break;
          case '4': $password = "ldap";
            break;
          case '5': $password = "db";
            break;
          default:  $password = "";
            break;
        }
      }

      $usermail = $email_form;
      $surname = $nom_form;
      $name = $prenom_form;

		mysql_select_db($mysqlMainDb,$db);
      $sql = "INSERT INTO prof_request(profname,profsurname,profuname,profpassword,
      profemail,proftmima,profcomm,status,date_open,comment) VALUES(
      '$name','$surname','$username','$password','$usermail','$department','$userphone','1',NOW(),'$usercomment')";
      $upd=mysql_query($sql,$db);
      //----------------------------- Email Message --------------------------
        $MailMessage = $mailbody1 . $mailbody2 . "$name $surname\n\n" . $mailbody3
        . $mailbody4 . $mailbody5 . "$mailbody6\n\n" . "$langDepartment: $department\n$langComments: $usercomment\n"
        . "$langProfUname : $username\n$langProfEmail : $usermail\n" . "$contactphone : $userphone\n\n\n$logo\n\n";
    if (!send_mail($gunet, $emailhelpdesk, '', $emailhelpdesk, $mailsubject, $MailMessage, $charset))
      {
        $tool_content .= "
	  	  <table width=\"99%\">
  	  	<tbody>
		    <tr>
    	  <td class=\"caution\" height='60'>
      	<p>$langMailErrorMessage &nbsp; <a href=\"mailto:$emailhelpdesk\">$emailhelpdesk</a></p>
		    </td>
    		</tr></tbody></table>";
        draw($tool_content,0);
        exit();
      }

      //------------------------------------User Message ----------------------------------------
    $tool_content .= "<table width=\"99%\"><tbody>
      <tr>
      <td class=\"well-done\" height='60'>
      <p>$langDearProf</p><p>$success</p><p>$infoprof</p>
      <p><a href=\"$urlServer\">$langBack</a></p>
  	 	</td>
	    </tr></tbody></table>";
    }	
	
	else	{  // errors exist - registration failed
            $tool_content .= "<table width='99%'><tbody><tr>" .
                              "<td class='caution' height='60'>";
                foreach ($registration_errors as $error) {
                        $tool_content .= "<p>$error</p>";
                }
	       $tool_content .= "<p><a href='$_SERVER[PHP_SELF]?prenom_form=$_POST[prenom_form]&nom_form=$_POST[nom_form]&userphone=$_POST[userphone]&uname=$_POST[uname]&email_form=$_POST[email_form]&usercomment=$_POST[usercomment]'>$langAgain</a></p>" .
                                 "</td></tr></tbody></table><br /><br />";
	}

} // end of submit

draw($tool_content,0);
?>
