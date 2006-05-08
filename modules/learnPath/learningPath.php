<?php

/*
Header, Copyright, etc ...
*/

include("../../include/lib/learnPathLib.inc.php");
include("claro_main.lib.php");
include("../../include/lib/fileDisplayLib.inc.php");

$require_current_course = TRUE;
$langFiles              = "learnPath";

$TABLELEARNPATH         = "lp_learnPath";
$TABLEMODULE            = "lp_module";
$TABLELEARNPATHMODULE   = "lp_rel_learnPath_module";
$TABLEASSET             = "lp_asset";
$TABLEUSERMODULEPROGRESS= "lp_user_module_progress";

$imgRepositoryWeb       = "../../images/";

include("../../include/init.php");

$nameTools = $langLearningPath;
$navigation[] = array("url"=>"learningPathList.php", "name"=> $langLearningPathList);

// $_SESSION
if ( isset($_GET['path_id']) && $_GET['path_id'] > 0)
{
    $_SESSION['path_id'] = (int) $_GET['path_id'];
}
elseif( (!isset($_SESSION['path_id']) || $_SESSION['path_id'] == "") )
{ 
    // if path id not set, redirect user to the home page of learning path
    header("Location: ./learningPathList.php");
    exit();
}

// permissions (only for the viewmode, there is nothing to edit here )
if ( $is_adminOfCourse )
{
    // if the fct return true it means that user is a course manager and than view mode is set to COURSE_ADMIN
    header("Location: ./learningPathAdmin.php?path_id=".$_SESSION['path_id']);
    exit();
}

begin_page();

echo "</td></tr></table>";
mysql_select_db($currentCourseID);

// main page
//####################################################################################\\
//############################## MODULE TABLE LIST PREPARATION ###############################\\
//####################################################################################\\

if($uid)
{
    $uidCheckString = "AND UMP.`user_id` = ".$uid;
}
else // anonymous
{
    $uidCheckString = "AND UMP.`user_id` IS NULL ";
}

$sql = "SELECT LPM.`learnPath_module_id`,
				LPM.`parent`,
				LPM.`lock`,
				M.`module_id`,
				M.`contentType`,
				M.`name`,
				UMP.`lesson_status`, UMP.`raw`,
				UMP.`scoreMax`, UMP.`credit`,
				A.`path`
           FROM (`".$TABLEMODULE."` AS M,
		   		`".$TABLELEARNPATHMODULE."` AS LPM)
     LEFT JOIN `".$TABLEUSERMODULEPROGRESS."` AS UMP
             ON UMP.`learnPath_module_id` = LPM.`learnPath_module_id`
             ".$uidCheckString."
     LEFT JOIN `".$TABLEASSET."` AS A
            ON M.`startAsset_id` = A.`asset_id`
          WHERE LPM.`module_id` = M.`module_id`
            AND LPM.`learnPath_id` = ". (int)$_SESSION['path_id']."
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
for( $i = 0 ; $i < sizeof($flatElementList) ; $i++ )
{
	if ($flatElementList[$i]['children'] > $maxDeep) $maxDeep = $flatElementList[$i]['children'] ;
}

/*================================================================
                      OUTPUT STARTS HERE
 ================================================================*/  
  

//####################################################################################\\
//##################################### TITLE ########################################\\
//####################################################################################\\
nameBox(LEARNINGPATH_, DISPLAY_);
// and comment !
commentBox(LEARNINGPATH_, DISPLAY_);

//####################################################################################\\
//############################## MODULE TABLE HEADER #################################\\
//####################################################################################\\

echo "\n".'<br />'."\n"
  	.'<table class="claroTable" width="100%" border="0" cellspacing="2">'."\n"
	.'<tr class="headerX" align="center" valign="top" bgcolor="#e6e6e6">'."\n"
	.'<th colspan="'.($maxDeep+1).'">'.$langModule.'</th>'."\n";


if ( $uid )
{
	// show only progress column for authenticated users
    echo '<th colspan="2">'.$langProgress.'</th>'."\n";
}

echo '</tr>'."\n\n"
	.'<tbody>'."\n\n";

   
  //####################################################################################\\
  //############################## MODULE TABLE LIST DISPLAY ###########################\\
  //####################################################################################\\

if (!isset($globalProg)) $globalProg = 0;

foreach ($flatElementList as $module)
{
    if( $module['scoreMax'] > 0 && $module['raw'] > 0 )
    {
        $progress = round($module['raw']/$module['scoreMax']*100);
    }
    else
    {
        $progress = 0;
    }
      
    if ( $module['contentType'] == CTEXERCISE_ )
    {
        $passExercise = ($module['credit'] == "CREDIT");
    }
    else
    {
        $passExercise = false;
    }
      
    if ( $module['contentType'] == CTSCORM_ && $module['scoreMax'] <= 0)
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

    // display the current module name (and link if allowed)
      
    $spacingString = "";
    for($i = 0; $i < $module['children']; $i++)
    {
        $spacingString .= '<td width="5">&nbsp;</td>'."\n";
    }
    
    $colspan = $maxDeep - $module['children']+1;
      
    echo '<tr align="center">'."\n"
		.$spacingString
		.'<td colspan="'.$colspan.'" align="left">'."\n";
    
    //-- if chapter head
    if ( $module['contentType'] == CTLABEL_ )
    {
        echo '<b>'.htmlspecialchars($module['name']).'</b>'."\n";
    }        
    //-- if user can access module
    elseif ( !$is_blocked )
    {
        if($module['contentType'] == CTEXERCISE_ ) 
        {
            $moduleImg = 'quiz.gif';
        }
        else
        {
            $moduleImg = choose_image(basename($module['path']));
        }
            
        $contentType_alt = selectAlt($module['contentType']);
        echo '<a href="module.php?module_id='.$module['module_id'].'">'
        	.'<img src="'.$imgRepositoryWeb.$moduleImg.'" alt="'.$contentType_alt.'" border="0" />'
        	.htmlspecialchars($module['name']).'</a>'."\n";
        // a module ALLOW access to the following modules if
        // document module : credit == CREDIT || lesson_status == 'completed'
        // exercise module : credit == CREDIT || lesson_status == 'passed'
        // scorm module : credit == CREDIT || lesson_status == 'passed'|'completed'

        if( $module['lock'] == 'CLOSE' && $module['credit'] != 'CREDIT' 
            && $module['lesson_status'] != 'COMPLETED' && $module['lesson_status'] != 'PASSED' 
            && !$passExercise 
          )
        {
            if($uid)
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
    //-- user is blocked by previous module, don't display link
    else
    {
        if($module['contentType'] == CTEXERCISE_ ) 
        {
            $moduleImg = 'quiz.gif';
        }
        else
        {
            $moduleImg = choose_image(basename($module['path']));
        }

        echo '<img src="'.$imgRepositoryWeb.$moduleImg.'" alt="'.$contentType_alt.'" border="0" />'."\n"
             .htmlspecialchars($module['name']);
    }
    echo '</td>'."\n";

    if( $uid && ($module['contentType'] != CTLABEL_) )
    {
        // display the progress value for current module
        echo '<td align="right">'.claro_disp_progress_bar ($progress, 1).'</td>'."\n"
        	.'<td align="left">'
			.'<small>&nbsp;'.$progress.'%</small>'
			.'</td>'."\n";
    }
    elseif( $uid && $module['contentType'] == CTLABEL_ )
    {
        echo '<td colspan="2">&nbsp;</td>'."\n";
    }
  
    if ($progress > 0)
    {
        $globalProg =  $globalProg+$progress;
    }
      
    if($module['contentType'] != CTLABEL_) 
        $moduleNb++; // increment number of modules used to compute global progression except if the module is a title
       
    echo '</tr>'."\n\n";
    $atleastOne = true;
}
  
echo '</tbody>'."\n\n";

if ($atleastOne == false)
{
    echo '<tfoot>'."\n\n"
		.'<tr>'."\n"
		.'<td align="center" colspan="3">'.$langNoModule.'</td>'."\n"
		.'</tr>'."\n\n"
		.'</tfoot>'."\n\n";
}
elseif($uid && $moduleNb > 0)
{
    // add a blank line between module progression and global progression
    echo '<tfoot>'."\n\n"
		.'<tr>'."\n"
		.'<td colspan="'.($maxDeep+3).'">&nbsp;</td>'."\n"
		.'</tr>'."\n\n"
    	// display progression
		.'<tr>'."\n"
		.'<td align="right" colspan="'.($maxDeep+1).'">'.$langGlobalProgress.'</td>'."\n"
		.'<td align="right">'
        .claro_disp_progress_bar(round($globalProg / ($moduleNb) ), 1 )
		.'</td>'."\n"
		.'<td align="left">'
		.'<small>&nbsp;'.round($globalProg / ($moduleNb) ) .'%</small>'
		.'</td>'."\n"
		.'</tr>'."\n\n"
		.'</tfoot>'."\n\n";
}
echo '</table>'."\n\n";


?>

</body>
</html>
