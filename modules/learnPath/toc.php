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

$require_current_course = TRUE;
if (isset($_GET['unit'])) {
    require_once '../../include/init.php';
    require_once 'include/constants.php';
} else {
    require_once '../../include/baseTheme.php';
}

//require_once '../../include/init.php';
//require_once 'include/constants.php';
require_once 'include/lib/learnPathLib.inc.php';
require_once 'include/lib/fileDisplayLib.inc.php';


$theme_id = $_SESSION['theme_options_id'] ?? get_config('theme_options_id');
$theme_options = Database::get()->querySingle("SELECT * FROM theme_options WHERE id = ?d", $theme_id);

echo "<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN' 'http://www.w3.org/TR/html4/loose.dtd'>
<html>
<head><title>-</title>
    <meta http-equiv='Content-Type' content='text/html; charset=$charset'>
    <!-- jQuery -->
    <script type='text/javascript' src='{$urlAppend}js/jquery" . JQUERY_VERSION . ".min.js'></script>
    
    <!-- Latest compiled and minified JavaScript -->
    <script src='{$urlAppend}js/bootstrap.bundle.min.js'></script>

    <!-- DataTables and Checkitor js-->
    <script src='{$urlAppend}js/jquery.dataTables.min.js'></script>
        
    <!-- Latest compiled and minified css -->
    <link rel='stylesheet' href='{$urlAppend}template/modern/css/bootstrap.min.css'>

    <link rel='stylesheet' href='{$urlAppend}template/modern/css/fonts_all/typography.css?" . time() . "'>

    <!-- Font Awesome - A font of icons -->
    <link href='{$urlAppend}template/modern/css/font-awesome-6.4.0/css/all.css' rel='stylesheet'>  
        
    <link href='{$urlAppend}template/modern/css/lp.css?".time()."' rel='stylesheet' type='text/css' />

    <link rel='stylesheet' type='text/css' href='{$urlAppend}template/modern/css/default.css?".time()."'>";
    if($theme_id > 0){
        echo "<link rel='stylesheet' type='text/css' href='{$urlAppend}courses/theme_data/$theme_id/style_str.css?".time()."'/>";
    }

    echo "<script>
    
</script>
</head>
<body>
<div class=' menu_left_learningPath'>";

if ($uid) {
    $uidCheckString = "AND UMP.`user_id` = " . intval($uid);
} else {
    // anonymous
    $uidCheckString = "AND UMP.`user_id` IS NULL ";
}

//  -------------------------- learning path list content ----------------------------
$sql = "SELECT M.*, LPM.*, A.`path`, UMP.`lesson_status`, UMP.`credit`
        FROM (`lp_module` AS M,
             `lp_rel_learnPath_module` AS LPM)
        LEFT JOIN `lp_asset` AS A ON M.`startAsset_id` = A.`asset_id`
        LEFT JOIN `lp_user_module_progress` AS UMP
           ON UMP.`learnPath_module_id` = LPM.`learnPath_module_id`
           AND UMP.`attempt` = ?d
           " . $uidCheckString . "
        WHERE M.`module_id` = LPM.`module_id`
          AND LPM.`learnPath_id` = ?d
          AND M.`course_id` = ?d
        ORDER BY LPM.`rank` ASC";
$result = Database::get()->queryArray($sql, $_SESSION['lp_attempt'], $_SESSION['path_id'], $course_id);

if (count($result) == 0) {
    echo "<div class='alert alert-warning'><i class='fa-solid fa-triangle-exclamation fa-lg'></i><span>$langNoModule</span></div>";
    exit();
}

$extendedList = array();
$modar = array();
foreach ($result as $list) {
    $modar['module_id'] = $list->module_id;
    $modar['course_id'] = $list->course_id;
    if (empty($list->name) and $list->contentType == 'LINK') {
        $modar['name'] = $list->path;
    } else {
        $modar['name'] = $list->name;
    }
    $modar['comment'] = $list->comment;
    $modar['accessibility'] = $list->accessibility;
    $modar['startAsset_id'] = $list->startAsset_id;
    $modar['contentType'] = $list->contentType;
    $modar['launch_data'] = $list->launch_data;
    $modar['learnPath_module_id'] = $list->learnPath_module_id;
    $modar['learnPath_id'] = $list->learnPath_id;
    $modar['lock'] = $list->lock;
    $modar['visible'] = $list->visible;
    $modar['specificComment'] = $list->specificComment;
    $modar['rank'] = $list->rank;
    $modar['parent'] = $list->parent;
    $modar['raw_to_pass'] = $list->raw_to_pass;
    $modar['path'] = $list->path;
    $modar['lesson_status'] = $list->lesson_status;
    $modar['credit'] = $list->credit;
    $extendedList[] = $modar;
}

// get LP name and comments
$q = Database::get()->querySingle("SELECT name, comment FROM lp_learnPath 
                                WHERE learnpath_id = ?d AND course_id = ?d", $modar['learnPath_id'], $modar['course_id']);
$lp_name = $q->name;
$lp_comment = $q->comment;

// build the array of modules
// build_element_list return a multi-level array, where children is an array with all nested modules
// build_display_element_list return an 1-level array where children is the deep of the module

$flatElementList = build_display_element_list(build_element_list($extendedList, 'parent', 'learnPath_module_id'));
$is_blocked = false;

// look for maxDeep
$maxDeep = 1; // used to compute colspan of <td> cells
for ($i = 0; $i < sizeof($flatElementList); $i++) {
    if ($flatElementList[$i]['children'] > $maxDeep) {
        $maxDeep = $flatElementList[$i]['children'];
    }
}

$unitParam = isset($_GET['unit'])? "&amp;unit=$_GET[unit]": '';

// -------------------------- learning path list header ----------------------------
echo "<ul><li class='category'>" . q($lp_name) . "</li>";
// ----------------------- LEARNING PATH LIST DISPLAY ---------------------------------
foreach ($flatElementList as $module) {
    //-------------visibility-----------------------------
    if ($module['visible'] == 0 || $is_blocked) {
        if ($is_editor) {
            $style = " style='color:grey;'";
        } else {
            continue; // skip the display of this file
        }
    } else {
        $style = "";
    }

    // indent a child based on label ownership
    $marginIndent = 0;
    for ($i = 0; $i < $module['children']; $i++) {
        $marginIndent += 10;
    }

    if ($module['contentType'] == CTLABEL_) { // chapter head
        echo "<li style=\"margin-left: " . $marginIndent . "px;\"><font " . $style . " style=\"font-weight: normal\">" . htmlspecialchars($module['name']) . "</font></li>";
    } else { // module
        if ($module['contentType'] == CTEXERCISE_) {
            $moduleImg = "fa-edit";
        } else if ($module['contentType'] == CTLINK_) {
            $moduleImg = "fa-link";
        } else if ($module['contentType'] == CTCOURSE_DESCRIPTION_) {
            $moduleImg = "fa-info-circle";
        } else if ($module['contentType'] == CTDOCUMENT_) {
            $moduleImg = choose_image(basename($module['path']));
        } else if ($module['contentType'] == CTSCORM_ || $module['contentType'] == CTSCORMASSET_) { // eidika otan einai scorm module, deixnoume allo eikonidio pou exei na kanei me thn proodo
            $moduleImg = "fa-file-code-o";
        } else if ($module['contentType'] == CTMEDIA_ || $module['contentType'] == CTMEDIALINK_) {
            $moduleImg = "fa-film";
        } else {
            $moduleImg = choose_image(basename($module['path']));
        }

        $contentType_alt = selectAlt($module['contentType']);

        // eikonidio pou deixnei an perasame h oxi to sygkekrimeno module
        unset($imagePassed);
        if ($module['credit'] == 'CREDIT' || $module['lesson_status'] == 'COMPLETED' || $module['lesson_status'] == 'PASSED') {
            if ($module['contentType'] == CTSCORM_ || $module['contentType'] == CTSCORMASSET_) {
                $moduleImg = 'fa-file-code-o';
                $imagePassed = "<span style='color:green'>".icon('fa-check text-success')."</span>";
            } else {
                $imagePassed = "<span style='color:green'>".icon('fa-check text-success', $module['lesson_status'])."</span>";
            }
        }

        if (($module['contentType'] == CTSCORM_ || $module['contentType'] == CTSCORMASSET_) && $module['lesson_status'] == 'FAILED') {
            $moduleImg = 'fa-file-code-o';
            $imagePassed = "<span style='color:red'>".icon('fa-xmark text-danger')."</span>";
        }

        echo "<li style=\"margin-left: " . $marginIndent . "px;\">" . icon($moduleImg);

        // emphasize currently displayed module or not
        if ($_SESSION['lp_module_id'] == $module['module_id']) {
            echo "&nbsp;<em>" . htmlspecialchars($module['name']) . "</em>";
        } else {
            echo "&nbsp;<a href='navigation/viewModule.php?course=$course_code&amp;viewModule_id=$module[module_id]$unitParam'" . $style . " target='scoFrame'>" . htmlspecialchars($module['name']) . "</a>";
        }
        if (isset($imagePassed)) {
            echo "&nbsp;&nbsp;" . $imagePassed;
        }
        echo "</li>";

        if (($module['lock'] == 'CLOSE') && ($module['credit'] != 'CREDIT' || ($module['lesson_status'] != 'COMPLETED' && $module['lesson_status'] != 'PASSED'))) {
            $is_blocked = true;
        }
    }
} // end of foreach

echo "</ul></div></body></html>";
