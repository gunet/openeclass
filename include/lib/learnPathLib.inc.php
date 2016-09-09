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
    // allow to chose between
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

            $output .= "<form method='POST' action='$_SERVER[SCRIPT_NAME]?course=$course_code'>
                " . rich_text_editor('insertCommentBox', 1, 50, $oldComment) . "
                <input type='hidden' name='cmd' value='update$col_name' />
                <input class='btn btn-primary' type='submit' value=$langSubmit /></form>";
        }
    }

    // delete mode
    if ($mode == DELETE_ && $is_editor) {
        $sql = "UPDATE `" . $tbl_name . "`
                 SET `" . $col_name . "` = ''
                 WHERE " . $where_cond;
        Database::get()->query($sql);
        $dsp = TRUE;
    }

    // display mode only or display was asked by delete mode or update mode
    if ($mode == DISPLAY_ || $dsp == TRUE) {
        $sql = "SELECT `" . $col_name . "`
                FROM `" . $tbl_name . "`
                WHERE " . $where_cond;

        $result = Database::get()->querySingle($sql);
        $currentComment = ($result && !empty($result->$col_name)) ? $result->$col_name : false;

        // display nothing if this is default comment and not an admin
        if (($currentComment == $defaultTxt) && !$is_editor) {
            return $output;
        }

        if (empty($currentComment)) {
            // if no comment and user is admin : display link to add a comment
            if ($is_editor) {
                $output .= '<a href="' . $_SERVER['SCRIPT_NAME'] . '?course=' . $course_code . '&amp;cmd=update' . $col_name . '">'
                    . $langAdd . '</a>';
            }
        } else {
            // display edit and delete links if user as the right to see it
            // display comment
            $output .= standard_text_escape($currentComment);
            
            if ($is_editor) {                
                $output .= "&nbsp;&nbsp;&nbsp;" . icon('fa-edit', $langModify, $_SERVER['SCRIPT_NAME'] . '?course=' . $course_code . '&amp;cmd=update' . $col_name . "");
                $output .= "&nbsp;&nbsp;&nbsp";
                $output .= icon('fa-times', $langDelete, "$_SERVER[SCRIPT_NAME]?course=$course_code&amp;cmd=del$col_name", 'onClick="javascript:if(!confirm(\'' . clean_str_for_javascript($langConfirmDelete) . '\')) return false;"');
                        
            }            
        }
    }

    return $output;
}

/*
 * This function is used to display name of module or learning path with admin links if needed
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
    // globals
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

            $output .= '<form method="POST" action="' . $_SERVER['SCRIPT_NAME'] . '?course=' . $course_code . '">' . "\n";

            if ($formlabel != FALSE) {                
                $output .= '<div class="col-xs-10"><div class="input-group">'
                        . '<input class="form-control" type="text" name="newName" size="50" maxlength="255" value="' . htmlspecialchars($oldName) . '">' . "\n"
                        . '<span class="input-group-btn">'
                        . '<button class="btn btn-primary" type="submit" value="'.$langModify.'">'.$langModify.'</button>'
                        . '</span>'
                        . '</div></div>'
                        . '<input type="hidden" name="cmd" value="updateName" />' . ""
                        . '</form>';
            }
        }
    }

    // display if display mode or asked by the update
    if ($mode == DISPLAY_ || $dsp == true) {
        $sql = "SELECT `name`
                FROM `" . $tbl_name . "`
                WHERE " . $where_cond . " AND `course_id` = ?d";

        $result = Database::get()->querySingle($sql, $course_id);
        $currentName = ($result && !empty($result->name)) ? $result->name : false;
        $output .= q($currentName);

        if ($is_editor) {
            $output .= '&nbsp;&nbsp;&nbsp;'.icon('fa-edit', $langModify, $_SERVER['SCRIPT_NAME'] . '?course=' . $course_code . '&amp;cmd=updateName');
        }
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

    $imgList[CTDOCUMENT_] = "fa-folder-open-o";
    $imgList[CTCLARODOC_] = "fa-folder-open-o";
    $imgList[CTEXERCISE_] = "fa-pencil-square-o";
    $imgList[CTSCORM_] = "fa-pencil-square-o";
    $imgList[CTSCORMASSET_] = "fa-pencil-square-o";
    $imgList[CTLINK_] = "fa-link";
    $imgList[CTCOURSE_DESCRIPTION_] = "fa-info-circle";
    $imgList[CTMEDIA_] = "fa-film";
    $imgList[CTMEDIALINK_] = "fa-film";

    if (array_key_exists($contentType, $imgList)) {
        return $imgList[$contentType];
    }

    return "fa-folder-open-o";
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
           $langLinks, $langCourseDescriptionShort, $langVideo;

    $altList[CTDOCUMENT_] = $langDoc;
    $altList[CTCLARODOC_] = $langDoc;
    $altList[CTEXERCISE_] = $langExercise;
    $altList[CTSCORM_] = $langAltScorm;
    $altList[CTSCORMASSET_] = $langAltScorm;
    $altList[CTLINK_] = $langLinks;
    $altList[CTCOURSE_DESCRIPTION_] = $langCourseDescriptionShort;
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
                $moduleImg = 'fa-pencil-square-o';
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
    $sql = "SELECT UMP.`raw` AS R, UMP.`scoreMax` AS SMax, M.`contentType` AS CTYPE, UMP.`lesson_status` AS STATUS
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
    $modules = Database::get()->queryArray($sql, $lpUid, $lpid, $course_id, CTLABEL_);

    $progress = 0;
    if (!is_array($modules) || empty($modules)) {
        $progression = 0;
    } else {
        // progression is calculated in pourcents
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
                $progress += $modProgress;
            }
        }
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
        $nbrOfVisibleModules = ($result && !empty($result->count)) ? $result->count : false;

        if (is_numeric($nbrOfVisibleModules)) {
            $progression = @round($progress / $nbrOfVisibleModules);
        } else {
            $progression = 0;
        }
    }
    return $progression;
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
    
    global $langAddModulesButton, $langExercise, $langNoEx, $langSelection, $course_code, $course_id;

    $output = "";    
    /* --------------------------------------
      DIALOG BOX SECTION
      -------------------------------------- */
    if (!empty($dialogBox)) {
        $output .= disp_message_box($dialogBox, $style) . '<br />' . "\n";
    }
    $output .= '<form method="POST" name="addmodule" action="' . $_SERVER['SCRIPT_NAME'] . '?course=' . $course_code . '&amp;cmdglobal=add">' . "\n";
    $output .= '<div class="table-responsive"><table class="table-default">' . "\n"
            . '<tr class="list-header">' . ""
            . '<th><div align="left">'
            . $langExercise
            . '</div></th>' . "\n"
            . '<th width="10"><div align="center">'
            . $langSelection
            . '</div></th>' . "\n"
            . '</tr>';

    // Display available modules
    $atleastOne = FALSE;
    $sql = "SELECT `id`, `title`, `description`
            FROM `exercise`
            WHERE course_id = ?d
            AND active = 1
            ORDER BY `title`, `id`";
    $exercises = Database::get()->queryArray($sql, $course_id);

    if (is_array($exercises) && !empty($exercises)) {        
        foreach ($exercises as $exercise) {            

            $output .= '<tr>' 
                    . '<td align="left">'
                    . '<label for="check_' . $exercise->id . '" >'
                    . icon('fa-pencil-square-o', $langExercise) .'&nbsp;'
                    . q($exercise->title)
                    . '</label>'
                    . '<br />';
            // COMMENT
            if (!empty($exercise->description)) {
                $output .= '<span class="comments">' . standard_text_escape($exercise->description) . '</span>'
                        . '</td>';
            } else {
                $output .= '</td>';
            }
            $output .= '<td align="center">'
                    . '<input type="checkbox" name="check_' . $exercise->id . '" id="check_' . $exercise->id . '" value="' . $exercise->id . '" />'
                    . '</td>'
                    . '</tr>';

            $atleastOne = true;            
        }//end while another module to display        
    }

    if (!$atleastOne) {
        $output .= '<tr>'
                . '<td colspan="2" align="center">'
                . $langNoEx
                . '</td>'
                . '</tr>';
    }

    // Display button to add selected modules

    if ($atleastOne) {
        $output .= '<tr>'
                . '<th colspan="2"><div class="pull-right">'
                . '<input class="btn btn-primary" type="submit" name="insertExercise" value="'.$langAddModulesButton.'">'                
                . '</div></th>'
                . '</tr>';
    }
    $output .= '</table></div></form>';            

    return $output;
}

/*
 * This function is used to display the list of document available in the course
 * It also displays the form used to add selected document in the learning path
 *
 * @param string $dialogBox Error or confirmation text
 * @return nothing
 *
 * @author Thanos Kyritsis <atkyritsis@upnet.gr>
 * @author Piraux Sebastien <pir@cerdecam.be>
 * @author Lederer Guillaume <led@cerdecam.be>
 */

function display_my_documents($dialogBox, $style) {
    global $curDirName;
    global $curDirPath;
    global $parentDir;
    global $langUp;
    global $langName;
    global $langSize;
    global $langDate;
    global $langAddModulesButton;
    global $fileList;    
    global $themeimg;
    global $langSelection, $langDirectory, $course_code;

    $output = '';
    
    $dspCurDirName = htmlspecialchars($curDirName);    
    $cmdParentDir = rawurlencode($parentDir);
    
    $output .= '<form action="' . $_SERVER['SCRIPT_NAME'] . '?course=' . $course_code . '" method = "post">';
    /* --------------------------------------
      DIALOG BOX SECTION
      -------------------------------------- */
    $colspan = 5;
    if (!empty($dialogBox)) {
        $output .= disp_message_box($dialogBox, $style) . "<br />";
    }
    
    /* CURRENT DIRECTORY */
    if ($curDirName) {
        $output .= '
        <table class="table-default">
        <tr>
          <td width="1" class="right">'.icon('fa-folder-o').'</td>
          <td>' . $langDirectory . ': <b>' . $dspCurDirName . '</b></td>';
        /* GO TO PARENT DIRECTORY */
        if ($curDirName) /* if the $curDirName is empty, we're in the root point
          and we can't go to a parent dir */ {
            $linkup = "<a href='$_SERVER[SCRIPT_NAME]?course=$course_code&amp;openDir=$cmdParentDir'>";
            $output .= "<td width='1'>$linkup<img src='$themeimg/folder_up.png' " .
                    "hspace='5' alt='$langUp' title='$langUp' /></a></td>" .
                    "<td width='10' class='right'><small>$linkup$langUp</a></small></td>";
        }
        $output .= '</tr></table>';
    }

    $output .= '<div class="table-responsive"><table class="table-default" >';
    $output .= "<tr class='list-header'>
                    <th colspan='2'><div align='left'>&nbsp;&nbsp;$langName</div></th>
                    <th>$langSize</th>
                    <th>$langDate</th>
                    <th>$langSelection</th>
                </tr>";

    // display file list
    if ($fileList) {
        while (list($fileKey, $fileName) = each($fileList['name'])) {
            $dspFileName = q($fileList['filename'][$fileKey]);
            $cmdFileName = str_replace("%2F", "/", rawurlencode($curDirPath . "/" . $fileName));
            if ($fileList['visible'][$fileKey] == 0) {
                continue; // skip the display of this file
            }
            if ($fileList['type'][$fileKey] == A_FILE) {
                $image = choose_image($fileName);
                $size = format_file_size($fileList['size'][$fileKey]);
                $date = nice_format($fileList['date'][$fileKey]);
                $file_url = file_url($fileList['path'][$fileKey], $dspFileName);                
                $play_url = file_playurl($fileList['path'][$fileKey], $dspFileName);
                $urlFileName = MultimediaHelper::chooseMediaAhrefRaw($file_url, $play_url, $dspFileName, $dspFileName);                
                $file_id = $fileList['id'][$fileKey];                
            } elseif ($fileList['type'][$fileKey] == A_DIRECTORY) {
                $image = 'fa-folder';
                $size = '&nbsp;';
                $date = '&nbsp;';
                $urlFileName = '<a href="' . $_SERVER['SCRIPT_NAME'] . '?course=' . $course_code . '&amp;openDir=' . $cmdFileName . '">' . $dspFileName . '</a>';
            }

            $output .= '<tr>
                <td class="center" width="1">' . icon($image, '') . '</td>
                <td align="left">' . $urlFileName . '</td>
                <td width="80" class="center">' . $size . '</td>
                <td width="80" class="center">' . $date . '</td>';

            if ($fileList['type'][$fileKey] == A_FILE) {                                
                $output .= '
                    <td width="10" class="center">
                        <input type="checkbox" name="document[]" value = ' . $file_id . '>
                    </td>';
            } else {
                $output .= '<td>&nbsp;</td>';
            }
            $output .= '</tr>';
            /* COMMENTS */
            if ($fileList['comment'][$fileKey] != "") {
                $fileList['comment'][$fileKey] = q($fileList['comment'][$fileKey]);
                $fileList['comment'][$fileKey] = parse_user_text($fileList['comment'][$fileKey]);
                $output .= '<tr>
                <td>&nbsp;</td>
                <td colspan="' . $colspan . '"><span class="comment">' . $fileList['comment'][$fileKey] . '</span></td>
                </tr>';
            }
        }  // end each ($fileList)
        // form button

        $output .= '
            <tr>
              <th colspan="' . $colspan . '"><div class="pull-right">
                <input type="hidden" name="openDir" value="' . $curDirPath . '" />                
                <input class="btn btn-primary" type="submit" name="submitInsertedDocument" value="'.$langAddModulesButton.'">        
              </th>
            </tr>';
    } // end if ( $fileList)
    else {
        $output .= '<tr><td colspan="4">&nbsp;</td></tr>';
    }
    $output .= '</table></div></form>';
    return $output;
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
    $tree = array();

    if (is_array($list)) {
        foreach ($list as $element) {
            if ($element[$idField] == $id) {
                $tree = $element; // keep all $list informations in the returned array
                // explicitly add 'name' and 'value' for the build_nested_select_menu function
                //$tree['name'] = $element['name']; // useless since 'name' is the same word in db and in the  build_nested_select_menu function
                $tree['value'] = $element[$idField];
                break;
            }
        }

        foreach ($list as $element) {
            if ($element[$parentField] == $id && ( $element[$parentField] != $element[$idField] )) {
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
 * @param $time a suspected SCORM 2004 time value, returned by the javascript API
 *
 * @author Thanos Kyritsis <atkyritsis@upnet.gr>
 */
function isScorm2004Time($time) {
    $mask = "/^PT[0-9]{1,2}H[0-9]{1,2}M[0-9]{2}.?[0-9]?[0-9]?S$/";
    if (preg_match($mask, $time)) {
        return TRUE;
    }
    return FALSE;
}

/**
 * This function allow to see if a time string is the SCORM requested format : hhhh:mm:ss.cc
 *
 * @param $time a suspected SCORM time value, returned by the javascript API
 *
 * @author Lederer Guillaume <led@cerdecam.be>
 */
function isScormTime($time) {
    $mask = "/^[0-9]{2,4}:[0-9]{2}:[0-9]{2}.?[0-9]?[0-9]?$/";
    if (preg_match($mask, $time)) {
        return TRUE;
    }
    return FALSE;
}

/**
 * This function allow to add times saved in the SCORM 2004 requested format:
 * timeinterval(second,10,2): PThHmMsS
 *
 * @param $time1 a suspected SCORM 1.2 time value, total_time,  in the API
 * @param $time2 a suspected SCORM 2004 time value, session_time to add, in the API
 *
 * @author Thanos Kyritsis <atkyritsis@upnet.gr>
 *
 */
function addScorm2004Time($time1, $time2) {
    if (isScorm2004Time($time2)) {
        //extract hours, minutes, secondes, ... from time1 and time2

        $mask = "/^([0-9]{2,4}):([0-9]{2}):([0-9]{2}).?([0-9]?[0-9]?)$/";
        $mask2004 = "/^PT([0-9]{1,2})H([0-9]{1,2})M([0-9]{2}).?([0-9]?[0-9]?)S$/";

        preg_match($mask, $time1, $matches);
        $hours1 = $matches[1];
        $minutes1 = $matches[2];
        $secondes1 = $matches[3];
        $primes1 = $matches[4];

        preg_match($mask2004, $time2, $matches);
        $hours2 = $matches[1];
        $minutes2 = $matches[2];
        $secondes2 = $matches[3];
        $primes2 = $matches[4];

        // calculate the resulting added hours, secondes, ... for result

        $primesReport = FALSE;
        $secondesReport = FALSE;
        $minutesReport = FALSE;
        $hoursReport = FALSE;

        //calculate primes

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

        //calculate secondes

        $total_secondes = $secondes1 + $secondes2;
        if ($primesReport) {
            $total_secondes ++;
        }
        if ($total_secondes >= 60) {
            $total_secondes -= 60;
            $secondesReport = TRUE;
        }

        //calculate minutes

        $total_minutes = $minutes1 + $minutes2;
        if ($secondesReport) {
            $total_minutes ++;
        }
        if ($total_minutes >= 60) {
            $total_minutes -= 60;
            $minutesReport = TRUE;
        }

        //calculate hours

        $total_hours = $hours1 + $hours2;
        if ($minutesReport) {
            $total_hours ++;
        }
        if ($total_hours >= 10000) {
            $total_hours -= 10000;
            $hoursReport = TRUE;
        }

        // construct and return result string

        if ($total_hours < 10) {
            $total_hours = "0" . $total_hours;
        }
        if ($total_minutes < 10) {
            $total_minutes = "0" . $total_minutes;
        }
        if ($total_secondes < 10) {
            $total_secondes = "0" . $total_secondes;
        }

        $total_time = $total_hours . ":" . $total_minutes . ":" . $total_secondes;
        // add primes only if != 0
        if ($total_primes != 0) {
            $total_time .= "." . $total_primes;
        }
        return $total_time;
    } else {
        return $time1;
    }
}

/**
 * This function allow to add times saved in the SCORM requested format : hhhh:mm:ss.cc
 *
 * @param $time1 a suspected SCORM time value, total_time,  in the API
 * @param $time2 a suspected SCORM time value, session_time to add, in the API
 *
 * @author Lederer Guillaume <led@cerdecam.be>
 *
 */
function addScormTime($time1, $time2) {
    if (isScormTime($time2)) {
        //extract hours, minutes, secondes, ... from time1 and time2

        $mask = "/^([0-9]{2,4}):([0-9]{2}):([0-9]{2}).?([0-9]?[0-9]?)$/";

        preg_match($mask, $time1, $matches);
        $hours1 = $matches[1];
        $minutes1 = $matches[2];
        $secondes1 = $matches[3];
        $primes1 = $matches[4];

        preg_match($mask, $time2, $matches);
        $hours2 = $matches[1];
        $minutes2 = $matches[2];
        $secondes2 = $matches[3];
        $primes2 = $matches[4];

        // calculate the resulting added hours, secondes, ... for result

        $primesReport = FALSE;
        $secondesReport = FALSE;
        $minutesReport = FALSE;
        $hoursReport = FALSE;

        //calculate primes

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

        //calculate secondes

        $total_secondes = $secondes1 + $secondes2;
        if ($primesReport) {
            $total_secondes ++;
        }
        if ($total_secondes >= 60) {
            $total_secondes -= 60;
            $secondesReport = TRUE;
        }

        //calculate minutes

        $total_minutes = $minutes1 + $minutes2;
        if ($secondesReport) {
            $total_minutes ++;
        }
        if ($total_minutes >= 60) {
            $total_minutes -= 60;
            $minutesReport = TRUE;
        }

        //calculate hours

        $total_hours = $hours1 + $hours2;
        if ($minutesReport) {
            $total_hours ++;
        }
        if ($total_hours >= 10000) {
            $total_hours -= 10000;
            $hoursReport = TRUE;
        }

        // construct and return result string

        if ($total_hours < 10) {
            $total_hours = "0" . $total_hours;
        }
        if ($total_minutes < 10) {
            $total_minutes = "0" . $total_minutes;
        }
        if ($total_secondes < 10) {
            $total_secondes = "0" . $total_secondes;
        }

        $total_time = $total_hours . ":" . $total_minutes . ":" . $total_secondes;
        // add primes only if != 0
        if ($total_primes != 0) {
            $total_time .= "." . $total_primes;
        }
        return $total_time;
    } else {
        return $time1;
    }
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

function disp_message_box1($message, $style = FALSE) {
    if ($style) {
        $cell = "";
    } else {
        $cell = "";
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
 * @param integer $progress progression in pourcent
 * @param integer $factor will be multiply by 100 to have the full size of the bar
 * (i.e. 1 will give a 100 pixel wide bar)
 */

function disp_progress_bar($progress, $factor) {
    global $themeimg, $langProgress;

    $maxSize = $factor * 100; //pixels
    $barwidth = $factor * $progress;

    // display progress bar
    // origin of the bar
    $progressBar = "
    <div class='progress' style='display: inline-block; width: 200px; margin-bottom:0px;'>
        <div class='progress-bar' role='progressbar' aria-valuenow='60' aria-valuemin='0' aria-valuemax='100' style='width: $progress%; min-width: 2em;'>
            $progress%
        </div>
    </div>";
    return $progressBar;
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
    return '<select name="' . $name . '">' . "\n"
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
 * This function accepts a sql query and a limiter number as arguments. Then it
 * limits the query's results into multiple pages and returns html code for
 * presenting links in order to browse through these pages. Should be used
 * together with get_limited_list().
 *
 * @param string $sql contains the sql query we want to limit
 * @param int $limiter how many entries we want to limit at
 * @param string $stringPreviousPage the string for the previous page title
 * @param string $stringNextPage the string for the next page title
 * @return string containing the links html code for browsing the pages
 * @author Thanos Kyritsis <atkyritsis@upnet.gr>
 */

function get_limited_page_links($sql, $limiter, $stringPreviousPage, $stringNextPage) {
    global $course_code;

    $totalnum = count(Database::get()->queryArray($sql));
    $firstpage = 1;
    $lastpage = ceil($totalnum / $limiter);

    if (isset($_GET['page']) && is_numeric($_GET['page'])) {
        $currentpage = (int) $_GET['page'];
        if ($currentpage < $firstpage || $currentpage > $lastpage) {
            $currentpage = $firstpage;
        }
    } else {
        $currentpage = $firstpage;
    }

    $prevpage = $currentpage - 1;
    $nextpage = $currentpage + 1;

    $url = basename($_SERVER['SCRIPT_NAME']) . "?course=$course_code";

    switch ($_SERVER['argc']) {
        case 0:
            $url .= "&amp;page=";
            break;
        case 1:
            $arguments = preg_replace('/[&|?]page=.*$/', '', '?' . $_SERVER['argv'][0]);

            if (!strcmp($arguments, NULL)) {
                $url .= "&amp;page=";
            } else {
                $url .= $arguments . "&amp;page=";
            }
            break;
        default:
            $url .= "&amp;page=";
            break;
    }

    if (isset($_REQUEST['path_id'])) {
        $prevstring = "<a href=\"" . $url . $prevpage . "&amp;path_id=" . urlencode($_REQUEST[path_id]) . "\">" . $stringPreviousPage . "</a> | ";
        $nextstring = "<a href=\"" . $url . $nextpage . "&amp;path_id=" . urlencode($_REQUEST[path_id]) . "\">" . $stringNextPage . "</a>";
    } else {
        $prevstring = "<a href=\"" . $url . $prevpage . "\">" . $stringPreviousPage . "</a> | ";
        $nextstring = "<a href=\"" . $url . $nextpage . "\">" . $stringNextPage . "</a>";
    }

    if ($currentpage == $firstpage) {
        $prevstring = $stringPreviousPage . " | ";
    }

    if ($currentpage == $lastpage) {
        $nextstring = $stringNextPage;
    }

    $wholestring = "<p>" . $prevstring . $nextstring . "</p>";

    if ($lastpage == $firstpage) {
        $wholestring = "";
    }

    return $wholestring;
}

/*
 * This function accepts a sql query and a limiter number as arguments. Then it
 * limits the query's results into multiple pages and returns the proper list
 * of results for the proper page we are currently browsing. Should be used
 * together with get_limited_page_links().
 *
 * @param string $sql contains the sql query we want to limit
 * @param int $limiter how many entries we want to limit at
 * @return string contains the links' html code for browsing the pages
 * @author Thanos Kyritsis <atkyritsis@upnet.gr>
 */

function get_limited_list($sql, $limiter) {
    $totalnum = count(Database::get()->queryArray($sql));
    $firstpage = 1;
    $lastpage = ceil($totalnum / $limiter);

    if (isset($_GET['page']) && is_numeric($_GET['page'])) {
        $currentpage = (int) $_GET['page'];
        if ($currentpage < $firstpage || $currentpage > $lastpage) {
            $currentpage = $firstpage;
        }
    } else {
        $currentpage = $firstpage;
    }

    $limit = ($currentpage - 1) * $limiter;

    $sql .= " LIMIT " . $limit . "," . $limiter;

    return Database::get()->queryArray($sql);
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
