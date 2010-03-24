<?php

/*=============================================================================
       	GUnet eClass 2.0
        E-learning and Course Management Program
================================================================================
       	Copyright(c) 2003-2010  Greek Universities Network - GUnet
        A full copyright notice can be read in "/info/copyright.txt".

       	Authors:    Costas Tsibanis <k.tsibanis@noc.uoa.gr>
                     Yannis Exidaridis <jexi@noc.uoa.gr>
                     Alexandros Diamantidis <adia@noc.uoa.gr>

        For a full list of contributors, see "credits.txt".

        This program is a free software under the terms of the GNU
        (General Public License) as published by the Free Software
        Foundation. See the GNU License for more details.
        The full license can be read in "license.txt".

       	Contact address: GUnet Asynchronous Teleteaching Group,
        Network Operations Center, University of Athens,
        Panepistimiopolis Ilissia, 15784, Athens, Greece
        eMail: eclassadmin@gunet.gr
==============================================================================*/

/*===========================================================================
	learnPathLib.inc.php
	@last update: 29-08-2009 by Thanos Kyritsis
	@authors list: Thanos Kyritsis <atkyritsis@upnet.gr>

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
define ( 'CTCLARODOC_', 'CLARODOC' );
define ( 'CTDOCUMENT_', 'DOCUMENT' );
define ( 'CTEXERCISE_', 'EXERCISE' );
define ( 'CTSCORM_', 'SCORM' );
define ( 'CTSCORMASSET_', 'SCORM_ASSET');
define ( 'CTLABEL_', 'LABEL' );
define ( 'CTCOURSE_DESCRIPTION_', 'COURSE_DESCRIPTION' );
define ( 'CTLINK_', 'LINK' );

/*
* mode used by {@link commentBox($type, $mode)} and {@link nameBox($type, $mode)}
*/
define ( 'DISPLAY_', 1 );
define ( 'UPDATE_', 2 );
define ( 'UPDATENOTSHOWN_', 4 );
define ( 'DELETE_', 3 );
define ( 'ASSET_', 1 );
define ( 'MODULE_', 2 );
define ( 'LEARNINGPATH_', 3 );
define ( 'LEARNINGPATHMODULE_', 4 );

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
function commentBox($type, $mode)
{
    $tbl_lp_learnPath            = "lp_learnPath";
    $tbl_lp_rel_learnPath_module = "lp_rel_learnPath_module";
    $tbl_lp_module               = "lp_module";
    // globals
    global $is_adminOfCourse;
    global $langModify, $langOk, $langErrorNameAlreadyExists, $langAdd, $langConfirmYourChoice;
    global $langDefaultLearningPathComment, $langDefaultModuleComment;
    global $langDefaultModuleAddedComment, $langDelete;
    // will be set 'true' if the comment has to be displayed
    $dsp = false;
    $output = "";

    // those vars will be used to build sql queries according to the comment type
    switch ( $type )
    {
        case MODULE_ :
            $defaultTxt = $langDefaultModuleComment;
            $col_name = 'comment';
            $tbl_name = $tbl_lp_module;
            if ( isset($_REQUEST['module_id'] ) )
            {
                $module_id = $_REQUEST['module_id'];
            }
            else
            {
                $module_id = $_SESSION['lp_module_id'];
            }
            $where_cond = "`module_id` = " . (int) $module_id;  // use backticks ( ` ) for col names and simple quote ( ' ) for string
            break;
        case LEARNINGPATH_ :
            $defaultTxt = $langDefaultLearningPathComment;
            $col_name = 'comment';
            $tbl_name = $tbl_lp_learnPath;
            $where_cond = '`learnPath_id` = '. (int) $_SESSION['path_id'];  // use backticks ( ` ) for col names and simple quote ( ' ) for string
            break;
        case LEARNINGPATHMODULE_ :
            $defaultTxt = $langDefaultModuleAddedComment;
            $col_name = 'specificComment';
            $tbl_name = $tbl_lp_rel_learnPath_module;
            $where_cond = "`learnPath_id` = " . (int) $_SESSION['path_id'] . "
              	AND `module_id` = " . (int) $_SESSION['lp_module_id'];
            break;
    }

    // update mode
    // allow to chose between
    // - update and show the comment and the pencil and the delete cross (UPDATE_)
    // - update and nothing displayed after form sent (UPDATENOTSHOWN_)
    if ( ( $mode == UPDATE_ || $mode == UPDATENOTSHOWN_ )  && $is_adminOfCourse )
    {
        if ( isset($_POST['insertCommentBox']) )
        {
            $sql = "UPDATE `" . $tbl_name . "`
                           SET `" . $col_name . "` = \"". addslashes($_POST['insertCommentBox'])."\"
                         WHERE " . $where_cond;
            db_query($sql);

            if($mode == UPDATE_)
            	$dsp = true;
            elseif($mode == UPDATENOTSHOWN_)
            	$dsp = false;
        }
        else // display form
        {
            // get info to fill the form in
            $sql = "SELECT `".$col_name."`
                       FROM `" . $tbl_name . "`
                      WHERE " . $where_cond;
            $oldComment = db_query_get_single_value($sql);

            $output .= '
      <form method="POST" action="'.$_SERVER['PHP_SELF'].'">' . "\n"
                .disp_html_area('insertCommentBox', $oldComment, 1, 50) . "\n"
                .'        <input type="hidden" name="cmd" value="update' . $col_name . '" />' . "\n"
                .'        <input type="submit" value="' . $langOk . '" />' . "\n"
                .'      </form>' . "\n"
            ;
        }

    }

    // delete mode
    if ( $mode == DELETE_ && $is_adminOfCourse)
    {
        $sql =  "UPDATE `" . $tbl_name . "`
                 SET `" . $col_name . "` = ''
                 WHERE " . $where_cond;
        db_query($sql);
        $dsp = TRUE;
    }

    // display mode only or display was asked by delete mode or update mode
    if ( $mode == DISPLAY_ || $dsp == TRUE )
    {
        $sql = "SELECT `".$col_name."`
                FROM `" . $tbl_name . "`
                WHERE " . $where_cond;

        $result = db_query($sql);
        if($result)
        {
           list($value) = mysql_fetch_row($result);
           mysql_free_result($result);
           $currentComment = $value;
        }
        else
        {
           $currentComment = false;
        }

        // display nothing if this is default comment and not an admin
        if ( ($currentComment == $defaultTxt) && !$is_adminOfCourse ) return $output;

        if ( empty($currentComment) )
        {
            // if no comment and user is admin : display link to add a comment
            if ( $is_adminOfCourse )
            {
                $output .= '' . "\n"
                .    '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=update' . $col_name . '">' . "\n"
                .    $langAdd . '</a>' . "\n"
                ;
            }
        }
        else
        {
            // display comment
            $output .= $currentComment;
            // display edit and delete links if user as the right to see it
            if ( $is_adminOfCourse )
            {
                $output .= '&nbsp;&nbsp;&nbsp;<a href="' . $_SERVER['PHP_SELF'] . '?cmd=update' . $col_name . '">' . "\n"
                .    '<img src="../../template/classic/img/edit.gif" alt="' . $langModify . '" title="'.$langModify.'" />'
                .    '</a>' . "\n"
                .    '<a href="' . $_SERVER['PHP_SELF'].'?cmd=del' . $col_name . '" '
                .    ' onclick="javascript:if(!confirm(\''.clean_str_for_javascript($langConfirmYourChoice).'\')) return false;">' . "\n"
                .    '<img src="../../template/classic/img/delete.gif" alt="'.$langDelete.'" title="'.$langDelete.'" /></a>' . "\n"
                ;
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
function nameBox($type, $mode, $formlabel = FALSE)
{
    $tbl_lp_learnPath            = "lp_learnPath";
    $tbl_lp_module               = "lp_module";

    // globals
    global $is_adminOfCourse;
    global $urlAppend, $langLearningPath1;
    global $langModify, $langOk, $langErrorNameAlreadyExists;

    // $dsp will be set 'true' if the comment has to be displayed
    $dsp = FALSE;
    $output = "";

    // those vars will be used to build sql queries according to the name type
    switch ( $type )
    {
        case MODULE_ :
            $col_name = 'name';
            $tbl_name = $tbl_lp_module;
            $where_cond = '`module_id` = ' . (int) $_SESSION['lp_module_id'];
            break;
        case LEARNINGPATH_ :
            $col_name = 'name';
            $tbl_name = $tbl_lp_learnPath;
            $where_cond = '`learnPath_id` = ' . (int) $_SESSION['path_id'];
            break;
    }

    // update mode
    if ( $mode == UPDATE_ && $is_adminOfCourse)
    {

        if ( isset($_POST['newName']) && !empty($_POST['newName']) )
        {

            $sql = "SELECT COUNT(`" . $col_name . "`)
                                 FROM `" . $tbl_name . "`
                                WHERE `" . $col_name . "` = '" . addslashes($_POST['newName']) . "'
                                  AND !(" . $where_cond . ")";
            $num = db_query_get_single_value($sql);

            if ($num == 0)  // name doesn't already exists
            {

                $sql = "UPDATE `" . $tbl_name . "`
                            SET `" . $col_name . "` = '" . addslashes($_POST['newName']) ."'
                            WHERE " . $where_cond;

                db_query($sql);
                $dsp = TRUE;
            }
            else
            {
                $output .= $langErrorNameAlreadyExists . '<br />';
                $dsp = TRUE;
            }
        }
        else // display form
        {
            $sql = "SELECT `name`
                    FROM `" . $tbl_name . "`
                    WHERE " . $where_cond;

            $oldName = db_query_get_single_value($sql);

            $output .= '
      <form method="POST" action="' . $_SERVER['PHP_SELF'].'">' . "\n";

             if($formlabel != FALSE)
             	//$output .= '<label for="newLabel">'.$formlabel.'</label>&nbsp;&nbsp;';

             $output .=  '<input type="text" name="newName" size="50" maxlength="255" value="'.htmlspecialchars($oldName).'" / class="FormData_InputText">' ."\n"
             .    '        <input type="hidden" name="cmd" value="updateName" />' ."\n"
             .    '        <input type="submit" value="' . $langOk . '" />' . "\n"
             .    '      </form>';
        }

    }

    // display if display mode or asked by the update
    if ( $mode == DISPLAY_ || $dsp == true )
    {
        $sql = "SELECT `name`
                FROM `" . $tbl_name . "`
                WHERE " . $where_cond;

        $result = db_query($sql);
        if($result)
        {
           list($value) = mysql_fetch_row($result);
           mysql_free_result($result);
           $currentName = $value;
        }
        else
        {
           $currentName = false;
        }

        //$output .= '<strong>'
        $output .=  $currentName;

        if ( $is_adminOfCourse )
            $output .= '&nbsp;&nbsp;&nbsp;<a href="' . $_SERVER['PHP_SELF'] . '?cmd=updateName">'
            .    '<img src="../../template/classic/img/edit.gif" alt="' . $langModify . '" title="' . $langModify . '" />'
            .    '</a>' . "\n";
        //$output .= '</strong>'."\n\n";
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
 function selectImage($contentType)
 {

      $imgList[CTDOCUMENT_] = "docs_on.gif";
      $imgList[CTCLARODOC_] = "clarodoc.gif";
      $imgList[CTEXERCISE_] = "exercise_on.gif";
      $imgList[CTSCORM_] = "scorm.gif";
      $imgList[CTSCORMASSET_] = "scorm.gif";
      $imgList[CTLINK_] = "links_on.gif";
      $imgList[CTCOURSE_DESCRIPTION_] = "description_on.gif";

      if (array_key_exists( $contentType , $imgList ))
      {
          return $imgList[$contentType];
      }

      return "docs_on.gif";

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
 function selectAlt($contentType)
 {
      global $langDoc, $langExercise, $langAltScorm;

      $altList[CTDOCUMENT_] = $langDoc;
      $altList[CTCLARODOC_] = $langDoc;
      $altList[CTEXERCISE_] = $langExercise;
      $altList[CTSCORM_] = $langAltScorm;
      $altList[CTSCORMASSET_] = $langAltScorm;

      if (array_key_exists( $contentType , $altList ))
      {
          return $altList[$contentType];
      }

      return "default.gif";
 }



/*
 * Check if an input string is a number
 *
 * @param string $var input to check
 * @return bool true if $var is a number, false otherwise
 *
 * @author Piraux Sebastien <pir@cerdecam.be>
 */
function is_num($var)
{
    for ( $i = 0; $i < strlen($var); $i++ )
    {
        $ascii = ord($var[$i]);

        // 48 to 57 are decimal ascii values for 0 to 9
        if ( $ascii >= 48 && $ascii <= 57)
        	continue;
        else
        	return FALSE;
    }

    return TRUE;
}


/*
 *  This function allows to display the modules content of a learning path.
 *  The function must be called from inside a learning path where the session variable path_id is known.
 */
function display_path_content()
{
    $tbl_lp_learnPath            = "lp_learnPath";
    $tbl_lp_rel_learnPath_module = "lp_rel_learnPath_module";
    $tbl_lp_user_module_progress = "lp_user_module_progress";
    $tbl_lp_module               = "lp_module";
    $tbl_lp_asset                = "lp_asset";

    global $_cid;
    global $langModule;
    global $imgRepositoryWeb;
    $style = "";
    $output = "";

    $sql = "SELECT M.`name`, M.`contentType`,
                   LPM.`learnPath_module_id`, LPM.`parent`,
                   A.`path`
            FROM `" . $tbl_lp_learnPath . "` AS LP,
                 `" . $tbl_lp_rel_learnPath_module . "` AS LPM,
                 `" . $tbl_lp_module . "` AS M
            LEFT JOIN `" . $tbl_lp_asset . "` AS A
              ON M.`startAsset_id` = A.`asset_id`
            WHERE LP.`learnPath_id` = " .  (int) $_SESSION['path_id'] . "
              AND LP.`learnPath_id` = LPM.`learnPath_id`
              AND LPM.`module_id` = M.`module_id`
            ORDER BY LPM.`rank`";
    $moduleList = db_query_fetch_all($sql);

    $extendedList = array();
    foreach( $moduleList as $module)
    {
        $extendedList[] = $module;
    }
    // build the array of modules
    // build_element_list return a multi-level array, where children is an array with all nested modules
    // build_display_element_list return an 1-level array where children is the deep of the module
    $flatElementList = build_display_element_list(build_element_list($extendedList, 'parent', 'learnPath_module_id'));

    // look for maxDeep
    $maxDeep = 1; // used to compute colspan of <td> cells
    for ($i = 0 ; $i < sizeof($flatElementList) ; $i++)
    {
        if ($flatElementList[$i]['children'] > $maxDeep) $maxDeep = $flatElementList[$i]['children'] ;
    }

    $output .= "\n".'<table width="99%">'."\n\n"
    .    '<thead>'."\n"
    .    '<tr align="center" valign="top">'."\n"
	.    '<th colspan="' . ($maxDeep+1).'">' . $langModule . '</th>'."\n"
    .    '</tr>'."\n"
    .    '</thead>'."\n\n"
	.	 '<tbody>'."\n"
    ;

    foreach ($flatElementList as $module)
    {
        $spacingString = '';
        for($i = 0; $i < $module['children']; $i++)
        	$spacingString .= '<td width="5">&nbsp;</td>'."\n";
        $colspan = $maxDeep - $module['children']+1;

        $output .= '<tr align="center" '.$style.'>' . "\n"
        .    $spacingString
        .    '<td colspan="' . $colspan . '" align="left">'
        ;

        if ($module['contentType'] == CTLABEL_) // chapter head
        {
            $output .= '<b>' . $module['name'] . '</b>';
        }
        else // module
        {
            if($module['contentType'] == CTEXERCISE_ )
            	$moduleImg = 'exercise_on.gif';
            else if($module['contentType'] == CTLINK_ )
        		$moduleImg = "links_on.gif";
        	else if($module['contentType'] == CTCOURSE_DESCRIPTION_ )
        		$moduleImg = "description_on.gif";
            else
            	$moduleImg = choose_image(basename($module['path']));

            $contentType_alt = selectAlt($module['contentType']);

            $output .= '<img src="' . $imgRepositoryWeb . $moduleImg . '" alt="' .$contentType_alt.'" title="' .$contentType_alt.'" />'
            .    $module['name']
            ;
        }
        $output .= '</td>'."\n"
		.	 '</tr>'."\n\n";
    }
    $output .= '</tbody>'."\n\n"
	.	 '</table>'."\n\n";

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
function get_learnPath_progress($lpid, $lpUid)
{

    $tbl_lp_learnPath            = "lp_learnPath";
    $tbl_lp_rel_learnPath_module = "lp_rel_learnPath_module";
    $tbl_lp_user_module_progress = "lp_user_module_progress";
    $tbl_lp_module               = "lp_module";

    // find progression for this user in each module of the path

    $sql = "SELECT UMP.`raw` AS R, UMP.`scoreMax` AS SMax, M.`contentType` AS CTYPE, UMP.`lesson_status` AS STATUS
             FROM `" . $tbl_lp_learnPath . "` AS LP,
                  `" . $tbl_lp_rel_learnPath_module . "` AS LPM,
                  `" . $tbl_lp_user_module_progress . "` AS UMP,
                  `" . $tbl_lp_module . "` AS M
            WHERE LP.`learnPath_id` = LPM.`learnPath_id`
              AND LPM.`learnPath_module_id` = UMP.`learnPath_module_id`
              AND UMP.`user_id` = " . (int) $lpUid . "
              AND LP.`learnPath_id` = " . (int) $lpid . "
              AND LPM.`visibility` = 'SHOW'
              AND M.`module_id` = LPM.`module_id`
              AND M.`contentType` != '" . CTLABEL_ . "'";

    $result = db_query($sql);
	$modules = array();

    while( $row = mysql_fetch_array($result) )
    {
        $modules [] = $row;
    }
    mysql_free_result($result);
    $progress = 0;
    if( !is_array($modules) || empty($modules) )
    {
        $progression = 0;
    }
    else
    {
        // progression is calculated in pourcents
        foreach( $modules as $module )
        {
            if( $module['SMax'] <= 0 )
            {
                $modProgress = 0 ;
            }
            else
            {
                $modProgress = @round($module['R']/$module['SMax']*100);
            }

            // in case of scorm module, progression depends on the lesson status value
            if (($module['CTYPE']=="SCORM") && ($module['SMax'] <= 0) && (( $module['STATUS'] == 'COMPLETED') || ($module['STATUS'] == 'PASSED')))
            {
                $modProgress = 100;
            }

            if ($modProgress >= 0)
            {
                $progress += $modProgress;
            }
        }
        // find number of visible modules in this path
        $sqlnum = "SELECT COUNT(M.`module_id`)
                    FROM `" . $tbl_lp_rel_learnPath_module . "` AS LPM,
                          `". $tbl_lp_module . "` AS M
                    WHERE LPM.`learnPath_id` = " . (int) $lpid . "
                    AND LPM.`visibility` = 'SHOW'
                    AND M.`contentType` != '" . CTLABEL_ . "'
                    AND M.`module_id` = LPM.`module_id`
                    ";
        $result = db_query($sqlnum);
        if($result) {
            list($value) = mysql_fetch_row($result);
            mysql_free_result($result);
            $nbrOfVisibleModules = $value;
        }
        else {
            $nbrOfVisibleModules = false;
        }


		if( is_numeric($nbrOfVisibleModules) )
          	$progression = @round($progress/$nbrOfVisibleModules);
		else
			$progression = 0;

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
function display_my_exercises($dialogBox, $style)
{
    $tbl_quiz_test = "exercices";

    global $langAddModule;
    global $langAddModulesButton;
    global $langExercise;
    global $langNoEx;
    global $langAddOneModuleButton;
    global $imgRepositoryWeb, $langComment, $langSelection;
    $output = "";

    $output .= '<!-- display_my_exercises output -->' . "\n\n";
    /*--------------------------------------
    DIALOG BOX SECTION
    --------------------------------------*/
    $colspan = 3;
    if( !empty($dialogBox) )
    {
        $output .= disp_message_box($dialogBox, $style).'<br />'."\n";
    }
    $output .= '    <form method="POST" name="addmodule" action="' . $_SERVER['PHP_SELF'] . '?cmdglobal=add">'."\n";
    $output .= '    <table width="99%" class="LearnPathSum">'."\n"
    .    '    <thead>'."\n"
    .    '    <tr align="center" class="LP_header">'."\n"
    .    '      <td><div align="left">'
    .    $langExercise
    .    '</div></td>'."\n"
    .    '      <td width="30%"><div align="center">'
    .    $langSelection
    .    '</div></td>'."\n"
    .    '    </tr>'."\n"
    .    '    </thead>'."\n"
    ;

    // Display available modules
    $atleastOne = FALSE;
    $sql = "SELECT `id`, `titre` AS `title`, `description`
            FROM `" . $tbl_quiz_test . "`
            ORDER BY  `titre`, `id`";
    $exercises = db_query_fetch_all($sql);

    if( is_array($exercises) && !empty($exercises) )
    {
		$output .= '    <tbody>' . "\n";

	    foreach ( $exercises as $exercise )
	    {
	        $output .= '    <tr>'."\n"
	        .    '      <td align="left">'
	        .    '<label for="check_'.$exercise['id'].'" >'
	        .    '<img src="' . $imgRepositoryWeb . 'exercise_on.gif" alt="' . $langExercise . '" title="' . $langExercise . '" />&nbsp;'
	        .    $exercise['title']
	        .    '</label>'
	        .    '<br />'."\n";
	        // COMMENT
	        if( !empty($exercise['description']) )
	        {
	            $output .= '      <small class="comments">' . $exercise['description'] . '</small>'
	            .    '</td>'."\n"
	            ;
	        } else {
	            $output .= '</td>'."\n"
	            ;
            }
	        $output .= '      <td align="center">'
	        .    '<input type="checkbox" name="check_' . $exercise['id'] . '" id="check_' . $exercise['id'] . '" value="' . $exercise['id'] . '" />'
	        .    '</td>'."\n"
	        .    '    </tr>'."\n"
	        ;


	        $atleastOne = true;
	    }//end while another module to display
	    //$output .= '    </tbody>'."\n";
	}

    if( !$atleastOne )
    {
        $output .= '    <tr>'."\n"
		.	 '      <td colspan="2" align="center">'
        .    $langNoEx
        .    '</td>'."\n"
		.	 '    </tr>'."\n"
        ;
    }

    // Display button to add selected modules

    if( $atleastOne )
    {
        $output .= '    <tr>'."\n"
		.	 '      <td>&nbsp;</td>'."\n"
		.	 '      <td>'
        .    '<input type="submit" name="insertExercise" value="'.$langAddModulesButton.'" class="LP_button"/>'
        .    '</td>'."\n"
		.	 '    </tr>'."\n"
        ;
    }
    $output .= '    </tbody>'."\n"
    .    '    </table>'."\n\n"
    .    '    </form>'."\n\n"
    .    '    <!-- end of display_my_exercises output -->' . "\n"
    ;

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

function display_my_documents($dialogBox, $style)
{
    global $is_adminOfCourse;
    global $courseDir;
    global $baseWorkDir;
    global $curDirName;
    global $curDirPath;
    global $parentDir;
    global $langAddModule;
    global $langUp;
    global $langName;
    global $langSize;
    global $langDate;
    global $langOk;
    global $langAddModulesButton;
    global $fileList;
    global $imgRepositoryWeb;
    global $secureDocumentDownload, $langSelection, $langDirectory;

    $output = "";
    /*
     * DISPLAY
     */

    $output .= '<!-- display_my_documents output -->' . "\n";
    $dspCurDirName = htmlspecialchars($curDirName);
    $cmdCurDirPath = rawurlencode($curDirPath);
    $cmdParentDir  = rawurlencode($parentDir);

    $output .= '
    <form action="' . $_SERVER['PHP_SELF'] . '" method="POST">';

    /*--------------------------------------
    DIALOG BOX SECTION
    --------------------------------------*/
    $colspan = 5;
    if( !empty($dialogBox) )
    {
        $output .= disp_message_box($dialogBox, $style)."<br />";
    }
    /*--------------------------------------
    CURRENT DIRECTORY LINE
    --------------------------------------*/



    /* CURRENT DIRECTORY */
    if ($curDirName) {
        $output .= '
    <table width="99%" class="FormData">
    <thead>
    <tr>
      <td width="1" class="right"><img src="' . $imgRepositoryWeb . 'opendir.gif" align="absbottom" vspace=2 hspace=5 alt="" /></td>
      <td>'.$langDirectory.': <b>'.$dspCurDirName.'</b></td>';
    /* GO TO PARENT DIRECTORY */
    if ($curDirName) /* if the $curDirName is empty, we're in the root point
    and we can't go to a parent dir */
    {
        $output .= '
      <td width="1" ><img src="' . $imgRepositoryWeb . 'parent.gif" border="0" align="absbottom" hspace="5" alt="" /></td>
      <td width="10" class="right"><a href="' . $_SERVER['PHP_SELF'] . '?openDir=' . $cmdParentDir . '"><small>' . $langUp . '</small></a></td>';
    }
        $output .= '
    </tr>
    </thead>
    </table>';
    }


    $output .= '
    <table width="99%" class="LearnPathSum">';
    $output .= "
    <thead>
    <tr class=\"LP_header\">
      <td colspan=\"2\"><div align=\"center\">$langName</div></td>
      <td><div align=\"center\">$langSize</div></tdh>
      <td><div align=\"center\">$langDate</div></td>
      <td><div align=\"center\">$langSelection</div></td>
      </tr>
    </thead>
    <tbody>";


    /*--------------------------------------
    DISPLAY FILE LIST
    --------------------------------------*/

    if ( $fileList )
    {
        $iterator = 0;
        while ( list( $fileKey, $fileName ) = each ( $fileList['name'] ) )
        {
		$dspFileName = htmlspecialchars($fileList['filename'][$fileKey]);
            	$cmdFileName = str_replace("%2F","/",rawurlencode($curDirPath."/".$fileName));

            if ($fileList['visibility'][$fileKey] == "i")
            {
                if ($is_adminOfCourse)
                {
                    $style = ' class="invisible"';
                }
                else
                {
                    $style = "";
                    continue; // skip the display of this file
                }
            }
            else
            {
                $style="";
            }

            if ($fileList['type'][$fileKey] == A_FILE)
            {
                $image       = choose_image($fileName);
                $size        = format_file_size($fileList['size'][$fileKey]);
                $date        = format_date($fileList['date'][$fileKey]);
                $doc_url = $cmdFileName;
                $urlFileName = "../../".$courseDir.$doc_url;
            }
            elseif ($fileList['type'][$fileKey] == A_DIRECTORY)
            {
                $image       = 'folder.gif';
                $size        = '&nbsp;';
                $date        = '&nbsp;';
                $urlFileName = $_SERVER['PHP_SELF'] . '?openDir=' . $cmdFileName;
            }

            $output .= '
    <tr align="center" ' . $style . '>
      <td align="left" width="1"><img src="' . $imgRepositoryWeb . $image . '" hspace="5" /></td>
      <td align="left"><a href="' . $urlFileName . '" ' . $style . '>'.$dspFileName.'</a></td>
      <td width="100"><small>' . $size . '</small></td>
      <td width="100"><small>' . $date . '</small></td>';

            if ($fileList['type'][$fileKey] == A_FILE)
            {
                $iterator++;
                $output .= '
      <td>
        <input type="checkbox" name="insertDocument_' . $iterator . '" id="insertDocument_' . $iterator . '" value="' . $curDirPath . "/" . $fileName . '" />
        <input type="hidden" name="filenameDocument_' . $iterator . '" id="filenameDocument_' . $iterator . '" value="' .$dspFileName .'" />
      </td>';
            }
            else
            {
                $output .= '
      <td>&nbsp;</td>';
            }
            $output .= '
    </tr>';

            /* COMMENTS */

            if ($fileList['comment'][$fileKey] != "" )
            {
                $fileList['comment'][$fileKey] = htmlspecialchars($fileList['comment'][$fileKey]);
                $fileList['comment'][$fileKey] = parse_user_text($fileList['comment'][$fileKey]);

                $output .= '
    <tr align="left">
      <td>&nbsp;</td>
      <td colspan="'.$colspan.'"><div class="comment">'.$fileList['comment'][$fileKey].'</div></td>
    </tr>';
            }
        }  // end each ($fileList)
        // form button


    $colspan1 = $colspan -1 ;
        $output .= '
    <tr>
      <td colspan="'.$colspan1.'" align="left">&nbsp;</td>
      <td align="right" width="100">
        <input type="hidden" name="openDir" value="'.$curDirPath.'" />
        <input type="hidden" name="maxDocForm" value ="'.$iterator.'" />
        <input type="submit" name="submitInsertedDocument" value="'.$langAddModulesButton.'" class="LP_button"/>
      </td>
    </tr>';
    } // end if ( $fileList)
	else
	{
		$output .= '
    <tr>
      <td colspan="4">&nbsp;</td>
    </tr>';
    }

	$output .= '
    </tbody>
    </table>

    </form>
    <!-- end of display_my_documents output -->'."\n";

	return $output;
}


/**
 * Build an tree of $list from $id using the 'parent'
 * table. (recursive function)
 * Rows with a father id not existing in the array will be ignored
 *
 * @param $list modules of the learning path list
 * @param $paramField name of the field containing the parent id
 * @param $idField name of the field containing the current id
 * @param $id learnPath_module_id of the node to build
 * @return tree of the learning path
 *
 * @author Piraux Sebastien <pir@cerdecam.be>
 */
function build_element_list($list, $parentField, $idField, $id = 0)
{
    $tree = array();

    if(is_array($list))
    {
        foreach ($list as $element)
        {
            if( $element[$idField] == $id )
            {
                $tree = $element; // keep all $list informations in the returned array
                // explicitly add 'name' and 'value' for the build_nested_select_menu function
                //$tree['name'] = $element['name']; // useless since 'name' is the same word in db and in the  build_nested_select_menu function
                $tree['value'] = $element[$idField];
                break;
            }
        }

        foreach ($list as $element)
        {
            if($element[$parentField] == $id && ( $element[$parentField] != $element[$idField] ))
            {
                if($id == 0)
                {
                    $tree[] = build_element_list($list, $parentField, $idField, $element[$idField]);
                }
                else
                {
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
function build_display_element_list($elementList, $deepness = 0)
{
    $count = 0;
    $first = true;
    $last = false;
    $displayElementList = array();

    foreach($elementList as $thisElement)
    {
        $count++;

        // temporary save the children before overwritten it
        if (isset($thisElement['children']))
        $temp = $thisElement['children'];
        else
        $temp = NULL; // re init temp value if there is nothing to put in it

        // we use 'children' to calculate the deepness of the module, it will be displayed
        // using a spacing multiply by deepness
        $thisElement['children'] = $deepness;

        //--- up and down arrows displayed ?
        if ($count == count($elementList) )
        $last = true;

        $thisElement['up'] = $first ? false : true;
        $thisElement['down'] = $last ? false : true;

        //---
        $first = false;

        $displayElementList[] = $thisElement;

        if ( isset( $temp ) && sizeof( $temp ) > 0 )
        {
            $displayElementList = array_merge( $displayElementList,
            build_display_element_list($temp, $deepness + 1 ) );
        }
    }
    return  $displayElementList;
}

/**
 * This function set visibility for all the nodes of the tree module_tree
 *
 * @param $module_tree tree of modules we want to change the visibility
 * @param $visibility ths visibility string as requested by the DB
 *
 * @author Piraux Sebastien <pir@cerdecam.be>
 */
function set_module_tree_visibility($module_tree, $visibility)
{

    $tbl_lp_rel_learnPath_module = "lp_rel_learnPath_module";

    foreach($module_tree as $module)
    {
        if($module['visibility'] != $visibility)
        {
            $sql = "UPDATE `" . $tbl_lp_rel_learnPath_module . "`
                        SET `visibility` = '" . addslashes($visibility) . "'
                        WHERE `learnPath_module_id` = " . (int) $module['learnPath_module_id'] . "
                          AND `visibility` != '" . addslashes($visibility) . "'";
            db_query($sql);
        }
        if (isset($module['children']) && is_array($module['children']) ) set_module_tree_visibility($module['children'], $visibility);
    }
}

/**
 * This function deletes all the nodes of the tree module_tree
 *
 * @param $module_tree tree of modules we want to change the visibility
 *
 * @author Piraux Sebastien <pir@cerdecam.be>
 */
function delete_module_tree($module_tree)
{
    $tbl_lp_rel_learnPath_module = "lp_rel_learnPath_module";
    $tbl_lp_user_module_progress = "lp_user_module_progress";
    $tbl_lp_module               = "lp_module";
    $tbl_lp_asset                = "lp_asset";

    foreach($module_tree as $module)
    {
        switch($module['contentType'])
        {
        	case CTSCORMASSET_ :
            case CTSCORM_ :
                // delete asset if scorm
                $delAssetSql = "DELETE
                                    FROM `".$tbl_lp_asset."`
                                    WHERE `module_id` =  ". (int)$module['module_id']."
                                    ";
                db_query($delAssetSql);
                // no break; because we need to delete modul
            case CTLABEL_ : // delete module if scorm && if label
                $delModSql = "DELETE FROM `" . $tbl_lp_module . "`
                                     WHERE `module_id` =  ". (int)$module['module_id'];
                db_query($delModSql);
                // no break; because we need to delete LMP and UMP
            default : // always delete LPM and UMP
                db_query("DELETE FROM `" . $tbl_lp_rel_learnPath_module . "`
                                        WHERE `learnPath_module_id` = " . (int)$module['learnPath_module_id']);
                db_query("DELETE FROM `" . $tbl_lp_user_module_progress . "`
                                        WHERE `learnPath_module_id` = " . (int)$module['learnPath_module_id']);

                break;
        }
    }
    if ( isset($module['children']) &&  is_array($module['children']) ) delete_module_tree($module['children']);
}
/**
 * This function return the node with $module_id (recursive)
 *
 *
 * @param $lpModules array the tree of all modules in a learning path
 * @param $iid node we are looking for
 * @param $field type of node we are looking for (learnPath_module_id, module_id,...)
 *
 * @return array the requesting node (with all its children)
 *
 * @author Thanos Kyritsis <atkyritsis@upnet.gr>
 * @author Piraux Sebastien <pir@cerdecam.be>
 */
function get_module_tree( $lpModules , $id, $field = 'module_id')
{
    foreach( $lpModules as $module)
    {
        if( $module[$field] == $id)
        {
            return $module;
        }
        elseif ( isset($module['children']) && is_array($module['children']) )
        {
            $temp = get_module_tree($module['children'], $id, $field);
            if( is_array($temp) )
            return $temp;
            // else check next node
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
function seconds_to_scorm_time($time)
{
    $hours     = floor( $time / 3600 );
    if( $hours < 10 )
    {
        $hours = "0".$hours;
    }
    $min     = floor( ( $time -($hours * 3600) ) / 60 );
    if( $min < 10)
    {
        $min = '0' . $min;
    }
    $sec    = $time - ($hours * 3600) - ($min * 60);
    if($sec < 10)
    {
        $sec = '0' . $sec;
    }

    return     $hours . ':' . $min . ':' . $sec;
}

/**
  * This function allows to see if a time string is the SCORM 2004 requested format:
  * timeinterval(second,10,2): PThHmMsS
  *
  * @param $time a suspected SCORM 2004 time value, returned by the javascript API
  *
  * @author Thanos Kyritsis <atkyritsis@upnet.gr>
  */
function isScorm2004Time($time)
{
    $mask = "/^PT[0-9]{1,2}H[0-9]{1,2}M[0-9]{2}.?[0-9]?[0-9]?S$/";
    if (preg_match($mask,$time))
     {
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
function isScormTime($time)
{
    $mask = "/^[0-9]{2,4}:[0-9]{2}:[0-9]{2}.?[0-9]?[0-9]?$/";
    if (preg_match($mask,$time))
     {
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
function addScorm2004Time($time1, $time2)
{
	if (isScorm2004Time($time2))
    {
          //extract hours, minutes, secondes, ... from time1 and time2

          $mask = "/^([0-9]{2,4}):([0-9]{2}):([0-9]{2}).?([0-9]?[0-9]?)$/";
          $mask2004 = "/^PT([0-9]{1,2})H([0-9]{1,2})M([0-9]{2}).?([0-9]?[0-9]?)S$/";

          preg_match($mask,$time1, $matches);
          $hours1 = $matches[1];
          $minutes1 = $matches[2];
          $secondes1 = $matches[3];
          $primes1 = $matches[4];

          preg_match($mask2004,$time2, $matches);
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

          if ($primes1 < 10) {$primes1 = $primes1*10;}
          if ($primes2 < 10) {$primes2 = $primes2*10;}
          $total_primes = $primes1 + $primes2;
          if ($total_primes >= 100)
          {
            $total_primes -= 100;
            $primesReport = TRUE;
          }

        //calculate secondes

          $total_secondes = $secondes1 + $secondes2;
          if ($primesReport) {$total_secondes ++;}
          if ($total_secondes >= 60)
          {
            $total_secondes -= 60;
            $secondesReport = TRUE;
          }

        //calculate minutes

          $total_minutes = $minutes1 + $minutes2;
          if ($secondesReport) {$total_minutes ++;}
          if ($total_minutes >= 60)
          {
            $total_minutes -= 60;
            $minutesReport = TRUE;
          }

        //calculate hours

          $total_hours = $hours1 + $hours2;
          if ($minutesReport) {$total_hours ++;}
          if ($total_hours >= 10000)
          {
            $total_hours -= 10000;
            $hoursReport = TRUE;
          }

        // construct and return result string

          if ($total_hours < 10) {$total_hours = "0" . $total_hours;}
          if ($total_minutes < 10) {$total_minutes = "0" . $total_minutes;}
          if ($total_secondes < 10) {$total_secondes = "0" . $total_secondes;}

        $total_time = $total_hours . ":" . $total_minutes . ":" . $total_secondes;
        // add primes only if != 0
        if ($total_primes != 0) {$total_time .= "." . $total_primes;}
        return $total_time;
	}
	else
	{
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
function addScormTime($time1, $time2)
{
       if (isScormTime($time2))
    {
          //extract hours, minutes, secondes, ... from time1 and time2

          $mask = "/^([0-9]{2,4}):([0-9]{2}):([0-9]{2}).?([0-9]?[0-9]?)$/";

          preg_match($mask,$time1, $matches);
          $hours1 = $matches[1];
          $minutes1 = $matches[2];
          $secondes1 = $matches[3];
          $primes1 = $matches[4];

          preg_match($mask,$time2, $matches);
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

          if ($primes1 < 10) {$primes1 = $primes1*10;}
          if ($primes2 < 10) {$primes2 = $primes2*10;}
          $total_primes = $primes1 + $primes2;
          if ($total_primes >= 100)
          {
            $total_primes -= 100;
            $primesReport = TRUE;
          }

        //calculate secondes

          $total_secondes = $secondes1 + $secondes2;
          if ($primesReport) {$total_secondes ++;}
          if ($total_secondes >= 60)
          {
            $total_secondes -= 60;
            $secondesReport = TRUE;
          }

        //calculate minutes

          $total_minutes = $minutes1 + $minutes2;
          if ($secondesReport) {$total_minutes ++;}
          if ($total_minutes >= 60)
          {
            $total_minutes -= 60;
            $minutesReport = TRUE;
          }

        //calculate hours

          $total_hours = $hours1 + $hours2;
          if ($minutesReport) {$total_hours ++;}
          if ($total_hours >= 10000)
          {
            $total_hours -= 10000;
            $hoursReport = TRUE;
          }

        // construct and return result string

          if ($total_hours < 10) {$total_hours = "0" . $total_hours;}
          if ($total_minutes < 10) {$total_minutes = "0" . $total_minutes;}
          if ($total_secondes < 10) {$total_secondes = "0" . $total_secondes;}

        $total_time = $total_hours . ":" . $total_minutes . ":" . $total_secondes;
        // add primes only if != 0
        if ($total_primes != 0) {$total_time .= "." . $total_primes;}
        return $total_time;
       }
       else
       {
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
function clean_str_for_javascript( $str )
{
    $output = $str;
    // 1. addslashes, prevent problems with quotes
    // must be before the str_replace to avoid double backslash for \n
    $output = addslashes($output);
    // 2. turn windows CR into *nix CR
    $output = str_replace("\r", '', $output);
    // 3. replace "\n" by uninterpreted '\n'
    $output = str_replace("\n",'\n', $output);
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

function parse_user_text($userText)
{

   $userText = make_clickable($userText);

   if (strpos($userText, '<!-- content: html -->') === false)
   {
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

function disp_tool_title($titlePart)
{
    // if titleElement is simply a string transform it into an array
    $string = "";
    if (is_array($titlePart))
    {
        $titleElement = $titlePart;
    }
    else
    {
        $titleElement['mainTitle'] = $titlePart;
    }

    if (isset($titleElement['supraTitle']))
    {
        $string .= '<small>' . $titleElement['supraTitle'] . '</small><br />' . "\n";
    }

    if ( isset($titleElement['mainTitle']) )
    {
        $string .= $titleElement['mainTitle'] . "\n";
    }

    if ( isset($titleElement['subTitle']) )
    {
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

function disp_message_box($message, $style = FALSE)
{
	if ($style) {
		$cell = "<td class=\"$style\">";
	}
	else {
		$cell = "<td class=\"left\">";
	}

    return "$cell $message" ;
}

function disp_message_box1($message, $style = FALSE)
{
	if ($style) {
		$cell = "";
	}
	else {
		$cell = "";
	}
    return "$cell $message" ;
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

function disp_button($url, $text, $confirmMessage = '')
{

    if (is_javascript_enabled() && ! preg_match('~^Mozilla/4\.[1234567]~', $_SERVER['HTTP_USER_AGENT']))
    {
        if ($confirmMessage != '')
        {
            $onClickCommand = "if(confirm('" . clean_str_for_javascript($confirmMessage) . "')){document.location='" . $url . "';return false}";
        }
        else
        {
            $onClickCommand = "document.location='".$url."';return false";
        }

        return '<button onclick="' . $onClickCommand . '">'
        .      $text
        .      '</button>&nbsp;' . "\n"
        ;
    }
    else
    {
        return '<nobr>[ <a href="' . $url . '">' . $text . '</a> ]</nobr>';
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

function disp_progress_bar ($progress, $factor)
{
    $maxSize  = $factor * 100; //pixels
    $barwidth = $factor * $progress ;

    // display progress bar
    // origin of the bar
    $progressBar = '<img src="../../template/classic/img/bar_1.gif" width="1" height="12" alt="" />';

    if($progress != 0)
            $progressBar .= '<img src="../../template/classic/img/bar_1u.gif" width="' . $barwidth . '" height="12" alt="" />';
    // display 100% bar

    if($progress!= 100 && $progress != 0)
            $progressBar .= '<img src="../../template/classic/img/bar_1m.gif" width="1" height="12" alt="" />';

    if($progress != 100)
            $progressBar .= '<img src="../../template/classic/img/bar_1r.gif" width="' . ($maxSize - $barwidth) . '" height="12" alt="" />';
    // end of the bar
    $progressBar .=  '<img src="../../template/classic/img/bar_1.gif" width="1" height="12" alt="" />';

    return $progressBar;
}


/*
 * Insert a sort of  HTML Wysiwyg textarea inside a FORM
 * the html area currently implemented is HTMLArea 3.0. To work correctly,
 * the area needs a specific stylesheet
 * previously loaded in the html header.
 *
 * @param string $name content for name attribute in textarea tag
 * @param string $content optional content previously inserted into    the    area
 * @param int     $rows optional    textarea rows
 * @param int     $cols optional    textarea columns
 * @param string $optAttrib    optional - additionnal tag attributes
 *                                       (wrap, class, ...)
 * @return void
 *
 * @global strin urlAppend from    claro_main.conf.php
 *
 * @author Thanos Kyritsis <atkyritsis@upnet.gr>
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 */

function disp_html_area($name, $content = '', $rows=5, $cols=50, $optAttrib='')
{
    global $langTextEditorDisable, $langTextEditorEnable, $langSwitchEditorToTextConfirm;
    global $urlAppend;
    $incPath = $urlAppend.'/include/htmlarea';

    ob_start();

    if( ! isset( $_SESSION['htmlArea'] ) )
    {
        // TODO use a config variable instead of hardcoded value
        $_SESSION['htmlArea'] = 'enabled';
    }

    if (isset($_REQUEST['areaContent'])) $content = stripslashes($_REQUEST['areaContent']);

    if (is_javascript_enabled())
    {
        if ( isset($_SESSION['htmlArea']) && $_SESSION['htmlArea'] != 'disabled' )
        {
            $switchState = 'off';
            $message     = $langTextEditorDisable;
            $areaContent = 'editor.getHTML()';
            $confirmCommand = "if(!confirm('".clean_str_for_javascript($langSwitchEditorToTextConfirm)."'))return(false);";
        }
        else
        {
            $switchState = 'on';
            $message     = $langTextEditorEnable;
            $areaContent = 'document.getElementById(\''.$name.'\').value';
            $confirmCommand = '';
        }

        $location = '\''
        .           $incPath.'/editorswitcher.php?'
        .           'switch='.$switchState
        .           '&sourceUrl=' . urlencode($_SERVER['REQUEST_URI'])
        .           '&areaContent='
        .           '\''
        .           '+escape('.$areaContent.')'
        ;



        echo "\n".'<div align="right">'
        .    '<small>'
        .    '<b>'
        .    '<a href="/" onClick ="' . $confirmCommand . 'window.location='
        .    $location . ';return(false);">'
        .    $message
        .    '</a>'
        .    '</b>'
        .    '</small>'
        .    '</div>'."\n"
        ;

    } // end if is_javascript_enabled()


echo '<textarea '
        .'id="'.$name.'" '
        .'name="'.$name.'" '
        .'rows="'.$rows.'" '
        .'cols="'.$cols.'" '
        .$optAttrib.' >'
        ."\n".$content."\n"
        .'</textarea>'."\n";

    $returnString = ob_get_contents();
    ob_end_clean();
    return $returnString;
}

/*
 * In order for HTMLArea to work correctly, the area needs a
 * specific Javascript code previously loaded in the html header.
 * This function returns that Javascript code.
 *
 * Previously this code was part of the disp_html_area()
 * function code, but it's a more clean implementation to split it
 * into a new function.
 *
 * @param string $name content for name attribute in textarea tag
 *
 * @return void
 *
 *
 * @author Thanos Kyritsis <atkyritsis@upnet.gr>
 * @author Hugues Peeters <hugues.peeters@claroline.net>
 */
function disp_html_area_head($name)
{
	global $urlAppend, $iso639_2_code;

	$incPath = $urlAppend.'/include/htmlarea';

    // ugly fix for using gr for greek instead of el
    // FIXME: use this function everywhere in eclass and then fix it
    if (strcmp($iso639_2_code, "el") == 0) {
    	$iso639_2_code = "gr";
    }

	return '
		<script type="text/javascript">
		  _editor_url = "'.$incPath.'";
		</script>
		<script type="text/javascript" src="'.$incPath.'/htmlarea.js"></script>
		<script type="text/javascript" src="'.$incPath.'/lang/'.$iso639_2_code.'.js"></script>
		<script type="text/javascript" src="'.$incPath.'/dialog.js"></script>

		<script type="text/javascript">
		var    editor = null;
		function initEditor() {
			var config = new HTMLArea.Config();
			config.height = "180px";
			config.hideSomeButtons(" showhelp undo redo popupeditor ");
			editor = new HTMLArea("'.$name.'", config);

			// comment the following two lines to    see    how    customization works
			editor.generate();
			return false;
		}
		</script>';
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

function build_nested_select_menu($name, $elementList)
{
    return '<select name="' . $name . '">' . "\n"
    .      implode("\n", prepare_option_tags($elementList) )
    .      '</select>' .  "\n"
    ;
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


function prepare_option_tags($elementList, $deepness = 0)
{
    foreach($elementList as $thisElement)
    {
        $tab = str_repeat('&nbsp;&nbsp;&nbsp;&nbsp;', $deepness);

        $optionTagList[] = '<option value="'.$thisElement['value'].'">'
        .                  $tab.$thisElement['name']
        .                  '</option>'
        ;
        if (   isset( $thisElement['children'] )
            && sizeof($thisElement['children'] ) > 0)
        {
            $optionTagList = array_merge( $optionTagList,
                                          prepare_option_tags($thisElement['children'],
                                                              $deepness + 1 ) );
        }
    }

    return  $optionTagList;
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

function get_limited_page_links($sql, $limiter, $stringPreviousPage, $stringNextPage)
{
	$totalnum = mysql_num_rows(db_query($sql));
	$firstpage = 1;
	$lastpage = ceil($totalnum / $limiter);

	if (isset( $_GET['page'] ) && is_numeric( $_GET['page'] )) {
		$currentpage = (int) $_GET['page'];
		if ($currentpage < $firstpage || $currentpage > $lastpage) {
			$currentpage = $firstpage;
		}
	}
	else {
		$currentpage = $firstpage;
	}

	$prevpage = $currentpage - 1;
	$nextpage = $currentpage + 1;

	$url = basename($_SERVER['PHP_SELF']);

	switch($_SERVER['argc']) {
		case 0:
			$url .= "?page=";
			break;
		case 1:
			$arguments = preg_replace('/[&|?]page=.*$/', '', '?'.$_SERVER['argv'][0]);

			if (!strcmp($arguments, NULL)) {
				$url .= "?page=";
			}
			else {
				$url .= $arguments."&amp;page=";
			}
			break;
		default:
			$url .= "?page=";
			break;
	}

if (isset($_REQUEST['path_id'])) {
	$prevstring = "<a href=\"".$url.$prevpage."&path_id=$_REQUEST[path_id]\">".$stringPreviousPage."</a> | ";
	$nextstring = "<a href=\"".$url.$nextpage."&path_id=$_REQUEST[path_id]\">".$stringNextPage."</a>";
} else {
	$prevstring = "<a href=\"".$url.$prevpage."\">".$stringPreviousPage."</a> | ";
	$nextstring = "<a href=\"".$url.$nextpage."\">".$stringNextPage."</a>";
}

	if ($currentpage == $firstpage) {
		$prevstring = $stringPreviousPage." | ";
	}

	if ($currentpage == $lastpage) {
		$nextstring = $stringNextPage;
	}

	$wholestring = "<p>".$prevstring.$nextstring."</p>";

	if ( $lastpage == $firstpage) {
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

function get_limited_list($sql, $limiter)
{

	$totalnum = mysql_num_rows(db_query($sql));
	$firstpage = 1;
	$lastpage = ceil($totalnum / $limiter);

	if (isset( $_GET['page'] ) && is_numeric( $_GET['page'] )) {
		$currentpage = (int) $_GET['page'];
		if ($currentpage < $firstpage || $currentpage > $lastpage) {
			$currentpage = $firstpage;
		}
	}
	else {
		$currentpage = $firstpage;
	}

	$limit = ($currentpage - 1) * $limiter;

	$sql .= " LIMIT ".$limit.",".$limiter;

	return db_query_fetch_all($sql);
}

?>
