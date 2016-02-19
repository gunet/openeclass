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
$data['action_bar'] = action_bar(array(
    array('title' => $langBack,
        'url' => "${urlServer}main/portfolio.php",
        'icon' => 'fa-reply',
        'level' => 'primary-label')),false);

$data['validPHP'] = version_compare(PHP_VERSION, '5.4.0') >= 0;
$data['serverVersion'] = Database::get()->attributes()->serverVersion();
$data['siteName'] = $siteName;

// Count prof requests with status = 1
$data['count_prof_requests'] = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM user_request WHERE state = 1 AND status = 1")->cnt;
// Find last course created
$data['lastCreatedCourse'] = Database::get()->querySingle("SELECT code, title, prof_names FROM course ORDER BY id DESC LIMIT 0, 1");
// Find last prof registration
$data['lastProfReg'] = Database::get()->querySingle("SELECT givenname, surname, username, registered_at FROM user WHERE status = 1 ORDER BY id DESC LIMIT 0,1");
// Find last stud registration
$data['lastStudReg'] = Database::get()->querySingle("SELECT givenname, surname, username, registered_at FROM user WHERE status = 5 ORDER BY id DESC LIMIT 0,1");
// Find admin's last login
$lastadminloginres = Database::get()->querySingle("SELECT `when` FROM loginout WHERE id_user = ?d AND action = 'LOGIN' ORDER BY `when` DESC LIMIT 1,1", $uid);
$data['lastregisteredprofs'] = 0;
$data['lastregisteredstuds'] = 0;
if ($lastadminloginres && $lastadminloginres->when) {
    $lastadminlogin = $lastadminloginres->when;
    // Count profs registered after last login
    $data['lastregisteredprofs'] = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM user WHERE status = 1 AND registered_at > ?t", $lastadminlogin)->cnt;
    // Count studs registered after last login
    $data['lastregisteredstuds'] = Database::get()->querySingle("SELECT COUNT(*) AS cnt FROM user WHERE status = 5 AND registered_at > ?t", $lastadminlogin)->cnt;
}
// INDEX RELATED
if (get_config('enable_indexing')) {
    require_once 'modules/search/indexer.class.php';
    $idx = new Indexer();

    $data['numDocs'] = $idx->getIndex()->numDocs();
    $data['isOpt'] = (!$idx->getIndex()->hasDeletions()) ? $m['yes'] : $m['no'];
    $data['idxHasDeletions'] = $idx->getIndex()->hasDeletions();
    

    $data['idxModal'] = modalConfirmation('confirmReindexDialog', 'confirmReindexLabel', $langConfirmEnableIndexTitle, $langConfirmEnableIndex, 'confirmReindexCancel', 'confirmReindexOk');
}
// CRON RELATED
$data['cronParams'] = Database::get()->queryArray("SELECT name, last_run FROM cron_params");

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

$data['menuTypeID'] = 3;
view('admin.index', $data);