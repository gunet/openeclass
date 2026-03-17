<?php

/*
 *  ========================================================================
 *  * Open eClass
 *  * E-learning and Course Management System
 *  * ========================================================================
 *  * Copyright 2003-2024, Greek Universities Network - GUnet
 *  *
 *  * Open eClass is an open platform distributed in the hope that it will
 *  * be useful (without any warranty), under the terms of the GNU (General
 *  * Public License) as published by the Free Software Foundation.
 *  * The full license can be read in "/info/license/license_gpl.txt".
 *  *
 *  * Contact address: GUnet Asynchronous eLearning Group
 *  *                  e-mail: info@openeclass.org
 *  * ========================================================================
 *
 */

$require_login = true;
$require_current_course = true;
$require_user_registration = true;
$require_help = true;
$helpTopic = 'progress';

require_once '../../include/baseTheme.php';
require_once 'functions.php';
require_once 'process_functions.php';
require_once 'ExerciseEvent.php';
require_once 'AssignmentEvent.php';
require_once 'AssignmentSubmitEvent.php';
require_once 'CommentEvent.php';
require_once 'BlogEvent.php';
require_once 'WikiEvent.php';
require_once 'ForumEvent.php';
require_once 'ForumTopicEvent.php';
require_once 'LearningPathEvent.php';
require_once 'LearningPathDurationEvent.php';
require_once 'RatingEvent.php';
require_once 'ViewingEvent.php';
require_once 'CourseParticipationEvent.php';
require_once 'GradebookEvent.php';
require_once 'CourseCompletionEvent.php';
require_once 'AttendanceEvent.php';

$toolName = $langProgress;

load_js('tools.js');

// Initialize tool_content
$tool_content = '';

// WRAP in progress-module div
$tool_content .= "<div class='progress-module'>";

// ALWAYS SHOW TAB NAVIGATION FIRST (except in special cases like PDF, preview, progressall)
$show_tabs = !isset($_GET['p']) && !isset($_GET['preview']) && !isset($_GET['progressall']);
$tab_q = '';

if ($show_tabs) {
    // Determine active tab
    if (isset($_REQUEST['tab'])) {
        $active_tab = $_REQUEST['tab'];
    } elseif (isset($_REQUEST['points_game_id'])) {
        $active_tab = 'points';
    } elseif (isset($_REQUEST['certificate_id'])) {
        $active_tab = 'certificates';
    } elseif (isset($_REQUEST['badge_id'])) {
        $ccmpl = Database::get()->querySingle("SELECT count(*) as cnt FROM badge WHERE course_id = ?d AND bundle = -1 AND id = ?d", $course_id, $_REQUEST['badge_id']);
        if ($ccmpl->cnt == 0) {
            $active_tab = 'badges';
        } else {
            $active_tab = 'course_completion';
        }
    } else {
        if(!$is_editor && !is_course_completion_active()) {
            $active_tab = 'badges';
        } else {
            $active_tab = 'course_completion';
        }
    }

    if(!$is_editor && !is_course_completion_active()) {
        $course_completion_tab = "";
    } else {
        $course_completion_tab = "
            <button class='progress-nav-tab " . ($active_tab == 'course_completion' ? 'active' : '') . "' data-tab='course_completion'>
                $langCourseCompletion
            </button>
        ";
    }

    $tab_q = "&tab=".$active_tab;
    
    // Add navigation tabs at the very beginning
    $tool_content .= "
    <div class='progress-nav-container'>
        <div class='progress-nav-tabs'>
            $course_completion_tab
            <button class='progress-nav-tab " . ($active_tab == 'badges' ? 'active' : '') . "' data-tab='badges'>
                $langBadges
            </button>
            <button class='progress-nav-tab " . ($active_tab == 'certificates' ? 'active' : '') . "' data-tab='certificates'>
                $langCertificates
            </button>
            <button class='progress-nav-tab " . ($active_tab == 'points' ? 'active' : '') . "' data-tab='points'>
                $langPointsGames
            </button>
        </div>
    </div>
    ";
}

// Add CSS styles - SCOPED TO .progress-module ONLY
$head_content .= "
<style>
/* ============================================
   TAB NAVIGATION - SCOPED
   ============================================ */
.progress-module .progress-nav-container {
    background: white;
    border-bottom: 1px solid #e5e7eb;
    margin: 0 0 30px 0;
    padding: 0;
}

.progress-module .progress-nav-tabs {
    display: flex;
    gap: 0;
    position: relative;
    overflow-x: auto;
    padding-left: 0;
    margin-left: 0;
}

.progress-module .progress-nav-tab {
    padding: 16px 24px;
    background: transparent;
    border: none;
    border-bottom: 3px solid transparent;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
    color: #6b7280;
    font-size: 15px;
    font-weight: 500;
    white-space: nowrap;
    position: relative;
}

.progress-module .progress-nav-tab:hover {
    color: #374151;
    background: #f9fafb;
}

.progress-module .progress-nav-tab.active {
    color: #2563eb;
    border-bottom-color: #2563eb;
}

/* ============================================
   CARD STYLING - ALL CARDS IN PROGRESS MODULE
   ============================================ */

/* Main card containers - NORMAL BORDER EVERYWHERE */
.progress-module .progress-card,
.progress-module .panel,
.progress-module .panel-default,
.progress-module .panel-success,
.progress-module .panel-info,
.progress-module .panel-body-progress,
.progress-module .card,
.progress-module .card-default,
.progress-module div[class*='panel'],
.progress-module > div > .panel,
.progress-module .row > div > .panel {
    background: #ffffff !important;
    border: 1px solid #e5e7eb !important;
    border-radius: 12px !important;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05) !important;
    margin-bottom: 25px !important;
}

/* ONLY panels WITH heading get blue border */
.progress-module .panel:has(.panel-heading),
.progress-module .card:has(.card-header) {
    border-left: 4px solid #3b82f6 !important;
}

/* Card headers styling */
.progress-module .panel-heading,
.progress-module .card-header,
.progress-module h3.panel-title {
    background: transparent !important;
    border: none !important;
    border-radius: 12px 12px 0 0 !important;
    padding: 20px 24px !important;
    margin: 0 !important;
}

.progress-module .progress-card-header h4,
.progress-module .panel-heading h4,
.progress-module .panel-heading h3,
.progress-module .panel-title,
.progress-module h3.panel-title {
    font-weight: 700 !important;
    color: #1f2937 !important;
    margin: 0 !important;
    font-size: 16px !important;
}

.progress-module .progress-card-header i,
.progress-module .panel-heading h4 i,
.progress-module .panel-heading h3 i {
    margin-right: 10px;
    color: #3b82f6;
}

/* Card body - ALL TYPES */
.progress-module .panel-body,
.progress-module .card-body,
.progress-module .panel-body-progress {
    padding: 24px !important;
    background: transparent !important;
}

/* Tables in cards */
.progress-module .table-default,
.progress-module .table,
.progress-module table {
    margin-bottom: 0 !important;
    background: transparent !important;
}

.progress-module .table-default thead,
.progress-module .table thead,
.progress-module table thead {
    background: #f9fafb !important;
}

.progress-module .table-default th,
.progress-module .table th,
.progress-module table th {
    font-weight: 600 !important;
    color: #374151 !important;
    padding: 12px 15px !important;
    font-size: 13px !important;
}

.progress-module .table-default td,
.progress-module .table td,
.progress-module table td {
    padding: 15px !important;
    color: #4b5563 !important;
    vertical-align: middle !important;
}

.progress-module .table-default tbody tr,
.progress-module .table tbody tr,
.progress-module table tbody tr {
    transition: background-color 0.2s ease !important;
}

.progress-module .table-default tbody tr:hover,
.progress-module .table tbody tr:hover,
.progress-module table tbody tr:hover {
    background-color: #f9fafb !important;
}

/* Form groups and content inside cards */
.progress-module .form-group {
    margin-bottom: 15px;
}

.progress-module .form-group label {
    font-weight: 600;
    color: #374151;
}

/* ============================================
   LEADERBOARD ACCORDION - SCOPED
   ============================================ */

.progress-module .leaderboard-accordion-header {
    background: #fff !important;
    border: 1px solid #e5e7eb !important;
    border-radius: 12px 12px 0 0 !important;
    padding: 20px 24px !important;
    min-height: 74px !important;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.2s ease;
    margin-top: 40px !important;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05) !important;
}

.progress-module .leaderboard-accordion-header:hover {
    background: #f9fafb !important;
}

.progress-module .leaderboard-accordion-header h4 {
    margin: 0 !important;
    font-size: 16px !important;
    font-weight: 700 !important;
    color: #1f2937 !important;
}

.progress-module .leaderboard-accordion-header h4 i {
    margin-right: 12px;
    color: #3b82f6;
}

.progress-module .leaderboard-accordion-icon {
    transition: transform 0.3s ease;
    color: #6b7280;
    font-size: 16px;
}

.progress-module .leaderboard-accordion-icon.open {
    transform: rotate(180deg);
}

.progress-module .leaderboard-accordion-content {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.4s ease;
    border: 1px solid #e5e7eb !important;
    border-top: none !important;
    border-radius: 0 0 12px 12px !important;
    background: #fff !important;
    margin-bottom: 25px !important;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05) !important;
}

.progress-module .leaderboard-accordion-content.open {
    max-height: 3000px;
}

.progress-module .leaderboard-accordion-body {
    padding: 24px !important;
    background: #fff !important;
}

/* ============================================
   LEADERBOARD ACCORDION - SCOPED
   ============================================ */

.progress-module .leaderboard-accordion-header {
    background: #fff !important;
    border: 1px solid #e5e7eb !important;
    border-radius: 12px 12px 0 0 !important;
    padding: 20px 24px !important;
    min-height: 74px !important;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.2s ease;
    margin-top: 40px !important;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05) !important;
}

.progress-module .leaderboard-accordion-header:hover {
    background: #f9fafb !important;
}

.progress-module .leaderboard-accordion-header h4 {
    margin: 0 !important;
    font-size: 16px !important;
    font-weight: 700 !important;
    color: #1f2937 !important;
}

.progress-module .leaderboard-accordion-header h4 i {
    margin-right: 12px;
    color: #3b82f6;
}

.progress-module .leaderboard-accordion-icon {
    transition: transform 0.3s ease;
    color: #6b7280;
    font-size: 16px;
}

.progress-module .leaderboard-accordion-icon.open {
    transform: rotate(180deg);
}

.progress-module .leaderboard-accordion-content {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.4s ease;
    border: 1px solid #e5e7eb !important;
    border-top: none !important;
    border-radius: 0 0 12px 12px !important;
    background: #fff !important;
    margin-bottom: 25px !important;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05) !important;
}

.progress-module .leaderboard-accordion-content.open {
    max-height: 3000px;
}

.progress-module .leaderboard-accordion-body {
    padding: 24px !important;
    background: #fff !important;
}

/* ============================================
   LEADERBOARD TABLE - CLEAN DESIGN
   ============================================ */

.progress-module .leaderboard-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 0 !important;
    background: transparent !important;
}

.progress-module .leaderboard-table thead {
    background: #f9fafb !important;
    border-bottom: 2px solid #e5e7eb !important;
}

.progress-module .leaderboard-table th {
    padding: 14px 16px !important;
    font-weight: 600 !important;
    color: #374151 !important;
    font-size: 13px !important;
    text-align: left !important;
    border: none !important;
}

.progress-module .leaderboard-table tbody tr {
    background-color: transparent;
    border-bottom: 1px solid #f3f4f6;
    transition: background-color 0.2s ease;
}

.progress-module .leaderboard-table tbody tr:last-child {
    border-bottom: none;
}

.progress-module .leaderboard-table tbody tr:hover {
    background-color: #f9fafb !important;
}

.progress-module .leaderboard-table td {
    padding: 16px !important;
    color: #4b5563 !important;
    vertical-align: middle !important;
    border: none !important;
}

/* Current user highlight - simple underline */
.progress-module .leaderboard-table tr.current-user-student {
    background: transparent !important;
    border-left: 3px solid #3b82f6 !important;
}

.progress-module .leaderboard-table tr.current-user-student td {
    color: #1f2937 !important;
    font-weight: 600 !important;
}

/* Leaderboard specific elements */
.progress-module .rank-number {
    font-weight: 700;
    color: #6b7280;
    font-size: 15px;
}

.progress-module .level-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: #fef3c7;
    color: #92400e;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.progress-module .user-name {
    font-weight: 500;
    color: #1f2937;
}

.progress-module .points-badge {
    display: inline-block;
    padding: 6px 14px;
    background: #dbeafe;
    color: #1e40af;
    border-radius: 20px;
    font-weight: 700;
    font-size: 13px;
}

.progress-module .leaderboard-table .progress {
    height: 8px;
    margin-bottom: 6px;
    background-color: #e5e7eb;
    border-radius: 10px;
    overflow: hidden;
}

.progress-module .leaderboard-table .progress-bar {
    background-color: #3b82f6;
    height: 100%;
    transition: width 0.3s ease;
}

.progress-module .leaderboard-table .progress-text {
    font-size: 11px;
    color: #6b7280;
    font-weight: 500;
}

/* ============================================
                        PROGRESS BAR 
   ============================================ */

.progress-module .card .progress {
    height: 8px !important;
    margin-bottom: 6px !important;
    background-color: #e5e7eb !important;
    border-radius: 10px !important;
    overflow: hidden !important;
}

.progress-module .card .progress-bar {
    background-color: #3b82f6 !important;
    height: 100% !important;
    transition: width 0.3s ease !important;
}

</style>
";

// Add JavaScript for tab switching
$head_content .= "
<script>
$(document).ready(function() {
    var currentTab = '" . (isset($_GET['tab']) ? $_GET['tab'] : (isset($_GET['points_game_id']) ? 'points' : (isset($_GET['certificate_id']) ? 'certificates' : (isset($_GET['badge_id']) ? 'badges' : 'course_completion')))) . "';
    
    $('.progress-nav-tab').click(function(e) {
        e.preventDefault();
        var tab = $(this).data('tab');
        
        // Navigate to URL with tab parameter
        window.location.href = '$_SERVER[SCRIPT_NAME]?course=$course_code&tab=' + tab;
    });
    
    // Add tab parameter to ALL internal progress links
    $('a[href*=\"progress/index.php\"], a[href*=\"progress?course\"], a[href^=\"?course=' + '$course_code' + '\"]').each(function() {
        var href = $(this).attr('href');
        if (href && !href.includes('&tab=') && !href.includes('?tab=')) {
            var separator = href.includes('?') ? '&' : '?';
            $(this).attr('href', href + separator + 'tab=' + currentTab);
        }
    });
    
    // Add tab parameter to ALL forms that submit to progress
    $('form').each(function() {
        var action = $(this).attr('action');
        if (!action || action.includes('progress')) {
            // Add hidden input with tab parameter
            if ($(this).find('input[name=\"tab\"]').length === 0) {
                $(this).append('<input type=\"hidden\" name=\"tab\" value=\"' + currentTab + '\">');
            }
        }
    });
    
    // Leaderboard accordion toggle
    $('.leaderboard-accordion-header').click(function() {
        var content = $(this).next('.leaderboard-accordion-content');
        var icon = $(this).find('.leaderboard-accordion-icon');
        
        content.toggleClass('open');
        icon.toggleClass('open');
    });
});
</script>
";

$display = TRUE;
if (isset($_REQUEST['certificate_id'])) {
    $param_name = 'certificate_id';
    $element_id = $_REQUEST['certificate_id'];
    $element = 'certificate';
    $element_title = get_cert_title($element, $element_id);
    $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code", "name" => $langProgress);
}

if (isset($_REQUEST['badge_id'])) {
    $param_name = 'badge_id';
    $element_id = $_REQUEST['badge_id'];
    $element = 'badge';
    $element_title = get_cert_title($element, $element_id);
    $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code", "name" => $langProgress);
}

if (isset($_REQUEST['points_game_id'])) {
    $param_name = 'points_game_id';
    $element_id = $_REQUEST['points_game_id'];
    $element = 'points_game';
    $element_title = get_cert_title($element, $element_id);
    $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code", "name" => $langProgress);
}

if ($is_editor) {

    // Top menu
    $tool_content .= "<div class='col-sm-12'>";
    if(isset($_GET['edit'])) {
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&$param_name=$element_id", "name" => $element_title);
        $pageName = $langConfig;
    } elseif (isset($_GET['act_mod']) || isset($_GET['act_rec_mod'])) { // modify certificate activity
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&$param_name=$element_id", "name" => $element_title);
        $pageName = $langEditChange;
    } elseif(isset($_GET['add'])) { // add certificate activity
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&$param_name=$element_id", "name" => $element_title);
        $pageName = "$langAdd $langOfGradebookActivity";
    } elseif (isset($_GET['newcert'])) { // new certificate activity
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code", "name" => $langProgress);
        $pageName = $langNewCertificate;
    } elseif (isset($_GET['newbadge'])) { // new badge activity
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code", "name" => $langProgress);
        $pageName = $langNewBadge;
    } elseif (isset($_GET['newpointsgame'])) { // new points game activity
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code", "name" => $langProgress);
        $pageName = $langNewPointsGame;
    } elseif (isset($_GET['u'])) {
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&$param_name=$element_id", "name" => $element_title);
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&$param_name=$element_id&progressall=true", "name" => $langUsers);
        $pageName = uid_to_name($_GET['u']);
        if ($element == 'points_game') {
            $back_bar = action_bar(array(
                array('title' => $langBack,
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&points_game_id=$element_id&tab=points",
                      'icon' => 'fa-reply',
                      'level' => 'primary')
            ), false);
            $tool_content .= $back_bar;
        } else {
            action_bar(array(
                array('title' => $langBack,
                      'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;$param_name=$element_id&amp;progressall=true",
                      'icon' => 'fa-reply',
                      'level' => 'primary')));
        }
    } elseif (isset($_GET['progressall'])) {
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&$param_name=$element_id", "name" => $element_title);
        $pageName = "$langProgress $langsOfStudents";
        $info_title = $langRefreshProgressInfo;
        action_bar(array(
            array('title' => $langBack,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;$param_name=$element_id",
                  'icon' => 'fa-reply',
                  'level' => 'primary'),
            array('title' => $langRefreshProgress,
                  'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;$param_name=$element_id&amp;refresh=true",
                  'icon' => 'fa-refresh',
                  'link-attrs' => "title='$info_title'",
                  'level' => 'primary-label',
                  'show' => $element != 'points_game'),
            array('title' => "$langExport",
                'url' => "dumpcertificateresults.php?course=$course_code&amp;$param_name=$element_id",
                'icon' => 'fa-file-excel',
                'level' => 'primary-label',
                'show' => $element != 'points_game')
            ));

    } elseif (isset($_GET['preview'])) { // certificate preview
        cert_output_to_pdf($element_id, $uid, null, null, null, null, null, null);
    }
    $tool_content .= "</div>";
    //end of the top menu

    if (isset($_GET['vis'])) { // activate or deactivate certificate / badge
        if (has_activity($element, $element_id) > 0) {
            update_visibility($element, $element_id, $_GET['vis']);
            Session::flash('message',$langGlossaryUpdated);
            Session::flash('alert-class', 'alert-success');
        } else {
            Session::flash('message',$langNotActivated);
            Session::flash('alert-class', 'alert-warning');
        }
        $redirect_url = "modules/progress/index.php?course=$course_code".$tab_q;
        redirect_to_home_page($redirect_url);
    }
    if (isset($_POST['newCertificate']) or isset($_POST['newBadge'])) {  // add a new certificate / badge
        $v = new Valitron\Validator($_POST);
        $v->rule('required', array('title'));
        $v->labels(array(
            'title' => "$langTheField $langTitle",
        ));

        if($v->validate()) {
            $table = (isset($_POST['newCertificate'])) ? 'certificate' : 'badge';
            $icon  = $_POST['template'];
            $expires = null;
            if (isset($_POST['enablecertdeadline'])) {
                $expires = date_format(date_create_from_format('d-m-Y H:i', $_POST['enddatepicker']), 'Y-m-d H:i');
            }
            add_certificate($table, $_POST['title'], $_POST['description'], $_POST['message'], $icon, $_POST['issuer'], 0, 0, $expires);
            Session::flash('message',$langNewCertificateSuc);
            Session::flash('alert-class', 'alert-success');
            redirect_to_home_page("modules/progress/index.php?course=$course_code".$tab_q);
        } else {
          // Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
            // $tab_redirect = isset($_POST['tab']) ? '&tab=' . $_POST['tab'] : '';
            // redirect_to_home_page("modules/progress/index.php?course=$course_code&new=1$tab_redirect");

            $errors = array($langFormErrors);
            foreach ($v->errors() as $field_error) {
                foreach ($field_error as $error) {
                    $errors[] = $error;
                }
            }
            Session::flashPost()->Messages($errors, 'alert-danger');
            redirect_to_home_page("modules/progress/index.php?course=$course_code&new=1$tab_q");
        }
    } elseif (isset($_POST['newPointsGame'])) {
        $v = new Valitron\Validator($_POST);
        $v->addRule('ascendingLevels', function ($field, $value, $params, $fields) {
            $prev = null;
    
            foreach ($fields['level_item_req_points'] as $points) {
                if (!is_numeric($points)) {
                    return false;
                }
        
                if ($prev !== null && $points < $prev) {
                    return false;
                }
        
                $prev = $points;
            }
        
            return true;
        });
        $v->rule('ascendingLevels', 'level_item_req_points');
        $v->rule('required', array('title', 'startdatepicker', 'enddatepicker'));
        $v->rule('dateFormat', 'startdatepicker', 'd-m-Y H:i');
        $v->rule('dateFormat', 'enddatepicker', 'd-m-Y H:i');
        $v->addRule('endAfterStart', function($field, $value, $params, $fields) {
            if (empty($fields['startdatepicker']) || empty($value)) {
                return false;
            }
        
            $start = DateTime::createFromFormat('d-m-Y H:i', $fields['startdatepicker']);
            $end   = DateTime::createFromFormat('d-m-Y H:i', $value);
        
            if (!$start || !$end) {
                return false;
            }

            return $end > $start;
        });
        $v->rule('endAfterStart', 'enddatepicker');
        $v->labels(array(
            'title' => "$langTheField $langTitle",
            'startdatepicker' => "$langTheField $langStartDate",
            'enddatepicker' => "$langTheField $langEndDate",
            'level_item_req_points' => "$langTheField $langPointsGameLevelRequiredPoints",
        ));
        if($v->validate()) {
            $startdate = date_format(date_create_from_format('d-m-Y H:i', $_POST['startdatepicker']), 'Y-m-d H:i');
            $enddate = date_format(date_create_from_format('d-m-Y H:i', $_POST['enddatepicker']), 'Y-m-d H:i');
            $config_arr = [
                'enable_leaderboard' => !empty($_POST['enable_leaderboard']) ? 1 : 0,
                'anonymize_leaderboard' => !empty($_POST['anonymize_leaderboard']) ? 1 : 0
            ];
            add_points_game($_POST['title'], $_POST['description'], $startdate, $enddate, $_POST['level_item_name'], $_POST['level_item_req_points'], $config_arr);
            Session::flash('message',$langNewPointsGameSuc);
            Session::flash('alert-class', 'alert-success');
            redirect_to_home_page("modules/progress/index.php?course=$course_code&tab=points");
        } else {
            $errors = array($langFormErrors);
            foreach ($v->errors() as $field_error) {
                foreach ($field_error as $error) {
                    $errors[] = $error;
                }
            }
            Session::flashPost()->Messages($errors, 'alert-danger');
            redirect_to_home_page("modules/progress/index.php?course=$course_code&tab=points&new=1");
        }
    } elseif (isset($_POST['edit_element'])) { // modify certificate / badge
        $v = new Valitron\Validator($_POST);
        $v->rule('required', array('title'));
        $v->labels(array(
            'title' => "$langTheField $langTitle",
        ));
        if($v->validate()) {
            modify($element, $element_id, $_POST['title'], $_POST['description'], $_POST['message'], $_POST['template'], $_POST['issuer']);
            Session::flash('message',$langQuotaSuccess);
            Session::flash('alert-class', 'alert-success');
            redirect_to_home_page("modules/progress/index.php?course=$course_code$tab_q");
        } else {
            Session::flashPost()->Messages($langFormErrors)->Errors($v->errors());
            redirect_to_home_page("modules/progress/index.php?course=$course_code&".$element."_id=".$element_id."&edit=1");
        }
    } elseif (isset($_POST['edit_points_game'])) {
        $v = new Valitron\Validator($_POST);
        $v->addRule('ascendingLevels', function ($field, $value, $params, $fields) {
            $prev = null;
    
            foreach ($fields['level_item_req_points'] as $points) {
                if (!is_numeric($points)) {
                    return false;
                }
        
                if ($prev !== null && $points < $prev) {
                    return false;
                }
        
                $prev = $points;
            }
        
            return true;
        });
        $v->rule('ascendingLevels', 'level_item_req_points');
        $v->rule('required', array('title', 'startdatepicker', 'enddatepicker'));
        $v->rule('dateFormat', 'startdatepicker', 'd-m-Y H:i');
        $v->rule('dateFormat', 'enddatepicker', 'd-m-Y H:i');
        $v->addRule('endAfterStart', function($field, $value, $params, $fields) {
            if (empty($fields['startdatepicker']) || empty($value)) {
                return false;
            }
        
            $start = DateTime::createFromFormat('d-m-Y H:i', $fields['startdatepicker']);
            $end   = DateTime::createFromFormat('d-m-Y H:i', $value);
        
            if (!$start || !$end) {
                return false;
            }

            return $end > $start;
        });
        $v->rule('endAfterStart', 'enddatepicker');
        $v->labels(array(
            'title' => "$langTheField $langTitle",
            'startdatepicker' => "$langTheField $langStartDate",
            'enddatepicker' => "$langTheField $langEndDate",
        ));
        if($v->validate()) {
            $startdate = date_format(date_create_from_format('d-m-Y H:i', $_POST['startdatepicker']), 'Y-m-d H:i');
            $enddate = date_format(date_create_from_format('d-m-Y H:i', $_POST['enddatepicker']), 'Y-m-d H:i');
            $config_arr = [
                'enable_leaderboard' => !empty($_POST['enable_leaderboard']) ? 1 : 0,
                'anonymize_leaderboard' => !empty($_POST['anonymize_leaderboard']) ? 1 : 0
            ];
            modify_points_game($_POST['points_game_id'], $_POST['title'], $_POST['description'], $startdate, $enddate, $_POST['level_item_name'], $_POST['level_item_req_points'], $config_arr);
            Session::flash('message',$langQuotaSuccess);
            Session::flash('alert-class', 'alert-success');
            redirect_to_home_page("modules/progress/index.php?course=$course_code&tab=points");
        } else {
            $errors = array($langFormErrors);
            foreach ($v->errors() as $field_error) {
                foreach ($field_error as $error) {
                    $errors[] = $error;
                }
            }
            Session::flashPost()->Messages($errors, 'alert-danger');
            redirect_to_home_page("modules/progress/index.php?course=$course_code&points_game_id=".$_POST['points_game_id']."&edit=1");
        }
    } elseif (isset($_POST['mod_cert_activity'])) { // modify certificate activity
        modify_certificate_activity($element, $element_id, $_POST['activity_id']);
        Session::flash('message',$langQuotaSuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    } elseif (isset($_POST['mod_points_game_rec_activity'])) { // modify certificate activity
        modify_points_game_rec_activity($element_id, $_POST['activity_id']);
        Session::flash('message',$langQuotaSuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    }
        // add resources to certificate
    elseif(isset($_POST['add_assignment'])) { // add assignment activity in certificate
        add_assignment_to_certificate($element, $element_id, AssignmentEvent::ACTIVITY);
        Session::flash('message', $langQuotaSuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    } elseif (isset($_POST['add_assignment_participation'])) { // add assignment participation activity in certificate
        add_assignment_to_certificate($element, $element_id, AssignmentSubmitEvent::ACTIVITY);
        Session::flash('message', $langQuotaSuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    } elseif (isset($_POST['add_exercise'])) { // add exercise activity in certificate
        add_exercise_to_certificate($element, $element_id);
        Session::flash('message',$langQuotaSuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    } elseif (isset($_POST['add_lp'])) { // add learning path activity in certificate
        add_lp_to_certificate($element, $element_id, LearningPathEvent::ACTIVITY);
        Session::flash('message',$langQuotaSuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    } elseif (isset($_POST['add_lpduration'])) { // add learning path duration activity in certificate
        add_lp_to_certificate($element, $element_id, LearningPathDurationEvent::ACTIVITY);
        Session::flash('message',$langQuotaSuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    } elseif (isset($_POST['add_document'])) { // add document activity in certificate
        add_document_to_certificate($element, $element_id);
        Session::flash('message',$langQuotaSuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    } elseif (isset($_POST['add_multimedia'])) { // add multimedia activity in certificate
        add_multimedia_to_certificate($element, $element_id);
        Session::flash('message',$langQuotaSuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    } elseif (isset($_POST['add_poll'])) { // add poll activity in certificate
        add_poll_to_certificate($element, $element_id);
        Session::flash('message',$langQuotaSuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    } elseif (isset($_POST['add_wiki'])) { // add wiki activity in certificate
        add_wiki_to_certificate($element, $element_id);
        Session::flash('message',$langQuotaSuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    } elseif (isset($_POST['add_rec_wiki'])) {
        add_rec_wiki_to_points_game($element_id);
        Session::flash('message',$langQuotaSuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    } elseif (isset($_POST['add_ebook'])) { // add ebook activity in certificate
        add_ebook_to_certificate($element, $element_id);
        Session::flash('message',$langQuotaSuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    } elseif (isset($_POST['add_forum'])) { // add forum activity in certificate
        add_forum_to_certificate($element, $element_id);
        Session::flash('message',$langQuotaSuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    } elseif (isset($_POST['add_rec_forum'])) {
        add_rec_forum_to_points_game($element_id);
        Session::flash('message',$langQuotaSuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    } elseif (isset($_POST['add_forumtopic'])) { // add forum activity in certificate
        add_forumtopic_to_certificate($element, $element_id);
        Session::flash('message',$langQuotaSuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    } elseif (isset($_POST['add_blog'])) {
        add_blog_to_certificate($element, $element_id);
        Session::flash('message',$langQuotaSuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    } elseif (isset($_POST['add_rec_blog'])) {
        add_rec_blog_to_points_game($element_id);
        Session::flash('message',$langQuotaSuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    } elseif (isset($_POST['add_blogcomment'])) {
        add_blogcomment_to_certificate($element, $element_id);
        Session::flash('message',$langQuotaSuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    } elseif (isset($_POST['add_rec_blogcomment'])) {
        add_rec_blogcomment_to_points_game($element_id);
        Session::flash('message',$langQuotaSuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    } elseif (isset($_POST['add_participation'])) {
        add_courseparticipation_to_certificate($element, $element_id);
        Session::flash('message',$langQuotaSuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    } elseif (isset($_POST['add_gradebook'])) {
        add_gradebook_to_certificate($element, $element_id);
        Session::flash('message',$langQuotaSuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    } elseif (isset($_POST['add_coursecompletiongrade'])) {
        add_coursecompletiongrade_to_certificate($element, $element_id);
        Session::flash('message',$langQuotaSuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    } elseif (isset($_POST['add_attendance'])) {
        add_attendance_to_certificate($element, $element_id);
        Session::flash('message', $langQuotaSuccess);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
    }
    // actions
    elseif (isset($_GET['del_cert_res'])) { // delete certificate / badge activity
        if (resource_usage($element, $_GET['del_cert_res'])) { // check if resource has been used by user
            Session::flash('message',$langUsedCertRes);
            Session::flash('alert-class', 'alert-warning');
            redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
        } else { // delete it otherwise
            delete_activity($element, $element_id, $_GET['del_cert_res']);
            Session::flash('message',$langAttendanceDel);
            Session::flash('alert-class', 'alert-success');
            redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id");
        }
    } elseif (isset($_GET['del_cert'])) {  //  delete certificate
        if (delete_certificate('certificate', $_GET['del_cert'])) {
            Session::flash('message',$langGlossaryDeleted);
            Session::flash('alert-class', 'alert-success');
            redirect_to_home_page("modules/progress/index.php?course=$course_code&tab=certificates");
        } else {
            $del_cert_param = $_GET['del_cert'];
            $warning_html = <<<HTML
$langUsedCertRes 
<p class='mt-3'>$langWarningAboutUsedCert 
    <div class='col-12 d-flex justify-content-center align-items-center flex-wrap gap-2'>
        <a class='btn submitAdminBtn' href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;purge_cert=$del_cert_param'>$langDelete</a>
    </div>
</p>
HTML;
            Session::flash('message', $warning_html);
            Session::flash('alert-class', 'alert-warning');
            redirect_to_home_page("modules/progress/index.php?course=$course_code&tab=certificates");
        }
    } elseif (isset($_GET['del_badge'])) {  //  delete badge
        if (delete_certificate('badge', $_GET['del_badge'])) {
            Session::flash('message',$langGlossaryDeleted);
            Session::flash('alert-class', 'alert-success');
            redirect_to_home_page("modules/progress/index.php?course=$course_code&tab=badges");
        } else {
            $del_badge_param = $_GET['del_badge'];
            $warning_html = <<<HTML
$langUsedCertRes 
<p class='mt-3'>$langWarningAboutUsedCert 
    <div class='col-12 d-flex justify-content-center align-items-center flex-wrap gap-2'>
        <a class='btn submitAdminBtn' href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;purge_cc=$del_badge_param'>$langDelete</a>
    </div>
</p>
HTML;
            Session::flash('message', $warning_html);
            Session::flash('alert-class', 'alert-warning');
            redirect_to_home_page("modules/progress/index.php?course=$course_code&tab=badges");
        }
    } elseif (isset($_GET['reset_points_game'])) { //reset points game
        if (reset_points_game($_GET['reset_points_game'])) {
            Session::flash('message',$langPointsGameReset);
            Session::flash('alert-class', 'alert-success');
            redirect_to_home_page("modules/progress/index.php?course=$course_code&tab=points");
        }
    } elseif (isset($_GET['purge_cc'])) { // purge badge
        if (purge_certificate('badge', $_GET['purge_cc'])) {
            Session::flash('message',$langGlossaryDeleted);
            Session::flash('alert-class', 'alert-success');
        }
        redirect_to_home_page("modules/progress/index.php?course=$course_code&tab=badges");
    } elseif (isset($_GET['purge_cert'])) { // purge certificate
        if (purge_certificate('certificate', $_GET['purge_cert'])) {
            Session::flash('message',$langGlossaryDeleted);
            Session::flash('alert-class', 'alert-success');
        }
        redirect_to_home_page("modules/progress/index.php?course=$course_code&tab=certificates");
    } elseif (isset($_GET['purge_points_game'])) {
        if (purge_certificate('points_game', $_GET['purge_points_game'])) {
            Session::flash('message',$langGlossaryDeleted);
            Session::flash('alert-class', 'alert-success');
        }
        redirect_to_home_page("modules/progress/index.php?course=$course_code&tab=points");
    } elseif (isset($_GET['newcert'])) {  // create new certificate
        certificate_settings('certificate');
        $display = FALSE;
    }  elseif (isset($_GET['newbadge'])) {  // create new badge
        certificate_settings('badge');
        $display = FALSE;
    }  elseif (isset($_GET['newpointsgame'])) {  // create new points game
        points_game_settings();
        $display = FALSE;
    } elseif (isset($_GET['newcc'])) { // create course completion (special type of badge)
        add_certificate('badge', $langCourseCompletion, '', $langCourseCompletionMessage, '', q(get_config('institution')), 0, -1, null);
        Session::flash('message',$langCourseCompletionCreated);
        Session::flash('alert-class', 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&tab=course_completion");
        $display = FALSE;
    } elseif (isset($_GET['edit'])) { // edit certificate / badge / points game settings
        if($element == 'points_game') {
            points_game_settings($element_id);
        } else {
            certificate_settings($element, $element_id);
        }
        $display = FALSE;
    } elseif (isset($_GET['add']) and isset($_GET['act'])) { // insert certificate / badge / points_game activity
        insert_activity($element, $element_id, $_GET['act']);
        $display = FALSE;
    } elseif(isset($_GET['add']) and isset($_GET['act_rec'])) { // insert points_game recurrent activity
        insert_rec_activity($element_id, $_GET['act_rec']);
        $display = FALSE;
    } elseif (isset($_GET['act_mod'])) { // modify certificate / badge activity
        display_modification_activity($element, $element_id, $_GET['act_mod']);
        $display = FALSE;
    } elseif (isset($_GET['act_rec_mod'])) {
        display_modification_rec_activity($element_id, $_GET['act_rec_mod']);
        $display = FALSE;
    } elseif (isset($_GET['progressall'])) { // display users progress (teacher view)
        if ($element == 'points_game') {
            display_users_points_game_progress($element_id);
        } else {
            display_users_progress($element, $element_id);
        }
        $display = FALSE;
    } elseif (isset($_GET['u'])) { // display detailed user progress
        if ($element != "points_game") {
            display_user_progress_details($element, $element_id, $_GET['u']);
        } else {
            display_user_points_game_details($element_id, $_GET['u']);
        }
        $display = FALSE;
    } elseif (isset($_GET['refresh'])) {
        refresh_user_progress($element, $element_id);
        Session::Messages($langRefreshProgressResults, 'alert-success');
        redirect_to_home_page("modules/progress/index.php?course=$course_code&$param_name=$element_id&progressall=true");
    }
} else if ($is_course_reviewer) {
    if (isset($_GET['progressall'])) { // display users progress (course reviewer view)
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&$param_name=$element_id", "name" => $element_title);
        $pageName = "$langProgress $langsOfStudents";
        action_bar(array(
            array('title' => $langBack,
                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;$param_name=$element_id",
                'icon' => 'fa-reply',
                'level' => 'primary-label')));
        display_users_progress($element, $element_id);
        $display = FALSE;
    } elseif (isset($_GET['u'])) { // display detailed user progress
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&$param_name=$element_id", "name" => $element_title);
        $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&$param_name=$element_id&progressall=true", "name" => $langUsers);
        $pageName = uid_to_name($_GET['u']);
        action_bar(array(
            array('title' => $langBack,
                'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;$param_name=$element_id&amp;progressall=true",
                'icon' => 'fa-reply',
                'level' => 'primary-label')));
        display_user_progress_details($element, $element_id, $_GET['u']);
        $display = FALSE;
    }
} elseif (isset($_GET['u'])) { // student view

    // action_bar(array(
    //     array('title' => $langPrint,
    //           'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&$param_name=$element_id&u=".$_GET['u']."&p=1",
    //           'icon' => 'fa-print',
    //           'level' => 'primary-label',
    //           'show' => has_certificate_completed($_GET['u'], $element, $element_id) and $element == "certificate")
    // ));

        $action_bar = action_bar(array(
	        array('title' => $langPrint,
	              'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&$param_name=$element_id&u=".$_GET['u']."&p=1",
	              'icon' => 'fa-print',
	              'level' => 'primary-label',
	              'show' => $element == "certificate" && has_certificate_completed($_GET['u'], $element, $element_id))
            ));
        $tool_content .= $action_bar;

}

if (isset($display) and $display) {
    if ($is_course_reviewer) {
        if (isset($element_id)) {
            $pageName = $element_title;
            if ($is_editor && $element == 'badge') {
                $bundle_check = Database::get()->querySingle("SELECT bundle FROM badge WHERE id = ?d", $element_id);
                if ($bundle_check && $bundle_check->bundle == -1) {
                    $pageName = '';
                }
            }
            
            // display certificate settings and resources
            display_activities($element, $element_id);
            
            // Add leaderboard accordion for points games
            if ($element == 'points_game') {
                display_leaderboard_accordion($element_id);
            }
        } else { 
            // Display content based on active tab - only call the relevant display function
            if ($active_tab == 'course_completion') {
                display_course_completion();      
            } elseif ($active_tab == 'badges') {
                display_badges();
            } elseif ($active_tab == 'certificates') {
                display_certificates();
            } elseif ($active_tab == 'points') {
                display_points_games();
            }
        }
    } else {
        check_user_details($uid); // security check
        
        if (isset($element_id)) {
            check_element_enabled($element, $element_id); //security check
            if ($element == 'points_game') {
                if (isset($_GET['u'])) {
                    $back_bar = action_bar(array(
                    array('title' => $langBack,
                        'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;points_game_id=$element_id&amp;tab=points",
                        'icon' => 'fa-reply',
                        'level' => 'primary')
                    ), false);
                    $tool_content .= $back_bar;
                    display_user_points_game_details($element_id, $_GET['u']);
                } elseif (isset($_GET['progressall'])) {
                    $navigation[] = array("url" => "$_SERVER[SCRIPT_NAME]?course=$course_code&$param_name=$element_id", "name" => $element_title);
                    $pageName = "$langProgress $langsOfStudents";
                    $action_bar = action_bar(array(
                        array('title' => $langBack,
                            'url' => "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;$param_name=$element_id",
                            'icon' => 'fa-reply',
                            'level' => 'primary')
                    ));
                    $tool_content .= $action_bar;
                    display_users_points_game_progress($element_id);
                } else {
                    display_activities($element, $element_id);
    
                    // Show leaderboard accordion if enabled by teacher
                    $game_config = Database::get()->querySingle("SELECT config FROM points_game WHERE id = ?d", $element_id);
                    if ($game_config) {
                        $config = json_decode($game_config->config, true);
                        $leaderboard_enabled = isset($config['enable_leaderboard']) && $config['enable_leaderboard'];
                        
                        if ($leaderboard_enabled) {
                            display_leaderboard_accordion($element_id);
                        }
                    }
                }
            } else {
                $certificate_expiration_date = get_cert_expiration_day($element, $element_id); // security check
                if (!is_null($certificate_expiration_date) and $certificate_expiration_date < date('Y-m-d H:i:s')) {
                    redirect_to_home_page();
                }
                if (isset($_GET['p']) and $_GET['p']) { // printable view
                    if (!has_certificate_completed($uid, $element, $element_id)) { // security check
                        redirect_to_home_page();
                    }
                    cert_output_to_pdf($element_id, $uid, null, null, null, null, null, null);
                } else {
                    if (!is_cert_visible($element, $element_id)) { // security check
                        redirect_to_home_page();
                    }
                    $pageName = $element_title;
                    // display detailed user progress
                    display_user_progress_details($element, $element_id, $uid);
                }
            }
        } else {
            // Display content based on active tab - same as admin view
            if (($active_tab == 'course_completion') && is_course_completion_active()) {
                display_course_completion();
            } elseif ($active_tab == 'points') {
                display_points_games();
            } elseif ($active_tab == 'certificates') {
                display_certificates();    
            } else {
                display_badges();
            }
            
        }
    }
}

// CLOSE progress-module wrapper
$tool_content .= "</div>";

draw($tool_content, 2, null, $head_content);