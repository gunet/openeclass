<?PHP
/*===========================================================================
*   Open eClass 2.1
*   E-learning and Course Management System
* ===========================================================================
*	Copyright(c) 2003-2008  Greek Universities Network - GUnet
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

/*
 * Logged In Component
 *
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 *
 * @abstract This component creates the content of the start page when the
 * user is logged in
 *
 */

$tool_content = "";

include ("perso.php");

$tool_content = "
<div id=\"leftnav_perso\">
  <table width=\"100%\">
  <thead>
  <tr>
    <th class=\"persoBoxTitle\">{LANG_MY_PERSO_LESSONS}</th>
  </tr>
  </thead>
  <tbody>
  <tr class=\"odd\">
    <td>
      {LESSON_CONTENT}
    </td>
  </tr>
  </tbody>
  </table>

  <br />

  <table width=\"100%\">
  <thead>
  <tr>
    <th class=\"persoBoxTitle\">{LANG_MY_PERSO_ANNOUNCEMENTS}</th>
  </tr>
  </thead>
  <tbody>
  <tr class=\"odd\">
    <td>
      {ANNOUNCE_CONTENT}
    </td>
  </tr>
  </tbody>
  </table>

      		<br />

<table width=\"100%\">
      			<thead>
      				<tr>
      					<th class=\"persoBoxTitle\">
      						{LANG_MY_PERSO_AGENDA}
      					</th>
      				</tr>
      			</thead>
      			<tbody>
      				<tr class=\"odd\">
      					<td>
	      					{AGENDA_CONTENT}
     					</td>
     				</tr>
      			</tbody>
      		</table>

	  	</div>

	  	<div id=\"content_main_perso\">

	  		<table width=\"100%\">
      			<thead>
      				<tr>
      					<th class=\"persoBoxTitle\">
      						{LANG_MY_PERSO_DEADLINES}
      					</th>
      				</tr>
      			</thead>
      			<tbody>
      				<tr class=\"odd\">
      					<td>
      						{ASSIGN_CONTENT}
     					</td>
     				</tr>
      			</tbody>
      		</table>

      		<br />
      		<table width=\"100%\">
      			<thead>
      				<tr>
      					<th class=\"persoBoxTitle\">
      						{LANG_MY_PERSO_DOCS}
      					</th>
      				</tr>
      			</thead>
      			<tbody>
      				<tr class=\"odd\">
      					<td>
      						{DOCS_CONTENT}
     					</td>
     				</tr>
     			</tbody>
     		</table>

     		<br />

     		<table width=\"100%\">
     			<thead>
     				<tr>
     					<th class=\"persoBoxTitle\">
     						{LANG_PERSO_FORUM}
     					</th>
     				</tr>
     			</thead>
     			<tbody>
     				<tr class=\"odd\">
     					<td>
     						{FORUM_CONTENT}
     					</td>
     				</tr>
      			</tbody>
      		</table>

      		</div>";
?>
