<?php
/**=============================================================================
GUnet e-Class 2.0
E-learning and Course Management Program
================================================================================
Copyright(c) 2003-2006  Greek Universities Network - GUnet
A full copyright notice can be read in "/info/copyright.txt".

Authors:    Costas Tsibanis <k.tsibanis@noc.uoa.gr>
Yannis Exidaridis <jexi@noc.uoa.gr>
Alexandros Diamantidis <adia@noc.uoa.gr>

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
newuser_info.php
* @version $Id$

@authors list: Karatzidis Stratos <kstratos@uom.gr>
Vagelis Pitsioygas <vagpits@uom.gr>
==============================================================================
@Description: Display all the available auth methods for user registration

Purpose: TDisplay all the available auth methods for user registration

==============================================================================
*/

$langFiles = array('registration','index','authmethods');
include '../../include/baseTheme.php';
include 'auth.inc.php';

//mysql_select_db($dbname);

$nameTools = $langReg;

$tool_content = "";
$tool_content .= "
  <table width=50% cellpading=0 cellspacing=0>
  <tr>
    <td>
    <FIELDSET>
	  <LEGEND>$langUserAccount
	  ";
	  
      //$tool_content .= "<a href=\"newuser_info.php\">".$langNewUser."</a>";
	  $auth = get_auth_active_methods();
      $e = 1;

      // check for close user registration 
      if (isset($close_user_registration) and $close_user_registration == TRUE)
	  {
          $newuser = "formuser.php";
		  $tool_content .= "$langUserAccountInfo1";
      } else {
          $newuser = "newuser.php";
		  $tool_content .= "$langUserAccountInfo2";
	  }

$tool_content .= "
      </LEGEND>
	  <br>
	  
      <p><img src='../../images/arrow_blue.gif'>&nbsp;&nbsp;
	  <!-- <a href=\"$newuser\">$langAuthReg</a> -->
	  <a href=\"$newuser\">$langNewAccount</a>
	  </p>
      ";

if(!empty($auth))
{

	if ($auth[1] > 1) {
       $tool_content .= "<br><p><span style=\"border-bottom: 1px dotted silver;\">$langUserAccountInfo3&nbsp;</span>:</p>";
    }
	
	foreach($auth as $k=>$v)
	{
		if($v!=1)
		{
			$tool_content .= "
			<p><img src='../../images/arrow_blue.gif'>&nbsp;&nbsp;
			$langNewAccountΑctivation&nbsp;
			(<a href=\"ldapnewuser.php?auth=".$v."\">".get_auth_info($v)."</a>)</p>
		";
		}
		else
		{
			continue;
		}
	}
}

$tool_content .= "
    
    <br>
    </FIELDSET>
	</td>
  </tr>
  </table>

  <br><br>
  <table width=50% cellpading=0 cellspacing=0>
  <tr>
    <td>
    <FIELDSET>
	  <LEGEND>".$langProfAccount." ".$langUserAccountInfo1."</LEGEND>
	  <br>
	  ";
	  
	  if(!empty($auth))
{
	$tool_content .= "
	<p><img src='../../images/arrow_blue.gif'>&nbsp;&nbsp;
	<a href=\"newprof.php\">$langNewAccount</a>
	</p>
";
	if ($auth[1] > 1) {
       $tool_content .= "<br><p><span style=\"border-bottom: 1px dotted silver;\">$langUserAccountInfo3</span>&nbsp;:</p>";
    }
	foreach($auth as $k=>$v)
	{
		if($v==1)		// bypass the eclass auth method, as it has already been displayed
		{
			continue;
		}
		else
		{
			$auth_method_settings = get_auth_settings($v);
			$tool_content .= "
			<p><img src='../../images/arrow_blue.gif'>&nbsp;&nbsp;
			$langNewAccountΑctivation 
			&nbsp;(<a href=\"ldapnewprof.php?auth=".$v."\">".get_auth_info($v)."</a>)</p>
		    ";

			if(!empty($auth_method_settings))
			{
				//$tool_content .= "<p>&nbsp;&nbsp;&nbsp;&nbsp;<small>".$auth_method_settings['auth_instructions'];
			}		
		}
	}
}
else
{
	$tool_content .= "
					<p>Η εγγραφή στην πλατφόρμα, πρός το παρόν δεν επιτρέπεται.</p>
							<p>Παρακαλούμε, ενημερώστε το διαχειριστή του συστήματος</p>
					";
}

$tool_content .= "
    <br>
    </FIELDSET>
	</td>
  </tr>
  </table>
  
";
	 
	 
draw($tool_content, 0, 'auth');

?>
