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

// Construct a table with platform identification info
$tool_content .= "
  <div class='alert alert-info'>$langOnlineUsers: <b>" . getOnlineUsers() . "</b></div>
  <br />
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
$tool_content .= "$langMySqlVersion<b>" . Database::get()->attributes()->serverVersion() . "</b>";
$tool_content .= "</td></tr>
    </table></fieldset>
  <br />";


// Count prof requests with status = 1
$count_prof_requests = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM user_request WHERE state = 1 AND status = 1")->cnt;
if ($count_prof_requests > 0) {
    $prof_request_msg = "$langThereAre $count_prof_requests $langOpenRequests";
} else {
    $prof_request_msg = $langNoOpenRequests;
}

// Find last course created
$myrow = Database::get()->querySingle("SELECT code, title, prof_names FROM course ORDER BY id DESC LIMIT 0, 1");
if ($myrow) {
    $last_course_info = "<b>" . q($myrow->title) . "</b> " . q($myrow->code) . ", " . q($myrow->prof_names) . ")";
} else {
    $last_course_info = $langNoCourses;
}

// Find last prof registration
$myrow = Database::get()->querySingle("SELECT givenname, surname, username, registered_at FROM user WHERE status = 1 ORDER BY id DESC LIMIT 0,1");
$last_prof_info = "<b>" . q($myrow->givenname) . " " . q($myrow->surname) . "</b> (" . q($myrow->username) . ", " . date("j/n/Y H:i", strtotime($myrow->registered_at)) . ")";

// Find last stud registration
$myrow = Database::get()->querySingle("SELECT givenname, surname, username, registered_at FROM user WHERE status = 5 ORDER BY id DESC LIMIT 0,1");
if ($myrow) {
    $last_stud_info = "<b>" . q($myrow->givenname) . " " . q($myrow->surname) . "</b> (" . q($myrow->username) . ", " . date("j/n/Y H:i", strtotime($myrow->registered_at)) . ")";
} else {
    // no student is yet registered
    $last_stud_info = $langLastStudNone;
}

// Find admin's last login
$lastadminloginres = Database::get()->querySingle("SELECT `when` FROM loginout WHERE id_user = ?d AND action = 'LOGIN' ORDER BY `when` DESC LIMIT 1,1", $uid);
$lastregisteredprofs = 0;
$lastregisteredstuds = 0;
if ($lastadminloginres && $lastadminloginres->when) {
    $lastadminlogin = $lastadminloginres->when;
    // Count profs registered after last login
    $lastregisteredprofs = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM user WHERE status = 1 AND registered_at > ?t", $lastadminlogin)->cnt;
    // Count studs registered after last login
    $lastregisteredstuds = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM user WHERE status = 5 AND registered_at > ?t", $lastadminlogin)->cnt;
}


$tool_content .= "
  <fieldset>
  <legend>$langInfoAdmin</legend>
    <table width='100%' class='tbl'>
    <tr>
      <th width='260'>$langOpenRequests:</th>
      <td>" . $prof_request_msg . "</td>
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
          <li><b>" . $lastregisteredprofs . "</b> $langTeachers</li>
          <li><b>" . $lastregisteredstuds . "</b> $langStudents </li>
        </ul>
      </td>
    </tr>
    </table>
  </fieldset>
  <br />";


// INDEX RELATED
if (get_config('enable_indexing')) {
    require_once 'modules/search/indexer.class.php';
    $idx = new Indexer();

    // optimize index
    if (isset($_GET['optimize'])) {
        $idx->getIndex()->optimize();
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

    // Auto to koumpi kalytera na mhn emfanizetai se production,
    // dioti eksartatai apo to php max exec time.
    // Kalytera to indexing na ginetai mono transparently
    // reindex everything
    //if (isset($_GET['reindex']))
    //    $idx->reindexAll();
    //$tool_content .= "
    //    <tr>
    //      <th></th>
    //      <td><a href='" . $_SERVER['SCRIPT_NAME'] . "?reindex'>$langReindex</a></td>
    //    </tr>";

    $tool_content .= "
        </table>
      </fieldset>
      <br />";
}

// CRON RELATED
$tool_content .= "<img src='cron.php' width='2' height='1' alt=''/>";
$res = Database::get()->queryArray("SELECT name, last_run FROM cron_params");
if (count($res) >= 1) {
    $tool_content .= "
      <fieldset>
      <legend>$langCronInfo</legend>
        <table width='100%' class='tbl'>
        <tr>
          <th width='260'>$langCronName</th>
          <td>$langCronLastRun</td>
        </tr>";

    foreach ($res as $row) {
        $tool_content .= "<tr><th>" . $row->name . "</th><td>" . $row->last_run . "</td></tr>";
    }

    $tool_content .= "
      </tbody>
      </table>
      <br />";
}


draw($tool_content, 3);
