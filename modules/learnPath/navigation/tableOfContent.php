<?php // $Id$
/**
 * CLAROLINE 
 *
 * @version 1.7 $Revision$
 *
 * @copyright (c) 2001, 2005 Universite catholique de Louvain (UCL)
 *
 * @license http://www.gnu.org/copyleft/gpl.html (GPL) GENERAL PUBLIC LICENSE
 *
 * @author Piraux Sébastien <pir@cerdecam.be>
 * @author Lederer Guillaume <led@cerdecam.be>
 *
 * @package CLLNP
 * @subpackage navigation
 *
 */

$require_current_course = TRUE;
$langFiles              = "learnPath";
require("../../../include/init.php");

/*
 * DB tables definition
 */
$TABLELEARNPATH         = "lp_learnPath";
$TABLEMODULE            = "lp_module";
$TABLELEARNPATHMODULE   = "lp_rel_learnPath_module";
$TABLEASSET             = "lp_asset";
$TABLEUSERMODULEPROGRESS= "lp_user_module_progress";

$imgRepositoryWeb = "../../../images/";

// lib of this tool
include("../../../include/lib/learnPathLib.inc.php");
include("../claro_main.lib.php");

//lib of document tool
include("../../../include/lib/fileDisplayLib.inc.php");

$lpUid =  $uid;
mysql_select_db($currentCourseID);

if($lpUid)
{
	$uidCheckString = "AND UMP.`user_id` = ". (int)$lpUid;
}
else // anonymous
{
   $uidCheckString = "AND UMP.`user_id` IS NULL ";
}

// get the list of available modules
$sql = "SELECT LPM.`learnPath_module_id` ,
			LPM.`parent`,
			LPM.`lock`,
            M.`module_id`,
            M.`contentType`,
            M.`name`,
            UMP.`lesson_status`, UMP.`raw`,
            UMP.`scoreMax`, UMP.`credit`,
            A.`path`
         FROM (`".$TABLELEARNPATHMODULE."` AS LPM,
              `".$TABLEMODULE."` AS M)
   LEFT JOIN `".$TABLEUSERMODULEPROGRESS."` AS UMP
           ON UMP.`learnPath_module_id` = LPM.`learnPath_module_id`
           ".$uidCheckString."
   LEFT JOIN `".$TABLEASSET."` AS A
          ON M.`startAsset_id` = A.`asset_id`
        WHERE LPM.`module_id` = M.`module_id`
          AND LPM.`learnPath_id` = '" . (int)$_SESSION['path_id'] ."'
          AND LPM.`visibility` = 'SHOW'
          AND LPM.`module_id` = M.`module_id`
     GROUP BY LPM.`module_id`
     ORDER BY LPM.`rank`";

$extendedList = claro_sql_query_fetch_all($sql);

// build the array of modules
// build_element_list return a multi-level array, where children is an array with all nested modules
// build_display_element_list return an 1-level array where children is the deep of the module
$flatElementList = build_display_element_list(build_element_list($extendedList, 'parent', 'learnPath_module_id'));

$is_blocked = false;
$atleastOne = false;
$moduleNb = 0;

// look for maxDeep
$maxDeep = 1; // used to compute colspan of <td> cells
for ($i=0 ; $i < sizeof($flatElementList) ; $i++)
{
	if ($flatElementList[$i]['children'] > $maxDeep) $maxDeep = $flatElementList[$i]['children'] ;
}

$moduleNameLength = 25; // size of 'name' to display in the list, the string will be partially displayed if it is more than $moduleNameLength letters long

// get the name of the learning path
$sql = "SELECT `name`
      FROM `".$TABLELEARNPATH."`
      WHERE `learnPath_id` = '". (int)$_SESSION['path_id']."'";

$lpName = claro_sql_query_get_single_value($sql);

echo '<p><b>'.wordwrap($lpName,$moduleNameLength,' ',1).'</b></p>'."\n"
	. '<p>'."\n"
	. '<small>'
	. $langView.' : '
	. '<a href="viewer.php?frames=0" target="_top">'.$langFullScreen.'</a>'
	. ' | '
	. '<a href="viewer.php?frames=1" target="_top">'.$langInFrames.'</a>'
	. '</small>'."\n"
	. '</p>'."\n\n"
	. '<table width="100%">'."\n\n"
	;
  
$previous = ""; // temp id of previous module, used as a buffer in foreach
$previousModule = ""; // module id that will be used in the previous link
$nextModule = ""; // module id that will be used in the next link

foreach ($flatElementList as $module)
{
	if($module['contentType'] == CTEXERCISE_ )
		$moduleImg = 'quiz.gif';
	else
		$moduleImg = choose_image(basename($module['path']));

	$contentType_alt = selectAlt($module['contentType']);
	if( $module['scoreMax'] > 0 && $module['raw'] > 0)
	{
		$progress = @round($module['raw']/$module['scoreMax']*100);
	}
	else
	{
		$progress = 0;
	}
	
	if ( $module['contentType'] == CTEXERCISE_ )
	{
		$passExercise = ($module['credit']=='CREDIT');
	}
	else
	{
		$passExercise = false;
	}
	
	if ( $module['contentType'] == CTSCORM_ )
	{
		if ( $module['lesson_status'] == 'COMPLETED' || $module['lesson_status'] == 'PASSED')
		{
			$progress = 100;
			$passExercise = true;
		}
		else
		{
			$progress = 0;
			$passExercise = false;
		}
	}

	echo '<tr>'."\n";
	// display the current module name (and link if allowed)
	$spacingString = '';

	for($i = 0; $i < $module['children']; $i++) $spacingString .= '<td>&nbsp;</td>';
	
	$colspan = $maxDeep - $module['children']+1;
        
      
	// spacing col
	echo $spacingString.'<td colspan="'.$colspan.'"><small>';
	if ( !$is_blocked )
	{
		if($module['contentType'] == CTLABEL_) // chapter head
		{
			echo '<b>'.$module['name'].'</b>';
		}
		else
		{
			if ( strlen($module['name']) > $moduleNameLength)
				$displayedName = substr($module['name'],0,$moduleNameLength)."...";
			else
				$displayedName = $module['name'];
				
			// bold the title of the current displayed module
			if( $_SESSION['module_id'] == $module['module_id'] )
			{
				$displayedName = '<b>'.$displayedName.'</b>';
				$previousModule = $previous;
			}
			// store next value if user has the right to access it
			if( $previous == $_SESSION['module_id'] )
			{
				$nextModule = $module['module_id'];
			}
			echo '<a href="startModule.php?viewModule_id='.$module['module_id'].'" target="mainFrame" title="'.htmlspecialchars($module['name']).'">'
				.'<img src="'.$imgRepositoryWeb.$moduleImg.'" alt="'.$contentType_alt.' : '.$module['name'].'" border="0" />'.$displayedName.'</a>';
		}
        // a module ALLOW access to the following modules if
        // document module : credit == CREDIT || lesson_status == 'completed'
        // exercise module : credit == CREDIT || lesson_status == 'passed'
        // scorm module : credit == CREDIT || lesson_status == 'passed'|'completed'

		if( $module['lock'] == 'CLOSE' && $module['credit'] != 'CREDIT' && $module['lesson_status'] != 'COMPLETED' && $module['lesson_status'] != 'PASSED' && !$passExercise )
		{
			if($lpUid)
			{
				$is_blocked = true; // following modules will be unlinked
			}
			else // anonymous : don't display the modules that are unreachable
			{
				$atleastOne = true; // trick to avoid having the "no modules" msg to be displayed
				break ;
			}
		}

	}
	else
	{
		if($module['contentType'] == CTLABEL_) // chapter head
		{
			echo '<b>'.$module['name'].'</b>';
		}
		else
		{
			if ( strlen($module['name']) > $moduleNameLength)
				$displayedName = substr($module['name'],0,$moduleNameLength).'...';
			else
				$displayedName = $module['name'];

			echo '<img src="'.$imgRepositoryWeb.$moduleImg.'" alt="'.$contentType_alt.'" border="0" />'.$displayedName;
		}
	}

	if (!isset($globalProg)) $globalProg = 0;

	if ($progress > 0)
	{
		$globalProg =  $globalProg+$progress;
	}
       
	echo '</small></td>'."\n".'<td>';

	if($module['contentType'] != CTLABEL_ )
	{
		$moduleNb++; // increment number of modules used to compute global progression except if the module is a title

		if($module['credit'] == 'CREDIT' || $module['lesson_status'] == 'COMPLETED' || $module['lesson_status'] == 'PASSED')
		{
			echo '<img src="'.$imgRepositoryWeb.'mark.gif" alt="'.$module['lesson_status'].'" />';
		}
		else
		{
			echo '&nbsp;';
		}
	}
	else
	{
		echo '&nbsp;';
	}
      
	$atleastOne = true;
	echo '</td>'."\n"
		.'</tr>'."\n\n";
	// used in the foreach the remember the id of the previous module_id
	// don't remember if label...
	if ($module['contentType'] != CTLABEL_ )
		$previous = $module['module_id'];
      
      
} // end of foreach ($flatElementList as $module)

echo '</table>'."\n\n";
   


//  set redirection link
if ( $is_adminOfCourse )
	$returl = '../learningPathAdmin.php';
else
	$returl = '../learningPath.php';

echo '<br />'."\n\n".'<center>'."\n";

// display previous and next links only if there is more than one module
if ( $moduleNb > 1 )
{
	$prevNextString = '<small>';
	
	if( $previousModule != '' )
	{
		$prevNextString .= '<a href="startModule.php?viewModule_id='.$previousModule.'" target="mainFrame">'.$langPrevious.'</a>';
	}
	else
	{
		$prevNextString .=  $langPrevious;
	}
	$prevNextString .=  ' | ';
	
	if( $nextModule != '' )
	{
		$prevNextString .=  '<a href="startModule.php?viewModule_id='.$nextModule.'" target="mainFrame">'.$langNext.'</a>';
	}
	else
	{
		$prevNextString .=  $langNext;
	}  
	$prevNextString .=  '</small><br /><br />'."\n";
	
	echo $prevNextString;
}

//  set redirection link 
if ( $is_adminOfCourse )
	$returl = '../learningPathAdmin.php';
else
	$returl = '../learningPath.php';
	
?>
<form action="<?php echo $returl; ?>" method="post" target="_top">
<input type="submit" value="<?php echo $langQuitViewer; ?>">
</form>

</center>
