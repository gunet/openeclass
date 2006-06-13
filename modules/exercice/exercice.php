<?php 
 
/*=============================================================================
       	GUnet e-Class 2.0 
        E-learning and Course Management Program  
================================================================================
       	Copyright(c) 2003-2006  Greek Universities Network - GUnet
        Á full copyright notice can be read in "/info/copyright.txt".
        
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
	exercise.php
	@last update: 17-4-2006 by Costas Tsibanis
	@authors list: Dionysios G. Synodinos <synodinos@gmail.com>
==============================================================================        
        @Description: Main script for the exercise tool

 	This is a tool plugin that allows course administrators - or others with the
 	same rights

 	The user can : - navigate through files and directories.
                       - upload a file
                       - delete, copy a file or a directory
                       - edit properties & content (name, comments, 
			 html content)

 	@Comments: The script is organised in four sections.

 	1) Execute the command called by the user
           Note (March 2004) some editing functions (renaming, commenting)
           are moved to a separate page, edit_document.php. This is also
           where xml and other stuff should be added.
   	2) Define the directory to display
  	3) Read files and directories from the directory defined in part 2
  	4) Display all of that on an HTML page
 
  	@TODO: eliminate code duplication between document/document.php, scormdocument.php
==============================================================================
*/

include('exercise.class.php');
include('question.class.php');
include('answer.class.php');
$require_current_course = TRUE;
$langFiles='exercice';

$require_help = TRUE;
$helpTopic = 'Exercise';

//include('../../include/init.php');

include '../../include/baseTheme.php';

$tool_content = "";

$nameTools = $langExercices;
//begin_page($nameTools);

/*******************************/
/* Clears the exercise session */
/*******************************/

if(session_is_registered('objExercise'))	{ session_unregister('objExercise');	}
if(session_is_registered('objQuestion'))	{ session_unregister('objQuestion');	}
if(session_is_registered('objAnswer'))		{ session_unregister('objAnswer');		}
if(session_is_registered('questionList'))	{ session_unregister('questionList');	}
if(session_is_registered('exerciseResult'))	{ session_unregister('exerciseResult');	}

$is_allowedToEdit=$is_adminOfCourse;

$TBL_EXERCICE_QUESTION='exercice_question';
$TBL_EXERCICES='exercices';
$TBL_QUESTIONS='questions';

// maximum number of exercises on a same page
$limitExPage=50;

// defines answer type for previous versions of Claroline, may be removed in Claroline 1.5
$sql="UPDATE `$TBL_QUESTIONS` SET q_position='1',type='2' WHERE q_position IS NULL OR q_position<'1' OR type='0'";
db_query($sql,$currentCourseID);

// selects $limitExPage exercises at the same time
@$from=$page*$limitExPage;

// only for administrator
if($is_allowedToEdit)
{
	if(!empty($choice))
	{
		// construction of Exercise
		$objExerciseTmp=new Exercise();

		if($objExerciseTmp->read($exerciseId))
		{
			switch($choice)
			{
				case 'delete':	// deletes an exercise
								$objExerciseTmp->delete();

								break;
				case 'enable':  // enables an exercise
								$objExerciseTmp->enable();
								$objExerciseTmp->save();

								break;
				case 'disable': // disables an exercise
								$objExerciseTmp->disable();
								$objExerciseTmp->save();

								break;
			}
		}

		// destruction of Exercise
		unset($objExerciseTmp);
	}

	$sql="SELECT id,titre,type,active FROM `$TBL_EXERCICES` ORDER BY id LIMIT $from,".($limitExPage+1);
	$result=db_query($sql,$currentCourseID);
}
// only for students
else
{
	$sql="SELECT id,titre,type,StartDate,EndDate,TimeConstrain,AttemptsAllowed ".
		"FROM `$TBL_EXERCICES` WHERE active='1' ORDER BY id LIMIT $from,".($limitExPage+1);
	$result=mysql_query($sql) or die("Error : SELECT at line ".__LINE__);
}

$nbrExercises=mysql_num_rows($result);


$tool_content .= "<table border=\"0\" align=\"center\" cellpadding=\"2\" cellspacing=\"2\" width=\"100%\"><tr>";


if($is_allowedToEdit)
{

  $tool_content .= <<<cData
  	<td width="80%">
		<a href="admin.php">${langNewEx}</a> |
	<a href="question_pool.php">${langQuestionPool}</a>
  </td>
  <td width="50%" align="right">
cData;

}
else 
{ 
	$tool_content .= "<td align=\"right\">";
}

if(isset($page))
{

	$page_temp = $page-1;
	$tool_content .= <<<cData
		<small><a href="${PHP_SELF}?page=${page_temp}">
	&lt;&lt; ${langPreviousPage}</a></small> |
cData;

}
elseif($nbrExercises > $limitExPage)
{
	$tool_content .= "<small>&lt;&lt; ${langPreviousPage} |</small>";
}

if($nbrExercises > $limitExPage)
{

	$page_temp = $page+1;
	$tool_content .= <<<cData
	<small><a href="${PHP_SELF}?page=${page_temp}>">${langNextPage} &gt;&gt;</a></small>
cData;

}
elseif(isset($page))
{
	$tool_content .= "<small>${langNextPage} &gt;&gt;</small>";
}

$tool_content .= <<<cData
	</td>
	</tr>
	</table>
	<table border="0" align="center" cellpadding="2" cellspacing="2" width="100%">
cData;

// shows the title bar only for the administrator
if($is_allowedToEdit)
{

$tool_content .= <<<cData
	<tr bgcolor="#E6E6E6">
	  <td align="center">
			${langExerciseName}
	  </td>
	  <td align="center">
			${langModify}
	  </td>
	  <td align="center">
			${langDelete}
	  </td>
	  <td align="center">
			${langActivate} / ${langDeactivate}
	  </td>
	  <td align="center">
			${langResults}
	  </td>
	</tr>
cData;

}

if(!$nbrExercises)
{

	$tool_content .= "<tr><td";
	if($is_allowedToEdit) 
		$tool_content .= " colspan=\"4\"";
	$tool_content .= ">${langNoEx}</td></tr>";
}

$i=1;

// while list exercises
while($row=mysql_fetch_array($result))
{

$tool_content .= "<tr>";

	// prof only
	if($is_allowedToEdit)
	{
	$page_temp = ($i+(@$page*$limitExPage)).'.';
  $tool_content .= <<<cData
	  <td width="60%">
		<table border="0" cellpadding="0" cellspacing="0" width="100%">
		<tr>
		<td width="20" align="right">${page_temp}</td>
		  <td width="1">&nbsp;</td>
		  <td><a href="exercice_submit.php?exerciseId=${row['id']}" 
cData;

	if(!$row['active']) 
		$tool_content .= "class=\"invisible\""; 
	$tool_content .= ">".$row['titre']."</a></td>";
	
	$langModify_temp = htmlspecialchars($langModify);
	$langConfirmYourChoice_temp = addslashes(htmlspecialchars($langConfirmYourChoice));
	$langDelete_temp = htmlspecialchars($langDelete);
	$tool_content .= <<<cData
		</tr></table></td>
	  <td width="10%" align="center"><a href="admin.php?exerciseId=${row['id']}">
	  <img src="../../images/edit.gif" border="0" alt="${langModify_temp}"></a></td>
	  <td width="10%" align="center">
	  <a href="${PHP_SELF}?choice=delete&exerciseId=${row['id']}" 
	  onclick="javascript:if(!confirm('${langConfirmYourChoice_temp}')) return false;">
	  <img src="../../images/delete.gif" border="0" alt="${langDelete_temp}"></a></td>
cData;

		// if active
		if($row['active'])
		{

			if (isset($page))	
				$tool_content .= "<td width='20%' align='center'>".
					"<a href=\"${PHP_SELF}?choice=disable&page=${page}&exerciseId=".$row[id]."\">".
					"<img src=\"../../images/visible.gif\" border=\"0\" alt=\"".htmlspecialchars($langDeactivate)."\"></a></td>";
			else
				$tool_content .= "<td width='20%' align='center'><a href='${PHP_SELF}?choice=disable&exerciseId=".$row[id]."'>".
					"<img src='../../images/visible.gif' border='0' alt='".htmlspecialchars($langDeactivate)."'></a></td>";
}
// else if not active
else
{
	if (isset($page))
		$tool_content .= "<td width='20%' align='center'>".
			"<a href='${PHP_SELF}?choice=enable&page=${page}&exerciseId=".$row[id]."'>".
			"<img src='../../images/invisible.gif' border='0' alt='".htmlspecialchars($langActivate)."'></a></td>";
	else
		$tool_content .= "<td width='20%' align='center'>".
			"<a href='${PHP_SELF}?choice=enable&exerciseId=".$row[id]."'>".
			"<img src='../../images/invisible.gif' border='0' alt='".htmlspecialchars($langActivate)."'></a></td>";
}

	$tool_content .= "<td width=\"20%\" align=\"center\">	<nobr><a href=\"results.php?&exerciseId=".$row[id]."\">".
	$langExerciseScores1."</a> | <a href=\"csv.php?&exerciseId=".$row[id]."\">".$langExerciseScores3."</a></nobr></td></tr>";
	}
	// student only
	else
	{
	$page_offset_temp = @($i+($page*$limitExPage)).'.';
	$tool_content .= <<<cData
	  <td width="100%">
		<table border="0" cellpadding="0" cellspacing="0" width="100%">
		<tr>
			<td width="20" align="right">${page_offset_temp}</td>
		  <td width="1">&nbsp;</td>
		  <td>
cData;

	$CurrentDate = time();

	$temp_StartDate = mktime(substr($row['StartDate'], 11,2),substr($row['StartDate'], 14,2),substr($row['StartDate'], 17,2),substr($row['StartDate'], 5,2),substr($row['StartDate'], 8,2),substr($row['StartDate'], 0,4));
	$temp_EndDate = mktime(substr($row['EndDate'], 11,2),substr($row['EndDate'], 14,2),substr($row['EndDate'], 17,2),substr($row['EndDate'], 5,2),substr($row['EndDate'], 8,2),substr($row['EndDate'], 0,4));
	if (($CurrentDate >= $temp_StartDate) && ($CurrentDate < $temp_EndDate)) {
		$tool_content .= "<a href=\"exercice_submit.php?exerciseId=".$row['id']."\">".$row['titre']."</a>";
	} else {
		$tool_content .= $row['titre'];
	}
	  $tool_content .= "<br>".$langExerciseStart.": ".$row['StartDate'].
	  	"<br>".$langExerciseEnd.": ".$row['EndDate'];
	  if ($row['TimeConstrain']>0)
	  	$tool_content .= "<br>".$langExerciseConstrain.": ".$row['TimeConstrain']." ".$langExerciseConstrainUnit;
	  if ($row['AttemptsAllowed']>0)	
	   $tool_content .= "<br>".$langExerciseAttemptsAllowed.": ".$row['AttemptsAllowed']; 
	  
	  $tool_content .= "</td></tr></table></td></tr>";

	}

	// skips the last exercise, that is only used to know if we have or not to create a link "Next page"
	if($i == $limitExPage)
	{
		break;
	}

	$i++;
}	// end while()

	$tool_content .= "</table>";

/*****************************************/
/* Exercise Results (uses tracking tool) */
/*****************************************/

// if tracking is enabled
if(isset($is_trackingEnabled)):

	$tool_content .= <<<cData
		<br><br>

		<table cellpadding="2" cellspacing="2" border="0" width="80%">
		<tr bgcolor="#E6E6E6" align="center">
		  <td width="50%">${langExercice}</td>
		  <td width="30%">${langDate}</td>
		  <td width="20%">${langResult}</td>
		</tr>
cData;

$sql="SELECT `ce`.`titre`, `te`.`exe_result` , `te`.`exe_weighting`, UNIX_TIMESTAMP(`te`.`exe_date`)
      FROM `$TBL_EXERCICES` AS ce , `$TBL_TRACK_EXERCICES` AS te
      WHERE `te`.`exe_user_id` = '$_uid'
      AND `te`.`exe_exo_id` = `ce`.`id`
      ORDER BY `te`.`exe_cours_id` ASC, `ce`.`titre` ASC, `te`.`exe_date`ASC";

$results=getManyResultsXCol($sql,4);

if(is_array($results))
{
	for($i = 0; $i < sizeof($results); $i++)
	{

/////////////////////////////////////////////////////////////////////////////////
	$date_strftime = strftime($dateTimeFormatLong,$results[$i][3]);
	$tool_content .= "<tr><td class=\"content\">".$results[$i][0]."></td>".
  	"<td class=\"content\" align=\"center\"><small>".$date_strftime."</small></td>".
  	"<td class=\"content\" align=\"center\">".$results[$i][1]." / ".$results[$i][2]."</td></tr>";

/////////////////////////////////////////////////////////////////////////////////
	}
}
else
	$tool_content .= "<tr><td colspan=\"3\">${langNoResult}</td></tr>";

$tool_content .= "</table>";

endif; // end if tracking is enabled
draw($tool_content, 2);
?>