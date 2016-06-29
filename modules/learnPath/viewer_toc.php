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

/* ===========================================================================
  viewer_toc.php
  @authors list: Thanos Kyritsis <atkyritsis@upnet.gr>

  based on Claroline version 1.7 licensed under GPL
  copyright (c) 2001, 2006 Universite catholique de Louvain (UCL)

  original file: navigation/tableOfContent.php Revision: 1.30

  Claroline authors: Piraux Sebastien <pir@cerdecam.be>
  Lederer Guillaume <led@cerdecam.be>
  ==============================================================================
  @Description: Script for displaying a navigation bar to the users when
  they are browsing a learning path

  @Comments:
  ==============================================================================
 */

$require_current_course = TRUE;
require_once '../../include/init.php';
require_once 'include/lib/learnPathLib.inc.php';
require_once 'include/lib/fileDisplayLib.inc.php';
require_once 'modules/gradebook/functions.php';
// The following is added for statistics purposes
require_once 'include/action.php';
$action = new action();
$action->record(MODULE_ID_LP);
/* * *********************************** */

if (isset($_GET['unit'])) {
    $unitParam = "&amp;unit=$_GET[unit]";
    $returl = $urlAppend . "modules/units/?course=$course_code&amp;id=$_GET[unit]";
} else {
    $unitParam = '';
    $returl = "navigation/viewModule.php?course=$course_code&amp;go=" .
        ($is_editor ? 'learningPathAdmin' : 'learningPath');
}

if ($uid) {
    $uidCheckString = "AND UMP.`user_id` = $uid";
} else { // anonymous
    $uidCheckString = "AND UMP.`user_id` IS NULL ";
}

// get the list of available modules
$sql = "SELECT MIN(LPM.`learnPath_module_id`) AS learnPath_module_id,
               MIN(LPM.`parent`) AS parent,
               MIN(LPM.`lock`) AS `lock`,
               MIN(M.`module_id`) AS module_id,
               MIN(M.`contentType`) AS contentType,
               MIN(M.`name`) AS name,
               MIN(UMP.`lesson_status`) AS lesson_status,
               MIN(UMP.`raw`) AS `raw`,
               MIN(UMP.`scoreMax`) AS scoreMax,
               MIN(UMP.`credit`) AS credit,
               MIN(A.`path`) AS path
       FROM (`lp_rel_learnPath_module` AS LPM,
             `lp_module` AS M)
   LEFT JOIN `lp_user_module_progress` AS UMP
          ON UMP.`learnPath_module_id` = LPM.`learnPath_module_id`
           " . $uidCheckString . "
   LEFT JOIN `lp_asset` AS A
          ON M.`startAsset_id` = A.`asset_id`
       WHERE LPM.`module_id` = M.`module_id`
         AND LPM.`learnPath_id` = ?d
         AND LPM.`visible` = 1
         AND LPM.`module_id` = M.`module_id`
         AND M.`course_id` = ?d
    GROUP BY LPM.`module_id`
    ORDER BY LPM.`rank`";
$moduleList = Database::get()->queryArray($sql, $_SESSION['path_id'], $course_id);

$extendedList = array();
$modar = array();
foreach ($moduleList as $module) {
    $modar['name'] = $module->name;
    $modar['contentType'] = $module->contentType;
    $modar['learnPath_module_id'] = $module->learnPath_module_id;
    $modar['parent'] = $module->parent;
    $modar['path'] = $module->path;
    $modar['lock'] = $module->lock;
    $modar['module_id'] = $module->module_id;
    $modar['lesson_status'] = $module->lesson_status;
    $modar['raw'] = $module->raw;
    $modar['scoreMax'] = $module->scoreMax;
    $modar['credit'] = $module->credit;
    $extendedList[] = $modar;
}

// build the array of modules
// build_element_list return a multi-level array, where children is an array with all nested modules
// build_display_element_list return an 1-level array where children is the deep of the module
$flatElementList = build_display_element_list(build_element_list($extendedList, 'parent', 'learnPath_module_id'));

$is_blocked = false;
$moduleNb = 0;

// get the name of the learning path
$sql = "SELECT `name`
          FROM `lp_learnPath`
         WHERE `learnPath_id` = ?d
           AND `course_id` = ?d";
$lpName = Database::get()->querySingle($sql, $_SESSION['path_id'], $course_id)->name;

$previous = ""; // temp id of previous module, used as a buffer in foreach
$previousModule = ""; // module id that will be used in the previous link
$nextModule = ""; // module id that will be used in the next link

foreach ($flatElementList as $module) {
    // spacing col
    if (!$is_blocked or $is_editor) {
        if ($module['contentType'] != CTLABEL_) { // chapter head
            // bold the title of the current displayed module
            if ($_SESSION['lp_module_id'] == $module['module_id']) {
                $previousModule = $previous;
            }
            // store next value if user has the right to access it
            if ($previous == $_SESSION['lp_module_id']) {
                $nextModule = $module['module_id'];
            }
        }
        // a module ALLOW access to the following modules if
        // document module : credit == CREDIT || lesson_status == 'completed'
        // exercise module : credit == CREDIT || lesson_status == 'passed'
        // scorm module : credit == CREDIT || lesson_status == 'passed'|'completed'

        if (($module['lock'] == 'CLOSE')
                && ( $module['credit'] != 'CREDIT'
                || ( $module['lesson_status'] != 'COMPLETED' && $module['lesson_status'] != 'PASSED'))) {
            $is_blocked = true; // following modules will be unlinked
        }
    }

    if ($module['contentType'] != CTLABEL_) {
        $moduleNb++; // increment number of modules used to compute global progression except if the module is a title
    }

// used in the foreach the remember the id of the previous module_id
    // don't remember if label...
    if ($module['contentType'] != CTLABEL_) {
        $previous = $module['module_id'];
    }
} // end of foreach ($flatElementList as $module)

$prevNextString = "";
// display previous and next links only if there is more than one module
if ($moduleNb > 1) {

    if ($previousModule != '') {
        $prevNextString .= '<li class="prevnext"><a href="navigation/viewModule.php?course=' . $course_code . '&amp;viewModule_id=' . $previousModule . $unitParam . '" target="scoFrame"><span class="fa fa-arrow-circle-left fa-lg"></span> </a></li>';
    } else {
        $prevNextString .= "<li class='prevnext'><a href='#' class='inactive'><span class='fa fa-arrow-circle-left'></span></a></li>";
    }
    if ($nextModule != '') {
        $prevNextString .= '<li class="prevnext"><a href="navigation/viewModule.php?course=' . $course_code . '&amp;viewModule_id=' . $nextModule . $unitParam . '" target="scoFrame"><span class="fa fa-arrow-circle-right fa-lg"></span></a></li>';
    } else {
        $prevNextString .= "<li class='prevnext'><a href='#' class='inactive'><span class='fa fa-arrow-circle-right'></span></a></li>";
    }
}


echo "<!DOCTYPE HTML>
<html>
<head>
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8'>
    <title>-</title>
    <!-- jQuery -->
    <script type='text/javascript' src='{$urlAppend}js/jquery-2.1.1.min.js'></script>

    <!-- Latest compiled and minified JavaScript -->
    <script src='{$urlAppend}template/default/js/bootstrap.min.js'></script>

    <script type='text/javascript' src='{$urlAppend}js/jquery.cookie.js'></script>

    <!-- Our javascript -->
    <script type='text/javascript' src='{$urlAppend}template/default/js/main.js'></script>

    <!-- SlimScroll -->
    <script src='{$urlAppend}js/jquery.slimscroll.min.js'></script>

    <!-- Latest compiled and minified CSS -->
    <link rel='stylesheet' href='{$urlAppend}template/default/CSS/bootstrap-custom.css'>

    <!-- Font Awesome - A font of icons -->
    <link href='{$urlAppend}template/default/CSS/font-awesome-4.2.0/css/font-awesome.css' rel='stylesheet'>

    <style>
        .navbar-inverse .navbar-nav > li > a {color: whitesmoke;}
        .navbar-inverse .navbar-nav > li > a.inactive, .navbar-inverse .navbar-nav > li > a.inactive:hover, .navbar-inverse .navbar-nav > li > a.inactive:focus {color: #9d9d9d; cursor: default;}
        .navbar-inverse .navbar-nav > li > a:hover, .navbar-inverse .navbar-nav > li > a:focus { color: #9BCCF7; }
        a#leftTOCtoggler {
            color: whitesmoke;
        }
        .navbar-collapse.collapse {
        display: block!important;
        }

        .navbar-nav>li, .navbar-nav {
        float: left !important;
        }

        .navbar-nav.navbar-right:last-child {
        margin-right: -15px !important;
        }

        .navbar-right {
        float: right!important;
        }

        .progressbar-plr{
            margin-top: 15px !important;
        }
        #navigation-btns li a, #navigation-btns li a span{
            padding-top: 0px !important;
            padding-bottom: 0px !important;
        }
        #navigation-btns li, #navigation-btns li a i{
            height: 50px;
            line-height: 50px;
        }
        #navigation-btns li a{
            padding-right: 0px;
            padding-left: 0px;
        }
        #navigation-btns li:nth-child(1){
            padding-left: 35px;
            padding-right: 5px;
        }
        #navigation-btns li:nth-child(2){
            padding-left: 5px;
            padding-right: 0px;
        }
        #navigation-btns #close-btn{
            padding-right:20px;
            padding-left:20px;
            border-left: 2px groove #999;
            margin-left: 35px;
        }
        #navigation-btns #close-btn span{
            font-size:16px;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        }
        #leftTOCtoggler{
            margin-top: 7px;
        }
        #toc_logo{
            padding-left: 10px;
            margin-left: 10px;
            border-left: 2px groove #999;
        }
    </style>
    <script type='text/javascript'>
    /* <![CDATA[ */

    $(document).ready(function() {
        var leftTOChiddenStatus = 0;
        if ($.cookie('leftTOChiddenStatus') !== undefined) {
            leftTOChiddenStatus = $.cookie('leftTOChiddenStatus');
        }
        var fs = window.parent.document.getElementById('colFrameset');
        var fsJQe = $('#colFrameset', window.parent.document);
        if (leftTOChiddenStatus != fsJQe.hasClass('hidden')) {
            fsJQe.toggleClass('hidden');
            if (fsJQe.hasClass('hidden')) {
                fs.cols = '0, *';
            } else {
                fs.cols = '200, *';
            }
        }
        $('#leftTOCtoggler').on('click', function() {
            var fs = window.parent.document.getElementById('colFrameset');
            var fsJQe = $('#colFrameset', window.parent.document);

            fsJQe.toggleClass('hidden');
            if (fsJQe.hasClass('hidden')) {
                fs.cols = '0, *';
                $.cookie('leftTOChiddenStatus', 1, { path: '/' });
            } else {
                fs.cols = '200, *';
                $.cookie('leftTOChiddenStatus', 0, { path: '/' });
            }
        });
    });

    /* ]]> */
    </script>
</head>
<body>

    <nav class='navbar navbar-inverse navbar-static-top' role='navigation'>
            <div class='container-fluid'>
                <div class='navbar-header col-xs-2'>
                  <a id='leftTOCtoggler' class='btn pull-left'><span class='fa fa-bars fa-lg'></span></a>
                  <a id='toc_logo' class='navbar-brand hidden-xs' href='#'><img class='img-responsive' style='height:20px;' src='{$themeimg}/eclass-new-logo-small.png' alt='Logo'></a>
                </div>
                <div class='navbar-header col-xs-10 pull-right'>
                    <ul id='navigation-btns' class='nav navbar-nav navbar-right '>
                        $prevNextString
                        <li id='close-btn'><a href='$returl' target='_top'><span class='fa fa-times fa-lg'>&nbsp;<span class='hidden-xs'>$langLogout</span></span></a></li>
                    </ul>
                    <div class='pull-right progressbar-plr'>";

                         if ($uid) {
                            $path_id = (int) $_SESSION['path_id'];
                            $lpProgress = get_learnPath_progress($path_id, $uid);
                            update_gradebook_book($uid, $path_id, $lpProgress/100, GRADEBOOK_ACTIVITY_LP);
                            echo disp_progress_bar($lpProgress, 1);
                        }
echo "</div>
                </div>
            </div>
    </nav>
    </body>
</html>";

