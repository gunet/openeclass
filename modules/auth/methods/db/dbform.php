<?php
/*===========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ===========================================================================
*	Copyright(c) 2003-2010  Greek Universities Network - GUnet
*	A full copyright notice can be read in "/info/copyright.txt".
*
*  	Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*				Yannis Exidaridis <jexi@noc.uoa.gr>
*				Alexandros Diamantidis <adia@noc.uoa.gr>
*
*	For a full list of contributors, see "credits.txt".
*
*	This program is a free software under the terms of the GNU
*	(General Public License) as published by the Free Software
*	Foundation. See the GNU License for more details.
*	The full license can be read in "license.txt".
*
*	Contact address: 	GUnet Asynchronous Teleteaching Group,
*						Network Operations Center, University of Athens,
*						Panepistimiopolis Ilissia, 15784, Athens, Greece
*						eMail: eclassadmin@gunet.gr
============================================================================*/

$dbdata = $auth_data;

if(!empty($dbdata))
{
    $dbsettings = $dbdata['auth_settings'];
    $dbinstructions = $dbdata['auth_instructions'];
    $edb = empty($dbsettings)?"":explode("|",$dbsettings);
    if(!empty($edb))
    {
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
	  	$dbhost = "";			//dbhost
	    $dbname = "";			//dbname
	    $dbuser = "";			//dbuser
	    $dbpass = "";			// dbpass
	    $dbtable = "";		//dbtable
	    $dbfielduser = "";	//dbfielduser
	    $dbfieldpass = "";	//dbfieldpass
	  }
}
else
{
    $dbsettings = $dbdata['auth_settings'];
    $dbinstructions = $dbdata['auth_instructions'];
    $dbhost = $dbsettings;
}


$tool_content .= "
    <tr>
      <th class=\"left\">$langdbhost:</th>
      <td><input class=\"FormData_InputText\" name=\"dbhost\" type=\"text\" size=\"30\" value=\"$dbhost\" /></td>
    </tr>";

$tool_content .= "
    <tr>
      <th class=\"left\">$langdbname:</th>
      <td><input class=\"FormData_InputText\" name=\"dbname\" type=\"text\" size=\"30\" value=\"$dbname\" /></td>
    </tr>
    <tr>
      <th class=\"left\">$langdbuser:</th>
    <td><input class=\"FormData_InputText\" name=\"dbuser\" type=\"text\" size=\"30\" value=\"$dbuser\" /></td>
    </tr>
    <tr>
      <th class=\"left\">$langdbpass:</th>
      <td><input class=\"FormData_InputText\" name=\"dbpass\" type=\"password\" size=\"30\" value=\"$dbpass\" /></td>
    </tr>
    <tr>
      <th class=\"left\">$langdbtable:</th>
      <td><input class=\"FormData_InputText\" name=\"dbtable\" type=\"text\" size=\"30\" value=\"$dbtable\" /></td>
    </tr>
    <tr>
      <th class=\"left\">$langdbfielduser:</th>
      <td><input class=\"FormData_InputText\" name=\"dbfielduser\" type=\"text\" size=\"30\" value=\"$dbfielduser\" /></td>
    </tr>
    <tr>
      <th class=\"left\">$langdbfieldpass:</th>
    <td><input class=\"FormData_InputText\" name=\"dbfieldpass\" type=\"text\" size=\"30\" value=\"$dbfieldpass\" /></td>
    </tr>
    <tr>
      <th class=\"left\">$langInstructionsAuth:</th>
      <td><textarea class=\"FormData_InputText\" name=\"dbinstructions\" cols=\"30\" rows=\"10\" wrap=\"virtual\">".$dbinstructions."</textarea></td>
    </tr>
";

?>
