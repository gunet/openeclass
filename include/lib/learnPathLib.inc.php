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

/* ===========================================================================
  learnPathLib.inc.php

  based on Claroline version 1.7 licensed under GPL
  copyright (c) 2001, 2006 Universite catholique de Louvain (UCL)

  original file: learnPath.lib.inc.php Revision: 1.41.2.2

  Claroline authors: Piraux Sebastien <pir@cerdecam.be>
  Lederer Guillaume <led@cerdecam.be>
  ==============================================================================
  @Description: This functions library is used by most of the pages of the
  learning path tool.
  ==============================================================================
 */

require_once 'modules/progress/LearningPathEvent.php';
require_once 'modules/progress/LearningPathDurationEvent.php';
require_once 'modules/analytics/LpAnalyticsEvent.php';
require_once 'include/lib/mediaresource.factory.php';

/*
 * content type
 */
define('CTCLARODOC_', 'CLARODOC');
define('CTDOCUMENT_', 'DOCUMENT');
define('CTEXERCISE_', 'EXERCISE');
define('CTSCORM_', 'SCORM');
define('CTSCORMASSET_', 'SCORM_ASSET');
define('CTLABEL_', 'LABEL');
define('CTCOURSE_DESCRIPTION_', 'COURSE_DESCRIPTION');
define('CTLINK_', 'LINK');
define('CTMEDIA_', 'MEDIA');
define('CTMEDIALINK_', 'MEDIALINK');

/*
 * mode used by {@link commentBox($type, $mode)} and {@link nameBox($type, $mode)}
 */
define('DISPLAY_', 1);
define('UPDATE_', 2);
define('UPDATENOTSHOWN_', 4);
define('DELETE_', 3);
define('ASSET_', 1);
define('MODULE_', 2);
define('LEARNINGPATH_', 3);
define('LEARNINGPATHMODULE_', 4);

const SCORM_TIME_MASK = "/^([0-9]{2,4}):([0-9]{2}):([0-9]{2}).?([0-9]?[0-9]?)$/";
const SCORM_2004_TIME_MASK = "/^PT(([0-9]{1,2})H)?(([0-9]{1,2})M)?(([0-9]{1,2}).?([0-9]?[0-9]?)S)?$/";

/*
 * This function is used to display comments of module or learning path with admin links if needed.
 * Admin links are 'edit' and 'delete' links.
 *
 * @param string $type MODULE_ , LEARNINGPATH_ , LEARNINGPATHMODULE_
 * @param string $mode DISPLAY_ , UPDATE_ , DELETE_
 *
 * @author Thanos Kyritsis <atkyritsis@upnet.gr>
 * @author Piraux Sebastien <pir@cerdecam.be>
 * @author Lederer Guillaume <led@cerdecam.be>
 */

function commentBox($type, $mode) {
    global $is_editor, $langModify, $langSubmit,
    $langAdd, $langConfirmDelete, $langDefaultLearningPathComment,
    $langDefaultModuleComment, $langDefaultModuleAddedComment, $langDelete, $course_code,
    $course_id;

    // will be set 'true' if the comment has to be displayed
    $dsp = false;
    $output = "";

    // those vars will be used to build sql queries according to the comment type
    switch ($type) {
        case MODULE_ :
            $defaultTxt = $langDefaultModuleComment;
            $col_name = 'comment';
            $tbl_name = 'lp_module';
            if (isset($_REQUEST['module_id'])) {
                $module_id = $_REQUEST['module_id'];
            } else {
                $module_id = $_SESSION['lp_module_id'];
            }
            $where_cond = "`module_id` = " . intval($module_id) . " AND `course_id` = " . intval($course_id);
            break;
        case LEARNINGPATH_ :
            $defaultTxt = $langDefaultLearningPathComment;
            $col_name = 'comment';
            $tbl_name = 'lp_learnPath';
            $where_cond = '`learnPath_id` = ' . intval($_SESSION['path_id']) . " AND `course_id` = " . intval($course_id);
            break;
        case LEARNINGPATHMODULE_ :
            $defaultTxt = $langDefaultModuleAddedComment;
            $col_name = 'specificComment';
            $tbl_name = 'lp_rel_learnPath_module';
            $where_cond = "`learnPath_id` = " . intval($_SESSION['path_id']) . " AND `module_id` = " . intval($_SESSION['lp_module_id']);
            break;
    }

    // update mode
    // allow to choose between
    // - update and show the comment and the pencil and the delete cross (UPDATE_)
    // - update and nothing displayed after form sent (UPDATENOTSHOWN_)
    if (( $mode == UPDATE_ || $mode == UPDATENOTSHOWN_ ) && $is_editor) {
        if (isset($_POST['insertCommentBox'])) {
            Database::get()->query("UPDATE $tbl_name SET $col_name = ?s WHERE " . $where_cond . "", $_POST['insertCommentBox']);

            if ($mode == UPDATE_) {
                $dsp = true;
            } else if ($mode == UPDATENOTSHOWN_) {
                $dsp = false;
            }
        } else { // display form
            // get info to fill the form in
            $sql = "SELECT `" . $col_name . "`
                       FROM `" . $tbl_name . "`
                      WHERE " . $where_cond;
            $oldComment = Database::get()->querySingle($sql)->$col_name;

            $output .= "<div class='card-body'><form method='POST' action='$_SERVER[SCRIPT_NAME]?course=$course_code'>
                <textarea class='form-control' name='insertCommentBox' rows='3'>$oldComment</textarea><br>
                <input type='hidden' name='cmd' value='update$col_name' />
                <input class='btn submitAdminBtn' type='submit' value=$langSubmit /></form></div>";
        }
    }

    // delete mode
    if ($mode == DELETE_ && $is_editor) {
        $sql = "UPDATE `" . $tbl_name . "` SET `" . $col_name . "` = '' WHERE " . $where_cond;
        Database::get()->query($sql);
        $dsp = TRUE;
    }

    // display mode only or display was asked by delete mode or update mode
    if ($mode == DISPLAY_ || $dsp == TRUE) {
        $sql = "SELECT `" . $col_name . "` FROM `" . $tbl_name . "` WHERE " . $where_cond;

        $result = Database::get()->querySingle($sql);
        $currentComment = ($result && !empty($result->$col_name)) ? $result->$col_name : false;

        // display nothing if this is default comment and not an admin
        if (($currentComment == $defaultTxt) && !$is_editor) {
            return $output;
        }

        $output .= "<div class='card-body'>";
        if (empty($currentComment)) {
            // if no comment and user is admin : display link to add a comment
            if ($is_editor) {
                $output .= '<a href="' . $_SERVER['SCRIPT_NAME'] . '?course=' . $course_code . '&amp;cmd=update' . $col_name . '">' . $langAdd . '</a>';
            }
        } else {
            $output .=  standard_text_escape($currentComment);
            if ($is_editor) {
                $output .= "&nbsp;&nbsp;&nbsp;" . icon('fa-edit', $langModify, $_SERVER['SCRIPT_NAME'] . '?course=' . $course_code . '&amp;cmd=update' . $col_name . "");
                $output .= "&nbsp;&nbsp;&nbsp";
                $output .= icon('fa-xmark link-delete', $langDelete, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;cmd=del$col_name", 'onClick="javascript:if(!confirm(\'' . clean_str_for_javascript($langConfirmDelete) . '\')) return false;"');
            }
        }
        $output .= "</div>";
    }

    return $output;
}

/*
 * @brief: This function is used to display name of module or learning path with admin links if needed
 *
 * @param string $type MODULE_ , LEARNINGPATH_
 * @param string $mode display(DISPLAY_) or update(UPDATE_) mode, no delete for a name
 * @param string $formlabel label for displaying in the form
 *
 * @author Thanos Kyritsis <atkyritsis@upnet.gr>
 * @author Piraux Sebastien <pir@cerdecam.be>
 * @author Lederer Guillaume <led@cerdecam.be>
 */

function nameBox($type, $mode, $formlabel = FALSE) {
    global $is_editor, $langModify, $langErrorNameAlreadyExists, $course_code, $course_id;

    // $dsp will be set 'true' if the comment has to be displayed
    $dsp = FALSE;
    $output = "";

    // those vars will be used to build sql queries according to the name type
    switch ($type) {
        case MODULE_ :
            $col_name = 'name';
            $tbl_name = "lp_module";
            $where_cond = '`module_id` = ' . intval($_SESSION['lp_module_id']);
            break;
        case LEARNINGPATH_ :
            $col_name = 'name';
            $tbl_name = "lp_learnPath";
            $where_cond = '`learnPath_id` = ' . intval($_SESSION['path_id']);
            break;
    }

    // update mode
    if ($mode == UPDATE_ && $is_editor) {

        if (isset($_POST['newName']) && !empty($_POST['newName'])) {
            $num = Database::get()->querySingle("SELECT COUNT(`" . $col_name . "`) AS count
                                 FROM `" . $tbl_name . "`
                                WHERE `" . $col_name . "` = ?s
                                  AND !(" . $where_cond . ") AND `course_id` = ?d", $_POST['newName'], $course_id)->count;

            if ($num == 0) {  // name doesn't already exists
                Database::get()->query("UPDATE `" . $tbl_name . "`
                            SET `" . $col_name . "` = ?s
                            WHERE " . $where_cond . " AND `course_id` = ?d", $_POST['newName'], $course_id);
                $dsp = TRUE;
            } else {
                $output .= $langErrorNameAlreadyExists . '<br />';
                $dsp = TRUE;
            }
        } else { // display form
            $oldName = Database::get()->querySingle("SELECT `name`
                    FROM `" . $tbl_name . "`
                    WHERE " . $where_cond . " AND `course_id` = ?d", $course_id)->name;

            $output .= '<div class="card-body">';
            $output .= '<form method="POST" action="' . $_SERVER['SCRIPT_NAME'] . '?course=' . $course_code . '">';

            if ($formlabel != FALSE) {
                $output .= '<div class="col-12"><div class="d-flex justify-content-start align-items-center gap-2">'
                        . '<input class="form-control max-input-width mt-0" type="text" name="newName" size="50" maxlength="255" value="' . htmlspecialchars($oldName) . '">'
                        . '<button class="btn submitAdminBtn" type="submit" value="'.$langModify.'">'.$langModify.'</button>'
                        . '</div></div>'
                        . '<input type="hidden" name="cmd" value="updateName" />'
                        . '</form>';
            }
            $output .= '</div>';
        }
    }

    // display if display mode or asked by the update
    if ($mode == DISPLAY_ || $dsp == true) {
        $sql = "SELECT `name` FROM `" . $tbl_name . "` WHERE " . $where_cond . " AND `course_id` = ?d";
        $result = Database::get()->querySingle($sql, $course_id);

        $currentName = ($result && !empty($result->name)) ? $result->name : false;
        $output .= "<div class='card-header border-0 d-flex justify-content-between align-items-center gap-3 flex-wrap'><h3>" . q($currentName);
        if ($is_editor) {
            $output .= '&nbsp;&nbsp;&nbsp;'.icon('fa-edit', $langModify, $_SERVER['SCRIPT_NAME'] . '?course=' . $course_code . '&amp;cmd=updateName');
        }
        $output .= "</h3></div>";
    }

    return $output;
}

/*
 * This function is used to display the correct image in the modules lists
 * It looks for the correct type in the array, and return the corresponding image name if found
 * else it returns a default image
 *
 * @param  string $contentType type of content in learning path
 * @return string name of the image with extension
 *
 * @author Thanos Kyritsis <atkyritsis@upnet.gr>
 * @author Piraux Sebastien <pir@cerdecam.be>
 * @author Lederer Guillaume <led@cerdecam.be>
 */

function selectImage($contentType) {

    $imgList[CTDOCUMENT_] = "fa-folder-open";
    $imgList[CTCLARODOC_] = "fa-folder-open";
    $imgList[CTEXERCISE_] = "fa-square-pen";
    $imgList[CTSCORM_] = "fa-square-pen";
    $imgList[CTSCORMASSET_] = "fa-square-pen";
    $imgList[CTLINK_] = "fa-link";
    $imgList[CTCOURSE_DESCRIPTION_] = "fa-info-circle";
    $imgList[CTMEDIA_] = "fa-film";
    $imgList[CTMEDIALINK_] = "fa-film";

    if (array_key_exists($contentType, $imgList)) {
        return $imgList[$contentType];
    }

    return "fa-folder-open";
}

/*
 * This function is used to display the correct alt text for image in the modules lists.
 * Mainly used at the same time than selectImage() to add an alternate text on the image.
 *
 * @param  string $contentType type of content in learning path
 * @return string text for the alt
 * @author Piraux Sebastien <pir@cerdecam.be>
 * @author Lederer Guillaume <led@cerdecam.be>
 */

function selectAlt($contentType) {
    global $langDoc, $langExercise, $langAltScorm, $langOther,
           $langLinks, $langDescription, $langVideo;

    $altList[CTDOCUMENT_] = $langDoc;
    $altList[CTCLARODOC_] = $langDoc;
    $altList[CTEXERCISE_] = $langExercise;
    $altList[CTSCORM_] = $langAltScorm;
    $altList[CTSCORMASSET_] = $langAltScorm;
    $altList[CTLINK_] = $langLinks;
    $altList[CTCOURSE_DESCRIPTION_] = $langDescription;
    $altList[CTMEDIA_] = $langVideo;
    $altList[CTMEDIALINK_] = $langLinks;

    if (array_key_exists($contentType, $altList)) {
        return $altList[$contentType];
    }

    return $langOther;
}

/*
 * Check if an input string is a number
 *
 * @param string $var input to check
 * @return bool true if $var is a number, false otherwise
 *
 * @author Piraux Sebastien <pir@cerdecam.be>
 */

function is_num($var) {
    for ($i = 0; $i < strlen($var); $i++) {
        $ascii = ord($var[$i]);

        // 48 to 57 are decimal ascii values for 0 to 9
        if ($ascii >= 48 && $ascii <= 57) {
            continue;
        } else {
            return FALSE;
        }
    }

    return TRUE;
}

/*
 *  This function allows to display the modules content of a learning path.
 *  The function must be called from inside a learning path where the session variable path_id is known.
 */

function display_path_content() {
    global $langModule, $course_id;

    $style = '';
    $output = '';

    $sql = "SELECT M.`name`, M.`contentType`,
                   LPM.`learnPath_module_id`, LPM.`parent`,
                   A.`path`
            FROM `lp_learnPath` AS LP,
                 `lp_rel_learnPath_module` AS LPM,
                 `lp_module` AS M
            LEFT JOIN `lp_asset` AS A
              ON M.`startAsset_id` = A.`asset_id`
            WHERE LP.`learnPath_id` = ?d
              AND LP.`learnPath_id` = LPM.`learnPath_id`
              AND LPM.`module_id` = M.`module_id`
              AND LP.`course_id` = ?d
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
        $extendedList[] = $modar;
    }
    // build the array of modules
    // build_element_list return a multi-level array, where children is an array with all nested modules
    // build_display_element_list return an 1-level array where children is the deep of the module
    $flatElementList = build_display_element_list(build_element_list($extendedList, 'parent', 'learnPath_module_id'));

    // look for maxDeep
    $maxDeep = 1; // used to compute colspan of <td> cells
    for ($i = 0; $i < sizeof($flatElementList); $i++) {
        if ($flatElementList[$i]['children'] > $maxDeep) {
            $maxDeep = $flatElementList[$i]['children'];
        }
    }

    $output .= "\n" . '<table width="99%">' . "\n\n"
            . '<tr align="center" valign="top">' . "\n"
            . '<th colspan="' . ($maxDeep + 1) . '">' . $langModule . '</th>' . "\n"
            . '</tr>' . "\n";

    foreach ($flatElementList as $module) {
        $spacingString = '';
        for ($i = 0; $i < $module['children']; $i++) {
            $spacingString .= '<td width="5">&nbsp;</td>' . "\n";
        }
        $colspan = $maxDeep - $module['children'] + 1;

        $output .= '<tr align="center" ' . $style . '>' . "\n"
                . $spacingString
                . '<td colspan="' . $colspan . '" align="left">';

        if ($module['contentType'] == CTLABEL_) { // chapter head
            $output .= '<b>' . $module['name'] . '</b>';
        } else { // module
            if ($module['contentType'] == CTEXERCISE_) {
                $moduleImg = 'fa-square-pen';
            } else if ($module['contentType'] == CTLINK_) {
                $moduleImg = 'fa-link';
            } else if ($module['contentType'] == CTCOURSE_DESCRIPTION_) {
                $moduleImg = 'fa-info-circle';
            } else if ($module['contentType'] == CTMEDIA_ || $module['contentType'] == CTMEDIALINK_) {
                $moduleImg = 'fa-film';
            } else {
                $moduleImg = choose_image(basename($module['path']));
            }

            $contentType_alt = selectAlt($module['contentType']);

            $output .= icon($moduleImg, $contentType_alt) . $module['name'];
        }
        $output .= '</td>' . "\n" . '</tr>' . "\n\n";
    }
    $output .= '     </table>' . "\n\n";
    return $output;
}

function calculate_learnPath_bestAttempt_progress($modules): array {
    if (!is_array($modules) || empty($modules)) {
        return array(0, 0);
    } else {
        $maxAttempt = 1; // discover number of attempts
        foreach ($modules as $module) {
            if ($module->attempt > $maxAttempt) {
                $maxAttempt = $module->attempt;
            }
        }

        // init progress per attempt
        $progress = array();
        for ($i = 1; $i <= $maxAttempt; $i++) {
            $progress[$i] = 0;
        }

        // progression is calculated in percents
        foreach ($modules as $module) {
            if ($module->SMax <= 0) {
                $modProgress = 0;
            } else {
                $modProgress = @round($module->R / $module->SMax * 100);
            }

            // in case of scorm module, progression depends on the lesson status value
            if (($module->CTYPE == "SCORM") && ($module->SMax <= 0) && (( $module->STATUS == 'COMPLETED') || ($module->STATUS == 'PASSED'))) {
                $modProgress = 100;
            }

            if ($modProgress >= 0) {
                $progress[$module->attempt] += $modProgress;
            }
        }

        $bestAttempt = 1; // discover best attempt
        for ($i = 1; $i <= $maxAttempt; $i++) {
            if ($progress[$i] > $progress[$bestAttempt]) {
                $bestAttempt = $i;
            }
        }

        return array($bestAttempt, $progress[$bestAttempt]);
    }
}

function calculate_learnPath_combined_progress($modules): int {
    if (!is_array($modules) || empty($modules)) {
        return 0;
    } else {
        $progress = [];

        // progression is calculated in percents
        foreach ($modules as $module) {
            if ($module->scoreMax <= 0) {
                $modProgress = 0;
            } else {
                $modProgress = @round($module->raw / $module->scoreMax * 100);
            }

            // in case of scorm module, progression depends on the lesson status value
            if (($module->contentType == "SCORM") && ($module->scoreMax <= 0) && (( $module->lesson_status == 'COMPLETED') || ($module->lesson_status == 'PASSED'))) {
                $modProgress = 100;
            }

            $id = $module->learnPath_module_id;
            if (!isset($progress[$id])) {
                $progress[$id] = $modProgress;
            } elseif ($modProgress >= 0) {
                $progress[$id] = max($modProgress, $progress[$id]);
            }
        }

        return array_sum($progress);
    }
}

function calculate_number_of_visible_modules($lpid) {
    global $course_id;

    // find number of visible modules in this path
    $sqlnum = "SELECT COUNT(M.`module_id`) AS count
                    FROM `lp_rel_learnPath_module` AS LPM,
                         `lp_module` AS M
                    WHERE LPM.`learnPath_id` = ?d
                    AND LPM.`visible` = 1
                    AND M.`contentType` != ?s
                    AND M.`module_id` = LPM.`module_id`
                    AND M.`course_id` = ?d";
    $result = Database::get()->querySingle($sqlnum, $lpid, CTLABEL_, $course_id);
    return ($result && !empty($result->count)) ? $result->count : false;
}

function calculate_learnPath_progress($lpid, $modules) {
    global $course_id;

    if (!is_array($modules) || empty($modules)) {
        return 0;
    }

    list($bestAttempt, $bestProgress) = calculate_learnPath_bestAttempt_progress($modules);
    if ($bestAttempt <= 0) {
        return 0;
    }

    $nbrOfVisibleModules = calculate_number_of_visible_modules($lpid);

    if (is_numeric($nbrOfVisibleModules)) {
        return @round($bestProgress / $nbrOfVisibleModules);
    } else {
        return 0;
    }
}

/**
 * Compute the progression into the $lpid learning path in pourcent
 *
 * @param $lpid id of the learning path
 * @param $lpUid user id
 *
 * @return integer percentage of progression os user $mpUid in the learning path $lpid
 */
function get_learnPath_progress($lpid, $lpUid) {
    global $course_id;

    // find progression for this user in each module of the path
    $sql = "SELECT UMP.`raw` AS R, UMP.`scoreMax` AS SMax, M.`contentType` AS CTYPE, UMP.`lesson_status` AS STATUS, UMP.`attempt`
             FROM `lp_learnPath` AS LP,
                  `lp_rel_learnPath_module` AS LPM,
                  `lp_user_module_progress` AS UMP,
                  `lp_module` AS M
            WHERE LP.`learnPath_id` = LPM.`learnPath_id`
              AND LPM.`learnPath_module_id` = UMP.`learnPath_module_id`
              AND UMP.`user_id` = ?d
              AND LP.`learnPath_id` = ?d
              AND LP.`course_id` = ?d
              AND LPM.`visible` = 1
              AND M.`module_id` = LPM.`module_id`
              AND M.`contentType` != ?s
            ORDER BY UMP.`attempt`, LPM.`rank`";
    $modules = Database::get()->queryArray($sql, $lpUid, $lpid, $course_id, CTLABEL_);
    return calculate_learnPath_progress($lpid, $modules);
}

function get_learnPath_progress_details($lpid, $lpUid, $total=true, $from_date = null): array {
    global $course_id;

    // find progression for this user in each module of the path
    $sql = "SELECT UMP.`raw` AS R, UMP.`scoreMax` AS SMax, M.`contentType` AS CTYPE, UMP.`lesson_status` AS STATUS, UMP.`total_time`,
                   UMP.`started`, UMP.`accessed`, UMP.`attempt`
             FROM `lp_learnPath` AS LP,
                  `lp_rel_learnPath_module` AS LPM,
                  `lp_user_module_progress` AS UMP,
                  `lp_module` AS M
            WHERE LP.`learnPath_id` = LPM.`learnPath_id`
              AND LPM.`learnPath_module_id` = UMP.`learnPath_module_id`
              AND UMP.`user_id` = ?d
              AND LP.`learnPath_id` = ?d
              AND LP.`course_id` = ?d
              AND LPM.`visible` = 1
              AND M.`module_id` = LPM.`module_id`
              AND M.`contentType` != ?s";
//            ORDER BY UMP.`attempt`, LPM.`rank`";

    if ($from_date) {
        $sql .= " AND UMP.`started` >= ?s";
    }

    $sql .= " ORDER BY UMP.`attempt`, LPM.`rank`";
    $params = [$lpUid, $lpid, $course_id, CTLABEL_];
    if ($from_date) {
        $params[] = $from_date;
    }

//    $modules = Database::get()->queryArray($sql, $lpUid, $lpid, $course_id, CTLABEL_);
    $modules = Database::get()->queryArray($sql, ...$params);
    $totalProgress = 0;
    $totalStarted = $totalAccessed = $totalStatus = $maxAttempt = "";
    $totalTime = "0000:00:00";

    if (is_array($modules) && !empty($modules)) {
        $maxAttempt = 1; // discover number of attempts
        $modsForProg = array(); // prepare sub-arrays for progression calculation
        foreach ($modules as $module) {
            if ($module->attempt > $maxAttempt) {
                $maxAttempt = $module->attempt;
            }
            $modsForProg[$module->attempt][] = $module;
        }

        $global_progress = $global_started = $global_accessed = $global_status = $global_time = array();
        for ($i = 1; $i <= $maxAttempt; $i++) {
            $global_started[$i] = "";
            $global_accessed[$i] = "";
            $global_status[$i] = "";
            $global_time[$i] = "0000:00:00";
            // total progress calculation
            $global_progress[$i] = calculate_learnPath_progress($lpid, $modsForProg[$i]);
        }

        foreach ($modules as $module) {
            // total time calculation
            $mtt = preg_replace('/\.[0-9]{0,2}/', '', $module->total_time ?? '');
            $global_time[$module->attempt] = addScormTime($global_time[$module->attempt], $mtt);

            // total started and accessed calculations
            if (!is_null($module->started)) {
                $mst = strtotime($module->started);
            }
            if (!is_null($module->accessed)) {
                $mat = strtotime($module->accessed);
            }
            if (isset($mst)) {
                if ($global_started[$module->attempt] === "") {
                    $global_started[$module->attempt] = $module->started;
                } else if (strtotime($global_started[$module->attempt]) > $mst) {
                    $global_started[$module->attempt] = $module->started;
                }
            }
            if (isset($mat)) {
                if ($global_accessed[$module->attempt] === "") {
                    $global_accessed[$module->attempt] = $module->accessed;
                } else if (strtotime($global_accessed[$module->attempt]) < $mat) {
                    $global_accessed[$module->attempt] = $module->accessed;
                }
            }

            // total status calculation
            if ($global_status[$module->attempt] === "" || (enum_lesson_status($module->STATUS) < enum_lesson_status($global_status[$module->attempt]))) {
                $global_status[$module->attempt] = $module->STATUS;
            }
            $totalTime = addScormTime($totalTime, $global_time[$module->attempt]);
        }

        $bestAttempt = 1; // discover best attempt
        for ($i = 1; $i <= $maxAttempt; $i++) {
            if ($global_progress[$i] > $global_progress[$bestAttempt] or
                enum_lesson_status($global_status[$i]) > enum_lesson_status($global_status[$bestAttempt])) {
                $bestAttempt = $i;
            }
        }

        $totalProgress = $global_progress[$bestAttempt];
        $totalStarted = $global_started[$bestAttempt];
        $totalAccessed = $global_accessed[$bestAttempt];
        $totalStatus = $global_status[$bestAttempt];
        if ($totalStatus === "PASSED" && $totalProgress < 100) {
            $totalStatus = "INCOMPLETE";
        }
    }

    if ($total) {
        return array($totalProgress, $totalTime, $totalStarted, $totalAccessed, $totalStatus, $maxAttempt);
    } else {
        $attempts = [];
        for ($i = 1; $i <= $maxAttempt; $i++) {
            $attempts[$i] = [
                $global_progress[$i],
                $global_time[$i],
                $global_started[$i],
                $global_accessed[$i],
                $global_status[$i],
                $i,
            ];
        }
        return $attempts;
    }
}

function get_learnPath_bestAttempt_progress($lpid, $lpUid): array {
    global $course_id;

    // find progression for this user in each module of the path
    $sql = "SELECT UMP.`raw` AS R, UMP.`scoreMax` AS SMax, M.`contentType` AS CTYPE, UMP.`lesson_status` AS STATUS, UMP.`attempt`
             FROM `lp_learnPath` AS LP,
                  `lp_rel_learnPath_module` AS LPM,
                  `lp_user_module_progress` AS UMP,
                  `lp_module` AS M
            WHERE LP.`learnPath_id` = LPM.`learnPath_id`
              AND LPM.`learnPath_module_id` = UMP.`learnPath_module_id`
              AND UMP.`user_id` = ?d
              AND LP.`learnPath_id` = ?d
              AND LP.`course_id` = ?d
              AND LPM.`visible` = 1
              AND M.`module_id` = LPM.`module_id`
              AND M.`contentType` != ?s
            ORDER BY UMP.`attempt`, LPM.`rank`";
    $modules = Database::get()->queryArray($sql, $lpUid, $lpid, $course_id, CTLABEL_);
    return calculate_learnPath_bestAttempt_progress($modules);
}

function get_learnPath_combined_progress($lpid, $lpUid): float {
    $sql = "SELECT
                   MAX(LPM.`learnPath_module_id`) as learnPath_module_id,
                   MAX(LPM.`parent`) as parent,
                   MAX(LPM.`lock`) as `lock`,
                   MAX(M.`module_id`) as module_id,
                   MAX(M.`contentType`) as contentType,
                   MAX(M.`name`) as name,
                   MAX(UMP.`lesson_status`) as lesson_status,
                   MAX(UMP.`raw`) as raw,
                   MAX(UMP.`scoreMax`) as scoreMax,
                   MAX(UMP.`credit`) as credit,
                   MAX(A.`path`) as path
              FROM (`lp_module` AS M, `lp_rel_learnPath_module` AS LPM)
         LEFT JOIN `lp_user_module_progress` AS UMP
                ON UMP.`learnPath_module_id` = LPM.`learnPath_module_id`
               AND UMP.`user_id` = ?d
         LEFT JOIN `lp_asset` AS A
                ON M.`startAsset_id` = A.`asset_id`
             WHERE LPM.`module_id` = M.`module_id`
               AND LPM.`learnPath_id` = ?d
               AND LPM.`visible` = 1
               AND LPM.`module_id` = M.`module_id`
               AND M.`contentType` != ?s
          GROUP BY LPM.`module_id`, UMP.`attempt`
          ORDER BY MIN(LPM.`rank`)";
    $modules = Database::get()->queryArray($sql, $lpUid, $lpid, CTLABEL_);
    $modulesProg = calculate_learnPath_combined_progress($modules);
    $nbrOfVisibleModules = calculate_number_of_visible_modules($lpid);

    if (is_numeric($nbrOfVisibleModules)) {
        return @round($modulesProg / $nbrOfVisibleModules);
    } else {
        return 0;
    }
}

/**
 * This function displays the list of available exercises in this course
 * With the form to add a selected exercise in the learning path
 *
 * @param string $dialogBox Error or confirmation text
 *
 * @author Thanos Kyritsis <atkyritsis@upnet.gr>
 * @author Piraux Sebastien <pir@cerdecam.be>
 * @author Lederer Guillaume <led@cerdecam.be>
 */
function display_my_exercises($dialogBox, $style) {

    global $langAdd, $langExercise, $langNoExercises, $langSelection, $course_code, $course_id, $urlServer, $langCancel, $langSelect;

    $output = "";
    /* --------------------------------------
      DIALOG BOX SECTION
      -------------------------------------- */
    if (!empty($dialogBox)) {
        $output .= disp_message_box($dialogBox, $style) . '<br />' . "\n";
    }
    // Display available modules
    $sql = "SELECT `id`, `title`, `description`
            FROM `exercise`
            WHERE course_id = ?d
            AND active = 1
            ORDER BY `title`";
    $exercises = Database::get()->queryArray($sql, $course_id);

    if (!empty($exercises)) {
        $output .= '<form method="POST" name="addmodule" action="' . $_SERVER['SCRIPT_NAME'] . '?course=' . $course_code . '&amp;cmdglobal=add">';
        $output .= "<div class='table-responsive'><table class='table-default'>
                    <thead>
                    <tr class='list-header'>
                        <th>
                            $langExercise
                        </th>
                        <th>
                            $langSelection
                        </th>
                    </tr>
                    </thead>";
            foreach ($exercises as $exercise) {
                $output .= "<tr><td>
                            <a href='{$urlServer}modules/exercise/admin.php?course=$course_code&amp;exerciseId=$exercise->id&amp;preview=1'>" . q($exercise->title) . "</a>";
                if (!empty($exercise->description)) {
                    $output .= "<span class='comments'>" . standard_text_escape($exercise->description) . "</span></td>";
                } else {
                    $output .= "</td>";
                }
                $output .= '<td>'
                        . '<label class="label-container" aria-label="'.$langSelect.'"><input type="checkbox" name="check_' . $exercise->id . '" id="check_' . $exercise->id . '" value="' . $exercise->id . '" /><span class="checkmark"></span></label>'
                        . '</td></tr>';
            }
        $output .= "</table></div>";
        $output .= "<div class='form-group'>";
        $output .= "<div class='col-12 d-inline-flex justify-content-end gap-2 mt-4'>";
        $output .= "<input class='btn submitAdminBtn c' type='submit' name='insertExercise' value='$langAdd'>";
        $output .= "<a href='learningPathAdmin.php?course=$course_code&amp;path_id=$_SESSION[path_id]' class='btn cancelAdminBtn'>$langCancel</a>";
        $output .= "</div></div>";
        $output .= "</form>";
    } else {
        $output .= "<div class='alert alert-warning'>
                        <i class='fa-solid fa-triangle-exclamation fa-lg'></i>
                        <span>$langNoExercises</span>
                    </div>";
    }
    return $output;
}

/*
 * @brief display documents list
 * based in function list_docs() in 'modules/units/insert_doc.php'
 */

function display_my_documents()
{
    global $langUp, $langName, $langSize, $langDate, $langAdd,
           $fileinfo, $langChoice,$langDirectory,  $langCancel,
           $course_code, $group_sql, $urlbase, $path, $langSelect;


    if (!empty($path)) {
        $dirname = Database::get()->querySingle("SELECT filename FROM document
                                                                   WHERE $group_sql AND path = ?s", $path);
        $parentpath = dirname($path);
        $dirname = htmlspecialchars($dirname->filename);
        $parentlink = $urlbase . $parentpath;
        $parenthtml = "<span class='float-end'><a href='$parentlink'>$langUp " .
            icon('fa-level-up') . "</a></span>";
        $colspan = 4;
    }
    $content = "<form action='$_SERVER[SCRIPT_NAME]?course=$course_code' method='post'>" .
        "<div class='table-responsive'><table class='table-default'>";
    if (!empty($path)) {
        $content .=
            "<tr>" .
            "<th colspan='$colspan'><div class='text-start'>$langDirectory: $dirname$parenthtml</div></th>" .
            "</tr>";
    }
    $content .=
        "<thead><tr class='list-header'>" .
        "<th>$langName</th>" .
        "<th>$langSize</th>" .
        "<th>$langDate</th>" .
        "<th>$langChoice</th>" .
        "</tr></thead>";

    $counter = 0;
    foreach (array(true, false) as $is_dir) {
        foreach ($fileinfo as $entry) {
            if ($entry['is_dir'] != $is_dir) {
                continue;
            }
            $dir = $entry['path'];
            if ($is_dir) {
                $image = 'fa-folder-open';
                $file_url = $urlbase . $dir;
                $link_text = $entry['name'];

                $link_href = "<a href='$file_url'>$link_text</a>";
            } else {
                $image = choose_image('.' . $entry['format']);
                $file_url = file_url($entry['path'], $entry['name'], $course_code);

                $dObj = $entry['object'];
                $dObj->setAccessURL($file_url);
                $dObj->setPlayURL(file_playurl($entry['path'], $entry['name'], $course_code));

                $link_href = MultimediaHelper::chooseMediaAhref($dObj);
            }
            if ($entry['visible'] == 'i') {
                $vis = 'invisible';
            } else {
                $vis = '';
            }
            $content .= "<tr class='$vis'>";
            $content .= "<td><div class='d-flex gap-2'>" . icon($image, '') . "$link_href</div>";

            /* * * comments ** */
            if (!empty($entry['comment'])) {
                $content .= "<br /><div class='comment'>" .
                    standard_text_escape($entry['comment']) .
                    "</div>";
            }
            $content .= "</td>";
            if ($is_dir) {
                // skip display of date and time for directories
                $content .= "<td>&nbsp;</td><td>&nbsp;</td>";
            } else {
                $size = format_file_size($entry['size']);
                $date = format_locale_date(strtotime($entry['date']), 'short', false);
                $content .= "<td>$size</td><td>$date</td>";
            }
            $content .= "<td>
                            <label class='label-container' aria-label='$langSelect'>
                                <input type='checkbox' name='document[]' value='$entry[id]' />
                                <span class='checkmark'></span>
                            </label>
                        </td>";
            $content .= "</tr>";
            $counter++;
        }
    }
    $content .= "</table></div>";
    $content .= "<div class='form-group'>";
    $content .= "<div class='col-12 d-inline-flex justify-content-end gap-2 mt-4'>";
    $content .= "<input class='btn submitAdminBtn' type='submit' name='submitInsertedDocument' value='$langAdd'>";
    $content .= "<a href='learningPathAdmin.php?course=$course_code&amp;path_id=$_SESSION[path_id]' class='btn cancelAdminBtn'>$langCancel</a>";
    $content .= "</div></div>";

    return $content;
}

/**
 * Build an tree of $list from $id using the 'parent'
 * table. (recursive function)
 * Rows with a father id not existing in the array will be ignored
 *
 * @param $list modules of the learning path list
 * @param $parentField name of the field containing the parent id
 * @param $idField name of the field containing the current id
 * @param $id learnPath_module_id of the node to build
 * @return tree of the learning path
 *
 * @author Piraux Sebastien <pir@cerdecam.be>
 */
function build_element_list($list, $parentField, $idField, $id = 0) {
    $id = strval($id);
    $tree = array();
    if (is_array($list)) {
        foreach ($list as $element) {
            if (strval($element[$idField]) === $id) {
                $tree = $element; // keep all $list informations in the returned array
                // explicitly add 'name' and 'value' for the build_nested_select_menu function
                //$tree['name'] = $element['name']; // useless since 'name' is the same word in db and in the  build_nested_select_menu function
                $tree['value'] = $element[$idField];
                break;
            }
        }

        foreach ($list as $element) {
            if (strval($element[$parentField]) === $id && (strval($element[$parentField]) !== strval($element[$idField]))) {
                if ($id == 0) {
                    $tree[] = build_element_list($list, $parentField, $idField, $element[$idField]);
                } else {
                    $tree['children'][] = build_element_list($list, $parentField, $idField, $element[$idField]);
                }
            }
        }
    }
    return $tree;
}

/**
 * return a flattened tree of the modules of a learnPath after having add
 * 'up' and 'down' fields to let know if the up and down arrows have to be
 * displayed. (recursive function)
 *
 * @param $elementList a tree array as one returned by build_element_list
 * @param $deepness
 * @return array containing infos of the learningpath, each module is an element
  of this array and each one has 'up' and 'down' boolean and deepness added in
 *
 * @author Piraux Sebastien <pir@cerdecam.be>
 */
function build_display_element_list($elementList, $deepness = 0) {
    $count = 0;
    $first = true;
    $last = false;
    $displayElementList = array();

    foreach ($elementList as $thisElement) {
        $count++;

        // temporary save the children before overwritten it
        if (isset($thisElement['children'])) {
            $temp = $thisElement['children'];
        } else {
            $temp = NULL; // re init temp value if there is nothing to put in it
        }

        // we use 'children' to calculate the deepness of the module, it will be displayed
        // using a spacing multiply by deepness
        $thisElement['children'] = $deepness;

        //--- up and down arrows displayed ?
        if ($count == count($elementList)) {
            $last = true;
        }

        $thisElement['up'] = $first ? false : true;
        $thisElement['down'] = $last ? false : true;

        //---
        $first = false;

        $displayElementList[] = $thisElement;

        if (isset($temp) && sizeof($temp) > 0) {
            $displayElementList = array_merge($displayElementList, build_display_element_list($temp, $deepness + 1));
        }
    }
    return $displayElementList;
}

/**
 * This function set visibility for all the nodes of the tree module_tree
 *
 * @param $module_tree tree of modules we want to change the visibility
 * @param $visibility ths visibility string as requested by the DB
 *
 * @author Piraux Sebastien <pir@cerdecam.be>
 */
function set_module_tree_visibility($module_tree, $visibility) {
    foreach ($module_tree as $module) {
        if ($module['visible'] != $visibility) {
            $sql = "UPDATE `lp_rel_learnPath_module`
                       SET `visible` = ?s
                     WHERE `learnPath_module_id` = ?d
                       AND `visible` != ?s";
            Database::get()->query($sql, $visibility, $module['learnPath_module_id'], $visibility);
        }
        if (isset($module['children']) && is_array($module['children'])) {
            set_module_tree_visibility($module['children'], $visibility);
        }
    }
}

/**
 * This function deletes all the nodes of the tree module_tree
 *
 * @param $module_tree tree of modules we want to change the visibility
 *
 * @author Piraux Sebastien <pir@cerdecam.be>
 */
function delete_module_tree($module_tree) {
    foreach ($module_tree as $module) {
        switch ($module['contentType']) {
            case CTSCORMASSET_ :
            case CTSCORM_ :
                // delete asset if scorm
                Database::get()->query("DELETE FROM `lp_asset` WHERE `module_id` =  ?d", $module['module_id']);
            // no break because we need to delete module
            case CTLABEL_ : // delete module if scorm && if label
                Database::get()->query("DELETE FROM `lp_module` WHERE `module_id` =  ?d", $module['module_id']);
            // no break because we need to delete LMP and UMP
            default : // always delete LPM and UMP
                Database::get()->query("DELETE FROM `lp_rel_learnPath_module`
                                              WHERE `learnPath_module_id` = ?d", $module['learnPath_module_id']);
                Database::get()->query("DELETE FROM `lp_user_module_progress`
                                              WHERE `learnPath_module_id` = ?d", $module['learnPath_module_id']);

                break;
        }

        // auto-delete from modules pool if not used elsewhere
        $cnt = Database::get()->querySingle("select count(learnPath_module_id) as count "
                . " from lp_rel_learnPath_module "
                . " where module_id = ?d "
                . " and learnPath_id in ("
                . " select learnPath_id from lp_learnPath where course_id = ?d"
                . " )", $module['module_id'], $module['course_id'])->count;

        if ($cnt == 0) {
            Database::get()->query("DELETE FROM `lp_asset` WHERE `module_id` =  ?d", $module['module_id']);
            Database::get()->query("DELETE FROM `lp_module` WHERE `module_id` =  ?d", $module['module_id']);
        }
    }
    if (isset($module['children']) && is_array($module['children'])) {
        delete_module_tree($module['children']);
    }
}

/**
 * This function return the node with $module_id (recursive)
 *
 *
 * @param $lpModules array the tree of all modules in a learning path
 * @param $id node we are looking for
 * @param $field type of node we are looking for (learnPath_module_id, module_id,...)
 *
 * @return array the requesting node (with all its children)
 *
 * @author Thanos Kyritsis <atkyritsis@upnet.gr>
 * @author Piraux Sebastien <pir@cerdecam.be>
 */
function get_module_tree($lpModules, $id, $field = 'module_id') {
    foreach ($lpModules as $module) {
        if ($module[$field] == $id) {
            return $module;
        } elseif (isset($module['children']) && is_array($module['children'])) {
            $temp = get_module_tree($module['children'], $id, $field);
            if (is_array($temp)) {
                return $temp;
            } // else check next node
        }
    }
}

/**
 * Convert the time recorded in seconds to a scorm type
 *
 * @author Piraux Sebastien <pir@cerdecam.be>
 * @param $time time in seconds to convert to a scorm type time
 * @return string compatible scorm type (smaller format)
 */
function seconds_to_scorm_time($time) {
    $hours = floor($time / 3600);
    if ($hours < 10) {
        $hours = "0" . $hours;
    }
    $min = floor(( $time - ($hours * 3600) ) / 60);
    if ($min < 10) {
        $min = '0' . $min;
    }
    $sec = $time - ($hours * 3600) - ($min * 60);
    if ($sec < 10) {
        $sec = '0' . $sec;
    }

    return $hours . ':' . $min . ':' . $sec;
}

/**
 * This function allows to see if a time string is the SCORM 2004 requested format:
 * timeinterval(second,10,2): PThHmMsS
 *
 * @param ?string $time a suspected SCORM 2004 time value, returned by the javascript API
 */
function isScorm2004Time(?string $time): bool {
    return preg_match(SCORM_2004_TIME_MASK, $time);
}

/**
 * This function allow to see if a time string is the SCORM requested format : hhhh:mm:ss.cc
 *
 * @param ?string $time a suspected SCORM time value, returned by the javascript API
 */
function isScormTime(?string $time): bool {
    return preg_match(SCORM_TIME_MASK, $time);
}

function extractScormTime(?string $time): array {
    preg_match(SCORM_TIME_MASK, $time, $matches);
    $hours = intval($matches[1] ?? 0);
    $minutes = intval($matches[2] ?? 0);
    $seconds = intval($matches[3] ?? 0);
    $primes = intval($matches[4] ?? 0);
    return array($hours, $minutes, $seconds, $primes);
}

function extractScorm2004Time(?string $time): array {
    preg_match(SCORM_2004_TIME_MASK, $time, $matches);
    $hours = intval($matches[2] ?? 0);
    $minutes = intval($matches[4] ?? 0);
    $seconds = intval($matches[6] ?? 0);
    $primes = intval($matches[7] ?? 0);
    return array($hours, $minutes, $seconds, $primes);
}

function calculateScormTime(int $hours1, int $minutes1, int $seconds1, int $primes1, int $hours2, int $minutes2, int $seconds2, int $primes2): string {
    // calculate the resulting added hours, seconds, ... for result
    $primesReport = FALSE;
    $secondsReport = FALSE;
    $minutesReport = FALSE;
    // $hoursReport = FALSE;

    // calculate primes
    if ($primes1 < 10) {
        $primes1 = $primes1 * 10;
    }
    if ($primes2 < 10) {
        $primes2 = $primes2 * 10;
    }
    $total_primes = $primes1 + $primes2;
    if ($total_primes >= 100) {
        $total_primes -= 100;
        $primesReport = TRUE;
    }

    // calculate seconds
    $total_seconds = $seconds1 + $seconds2;
    if ($primesReport) {
        $total_seconds++;
    }
    if ($total_seconds >= 60) {
        $total_seconds -= 60;
        $secondsReport = TRUE;
    }

    // calculate minutes
    $total_minutes = $minutes1 + $minutes2;
    if ($secondsReport) {
        $total_minutes ++;
    }
    if ($total_minutes >= 60) {
        $total_minutes -= 60;
        $minutesReport = TRUE;
    }

    // calculate hours
    $total_hours = $hours1 + $hours2;
    if ($minutesReport) {
        $total_hours++;
    }
    if ($total_hours >= 10000) {
        $total_hours -= 10000;
        // $hoursReport = TRUE;
    }

    // construct and return result string
    if ($total_hours < 10) {
        $total_hours = "0" . $total_hours;
    }
    if ($total_minutes < 10) {
        $total_minutes = "0" . $total_minutes;
    }
    if ($total_seconds < 10) {
        $total_seconds = "0" . $total_seconds;
    }

    $total_time = $total_hours . ":" . $total_minutes . ":" . $total_seconds;
    // add primes only if != 0
    if ($total_primes != 0) {
        $total_time .= "." . $total_primes;
    }
    return $total_time;
}

/**
 * This function allow to add times saved in the SCORM 2004 requested format:
 * timeinterval(second,10,2): PThHmMsS
 *
 * @param ?string $time1 a suspected SCORM 1.2 time value, total_time, in the API
 * @param ?string $time2 a suspected SCORM 2004 time value, session_time to add, in the API
 */
function addScorm2004Time(?string $time1, ?string $time2): string {
    list($hours1, $minutes1, $seconds1, $primes1) = extractScormTime($time1);
    list($hours2, $minutes2, $seconds2, $primes2) = extractScorm2004Time($time2);
    return calculateScormTime($hours1, $minutes1, $seconds1, $primes1, $hours2, $minutes2, $seconds2, $primes2);
}

/**
 * This function allow to add times saved in the SCORM requested format: hhhh:mm:ss.cc
 *
 * @param ?string $time1 a suspected SCORM time value, total_time, in the API
 * @param ?string $time2 a suspected SCORM time value, session_time to add, in the API
 */
function addScormTime(?string $time1, ?string $time2): string {
    list($hours1, $minutes1, $seconds1, $primes1) = extractScormTime($time1);
    list($hours2, $minutes2, $seconds2, $primes2) = extractScormTime($time2);
    return calculateScormTime($hours1, $minutes1, $seconds1, $primes1, $hours2, $minutes2, $seconds2, $primes2);
}

/*
 * function that cleans php string for javascript
 *
 * This function is needed to clean strings used in javascript output
 * Newlines are prohibited in the script, specialchar  are prohibited
 * quotes must be addslashes
 *
 * @param $str string original string
 * @return string cleaned string
 *
 * @author Piraux Sebastien <pir@cerdecam.be>
 *
 */

function clean_str_for_javascript($str) {
    $output = $str;
    // 1. addslashes, prevent problems with quotes
    // must be before the str_replace to avoid double backslash for \n
    $output = addslashes($output);
    // 2. turn windows CR into *nix CR
    $output = str_replace("\r", '', $output);
    // 3. replace "\n" by uninterpreted '\n'
    $output = str_replace("\n", '\n', $output);
    // 4. convert special chars into html entities
    $output = htmlspecialchars($output);

    return $output;
}

/*
 * Parse the user text (e.g. stored in database)
 * before displaying it to the screen
 * For example it change new line charater to <br> tag etc.
 *
 * @param string $userText original user tex
 * @return string parsed user text
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 */

function parse_user_text($userText) {
    $userText = make_clickable($userText);
    if (strpos($userText, '<!-- content: html -->') === false) {
        // only if the content isn't HTML change new line to <br>
        // Note the '<!-- content: html -->' is introduced by HTML Area
        $userText = nl2br($userText);
    }
    return $userText;
}

/*
 * Displays the title of a tool. Optionally, there can be a subtitle below
 * the normal title, and / or a supra title above the normal title.
 *
 * e.g. supra title:
 * group
 * GROUP PROPERTIES
 *
 * e.g. subtitle:
 * AGENDA
 * calender & events tool
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @param  mixed $titleElement - it could either be a string or an array
 *                               containing 'supraTitle', 'mainTitle',
 *                               'subTitle'
 * @return void
 */

function disp_tool_title($titlePart) {
    // if titleElement is simply a string transform it into an array
    $string = "";
    if (is_array($titlePart)) {
        $titleElement = $titlePart;
    } else {
        $titleElement['mainTitle'] = $titlePart;
    }

    if (isset($titleElement['supraTitle'])) {
        $string .= '<small>' . $titleElement['supraTitle'] . '</small><br />' . "\n";
    }

    if (isset($titleElement['mainTitle'])) {
        $string .= $titleElement['mainTitle'] . "\n";
    }

    if (isset($titleElement['subTitle'])) {
        $string .= '      ' . $titleElement['subTitle'] . '' . "\n";
    }

    return $string;
}

/*
 * Prepare display of the message box appearing on the top of the window,
 * just    below the tool title. It is recommended to use this function
 * to display any confirmation or error messages, or to ask to the user
 * to enter simple parameters.
 *
 * @author Thanos Kyritsis <atkyritsis@upnet.gr>
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @param string $message - include your self any additionnal html
 *                          tag if you need them
 * @return $string - the
 */

function disp_message_box($message, $style = FALSE) {
    if ($style) {
        $cell = "<td class=\"$style\">";
    } else {
        $cell = "<td class=\"left\">";
    }
    return "$cell $message";
}

/*
 * Prepare the display of a clickable button
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 *
 * @param string $url url inserted into the 'href' part of the tag
 * @param string $text text inserted between the two <a>...</a> tags (note : it
 *        could also be an image ...)
 * @param string $confirmMessage (optionnal) introduce a javascript confirmation popup
 * @return string the button
 */

function disp_button($url, $text, $confirmMessage = '') {
    if (is_javascript_enabled() && !preg_match('~^Mozilla/4\.[1234567]~', $_SERVER['HTTP_USER_AGENT'])) {
        if ($confirmMessage != '') {
            $onClickCommand = "if(confirm('" . clean_str_for_javascript($confirmMessage) . "')){document.location='" . $url . "';return false}";
        } else {
            $onClickCommand = "document.location='" . $url . "';return false";
        }

        return '<button onclick="' . $onClickCommand . '">'
                . $text
                . '</button>&nbsp;' . "\n";
    } else {
        return '[ <a href="' . $url . '">' . $text . '</a> ]';
    }
}

/*
 * Function used to draw a progression bar
 *
 * @author Thanos Kyritsis <atkyritsis@upnet.gr>
 * @author Piraux S�astien <pir@cerdecam.be>
 *
 * @param integer $progress progression in percent
 * @param integer $factor will be multiplied by 100 to have the full size of the bar
 * (i.e. 1 will give a 100 pixel wide bar)
 */

function disp_progress_bar($progress, $factor) {

    $maxSize = $factor * 100; //pixels
    $barwidth = $factor * $progress;

    // display progress bar
    // origin of the bar

    // $progressBar = "
    // <div class='progress' style='display: inline-block; width: 200px; margin-bottom:0px;'>
    //     <div class='progress-bar' role='progressbar' aria-valuenow='60' aria-valuemin='0' aria-valuemax='100' style='width: $progress%; min-width: 2em;'>
    //         $progress%
    //     </div>
    // </div>";

    $progressBar = "
    <div class='progress-circle-bar' role='progressbar' aria-valuenow=$progress aria-valuemin='0' aria-valuemax='100' style='--value: $progress; --size: 6rem;'></div>";

    return $progressBar;
}

/**
 * Function used to draw the lesson status of a learning path module
 *
 * @param string $lessonStatus The learning path module lesson status
 * @return string The lesson status formatted for displaying
 */
function disp_lesson_status(string $lessonStatus): string {
    if ($lessonStatus == "NOT ATTEMPTED") {
        return $GLOBALS['langNotAttempted'];
    } else if ($lessonStatus == "PASSED") {
        return $GLOBALS['langPassed'];
    } else if ($lessonStatus == "FAILED") {
        return $GLOBALS['langFailed'];
    } else if ($lessonStatus == "COMPLETED") {
        return $GLOBALS['langAlreadyBrowsed'];
    } else if ($lessonStatus == "BROWSED") {
        return $GLOBALS['langAlreadyBrowsed'];
    } else if ($lessonStatus == "INCOMPLETE") {
        return $GLOBALS['langNeverBrowsed'];
    } else {
        return strtolower($lessonStatus);
    }
}

function enum_lesson_status(string $lessonStatus): int {
    if ($lessonStatus == "NOT ATTEMPTED") {
        return 1;
    } else if ($lessonStatus == "INCOMPLETE") {
        return 2;
    } else if ($lessonStatus == "FAILED") {
        return 3;
    } else if ($lessonStatus == "COMPLETED") {
        return 4;
    } else if ($lessonStatus == "BROWSED") {
        return 4;
    } else if ($lessonStatus == "PASSED") {
        return 5;
    } else {
        return 0;
    }
}

/*
 * function build_nested_select_menu($name, $elementList)
 * Build in a relevant way 'select' menu for an HTML form containing nested data
 *
 * @author Hugues Peeters <peeters@ipm.ucl.ac.be>
 *
 * @param string $name, name of the select tag
 *
 * @param array nested data in a composite way
 *
 *  Exemple :
 *
 *  $elementList[1]['name'    ] = 'level1';
 *  $elementList[1]['value'   ] = 'level1';
 *
 *  $elementList[1]['children'][1]['name' ] = 'level2';
 *  $elementList[1]['children'][1]['value'] = 'level2';
 *
 *  $elementList[1]['children'][2]['name' ] = 'level2';
 *  $elementList[1]['children'][2]['value'] = 'level2';
 *
 *  $elementList[2]['name' ]  = 'level1';
 *  $elementList[2]['value']  = 'level1';
 *
 * @return string the HTML flow
 * @desc depends on prepare option tags
 *
 */

function build_nested_select_menu($name, $elementList) {
    return '<select class="form-select" name="' . $name . '">' . "\n"
            . implode("\n", prepare_option_tags($elementList))
            . '</select>' . "\n";
}

/*
 * prepare the 'option' html tag for the disp_nested_select_menu()
 * function
 *
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 * @param array $elementList
 * @param int  $deepness (optionnal, default is 0)
 * @return array of option tag list
 */

function prepare_option_tags($elementList, $deepness = 0) {
    foreach ($elementList as $thisElement) {
        $tab = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $deepness);

        $optionTagList[] = '<option value="' . $thisElement['value'] . '">'
                . $tab . $thisElement['name']
                . '</option>';
        if (isset($thisElement['children']) && sizeof($thisElement['children']) > 0) {
            $optionTagList = array_merge($optionTagList, prepare_option_tags($thisElement['children'], $deepness + 1));
        }
    }
    return $optionTagList;
}

/*
 * This function checks whether a Learning Path Module exists and is visible
 * for a non-teacher user. If requested, the same check can be made for a
 * Learning Path, not just the module. Because the Learning Path and Learning
 * Path Module can be chosen via GET arguments, we are in danger of people
 * accessing stuff they shouldn't by guessing ids.
 *
 * @param boolean $is_editor contains whether the current user is admin of the current course
 * @param string $course_code contains the current course id
 * @param boolean $extraQuery contains whether the extra check will be made or not
 * @param boolean $extraDepth contains how far we are from the redirected location
 * @author Thanos Kyritsis <atkyritsis@upnet.gr>
 */

function check_LPM_validity($is_editor, $course_code, $extraQuery = false, $extraDepth = false) {
    global $course_id;

    $depth = ($extraDepth) ? "../" : "./";

    if (!isset($_SESSION['path_id']) || !isset($_SESSION['lp_module_id']) || empty($_SESSION['path_id']) || empty($_SESSION['lp_module_id'])) {
        header("Location: " . $depth . "index.php?course=$course_code");
        exit();
    }

    if ($extraQuery) {
        $lp = Database::get()->querySingle("SELECT visible FROM lp_learnPath WHERE learnPath_id = ?d AND `course_id` = ?d", $_SESSION['path_id'], $course_id);

        if (!$is_editor && $lp->visible == 0) {
            // if the learning path is invisible, don't allow users in it
            header("Location: " . $depth . "index.php?course=$course_code");
            exit();
        }

        if (!$is_editor) {
            // check for blocked learning path
            $rank0 = Database::get()->querySingle("SELECT `rank` FROM lp_learnPath
                                WHERE learnPath_id = ?d AND `course_id` = ?d ORDER BY `rank` LIMIT 1", $_SESSION['path_id'], $course_id)->rank;
            $lps = Database::get()->queryArray("SELECT `learnPath_id`, `lock` FROM lp_learnPath WHERE `course_id` = ?d AND `rank` < ?d", $course_id, $rank0);
            foreach ($lps as $lp) {
                if ($lp->lock == 'CLOSE') {
                    $prog = get_learnPath_progress($lp->learnPath_id, $_SESSION['uid']);
                    if ($prog < 100) {
                        header("Location: ./index.php?course=$course_code");
                    }
                }
            }
        }
    }

    $visfrom = "FROM lp_rel_learnPath_module WHERE learnPath_id = ?d AND module_id = ?d";
    $lpmcnt = Database::get()->querySingle("SELECT COUNT(visible) AS count " . $visfrom, $_SESSION['path_id'], $_SESSION['lp_module_id'])->count;
    $lpm = Database::get()->querySingle("SELECT visible " . $visfrom, $_SESSION['path_id'], $_SESSION['lp_module_id']);
    if ($lpmcnt <= 0 || (!$is_editor && $lpm->visible == 0)) {
        // if the combination path/module is invalid, don't allow users in it
        header("Location: " . $depth . "index.php?course=$course_code");
        exit();
    }

    if (!$is_editor) { // check if we try to overwrite a blocked module
        $lpm_id = Database::get()->querySingle("SELECT `lock`, `rank` FROM lp_rel_learnPath_module
                                WHERE `learnPath_id` = ?d AND module_id = ?d", $_SESSION['path_id'], $_SESSION['lp_module_id']);
        $q = Database::get()->queryArray("SELECT learnPath_module_id
                                            FROM lp_rel_learnPath_module
                                           WHERE learnPath_id = ?d
                                             AND `rank` < ?d", $_SESSION['path_id'], $lpm_id->rank);
        foreach ($q as $m) {
            $progress = Database::get()->querySingle("SELECT credit, lesson_status
                                                        FROM lp_user_module_progress
                                                       WHERE learnPath_module_id = ?d
                                                         AND learnPath_id = ?d
                                                         AND user_id = ?d", $m->learnPath_module_id, $_SESSION['path_id'], $_SESSION['uid']);
            if (($lpm_id->lock == 'CLOSE') && ($progress->credit != 'CREDIT' || ($progress->lesson_status != 'COMPLETED' && $progress->lesson_status != 'PASSED'))) {
                header("Location: " . $depth . "index.php?course=$course_code");
                exit();
            }
        }
    }
}

function deleteLearningPath($pathId) {
    global $course_code, $course_id, $webDir;

    if (is_dir($webDir . "/courses/" . $course_code . "/scormPackages/path_" . intval($pathId))) {
        $findsql = "SELECT M.`module_id`
						FROM  `lp_rel_learnPath_module` AS LPM, `lp_module` AS M
						WHERE LPM.`learnPath_id` = ?d
						AND ( M.`contentType` = ?s OR M.`contentType` = ?s OR M.`contentType` = ?s)
						AND LPM.`module_id` = M.`module_id`
						AND M.`course_id` = ?d";
        $findResult = Database::get()->queryArray($findsql, $pathId, CTSCORM_, CTSCORMASSET_, CTLABEL_, $course_id);

        // Delete the startAssets
        $delAssetSql = "DELETE FROM `lp_asset` WHERE 1=0";
        // DELETE the SCORM modules
        $delModuleSql = "DELETE FROM `lp_module` WHERE (`contentType` = ?s OR `contentType` = ?s OR `contentType` = ?s) AND (1=0";

        foreach ($findResult as $delList) {
            $delAssetSql .= " OR `module_id`= " . intval($delList->module_id);
            $delModuleSql .= " OR (`module_id`= " . intval($delList->module_id) . " AND `course_id` = " . intval($course_id) . " )";
        }
        Database::get()->query($delAssetSql);

        $delModuleSql .= ")";
        Database::get()->query($delModuleSql, CTSCORM_, CTSCORMASSET_, CTLABEL_);

        // DELETE the directory containing the package and all its content
        $real = realpath($webDir . "/courses/" . $course_code . "/scormPackages/path_" . intval($pathId));
        claro_delete_file($real);
    } else { // end of dealing with the case of a scorm learning path.
        $findsql = "SELECT M.`module_id`
						FROM  `lp_rel_learnPath_module` AS LPM,
						`lp_module` AS M
						WHERE LPM.`learnPath_id` = ?d
						AND M.`contentType` = ?s
						AND LPM.`module_id` = M.`module_id`
						AND M.`course_id` = ?d";
        $findResult = Database::get()->queryArray($findsql, $pathId, CTLABEL_, $course_id);
        // delete labels of non scorm learning path
        $delLabelModuleSql = "DELETE FROM `lp_module` WHERE 1=0";

        foreach ($findResult as $delList) {
            $delLabelModuleSql .= " OR (`module_id`=" . intval($delList->module_id) . " AND `course_id` = " . intval($course_id) . " )";
        }
        Database::get()->query($delLabelModuleSql);
    }

    // delete everything for this path (common to normal and scorm paths) concerning modules, progress and path
    // delete all user progression
    Database::get()->query("DELETE FROM `lp_user_module_progress` WHERE `learnPath_id` = ?d", $pathId);
    // delete all relation between modules and the deleted learning path
    Database::get()->query("DELETE FROM `lp_rel_learnPath_module` WHERE `learnPath_id` = ?d", $pathId);

    // delete the learning path
    $lp_name = Database::get()->querySingle("SELECT name FROM `lp_learnPath`
                                                                WHERE `learnPath_id` = ?d
                                                                AND `course_id` = ?d", $pathId, $course_id)->name;
    Database::get()->query("DELETE FROM `lp_learnPath`
                                                WHERE `learnPath_id` = ?d
                                                AND `course_id` = ?d", $pathId, $course_id);

    return $lp_name;
}

function triggerLPGame($courseId, $uid, $lpId, $eventName) {
    $eventData = new stdClass();
    $eventData->courseId = $courseId;
    $eventData->uid = $uid;
    $eventData->activityType = LearningPathEvent::ACTIVITY;
    $eventData->module = MODULE_ID_LP;
    $eventData->resource = intval($lpId);

    LearningPathEvent::trigger($eventName, $eventData);

    $eventData->activityType = LearningPathDurationEvent::ACTIVITY;
    LearningPathDurationEvent::trigger($eventName, $eventData);
}

/**
 * @brief trigger learning analytics
 * @param $courseId
 * @param $uid
 * @param $lpId
 * @param $eventName
 */
function triggerLPAnalytics($courseId, $uid, $lpId) {
    $data = new stdClass();
    $data->course_id = $courseId;
    $data->uid = $uid;
    $data->resource = $lpId;
    $data->element_type = 90;

    LpAnalyticsEvent::trigger(LpAnalyticsEvent::LPPERCENTAGE, $data, true);
}
