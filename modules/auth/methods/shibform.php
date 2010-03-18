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
*				Network Operations Center, University of Athens,
*				Panepistimiopolis Ilissia, 15784, Athens, Greece
*				eMail: eclassadmin@gunet.gr
============================================================================*/

if ($f = @fopen("${webDir}secure/index.php", "r")) {
	while (!feof($f)) {
		$buffer = fgets($f, 4096);
		if (strpos($buffer, 'shib_email')) {
			$shibemail = strstr($buffer, '=');
			$shibemail = trim(substr($shibemail, 1, -2));
		}
		if (strpos($buffer, 'shib_uname')) {
			$shibuname = strstr($buffer, '=');
			$shibuname = trim(substr($shibuname, 1, -2));
		}
		if (strpos($buffer, 'shib_nom')) {
			$shibcn = strstr($buffer, '=');
			$shibcn = trim(substr($shibcn, 1, -2));
		}
	}
        fclose($f);
}

$r = mysql_fetch_array(db_query("SELECT auth_settings, auth_instructions FROM auth WHERE auth_id = 6"));
$shibsettings = $r['auth_settings'];
$shibinstructions = $r['auth_instructions'];
if ($shibsettings != 'shibboleth' and $shibsettings != "") {
	$shibseparator = $shibsettings;
	$checkedshib = 'checked';
} else {
	$checkedshib = $shibseparator = "";
}
$tool_content .= sprintf("<tr><td colspan='2' align='justify'>$langExplainShib</td></tr>", $webDir);
@$tool_content .= "<tr><th class='left'>$langShibEmail:</th>
<td><input class='FormData_InputText' name='shibemail' type='text' size='30' value='".$shibemail."' /></td>
</tr>
<tr><th class='left'>$langShibUsername:</th>
<td><input class='FormData_InputText' name='shibuname' type='text' size='30' value='".$shibuname."' /></td>
</tr>
<tr><th class='left' rowspan='2'>$langShibCn:</th>
<td><input class='FormData_InputText' name='shibcn' type='text' size='30' value='".$shibcn."' /></td>
</tr>
<tr><td bgcolor='#F8F8F8'><input type='checkbox' name='checkseparator' $checkedshib />
&nbsp;$langCharSeparator&nbsp;
<input class='FormData_InputText' name='shibseparator' type='text' size='1' maxlength='2' value='".q($shibseparator)."' /></th>
</tr>
<tr><th class='left'>$langInstructionsAuth:</td>
<td><textarea class='FormData_InputText' name='shibinstructions' cols='30' rows='10' wrap='virtual'>".$shibinstructions."</textarea></td></tr>";
?>
