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
require_once 'modalconfirmation.php';

$pageName = $langAdmin;
define('HIDE_TOOL_TITLE', 1);

// Construct a table with platform identification info
$tool_content .= action_bar(array(
        array('title' => $langBack,
            'url' => "${urlServer}main/portfolio.php",
            'icon' => 'fa-reply',
            'level' => 'primary-label')),false);
$tool_content .= "
    <div class='row'>
        <div class='col-md-12'>
        <div class='panel'><div class='panel-body'>
            $langOnlineUsers : <b>" . getOnlineUsers() . "</b>
                </div></div>
        </div>
    </div>
    <div class='row'>
        <div class='col-md-12'>
            <div class='panel panel-default'>
                <div class='panel-heading'>
                    <h3 class='panel-title'>$langPlatformIdentity</h3>
                </div>                
                <div class='panel-body'>                
                    <div class='row margin-bottom-thin'>
                    <div class='col-sm-3'>
                        " . icon('fa-check') . " <strong>$langWebServerVersion</strong> 
                    </div>
                    <div class='col-sm-9'>
                        <em>" . q($_SERVER['SERVER_SOFTWARE']) . "</em>
                    </div>
                </div>
                <div class='row margin-bottom-thin'>
                <div class='col-sm-3'>";
                if (version_compare(PHP_VERSION, '5.4.0') >= 0) {
                    $info_icon = icon('fa-check');
                    $info_text = '';
                } else {
                    $info_icon = icon('fa-ban');
                    $info_text = "<div class='row margin-bottom-thin'>
                                    <div class='col-sm-12'>
                                        <div class='alert alert-danger'>$langWarnAboutPHP</div>
                                    </div>
                                  </div>";
                }
                $tool_content .= "$info_icon <strong>$langPHPVersion</strong></div>";                
                $tool_content .= "<div class='col-sm-9'>";
                $tool_content .= "<em>" . PHP_VERSION . "</em>";                
                $tool_content .= "</div></div>";
                $tool_content .= $info_text;
                $tool_content .= "<div class='row margin-bottom-thin'>
                    <div class='col-sm-3'>" . icon('fa-check') . "
                        <strong>$langMySqlVersion</strong>
                    </div>
                    <div class='col-sm-9'>
                        <em>" . Database::get()->attributes()->serverVersion() . "</em>
                    </div>
                </div>
                <div class='row margin-bottom-thin'>
                    <div class='col-sm-3'>
                        <strong>$langVersion:</strong>
                    </div>
                    <div class='col-sm-9'>
                        <em>$siteName " . ECLASS_VERSION . "</em>
                    </div>
                </div>
            </div>
        </div>";


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
    <div class='panel panel-default'>
        <div class='panel-heading'>
            <h3 class='panel-title'>$langInfoAdmin</h3>
        </div>
        <div class='panel-body'>
            <div class='row margin-bottom-thin'>
                <div class='col-sm-4'>
                <strong>$langOpenRequests:</strong>
                </div>
                <div class='col-sm-8'>
                    $prof_request_msg
                </div>                        
            </div>
            <div class='row margin-bottom-thin'>
                <div class='col-sm-4'>
                <strong>$langLastLesson</strong>
                </div>
                <div class='col-sm-8'>
                    $last_course_info
                </div>                        
            </div>
            <div class='row margin-bottom-thin'>
                <div class='col-sm-4'>
                <strong>$langLastProf</strong>
                </div>
                <div class='col-sm-8'>
                    $last_prof_info
                </div>                        
            </div>
            <div class='row margin-bottom-thin'>
                <div class='col-sm-4'>
                <strong>$langLastStud</strong>
                </div>
                <div class='col-sm-8'>
                    $last_stud_info
                </div>                        
            </div>
            <div class='row margin-bottom-thin'>
                <div class='col-sm-4'>
                <strong>$langAfterLastLoginInfo</strong>
                </div>
                <div class='col-sm-8'>
                $langAfterLastLogin
                    <ul class='custom_list'>
                      <li><b>" . $lastregisteredprofs . "</b> $langTeachers</li>
                      <li><b>" . $lastregisteredstuds . "</b> $langStudents </li>
                    </ul>
                </div>                        
            </div>                 
        </div>
    </div>";


// INDEX RELATED
if (get_config('enable_indexing')) {
    require_once 'modules/search/indexer.class.php';
    $idx = new Indexer();

    $numDocs = $idx->getIndex()->numDocs();
    $isOpt = (!$idx->getIndex()->hasDeletions()) ? $langYes : $langNo;

    
    $tool_content .= "
    <div class='panel panel-default'>
        <div class='panel-heading'>
            <h3 class='panel-title'>$langIndexInfo</h3>
        </div>
        <div class='panel-body'>
            <div class='row margin-bottom-thin'>
                <div class='col-sm-3'>
                <strong>$langIndexNumDocs:</strong>
                </div>
                <div class='col-sm-9'>
                    $numDocs
                </div>                        
            </div>
            <div class='row margin-bottom-thin'>
                <div class='col-sm-3'>
                <strong>$langIndexIsOptimized</strong>
                </div>
                <div class='col-sm-9'>
                    $isOpt
                </div>
            </div>";
if ($idx->getIndex()->hasDeletions()) {   
    $tool_content .= "        
            <div class='row margin-bottom-thin'>
                <div class='col-sm-9 col-sm-offset-3'>
                    <a href='../search/optpopup.php' onclick=\"return optpopup('../search/optpopup.php', 600, 500)\">$langOptimize</a>
                </div>            
            </div>";
}
$tool_content .= "
            <div class='row margin-bottom-thin'>
                <div class='col-sm-9 col-sm-offset-3'>
                    <a id='reindex_link' href='../search/idxpopup.php?reindex'>$langReindex</a>
                </div>            
            </div>    
        </div>
    </div>";
    $tool_content .= modalConfirmation('confirmReindexDialog', 'confirmReindexLabel', $langConfirmEnableIndexTitle, $langConfirmEnableIndex, 'confirmReindexCancel', 'confirmReindexOk');
}

// CRON RELATED
$tool_content .= "<img src='cron.php' width='2' height='1' alt=''/>";
$res = Database::get()->queryArray("SELECT name, last_run FROM cron_params");
if (count($res) >= 1) {
    $tool_content .= "
    <div class='panel panel-default'>
        <div class='panel-heading'>
            <h3 class='panel-title'>$langCronInfo</h3>
        </div>
        <div class='panel-body'>
            <div class='row margin-bottom-thin'>
                <div class='col-sm-3'>
                <strong>$langCronName</strong>
                </div>
                <div class='col-sm-9'>
                    $langCronLastRun
                    <div class='row'>";
                
foreach ($res as $row) {
    $tool_content .= "<div class='col-xs-6'>" . $row->name . "</div><div class='col-xs-6'>" . $row->last_run . "</div>";
}    
$tool_content .= "</div>
                </div>
            </div>        
        </div>
    </div>";
}
$tool_content .= "</div></div>";

$head_content = <<<EOF
<script type='text/javascript'>
/* <![CDATA[ */

var optwindow = null;
var reidxwindow = null;
                
function optpopup(url, w, h) {
    var left = (screen.width/2)-(w/2);
    var top = (screen.height/2)-(h/2);
    
    if (optwindow == null || optwindow.closed) {
        optwindow = window.open(url, 'optpopup', 'resizable=yes, scrollbars=yes, status=yes, width='+w+', height='+h+', top='+top+', left='+left);
        if (window.focus && optwindow !== null) {
            optwindow.focus();
        }
    } else {
        optwindow.focus();
    }
    
    return false;
}
                
function reidxpopup(url, w, h) {
    var left = (screen.width/2)-(w/2);
    var top = (screen.height/2)-(h/2);
    
    if (reidxwindow == null || reidxwindow.closed) {
        reidxwindow = window.open(url, 'reidxpopup', 'resizable=yes, scrollbars=yes, status=yes, width='+w+', height='+h+', top='+top+', left='+left);
        if (window.focus && reidxwindow !== null) {
            reidxwindow.focus();
        }
    } else {
        reidxwindow.focus();
    }
    
    return false;
}
 
$(document).ready(function() {
    
    $('#confirmReindexDialog').modal({
        show: false,
        keyboard: false,
        backdrop: 'static'
    });
        
    $("#confirmReindexCancel").click(function() {
        $("#confirmReindexDialog").modal("hide");
    });
        
    $("#confirmReindexOk").click(function() {
        $("#confirmReindexDialog").modal("hide");
        reidxpopup('../search/idxpopup.php?reindex', 600, 500);
    });
    
    $('#reindex_link').click(function(event) {
        event.preventDefault();
        $("#confirmReindexDialog").modal("show");
    });
    
});

/* ]]> */
</script>
EOF;
draw($tool_content, 3, null, $head_content);
