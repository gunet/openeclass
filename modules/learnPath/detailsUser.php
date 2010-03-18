<?php
/*========================================================================
*   Open eClass 2.3
*   E-learning and Course Management System
* ========================================================================
*  Copyright(c) 2003-2010  Greek Universities Network - GUnet
*  A full copyright notice can be read in "/info/copyright.txt".
*
*  Developers Group:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*			Yannis Exidaridis <jexi@noc.uoa.gr>
*			Alexandros Diamantidis <adia@noc.uoa.gr>
*			Tilemachos Raptis <traptis@noc.uoa.gr>
*
*  For a full list of contributors, see "credits.txt".
*
*  Open eClass is an open platform distributed in the hope that it will
*  be useful (without any warranty), under the terms of the GNU (General
*  Public License) as published by the Free Software Foundation.
*  The full license can be read in "/info/license/license_gpl.txt".
*
*  Contact address: 	GUnet Asynchronous eLearning Group,
*  			Network Operations Center, University of Athens,
*  			Panepistimiopolis Ilissia, 15784, Athens, Greece
*  			eMail: info@openeclass.org
* =========================================================================*/

/*===========================================================================
	detailsUser.php
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
$require_prof = TRUE;

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

$navigation[] = array("url"=>"learningPathList.php", "name"=> $langLearningPaths);
$navigation[] = array("url"=>"detailsAll.php", "name"=> $langTrackAllPathExplanation);
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
			AND `cu`.`cours_id` = $cours_id
			AND `u`.`user_id` = '". (int)$_REQUEST['uInfo']."'";

$results = db_query_fetch_all($sql);

if( empty($results) )
{
	header("Location: ./detailsAll.php");
	exit();
}

$trackedUser = $results[0];

$nameTools = $trackedUser['lastname']." ".$trackedUser['firstname'];
/*
$tool_content .= ucfirst(strtolower($langUser)).': <br />'."\n"
	.'<ul>'."\n"
	.'<li>'.$langLastName.': '.$trackedUser['lastname'].'</li>'."\n"
	.'<li>'.$langName.': '.$trackedUser['firstname'].'</li>'."\n"
	.'<li>'.$langEmail.': ';
if( empty($trackedUser['email']) )	$tool_content .= $langNoEmail;
else 			$tool_content .= $trackedUser['email'];

$tool_content .= '</li>'."\n"
	.'</ul>'."\n"
	.'</p>'."\n";
*/
mysql_select_db($currentCourseID);
// get list of learning paths of this course
// list available learning paths
$sql = "SELECT LP.`name`, LP.`learnPath_id`
			FROM `".$TABLELEARNPATH."` AS LP
			ORDER BY LP.`rank`";

$lpList = db_query_fetch_all($sql);

// table header
$tool_content .= '<table width="99%" class="LearnPathSum">'."\n"
	.'      <thead>'."\n"
	.'      <tr>'."\n"
	.'        <th>&nbsp;</th>'."\n"
	.'        <th align="left"><div align="left">'.$langLearningPath.'</div></th>'."\n"
	.'        <th colspan="2" width="30%">'.$langProgress.'</th>'."\n"
	.'      </tr>'."\n"
	.'      </thead>'."\n"
	.'      <tbody>'."\n";
if(sizeof($lpList) == 0)
{
$tool_content .= '    <tr>'."\n"
	.'        <td colspan="3" align="center">'.$langNoLearningPath.'</td>'."\n"
	.'      </tr>'."\n";
}
else
{
	// display each learning path with the corresponding progression of the user
	$k=0;
	foreach($lpList as $lpDetails)
	{
		if ($k%2==0) {
	       $tool_content .= "      <tr>";
	    } else {
	       $tool_content .= "      <tr class=\"odd\">";
        }
		$lpProgress = get_learnPath_progress($lpDetails['learnPath_id'],$_GET['uInfo']);
		$tool_content .= ''."\n"
			.'        <td width="1%"><img src="../../template/classic/img/arrow_grey.gif" alt="" border="0" /></td>'."\n"
			.'        <td><a href="detailsUserPath.php?uInfo='.$_GET['uInfo'].'&path_id='.$lpDetails['learnPath_id'].'">'.htmlspecialchars($lpDetails['name']).'</a></td>'."\n"
			.'        <td align="right">'.""
			.disp_progress_bar($lpProgress, 1)
			.'</td>'."\n"
			.'        <td align="left"><small>'.$lpProgress.'%</small></td>'."\n"
			.'      </tr>'."\n";
		$k++;
	}
}
$tool_content .= '      </tbody>'."\n"
            	.'      </table>'."\n";


draw($tool_content, 2, "learnPath", $head_content);

?>
