<?
$require_current_course = TRUE;

require_once("../../include/baseTheme.php");
require_once("../../include/lib/learnPathLib.inc.php");
require_once("../../include/lib/fileDisplayLib.inc.php");

echo "<link rel='stylesheet' href='../../template/classic/tool_content.css' type='text/css'>";
echo "<link rel='stylesheet' href='tool.css' type='text/css'>";

$TABLEMODULE            = "lp_module";
$TABLELEARNPATHMODULE   = "lp_rel_learnPath_module";
$TABLEASSET             = "lp_asset";
$TABLEUSERMODULEPROGRESS= "lp_user_module_progress";
$imgRepositoryWeb       = "../../template/classic/img/";

mysql_select_db($currentCourseID);

if($uid)
{
	$uidCheckString = "AND UMP.`user_id` = ". (int)$uid;
}
else // anonymous
{
   $uidCheckString = "AND UMP.`user_id` IS NULL ";
}

//  -------------------------- learning path list content ----------------------------
$sql = "SELECT M.*, LPM.*, A.`path`, UMP.`lesson_status`, UMP.`credit`
        FROM (`".$TABLEMODULE."` AS M,
             `".$TABLELEARNPATHMODULE."` AS LPM)
        LEFT JOIN `".$TABLEASSET."` AS A ON M.`startAsset_id` = A.`asset_id`
        LEFT JOIN `".$TABLEUSERMODULEPROGRESS."` AS UMP
           ON UMP.`learnPath_module_id` = LPM.`learnPath_module_id`
           ".$uidCheckString."
        WHERE M.`module_id` = LPM.`module_id`
          AND LPM.`learnPath_id` = ". (int)$_SESSION['path_id']."
        ORDER BY LPM.`rank` ASC";

$result = db_query($sql);

if (mysql_num_rows($result) == 0) {
	echo "<p class='alert1'>$langNoModule</p>";
	exit;
}

$extendedList = array();
while ($list = mysql_fetch_array($result, MYSQL_ASSOC))
{
    $extendedList[] = $list;
}

// build the array of modules
// build_element_list return a multi-level array, where children is an array with all nested modules
// build_display_element_list return an 1-level array where children is the deep of the module

$flatElementList = build_display_element_list(build_element_list($extendedList, 'parent', 'learnPath_module_id'));
$i = 0;

// look for maxDeep
$maxDeep = 1; // used to compute colspan of <td> cells
for ($i=0 ; $i < sizeof($flatElementList) ; $i++)
{
    if ($flatElementList[$i]['children'] > $maxDeep) $maxDeep = $flatElementList[$i]['children'] ;
}

// -------------------------- learning path list header ----------------------------
echo "<table width=\"99%\" class=\"LearnPathSum\"><thead>
<tr class=\"LP_header\">
<td colspan=\"".($maxDeep+1)."\"><div align=\"center\">".$langContents."</div></td>
</tr></thead><tbody>";

// ----------------------- LEARNING PATH LIST DISPLAY ---------------------------------
foreach ($flatElementList as $module)
{
    //-------------visibility-----------------------------
    if ($module['visibility'] == 'HIDE')
    {
        if ($is_adminOfCourse)
        {
            $style=" class=\"invisible\"";
            $image_bullet = "off";
        }
        else
        {
            continue; // skip the display of this file
        }
    }
    else
    {
        $style="";
        $image_bullet = "on";
    }
    $spacingString = "";
    for($i = 0; $i < $module['children']; $i++)
           $spacingString .= "
      <td width='5'>&nbsp;</td>";

    $colspan = $maxDeep - $module['children']+1;
    echo "<tr align=\"center\"".$style.">".$spacingString."
      <td colspan=\"".$colspan."\" align=\"left\">";

    if ($module['contentType'] == CTLABEL_) // chapter head
    {
        echo "<font ".$style." style=\"font-weight: bold\">".htmlspecialchars($module['name'])."</font>";
    }
    else // module
    {
        if($module['contentType'] == CTEXERCISE_ )
            $moduleImg = "exercise_$image_bullet.gif";
        else if($module['contentType'] == CTLINK_ )
        	$moduleImg = "links_$image_bullet.gif";
        else if($module['contentType'] == CTCOURSE_DESCRIPTION_ )
        	$moduleImg = "description_$image_bullet.gif";
        else if($module['contentType'] == CTDOCUMENT_ )
        	$moduleImg = "docs_$image_bullet.gif";
        else
            $moduleImg = choose_image(basename($module['path']));

        $contentType_alt = selectAlt($module['contentType']);
        
        unset($imagePassed);
        if($module['credit'] == 'CREDIT' || $module['lesson_status'] == 'COMPLETED' || $module['lesson_status'] == 'PASSED')
        	$imagePassed = '<img src="'.$imgRepositoryWeb.'tick1.gif" alt="'.$module['lesson_status'].'" title="'.$module['lesson_status'].'" />';

        if(isset($imagePassed))
        	echo $imagePassed;
        
        echo "<span style=\"vertical-align: middle;\">
	<img src=\"".$imgRepositoryWeb."".$moduleImg."\" title=\"".$contentType_alt."\" border=\"0\"></span>&nbsp;";
        
	echo "<a href='navigation/startModule.php?viewModule_id=$module[module_id]'".$style." target='mainFrame'>". htmlspecialchars($module['name']). "</a>"; 

    }
echo "</td></tr>";
} // end of foreach
echo "</tbody></table>";
?>
