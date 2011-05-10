<?php
 //* @version $Id$
/*****************************************************************************
        DEAL WITH  BASETHEME, OTHER INCLUDES AND NAMETOOLS
******************************************************************************/
// Check if user is administrator and if yes continue

/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* =========================================================================*/

// Othewise exit with appropriate message
$require_admin = true;
// Include baseTheme
include '../../include/baseTheme.php';
// Define $nameTools
$nameTools = $langAdmin;
define('HIDE_TOOL_TITLE', 1);

/*****************************************************************************
        MAIN BODY
******************************************************************************/

// This is used for inserting data in 'monthly_report' table.
// The work is done every time the admin page is called in order to
// ensure correct (up-to-date) information on the table.
require_once "summarizeMonthlyData.php";

mysql_select_db($mysqlMainDb);

// Constract a table with platform identification info
$tool_content .= "
  <br />
  <fieldset>
  <legend>$langPlatformIdentity</legend>
    <table width='100%' class='tbl'>
    <tr>
      <th width='110'>Version:</th>
      <td>$langAboutText <b>$siteName " . ECLASS_VERSION . "</b></td>
    </tr>
    <tr>
      <th>IP Host:</th>
      <td>$langHostName <b>$_SERVER[SERVER_NAME]</b></td>
    </tr>
    <tr>
      <th>Web Server:</th>
      <td>$langWebVersion <b>$_SERVER[SERVER_SOFTWARE]</b></td>
    </tr>
    <tr>
      <th>Data Base Server:</th>
      <td>";
        if (extension_loaded('mysql'))
            $tool_content .= "$langMySqlVersion<b>".mysql_get_server_info()."</b>";
        else // If not display message no MySQL
            $tool_content .= "<font color='red'>".$langNoMysql."</font>";
    $tool_content .= "</td>
    </tr>
    </table>
  </fieldset>
  <br />";


// Count prof requests with status = 1
$sql = "SELECT COUNT(*) AS cnt FROM prof_request WHERE status=1 AND statut=1";
$result = mysql_query($sql);
$myrow = mysql_fetch_array($result);
$count_prof_requests = $myrow['cnt'];
if ($count_prof_requests > 0) {
    $prof_request_msg = "$langThereAre $count_prof_requests $langOpenRequests";
} else {
    $prof_request_msg = $langNoOpenRequests;
}

// Find last course created
$sql = "SELECT code, intitule, titulaires FROM cours ORDER BY cours_id DESC LIMIT 0,1";
$result = mysql_query($sql);
$myrow = mysql_fetch_array($result);
if (empty($myrow)) {
	$last_course_info = $langNoCourses;
} else {
	$last_course_info = "<b>".$myrow['intitule']."</b> (".$myrow['code'].", ".$myrow['titulaires'].")";
}

// Find last prof registration
$sql = "SELECT prenom, nom, email, registered_at FROM user WHERE statut = 1 ORDER BY user_id DESC LIMIT 0,1";
$result = mysql_query($sql);
$myrow = mysql_fetch_array($result);
$last_prof_info = "<b>".$myrow['prenom']." ".$myrow['nom']."</b> (".$myrow['email'].", ".date("j/n/Y H:i",$myrow['registered_at']).")";

// Find last stud registration
$sql = "SELECT prenom, nom, email, registered_at FROM user WHERE statut = 5 ORDER BY user_id DESC LIMIT 0,1";
$result = mysql_query($sql);
$myrow = mysql_fetch_array($result);
$last_stud_info = "<b>".$myrow['prenom']." ".$myrow['nom']."</b> (".$myrow['email'].", ".date("j/n/Y H:i",$myrow['registered_at']).")";

// Find admin's last login
$sql = "SELECT `when` FROM loginout WHERE id_user = '".$uid."' AND action = 'LOGIN' ORDER BY `when` DESC LIMIT 1,1";
$result = mysql_query($sql);
$myrow = mysql_fetch_array($result);
$lastadminlogin = strtotime($myrow['when']!=""?$myrow['when']:0);

// Count profs registered after last login
$sql = "SELECT COUNT(*) AS cnt FROM user WHERE statut = 1 AND registered_at > '".$lastadminlogin."'";
$result = mysql_query($sql);
$myrow = mysql_fetch_array($result);
$lastregisteredprofs = $myrow['cnt'];

// Count studs registered after last login
$sql = "SELECT COUNT(*) AS cnt FROM user WHERE statut = 5 AND registered_at > '".$lastadminlogin."'";
$result = mysql_query($sql);
$myrow = mysql_fetch_array($result);
$lastregisteredstuds = $myrow['cnt'];


$tool_content .= "
  <fieldset>
  <legend>$langInfoAdmin</legend>
    <table width=\"100%\" class=\"tbl\">
    <tr>
      <th width=\"260\">$langOpenRequests:</th>
      <td>".$prof_request_msg."</td>
    </tr>
    <tr>
      <th>$langLastLesson</th>
      <td>$last_course_info</td>
    </tr>
    <tr>
      <th>$langLastProf</th>
      <td>$last_prof_info</td>
    </tr>
    <tr>
      <th>$langLastStud</th>
      <td>$last_stud_info</td>
    </tr>
    <tr>
      <th>$langAfterLastLoginInfo</th>
      <td>$langAfterLastLogin
        <ul class='custom_list'>
          <li><b>".$lastregisteredprofs."</b> $langTeachers</li>
          <li><b>".$lastregisteredstuds."</b> $langUsersS </li>
        </ul>
      </td>
    </tr>
    </table>
  </fieldset>
  <br />";

/*****************************************************************************
        DISPLAY HTML
******************************************************************************/
// Call draw function to display the HTML
// $tool_content: the content to display
// 3: display administrator menu
// admin: use tool.css from admin folder
draw($tool_content,3);
