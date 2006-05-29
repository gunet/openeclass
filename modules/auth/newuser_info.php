<?php 
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.0 $Revision$                            |
      +----------------------------------------------------------------------+
      | $Id$          |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |    This program is free software; you can redistribute it and/or     |
      |    modify it under the terms of the GNU General Public License       |
      |    as published by the Free Software Foundation; either version 2    |
      |   of the License, or (at your option) any later version.             |
      |                                                                      |
      |   This program is distributed in the hope that it will be useful,    |
      |   but WITHOUT ANY WARRANTY; without even the implied warranty of     |
      |   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the      |
      |   GNU General Public License for more details.                       |
      |                                                                      |
      |   You should have received a copy of the GNU General Public License  |
      |   along with this program; if not, write to the Free Software        |
      |   Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA          |
      |   02111-1307, USA. The GPL license is also available through the     |
      |   world-wide-web at http://www.gnu.org/copyleft/gpl.html             |
      +----------------------------------------------------------------------+
      | Authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>                |
      |          Hugues Peeters    <peeters@ipm.ucl.ac.be>                   |
      |          Christophe Geschι <gesche@ipm.ucl.ac.be>                    |
      +----------------------------------------------------------------------+
 */

$langFiles = array('registration','gunet');
include '../../include/baseTheme.php';
include 'auth.inc.php';
if(isset($already_second)) {
	session_register("uid");
	session_unregister("statut");
	session_unregister("prenom");
	session_unregister("nom");
	session_unregister("uname");
}
//@include "check_admin.inc";
$nameTools = $reguser;

// Initialise $tool_content
$tool_content = "";
// Main body

$tool_content .= "<table width=\"99%\">
<tr valign=\"top\" bgcolor=\"".$color2."\">
<td>";
$auth = get_auth_id();
if(!empty($auth))
{
	$tool_content .= "<ul>";
	$tool_content .= "<li><a href=\"newuser.php\">".$regnoldap."</a><br /></li>";
	if($auth!=1)
	{
		$auth_method_settings = get_auth_settings($auth);
		$tool_content .= "<li><a href=\"ldapnewuser.php\">".$regldap."</a></li>";
		if(!empty($auth_method_settings))
		{
			$tool_content .= "<br />".$auth_method_settings['auth_instructions'];
		}
	}
	$tool_content .= "</ul>";
}
else
{
	$tool_content .= "<br />Η εγγραφή στην πλατφόρμα, πρός το παρόν δεν επιτρέπεται.<br />
	Παρακαλούμε, ενημερώστε το διαχειριστή του συστήματος<br />";
}

$tool_content .= "</td></tr></table>";


$tool_content .= "<br />";

draw($tool_content,1);
?>