<?php

/*
Header
*/

/**
 * CLAROLINE 
 *
 * This functions library is used by most of the pages of the learning path tool
 *
 * @version version 1.7 $Revision$
 *
 * @copyright (c) 2001, 2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE 
 *
 * @author Piraux Sébastien <pir@cerdecam.be>
 * @author Lederer Guillaume <led@cerdecam.be>
 *
 * @package CLLNP
 *
 */


/**
* content type
*/
define ( 'CTCLARODOC_', 'CLARODOC' );
/**
* content type
*/
define ( 'CTDOCUMENT_', 'DOCUMENT' );
/**
* content type
*/
define ( 'CTEXERCISE_', 'EXERCISE' );
/**
* content type
*/
define ( 'CTSCORM_', 'SCORM' );
/**
* content type
*/
define ( 'CTLABEL_', 'LABEL' );
/**
* content type
*/
define ( 'CTCOURSE_DESCRIPTION_', 'COURSE_DESCRIPTION' );


/**
* mode used by {@link commentBox($type, $mode)} and {@link nameBox($type, $mode)}
*/
define ( 'DISPLAY_', 1 );
/**
* mode used by {@link commentBox($type, $mode)} and {@link nameBox($type, $mode)}
*/
define ( 'UPDATE_', 2 );
define ( 'UPDATENOTSHOWN_', 4 );

/**
* mode used by {@link commentBox($type, $mode)} and {@link nameBox($type, $mode)}
*/
define ( 'DELETE_', 3 );

/**
* type used by {@link commentBox($type, $mode)} and {@link nameBox($type, $mode)}
*/
define ( 'ASSET_', 1 );
/**
* type used by {@link commentBox($type, $mode)} and {@link nameBox($type, $mode)}
*/
define ( 'MODULE_', 2 );
define ( 'LEARNINGPATH_', 3 );
define ( 'LEARNINGPATHMODULE_', 4 );

/**
 * This function is used to display comments of module or learning path with admin links if needed.
 * Admin links are 'edit' and 'delete' links.
 *
 * @param string $type MODULE_ , LEARNINGPATH_ , LEARNINGPATHMODULE_
 * @param string $mode DISPLAY_ , UPDATE_ , DELETE_
 *
 * @author Piraux Sébastien <pir@cerdecam.be>
 * @author Lederer Guillaume <led@cerdecam.be>
 */
function commentBox($type, $mode)
{
    $tbl_lp_learnPath            = "lp_learnPath";
    $tbl_lp_rel_learnPath_module = "lp_rel_learnPath_module";
    $tbl_lp_module               = "lp_module";
    // globals
    global $is_adminOfCourse;
    global $langModify, $langOk, $langErrorNameAlreadyExists, $langAddComment, $langConfirmYourChoice;
    global $langDefaultLearningPathComment, $langDefaultModuleComment;
    global $langDefaultModuleAddedComment, $langDelete;
    // will be set 'true' if the comment has to be displayed
    $dsp = false;

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
                $module_id = $_SESSION['module_id'];
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
                                        AND `module_id` = " . (int) $_SESSION['module_id'];  // use backticks ( ` ) for col names and simple quote ( ' ) for string
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
            claro_sql_query($sql);
            
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
            $oldComment = claro_sql_query_get_single_value($sql);

            echo '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">' . "\n"
                .claro_disp_html_area('insertCommentBox', $oldComment, 15, 55).'<br />' . "\n"
                .'<input type="hidden" name="cmd" value="update' . $col_name . '" />'
                .'<input type="submit" value="' . $langOk . '" />' . "\n"
                .'<br />' . "\n"
                .'</form>' . "\n"
            ;
        }

    }

    // delete mode
    if ( $mode == DELETE_ && $is_adminOfCourse)
    {
        $sql =  "UPDATE `" . $tbl_name . "`
                 SET `" . $col_name . "` = ''
                 WHERE " . $where_cond;
        claro_sql_query($sql);
        $dsp = TRUE;
    }

    // display mode only or display was asked by delete mode or update mode
    if ( $mode == DISPLAY_ || $dsp == TRUE )
    {
        $sql = "SELECT `".$col_name."`
                FROM `" . $tbl_name . "`
                WHERE " . $where_cond;

        $result = mysql_query($sql);
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
        if ( ($currentComment == $defaultTxt) && !$is_adminOfCourse ) return 0;

        if ( empty($currentComment) )
        {
            // if no comment and user is admin : display link to add a comment
            if ( $is_adminOfCourse )
            {
                echo '<p>' . "\n"
                .    '<a class="claroCmd" href="' . $_SERVER['PHP_SELF'] . '?cmd=update' . $col_name . '">' . "\n"
                .    $langAddComment . '</a>' . "\n"
                .    '</p>' . "\n"
                ;
            }
        }
        else
        {
            // display comment
            echo "<p>".$currentComment."</p>";
            // display edit and delete links if user as the right to see it
            if ( $is_adminOfCourse )
            {

                echo '<p>' . "\n"
                .    '<small>' . "\n"
                .    '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=update' . $col_name . '">' . "\n"
                .    '<img src="../../images/edit.gif" alt="' . $langModify . '" border="0" />'
                .    '</a>' . "\n"
                .    '<a href="' . $_SERVER['PHP_SELF'].'?cmd=del' . $col_name . '" '
                .    ' onclick="javascript:if(!confirm(\''.clean_str_for_javascript($langConfirmYourChoice).'\')) return false;">' . "\n"
                .    '<img src="../../images/delete.gif" alt="' . $langDelete . '" border="0" />' . "\n"
                .    '</a>' . "\n"
                .    '</small>' . "\n"
                .    '</p>' . "\n"
                ;
            }
        }
    }

    return 0;
}

/**
  * This function is used to display name of module or learning path with admin links if needed
  *
  * @param string $type MODULE_ , LEARNINGPATH_
  * @param string $mode display(DISPLAY_) or update(UPDATE_) mode, no delete for a name
  * @author Piraux Sébastien <pir@cerdecam.be>
  * @author Lederer Guillaume <led@cerdecam.be>
  */
function nameBox($type, $mode)
{
    $tbl_lp_learnPath            = "lp_learnPath";
    $tbl_lp_module               = "lp_module";

    // globals
    global $is_adminOfCourse;
    global $urlAppend;
    global $langModify, $langOk, $langErrorNameAlreadyExists;

    // $dsp will be set 'true' if the comment has to be displayed
    $dsp = FALSE;

    // those vars will be used to build sql queries according to the name type
    switch ( $type )
    {
        case MODULE_ :
            $col_name = 'name';
            $tbl_name = $tbl_lp_module;
            $where_cond = '`module_id` = ' . (int) $_SESSION['module_id'];
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
            $num = claro_sql_query_get_single_value($sql);

            if ($num == 0)  // name doesn't already exists
            {

                $sql = "UPDATE `" . $tbl_name . "`
                                      SET `" . $col_name . "` = '" . addslashes($_POST['newName']) ."'
                                    WHERE " . $where_cond;

                claro_sql_query($sql);
                $dsp = TRUE;
            }
            else
            {
                echo $langErrorNameAlreadyExists . '<br />';
                $dsp = TRUE;
            }
        }
        else // display form
        {
            $sql = "SELECT `name`
                    FROM `" . $tbl_name . "`
                    WHERE " . $where_cond;

            $oldName = claro_sql_query_get_single_value($sql);

            echo '<form method="POST" action="' . $_SERVER['PHP_SELF'].'">' . "\n"
            .    '<input type="text" name="newName" size="50" maxlength="255" value="'.htmlspecialchars($oldName).'" />'
            .    '<br />' . "\n"
            .    '<input type="hidden" name="cmd" value="updateName" />' ."\n"
            .    '<input type="submit" value="' . $langOk . '" />' . "\n"
            .    '<br />' . "\n"
            .    '</form>' . "\n"
            ;
        }

    }

    // display if display mode or asked by the update
    if ( $mode == DISPLAY_ || $dsp == true )
    {
        $sql = "SELECT `name`
                      FROM `" . $tbl_name . "`
                     WHERE " . $where_cond;

        $result = mysql_query($sql);
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

        echo '<h4>' 
        .    $currentName;

        if ( $is_adminOfCourse )
            echo '<br /><a href="' . $_SERVER['PHP_SELF'] . '?cmd=updateName">'
            .    '<img src="../../images/edit.gif" alt="' . $langModify . '" border="0" />'
            .    '</a>' . "\n";
        echo '</h4>'."\n\n";
    }

    return 0;
}

/**
  * This function is used to display the correct image in the modules lists
  * It looks for the correct type in the array, and return the corresponding image name if found
  * else it returns a default image
  *
  * @param  string $contentType type of content in learning path
  * @return string name of the image with extension
  * @author Piraux Sébastien <pir@cerdecam.be>
  * @author Lederer Guillaume <led@cerdecam.be>
  */
 function selectImage($contentType)
 {

      $imgList[CTDOCUMENT_] = "document.gif";
      $imgList[CTCLARODOC_] = "clarodoc.gif";
      $imgList[CTEXERCISE_] = "quiz.gif";
      $imgList[CTSCORM_] = "scorm.gif";

      if (array_key_exists( $contentType , $imgList ))
      {
          return $imgList[$contentType];
      }

      return "default.gif";

 }
 /**
  * This function is used to display the correct alt texte for image in the modules lists.
  * Mainly used at the same time than selectImage() to add an alternate text on the image.
  *
  * @param  string $contentType type of content in learning path
  * @return string text for the alt
  * @author Piraux Sébastien <pir@cerdecam.be>
  * @author Lederer Guillaume <led@cerdecam.be>
  */
 function selectAlt($contentType)
 {
      global $langAltDocument, $langAltClarodoc, $langAltExercise, $langAltScorm;

      $altList[CTDOCUMENT_] = $langAltDocument;
      $altList[CTCLARODOC_] = $langAltClarodoc;
      $altList[CTEXERCISE_] = $langAltExercise;
      $altList[CTSCORM_] = $langAltScorm;

      if (array_key_exists( $contentType , $altList ))
      {
          return $altList[$contentType];
      }

      return "default.gif";
 }

/**
 * This function receives an array like $table['idOfThingToOrder'] = $requiredOrder and will return a sorted array
 * like $table[$i] = $idOfThingToOrder
 * the id list is sorted according to the $requiredOrder values
 *
 * @param  $formValuesTab array an array like these sent by the form on learingPathAdmin.php for an exemple
 *
 * @return array an array of the sorted list of ids
 *
 * @author Piraux Sébastien <pir@cerdecam.be>
 * @author Lederer Guillaume <led@cerdecam.be>
 */
function setOrderTab ( $formValuesTab )
{
    global $langErrorInvalidParms, $langErrorValuesInDouble;
    global $dialogBox;

    $tabOrder = array(); // declaration to avoid bug in "elseif (in_array ... "
    $i = 0;
    foreach ( $formValuesTab as $key => $requiredOrder)
    {
        // error if input is not a number
        if( !is_num($requiredOrder) )
        {
            $dialogBox .= $langErrorInvalidParms;
            return 0;
        }
        elseif( in_array($requiredOrder, $tabOrder) )
        {
            $dialogBox .= $langErrorValuesInDouble;
            return 0;
        }
        // $tabInvert = required order => id module
        $tabInvert[$requiredOrder] = $key;
        // $tabOrder = required order : unsorted
        $tabOrder[$i] = $requiredOrder;
        $i++;
    }
    // $tabOrder = required order : sorted
    sort($tabOrder);
    $i = 0;
    foreach ($tabOrder as $key => $order)
    {
        // $tabSorted = new Order => id learning path
        $tabSorted[$i] = $tabInvert[$order];
        $i++;
    }
    return $tabSorted;
}


/**
 * Check if an input string is a number
 *
 * @param string $var input to check
 * @return bool true if $var is a number, false otherwise
 *
 * @author Piraux Sébastien <pir@cerdecam.be>
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


/**
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
    $moduleList = claro_sql_query_fetch_all($sql);

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

    echo "\n".'<table class="claroTable" width="100%"  border="0" cellspacing="2">'."\n\n"
    .    '<tr class="headerX" align="center" valign="top" bgcolor="#e6e6e6">'."\n"
	.    '<th colspan="' . ($maxDeep+1).'">' . $langModule . '</th>'."\n"
    .    '</tr>'."\n\n"
	.	 '<tbody>'."\n"
    ;

    foreach ($flatElementList as $module)
    {
        $spacingString = '';
        for($i = 0; $i < $module['children']; $i++)
        	$spacingString .= '<td width="5">&nbsp;</td>'."\n";
        $colspan = $maxDeep - $module['children']+1;

        echo '<tr align="center" '.$style.'>' . "\n"
        .    $spacingString 
        .    '<td colspan="' . $colspan . '" align="left">'
        ;

        if ($module['contentType'] == CTLABEL_) // chapter head
        {
            echo '<b>' . $module['name'] . '</b>';
        }
        else // module
        {
            if($module['contentType'] == CTEXERCISE_ )
            	$moduleImg = 'quiz.gif';
            else
            	$moduleImg = choose_image(basename($module['path']));
            	
            $contentType_alt = selectAlt($module['contentType']);

            echo '<img src="' . $imgRepositoryWeb . $moduleImg . '" alt="' .$contentType_alt.'" border="0" />'
            .    $module['name']
            ;
        }
        echo '</td>'."\n"
		.	 '</tr>'."\n\n";
    }
    echo '</tbody>'."\n\n"
	.	 '</table>'."\n\n";
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

    $result = mysql_query($sql);
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
        $result = mysql_query($sqlnum);
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
 * @author Piraux Sébastien <pir@cerdecam.be>
 * @author Lederer Guillaume <led@cerdecam.be>
 */
function display_my_exercises($dialogBox)
{
    //$tbl_cdb_names = claro_sql_get_course_tbl();
    //$tbl_quiz_test = $tbl_cdb_names['quiz_test'];
    $tbl_quiz_test = "quiz_test";

    global $langAddModule;
    global $langAddModulesButton;
    global $langExercise;
    global $langNoEx;
    global $langAddOneModuleButton;
    global $imgRepositoryWeb;

    echo '<!-- display_my_exercises output -->' . "\n";
    /*--------------------------------------
    DIALOG BOX SECTION
    --------------------------------------*/
    $colspan = 4;
    if( !empty($dialogBox) )
    {
        echo claro_disp_message_box($dialogBox).'<br />'."\n";
    }
    echo '<table class="claroTable" width="100%" border="0" cellspacing="">'."\n\n"
    .    '<tr class="headerX" align="center" valign="top">'."\n"
    .    '<th width="10%">'
    .    $langAddModule
    .    '</th>'."\n"
    .    '<th>'
    .    $langExercise
    .    '</th>'."\n"
    .    '</tr>'."\n\n"
    ;

    // Display available modules
    echo '<form method="POST" name="addmodule" action="' . $_SERVER['PHP_SELF'] . '?cmdglobal=add">'."\n";
    $atleastOne = FALSE;
    $sql = "SELECT `id`, `titre` AS `title`, `description`
            FROM `" . $tbl_quiz_test . "`
            ORDER BY  `titre`, `id`";
    $exercises = claro_sql_query_fetch_all($sql);
    
    if( is_array($exercises) && !empty($exercises) )
    {
		echo '<tbody>' . "\n\n";
		
	    foreach ( $exercises as $exercise )
	    {
	        echo '<tr>'."\n"
	        .    '<td align="center">'
	        .    '<input type="checkbox" name="check_' . $exercise['id'] . '" id="check_' . $exercise['id'] . '" value="' . $exercise['id'] . '" />'
	        .    '</td>'."\n"
	        .    '<td align="left">'
	        .    '<label for="check_'.$exercise['id'].'" >'
	        .    '<img src="' . $imgRepositoryWeb . 'quiz.gif" alt="' . $langExercise . '" />'
	        .    $exercise['title']
	        .    '</label>'
	        .    '</td>'."\n"
	        .    '</tr>'."\n\n"
	        ;

	        // COMMENT

	        if( !empty($exercise['description']) )
	        {
	            echo '<tr>'."\n"
	            .    '<td>&nbsp;</td>'."\n"
	            .    '<td>'
	            .    '<small>' . $exercise['description'] . '</small>'
	            .    '</td>'."\n"
	            .    '</tr>'."\n\n"
	            ;
	        }
	        $atleastOne = true;
	    }//end while another module to display
	    echo '</tbody>'."\n\n";
	}
    
    echo '<tfoot>'."\n\n";
    
    if( !$atleastOne )
    {
        echo '<tr>'."\n"
		.	 '<td colspan="2" align="center">'
        .    $langNoEx
        .    '</td>'."\n"
		.	 '</tr>'."\n\n"
        ;
    }

    // Display button to add selected modules

    echo '<tr>'."\n"
    .    '<td colspan="2">'
    .    '<hr noshade size="1">'
    .    '</td>'."\n"
	.	 '</tr>'."\n\n"
    ;
    if( $atleastOne )
    {
        echo '<tr>'."\n"
		.	 '<td colspan="2">'
        .    '<input type="submit" name="insertExercise" value="'.$langAddModulesButton.'" />'
        .    '</td>'."\n"
		.	 '</tr>'."\n\n"
        ;
    }
    echo '</form>'."\n\n"
    .    '</tfoot>'."\n\n"
    .    '</table>'."\n\n"
    .    '<!-- end of display_my_exercises output -->' . "\n"
    ;
}

/**
  * This function is used to display the list of document available in the course
  * It also displays the form used to add selected document in the learning path
  *
  * @param string $dialogBox Error or confirmation text
  * @return nothing
  * @author Piraux Sébastien <pir@cerdecam.be>
  * @author Lederer Guillaume <led@cerdecam.be>
  */

function display_my_documents($dialogBox)
{
    global $is_adminOfCourse;

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
    
    global $secureDocumentDownload;

    /**
     * DISPLAY
     */
    echo '<!-- display_my_documents output -->' . "\n";

    $dspCurDirName = htmlspecialchars($curDirName);
    $cmdCurDirPath = rawurlencode($curDirPath);
    $cmdParentDir  = rawurlencode($parentDir);

    echo '<br />'
    .    '<form action="' . $_SERVER['PHP_SELF'] . '" method="POST">';

    /*--------------------------------------
    DIALOG BOX SECTION
    --------------------------------------*/
    $colspan = 4;
    if( !empty($dialogBox) )
    {
        echo claro_disp_message_box($dialogBox);
    }
    /*--------------------------------------
    CURRENT DIRECTORY LINE
    --------------------------------------*/

    /* GO TO PARENT DIRECTORY */
    if ($curDirName) /* if the $curDirName is empty, we're in the root point
    and we can't go to a parent dir */
    {
        echo '<a href="' . $_SERVER['PHP_SELF'] . '?cmd=exChDir&amp;file=' . $cmdParentDir . '">' . "\n"
        .    '<img src="' . $imgRepositoryWeb . 'parent.gif" border="0" align="absbottom" hspace="5" alt="" />'."\n"
        .    '<small>' . $langUp . '</small>' . "\n"
        .    '</a>' . "\n"
        ;
    }
    /* CURRENT DIRECTORY */
    echo '<table class="claroTable" width="100%" border="0" cellspacing="2">';
    if ( $curDirName ) /* if the $curDirName is empty, we're in the root point
    and there is'nt a dir name to display */
    {
        echo '<!-- current dir name -->' . "\n"
        .    '<tr>' . "\n"
        .    '<th class="superHeader" colspan="' . $colspan . '" align="left">'. "\n"
        .    '<img src="' . $imgRepositoryWeb . 'opendir.gif" align="absbottom" vspace=2 hspace=5 alt="" />' . "\n"
        .    $dspCurDirName . "\n"
        .    '</td>' . "\n"
        .    '</tr>' . "\n"
        ;
    }

    echo '<tr class="headerX" align="center" valign="top" bgcolor="#e6e6e6">'
    .    '<th>' . $langAddModule . '</th>' . "\n"
    .    '<th>' . $langName . '</th>' . "\n"
    .    '<th>' . $langSize . '</th>' . "\n"
    .    '<th>' . $langDate . '</th>' . "\n"
    .    '</tr><tbody>' . "\n"
    ;


    /*--------------------------------------
    DISPLAY FILE LIST
    --------------------------------------*/

    if ( $fileList )
    {
        $iterator = 0;

        while ( list( $fileKey, $fileName ) = each ( $fileList['name'] ) )
        {

            $dspFileName = htmlspecialchars($fileName);
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
                
                if ( strstr($_SERVER['SERVER_SOFTWARE'], 'Apache')
                    && (isset($secureDocumentDownload) && $secureDocumentDownload == true) )
                {
                    // slash argument method - only compatible with Apache
                    $doc_url = $cmdFileName;
                }
                else
                {
                    // question mark argument method, for IIS ...
                    $doc_url = '?url=' . $cmdFileName;
                }
                
                $urlFileName = '../document/goto/index.php'.$doc_url;
            }
            elseif ($fileList['type'][$fileKey] == A_DIRECTORY)
            {
                $image       = 'folder.gif';
                $size        = '&nbsp;';
                $date        = '&nbsp;';
                $urlFileName = $_SERVER['PHP_SELF'] . '?openDir=' . $cmdFileName;
            }

            echo '<tr align="center" ' . $style . '>'."\n";

            if ($fileList['type'][$fileKey] == A_FILE)
            {
                $iterator++;
                echo '<td>'
                .    '<input type="checkbox" name="insertDocument_' . $iterator . '" id="insertDocument_' . $iterator . '" value="' . $curDirPath . "/" . $fileName . '" />'
                .    '</td>' . "\n"
                ;

            }
            else
            {
                echo '<td>&nbsp;</td>';
            }
            echo '<td align="left">'
            .    '<a href="' . $urlFileName . '" ' . $style . '>'
            .    '<img src="' . $imgRepositoryWeb . $image . '" border="0" hspace="5" alt="" />' . $dspFileName . '</a>'
            .    '</td>'."\n"
            .    '<td><small>' . $size . '</small></td>' . "\n"
            .    '<td><small>' . $date . '</small></td>' . "\n"
            ;

            /* NB : Before tracking implementation the url above was simply
            * "<a href=\"",$urlFileName,"\"",$style,">"
            */


            echo '</tr>' . "\n";

            /* COMMENTS */

            if ($fileList['comment'][$fileKey] != "" )
            {
                $fileList['comment'][$fileKey] = htmlspecialchars($fileList['comment'][$fileKey]);
                $fileList['comment'][$fileKey] = claro_parse_user_text($fileList['comment'][$fileKey]);

                echo '<tr align="left">'."\n"
                	.'<td>&nbsp;</td>'."\n"
                	.'<td colspan="'.$colspan.'">'."\n"
                	.'<div class="comment">'
                	.$fileList['comment'][$fileKey]
	                .'</div>'."\n"
	                .'</td>'."\n"
	                .'</tr>'."\n";
            }
        }  // end each ($fileList)
        // form button
        echo '</tbody><tfoot>'
        	.'<tr><td colspan="4"><hr noshade size="1"></td></tr>'."\n";

        echo '<tr>'."\n"
			.'<td colspan="'.$colspan.'" align="left">'."\n"
			.'<input type="hidden" name="openDir" value="'.$curDirPath.'" />'."\n"
			.'<input type="hidden" name="maxDocForm" value ="'.$iterator.'" />'."\n"
			.'<input type="submit" name="submitInsertedDocument" value="'.$langAddModulesButton.'" />'."\n"
			.'</td>'."\n"
			.'</tr>'."\n";
    } // end if ( $fileList)
	else
	{
		echo '<tr><td colspan="4"><hr noshade size="1"></td></tr>'."\n";
    }

	echo '</tfoot></table>'."\n"
    	.'</form>'."\n"
    	.'<!-- end of display_my_documents output -->'."\n";

}

/**
 * Recursive Function used to find the deep of a module in a learning path
 * DEPRECATED : no more since the display has been reorganised
 *
 * @param integer $id id_of_module that we are looking for deep
 * @param array $searchInarray of parents of modules in a learning path $searchIn[id_of_module] = parent_of_this_module
 *
 * @author Piraux Sébastien <pir@cerdecam.be>
 */
function find_deep($id, $searchIn)
{
    if ( $searchIn[$id] == 0 || !isset($searchIn[$id]) && $id == $searchIn[$id])
    return 0;
    else
    return find_deep($searchIn[$id],$searchIn) + 1;
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
 * @author Piraux Sébastien <pir@cerdecam.be>     
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
                // explicitly add 'name' and 'value' for the claro_build_nested_select_menu function
                //$tree['name'] = $element['name']; // useless since 'name' is the same word in db and in the  claro_build_nested_select_menu function
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
 * @author Piraux Sébastien <pir@cerdecam.be>
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
 * @author Piraux Sébastien <pir@cerdecam.be>
 */
function set_module_tree_visibility($module_tree, $visibility)
{
    //$tbl_cdb_names = claro_sql_get_course_tbl();
    //$tbl_lp_rel_learnPath_module = $tbl_cdb_names['lp_rel_learnPath_module'];
    $tbl_lp_rel_learnPath_module = "lp_rel_learnPath_module";

    foreach($module_tree as $module)
    {
        if($module['visibility'] != $visibility)
        {
            $sql = "UPDATE `" . $tbl_lp_rel_learnPath_module . "`
                        SET `visibility` = '" . addslashes($visibility) . "'
                        WHERE `learnPath_module_id` = " . (int) $module['learnPath_module_id'] . "
                          AND `visibility` != '" . addslashes($visibility) . "'";
            claro_sql_query ($sql);
        }
        if (isset($module['children']) && is_array($module['children']) ) set_module_tree_visibility($module['children'], $visibility);
    }
}

/**
 * This function deletes all the nodes of the tree module_tree
 *
 * @param $module_tree tree of modules we want to change the visibility
 *
 * @author Piraux Sébastien <pir@cerdecam.be>
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
            case CTSCORM_ :
                // delete asset if scorm
                $delAssetSql = "DELETE
                                    FROM `".$tbl_lp_asset."`
                                    WHERE `module_id` =  ". (int)$module['module_id']."
                                    ";
                claro_sql_query($delAssetSql);
                // no break; because we need to delete modul
            case CTLABEL_ : // delete module if scorm && if label
                $delModSql = "DELETE FROM `" . $tbl_lp_module . "`
                                     WHERE `module_id` =  ". (int)$module['module_id'];
                claro_sql_query($delModSql);
                // no break; because we need to delete LMP and UMP
            default : // always delete LPM and UMP
                claro_sql_query("DELETE FROM `" . $tbl_lp_rel_learnPath_module . "`
                                        WHERE `learnPath_module_id` = " . (int)$module['learnPath_module_id']);
                claro_sql_query("DELETE FROM `" . $tbl_lp_user_module_progress . "`
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
 * @author Piraux Sébastien <pir@cerdecam.be>
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
            $temp = get_module_tree($module['children'], $id);
            if( is_array($temp) )
            return $temp;
            // else check next node
        }

    }
}

/**
 * Convert the time recorded in seconds to a scorm type 
 *
 * @author Piraux Sébastien <pir@cerdecam.be>
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

/* ************************************* Thanos MODS ********************** */


/******************** FILE MANAGE LIB ****************/

/**
 * Delete a file or a directory (and its whole content)
 *
 * @param  - $filePath (String) - the path of file or directory to delete
 * @return - boolean - true if the delete succeed
 *           boolean - false otherwise.
 */

function claro_delete_file($filePath)
{
    if( is_file($filePath) )
    {
        return unlink($filePath);
    }
    elseif( is_dir($filePath) )
    {
        $dirHandle = opendir($filePath);

        if ( ! $dirHandle ) return false;

        $removableFileList = array();

        while ( $file = readdir($dirHandle) )
        {
            if ( $file == '.' || $file == '..') continue;

            $removableFileList[] = $filePath . '/' . $file;
        }

        closedir($dirHandle); // impossible to test, closedir return void ...

        if ( sizeof($removableFileList) > 0)
        {
            foreach($removableFileList as $thisFile)
            {
                if ( ! claro_delete_file($thisFile) ) return false;
            }
        }
       
        return rmdir($filePath);

    } // end elseif is_dir()
}

?>