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
      |          Christophe Gesché <gesche@ipm.ucl.ac.be>                    |
      +----------------------------------------------------------------------+
 */

$langFiles = array('registration', 'admin', 'gunet');
include '../../include/baseTheme.php';
require_once 'auth.inc.php';
//check_admin();

//$nameTools = $langByLdap;
$auth = get_auth_id();
$msg = get_auth_info($auth);
if(!empty($msg)) $nameTools = $msg;
$navigation[] = array("url"=>"../admin/", "name"=> $admin);
$navigation[] = array("url"=>"newprof_info.php", "name"=> $regprof);
//$page_title = $regprofldap;

$tool_content = "";

$tool_content .= "<table><tr>
		<td>
			<form method=\"POST\" action=\"ldapsearch_prof.php\">
				<table>
				<tr><td>Username</td>
					<td><input type=\"text\" name=\"ldap_email\" value=\"".@$m."\"></td>
				</tr>
				<tr><td>Password</td>
				<td><input type=\"password\" name=\"ldap_passwd\" value=\"".@$m."\"></td>
				</tr>
				<tr colspan=2>
					<td><br><input type=\"submit\" name=\"is_submit\" value=\"".$reg."\">
					<br /><br />
					</td>
				</tr>
			<br /><br /></table>
		</form><br />";
		
draw($tool_content,1);

?>
