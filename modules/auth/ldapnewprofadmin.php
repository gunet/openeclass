<? 
/**=============================================================================
       	GUnet e-Class 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2007  Greek Universities Network - GUnet
        A full copyright notice can be read in "/info/copyright.txt".
        
        For a full list of contributors, see "credits.txt".  
     
        This program is a free software under the terms of the GNU 
        (General Public License) as published by the Free Software 
        Foundation. See the GNU License for more details. 
        The full license can be read in "license.txt".
     
       	Contact address: GUnet Asynchronous Teleteaching Group, 
        Network Operations Center, University of Athens, 
        Panepistimiopolis Ilissia, 15784, Athens, Greece
        eMail: eclassadmin@gunet.gr
==============================================================================*/

/**===========================================================================
	ldapsearch.php
	@authors list: Karatzidis Stratos <kstratos@uom.gr>
		       Vagelis Pitsioygas <vagpits@uom.gr>
==============================================================================        
  @Description: This script/file tries to authenticate the user, using
  his user/pass pair and the authentication method defined by the admin
  
==============================================================================
*/

$require_admin = TRUE;

include '../../include/baseTheme.php';
include '../../include/sendMail.inc.php';
require_once 'auth.inc.php';

$msg = "$langProfReg (".(get_auth_info($auth)).")";
$nameTools = $msg;
$navigation[] = array("url" => "../admin/index.php", "name" => $langAdmin);
$navigation[] = array("url" => "../admin/listreq.php", "name" => $langOpenProfessorRequests);
$tool_content = "";

// -----------------------------------------
// 				professor registration
// -----------------------------------------

if (isset($submit))  {
      $auth = $_POST['auth'];
      $pn = $_POST['pn'];
      $ps = $_POST['ps'];
      $pu = $_POST['pu'];
      $pe = $_POST['pe'];
			$department = $_POST['department'];
		
		// check if user name exists
    	$username_check=mysql_query("SELECT username FROM `$mysqlMainDb`.user WHERE username='".escapeSimple($pu)."'");
	    while ($myusername = mysql_fetch_array($username_check))
  	  {
    	  $user_exist=$myusername[0];
	    }
	  	if(isset($user_exist) and $pu == $user_exist) {
	  	   $tool_content .= "<p>$langUserFree</p><br><br><center><p><a href='../admin/listreq.php'>$langBackReq</a></p></center>";
				 draw($tool_content,0);
		     exit();
	    }

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
	
		$registered_at = time();
    $expires_at = time() + $durationAccount; 

		$sql=db_query("INSERT INTO user (user_id, nom, prenom, username, password, email, statut, department, inst_id, registered_at, expires_at)
       VALUES ('NULL', '$pn', '$ps', '$pu', '$password', '$pe','1','$department', '0', '$registered_at', '$expires_at')", $mysqlMainDb);
			
			// close request
      //  Update table prof_request ------------------------------
      $rid = intval($_POST['rid']);
      db_query("UPDATE prof_request set status = '2',date_closed = NOW() WHERE rid = '$rid'");
      
			$emailbody = "$langDestination $pu $ps\n" .
                                "$langYouAreReg $siteName $langSettings $pu\n" .
                                "$langPass: $password\n$langAddress $siteName: " .
                                "$urlServer\n$langProblem\n$langFormula" .
                                "$administratorName $administratorSurname" .
                                "$langManager $siteName \n$langTel $telephone \n" .
                                "$langEmail: $emailAdministrator";
		
    if (!send_mail($gunet, $emailhelpdesk, '', $emailhelpdesk, $mailsubject, $emailbody, $charset))  {
		      $tool_content .= "<table width=\"99%\"><tbody><tr>
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
			<p>$profsuccess</p><br><br>
			<center><p><a href='../admin/listreq.php'>$langBackReq</a></p></center>
      </td>
      </tr></tbody></table>";

} else {  // display the form
		$tool_content .= "<form action=\"$_SERVER[PHP_SELF]\" method=\"post\">
			<table width=\"99%\"><caption>$langNewProf</caption><tbody>
      <tr valign=\"top\" bgcolor=\"".$color2."\">
      <th style='text-align: left; background: #E6EDF5; color: #4F76A3; font-size: 90%' width=\"3%\" nowrap><b>".$langSurname."</b></th>
    	<td>$ps</td></tr>
      <input type=\"hidden\" name=\"ps\" value=\"$ps\">
      <tr bgcolor=\"".$color2."\">
      <th style='text-align: left; background: #E6EDF5; color: #4F76A3; font-size: 90%' width=\"3%\" nowrap><b>".$langName."</b></th>
      <td>$pn</td></tr>
      <input type=\"hidden\" name=\"pn\" value=\"$pn\">
      <tr bgcolor=\"".$color2."\">
      <th style='text-align: left; background: #E6EDF5; color: #4F76A3; font-size: 90%' width=\"3%\" nowrap><b>".$langUsername."</b></th>
      <td>$pu</td>
      <input type=\"hidden\" name=\"pu\" value=\"$pu\">
      </tr>
      <tr bgcolor=\"".$color2."\">
       <th style='text-align: left; background: #E6EDF5; color: #4F76A3; font-size: 90%' width=\"3%\" nowrap><b>".$langEmail."</b></th>
       <td>$pe</b></td>
      <input type=\"hidden\" name=\"pe\" value=\"$pe\" >
       </tr>
      <tr bgcolor=\"".$color2."\">
        <th style='text-align: left; background: #E6EDF5; color: #4F76A3; font-size: 90%'>".$langDepartment.":</th>
        <td><select name=\"department\">";
				$deps=mysql_query("SELECT name, id FROM faculte ORDER BY id");
				while ($dep = mysql_fetch_array($deps))
					  $tool_content .= "\n<option value=\"".$dep[1]."\">".$dep[0]."</option>";
        $tool_content .= "</select>
        </td>
        </tr>
        <tr><td>&nbsp;</td>
        <td><input type=\"submit\" name=\"submit\" value=\"".$langOk."\" >
        <input type=\"hidden\" name=\"auth\" value=\"$auth\" >
        </td>
        </tr>
        <input type='hidden' name='rid' value='".@$id."'>
        </tbody></table></form>";
 }
draw($tool_content,0);
?>
