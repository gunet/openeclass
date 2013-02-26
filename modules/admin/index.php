<?php
/* ========================================================================
 * Open eClass 3.0
 * E-learning and Course Management System
 * ========================================================================
 * Copyright 2003-2012  Greek Universities Network - GUnet
 * A full copyright notice can be read in "/info/copyright.txt".
 * For a full list of contributors, see "credits.txt".
 *
 * Open eClass is an open platform distributed in the hope that it will
 * be useful (without any warranty), under the terms of the GNU (General
 * Public License) as published by the Free Software Foundation.
 * The full license can be read in "/info/license/license_gpl.txt".
 *
 * Contact address: GUnet Asynchronous eLearning Group,
 *                  Network Operations Center, University of Athens,
 *                  Panepistimiopolis Ilissia, 15784, Athens, Greece
 *                  e-mail: info@openeclass.org
 * ======================================================================== */

$require_usermanage_user = TRUE;

require_once '../../include/baseTheme.php';

$nameTools = $langAdmin;
define('HIDE_TOOL_TITLE', 1);

// This is used for inserting data in 'monthly_report' table.
// The work is done every time the admin page is called in order to
// ensure correct (up-to-date) information on the table.
require_once "summarizeMonthlyData.php";
mysql_select_db($mysqlMainDb);

// Construct a table with platform identification info
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
    $tool_content .= "$langMySqlVersion<b>".mysql_get_server_info()."</b>";
    $tool_content .= "</td>
    </tr>
    </table>
  </fieldset>
  <br />";


// Count prof requests with status = 1
$sql = "SELECT COUNT(*) AS cnt FROM user_request WHERE status=1 AND statut=1";
$result = db_query($sql);
$myrow = mysql_fetch_array($result);
$count_prof_requests = $myrow['cnt'];
if ($count_prof_requests > 0) {
    $prof_request_msg = "$langThereAre $count_prof_requests $langOpenRequests";
} else {
    $prof_request_msg = $langNoOpenRequests;
}

// Find last course created
$sql = "SELECT code, title, prof_names FROM course ORDER BY id DESC LIMIT 0,1";
$result = db_query($sql);
if (mysql_num_rows($result)) {
        $myrow = mysql_fetch_array($result);
	$last_course_info = "<b>".q($myrow['title'])."</b> ".q($myrow['code']).", ".q($myrow['prof_names']).")";
} else {
	$last_course_info = $langNoCourses;
}

// Find last prof registration
$sql = "SELECT prenom, nom, username, registered_at FROM user WHERE statut = 1 ORDER BY user_id DESC LIMIT 0,1";
$result = db_query($sql);
$myrow = mysql_fetch_array($result);
$last_prof_info = "<b>".q($myrow['prenom'])." ".q($myrow['nom'])."</b> (".q($myrow['username']).", ".date("j/n/Y H:i",$myrow['registered_at']).")";

// Find last stud registration
$sql = "SELECT prenom, nom, username, registered_at FROM user WHERE statut = 5 ORDER BY user_id DESC LIMIT 0,1";
$result = db_query($sql);
if ( ($myrow = mysql_fetch_array($result)) != FALSE) {
	$last_stud_info = "<b>".q($myrow['prenom'])." ".q($myrow['nom'])."</b> (".q($myrow['username']).", ".date("j/n/Y H:i",$myrow['registered_at']).")";
}
else {
	// no student is yet registered
	$last_stud_info = $langLastStudNone;
}

// Find admin's last login
$sql = "SELECT `when` FROM loginout WHERE id_user = $uid AND action = 'LOGIN' ORDER BY `when` DESC LIMIT 1,1";
$result = db_query($sql);
$myrow = mysql_fetch_array($result);
$lastadminlogin = strtotime($myrow['when']!=""?$myrow['when']:0);

// Count profs registered after last login
$sql = "SELECT COUNT(*) AS cnt FROM user WHERE statut = 1 AND registered_at > '".$lastadminlogin."'";
$result = db_query($sql);
$myrow = mysql_fetch_array($result);
$lastregisteredprofs = $myrow['cnt'];

// Count studs registered after last login
$sql = "SELECT COUNT(*) AS cnt FROM user WHERE statut = 5 AND registered_at > '".$lastadminlogin."'";
$result = db_query($sql);
$myrow = mysql_fetch_array($result);
$lastregisteredstuds = $myrow['cnt'];


$tool_content .= "
  <fieldset>
  <legend>$langInfoAdmin</legend>
    <table width='100%' class='tbl'>
    <tr>
      <th width='260'>$langOpenRequests:</th>
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
          <li><b>".$lastregisteredstuds."</b> $langStudents </li>
        </ul>
      </td>
    </tr>
    </table>
  </fieldset>
  <br />";

require_once 'modules/search/indexer.class.php';
require_once 'modules/search/courseindexer.class.php';
$idx = new Indexer();

if (isset($_GET['optimize']))
    $idx->finalize();

if (isset($_GET['reindex'])) {
    $cidx = new CourseIndexer($idx);
    $cidx->reindex();
}

$numDocs = $idx->getIndex()->numDocs();
$isOpt = (!$idx->getIndex()->hasDeletions()) ? $m['yes'] : $m['no'];

$tool_content .= "
  <fieldset>
  <legend>$langIndexInfo</legend>
    <table width='100%' class='tbl'>
    <tr>
      <th width='260'>$langIndexNumDocs:</th>
      <td>" . $numDocs . "</td>
    </tr>
    <tr>
      <th>$langIndexIsOptimized</th>
      <td>" . $isOpt . "</td>
    </tr>";

if ($idx->getIndex()->hasDeletions()) {
    $tool_content .= "
    <tr>
      <th></th>
      <td><a href='" . $_SERVER['SCRIPT_NAME'] . "?optimize'>$langOptimize</a></td>
    </tr>";
}

$tool_content .= "
    <tr>
      <th></th>
      <td><a href='" . $_SERVER['SCRIPT_NAME'] . "?reindex'>$langReindex</a></td>
    </tr>
    </table>
  </fieldset>
  <br />";

draw($tool_content,3);
