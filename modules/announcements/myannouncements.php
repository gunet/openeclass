<?php 
 /*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.3.0 $Revision$                            |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2002 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      | $Id$        |
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
$require_login = TRUE;

$local_style = 'em { font-weight: bold; color: #f0741e; font-size:11pt; }
h5 { font-weight: normal; }
.normal { font-size: 10pt; }';

$langFiles = 'announcements';
include('../../include/init.php');
include('../../include/lib/textLib.inc.php'); 
begin_page($langMyAnnouncements);

$result = db_query("SELECT * FROM annonces,cours_user 
			WHERE annonces.code_cours=cours_user.code_cours 
			AND cours_user.user_id='$uid' 
			ORDER BY temps DESC",$mysqlMainDb) OR die("DB problem");

	echo "<table width=\"600\" cellpadding=\"2\" cellspacing=\"4\" border=\"0\">";
	while ($myrow = mysql_fetch_array($result))
	{	
		$content = $myrow['contenu'];
		$content = make_clickable($content);
		$content = nl2br($content);
		$row = mysql_fetch_array(db_query("SELECT intitule,titulaires FROM cours WHERE code='$myrow[code_cours]'"));
		echo "<tr><td bgcolor=\"$color2\"><em>$row[intitule]</em> ($langTitulaire <b>$row[titulaires]</b>)
			<h5>($langAnn : ".$myrow['temps'].")</h5></td></tr>
		      <tr><td class=\"normal\">$content</td></tr>";
	}	// while loop
	echo "
	</table>";
?>
		<hr noshade size="1">
	</td>
	</tr>
</table>
</body>
</html>
