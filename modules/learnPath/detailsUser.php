<?php
/*=============================================================================
       	GUnet e-Class 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
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
	detailsUser.php
	@last update: 30-06-2006 by Thanos Kyritsis
	@authors list: Thanos Kyritsis <atkyritsis@upnet.gr>
	               
	based on Claroline version 1.7 licensed under GPL
	      copyright (c) 2001, 2006 Universite catholique de Louvain (UCL)
	      
	      original file: tracking/userLog.php Revision: 1.37
	      
	Claroline authors: Thomas Depraetere <depraetere@ipm.ucl.ac.be>
                      Hugues Peeters    <peeters@ipm.ucl.ac.be>
                      Christophe Gesche <gesche@ipm.ucl.ac.be>
                      Sebastien Piraux  <piraux_seb@hotmail.com>
==============================================================================        
    @Description: This script presents the student's progress for all 
                  learning paths available in a course to the teacher.
                  
                  Only the Learning Path specific code was ported and 
                  modified from the original claroline file.

    @Comments:
 
    @todo: 
==============================================================================
*/

require_once("../../include/lib/learnPathLib.inc.php");
$require_current_course = TRUE;

$TABLECOURSUSER	        = "cours_user";
$TABLEUSER              = "user";
$TABLELEARNPATH         = "lp_learnPath";
$TABLEMODULE            = "lp_module";
$TABLELEARNPATHMODULE   = "lp_rel_learnPath_module";
$TABLEASSET             = "lp_asset";
$TABLEUSERMODULEPROGRESS= "lp_user_module_progress";

require_once("../../include/baseTheme.php");
$head_content = "";
$tool_content = "";

$navigation[] = array("url"=>"learningPathList.php", "name"=> $langLearningPathList);
if (! $is_adminOfCourse ) claro_die($langNotAllowed);
$navigation[] = array("url"=>"detailsAll.php", "name"=> $langTrackAllPath);
$nameTools = $langTrackUser;

// user info can not be empty, return to the list of details
if( empty($_REQUEST['uInfo']) )
{
	header("Location: ./detailsAll.php");
	exit();
}


// check if user is in this course
$sql = "SELECT `u`.`nom` AS `lastname`,`u`.`prenom` AS `firstname`, `u`.`email`
			FROM `".$TABLECOURSUSER."` as `cu` , `".$TABLEUSER."` as `u`
			WHERE `cu`.`user_id` = `u`.`user_id`
				AND `cu`.`code_cours` = '". addslashes($currentCourseID) ."'
				AND `u`.`user_id` = '". (int)$_REQUEST['uInfo']."'";
                            
$results = db_query_fetch_all($sql);

if( empty($results) ) 
{
	header("Location: ./detailsAll.php");
	exit();
}

$trackedUser = $results[0];

$tool_content .= ucfirst(strtolower($langUser)).': <br />'."\n"
	.'<ul>'."\n"
	.'<li>'.$langLastName.': '.$trackedUser['lastname'].'</li>'."\n"
	.'<li>'.$langName.': '.$trackedUser['firstname'].'</li>'."\n"
	.'<li>'.$langEmail.': ';
if( empty($trackedUser['email']) )	$tool_content .= $langNoEmail;
else 								$tool_content .= $trackedUser['email'];

$tool_content .= '</li>'."\n"
	.'</ul>'."\n"
	.'</p>'."\n";                            

mysql_select_db($currentCourseID);
// get list of learning paths of this course
// list available learning paths
$sql = "SELECT LP.`name`, LP.`learnPath_id`
			FROM `".$TABLELEARNPATH."` AS LP
			ORDER BY LP.`rank`";

$lpList = db_query_fetch_all($sql);

// table header
$tool_content .= '<table cellpadding="2" cellspacing="1" border="0" align="center">'."\n"
	.'<thead>'."\n"
	.'<tr>'."\n"
	.'<th>'.$langLearningPath.'</th>'."\n"
	.'<th colspan="2">'.$langProgress.'</th>'."\n"
	.'</tr>'."\n"
	.'</thead>';
if(sizeof($lpList) == 0)
{
	echo '<tfoot>'."\n".'<tr>'."\n"
		.'<td colspan="3" align="center">'.$langNoLearningPath.'</td>'."\n"
		.'</tr>'."\n".'</tfoot>'."\n";
}
else
{
	// display each learning path with the corresponding progression of the user
	foreach($lpList as $lpDetails)
	{
		
		$lpProgress = get_learnPath_progress($lpDetails['learnPath_id'],$_GET['uInfo']);
		$tool_content .= "\n".'<tr>'."\n"
			.'<td><a href="detailsUserPath.php?uInfo='.$_GET['uInfo'].'&path_id='.$lpDetails['learnPath_id'].'">'.htmlspecialchars($lpDetails['name']).'</a></td>'."\n"
			.'<td align="right">'."\n"
			.claro_disp_progress_bar($lpProgress, 1)
			.'</td>'."\n"
			.'<td align="left"><small>'.$lpProgress.'%</small></td>'."\n"
			.'</tr>'."\n";
	}
}
$tool_content .= '</table>'."\n"
	.'</td>'."\n".'</tr>'."\n";


draw($tool_content, 2, "learnPath", $head_content);

?>
