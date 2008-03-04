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

include('exercise.class.php');
include('question.class.php');
include('answer.class.php');
$require_current_course = TRUE;

$require_help = TRUE;
$helpTopic = 'Exercise';
$guest_allowed = true;

include '../../include/baseTheme.php';

/**** The following is added for statistics purposes ***/
include('../../include/action.php');
$action = new action();
$action->record('MODULE_ID_EXERCISE');

$tool_content = "";
$nameTools = $langExercices;

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
	$result=db_query($sql);
}

$nbrExercises=mysql_num_rows($result);

if($is_allowedToEdit) {
	$tool_content .= "
      <div id=\"operations_container\">
        <ul id=\"opslist\">\n";

  $tool_content .= <<<cData
          <li><a href="admin.php">${langNewEx}</a> |<a href="question_pool.php">${langQuestionPool}</a></li>
cData;

$tool_content .= "
        </ul>
      </div>";
} else  { 
	$tool_content .= "<!--<td align=\"right\">-->";
}

if(isset($page)) {
	$tool_content .= <<<cData
		<small><a href="$_SERVER[PHP_SELF]?page=$page-1">
	&lt;&lt; ${langPrevious}</a></small> |
cData;
}
elseif($nbrExercises > $limitExPage) {
	$tool_content .= "<small>&lt;&lt; ${langPrevious} |</small>";
}

if($nbrExercises > $limitExPage) {

	$tool_content .= <<<cData
	<small><a href="${PHP_SELF}?page=$page+1>">${langNext} &gt;&gt;</a></small>
cData;

} elseif(isset($page)) {
	$tool_content .= "<small>${langNext} &gt;&gt;</small>";
}

$tool_content .= <<<cData

      <br>

      <table align="left" width="99%">
      <thead>
      <tr>

cData;

// shows the title bar only for the administrator
if($is_allowedToEdit) {

$tool_content .= <<<cData
        <th>${langExerciseName}</th>
        <th>${langModify}</th>
        <th>${langDelete}</th>
        <th>${langActivate} / ${langDeactivate}</th>
        <th>${langResults}</th>
      </tr>
      </thead>
cData;

} else {

// student view
$tool_content .= <<<cData

        <th>$langExerciseName</th>
        <th width='150'>$langExerciseStart</th>
        <th width='150'>$langExerciseEnd</th>
        <th width='50'>$langExerciseConstrain</th>
        <th width='50'>$langExerciseAttemptsAllowed</th>
      </tr>
      </thead>
cData;

}

if(!$nbrExercises) {
	$tool_content .= "
      <tr>
        <td";
	if($is_allowedToEdit) 
		$tool_content .= " colspan=\"4\"";
		$tool_content .= ">${langNoEx}</td>
      </tr>";
}

$i=1;

// while list exercises
while($row=mysql_fetch_array($result))
{

$tool_content .= "
      <tr>";

	// prof only
	if($is_allowedToEdit)
	{
	$page_temp = ($i+(@$page*$limitExPage)).'.';
  $tool_content .= <<<cData
  
        <td width="60%">${page_temp}&nbsp;<a href="exercice_submit.php?exerciseId=${row['id']}" 
cData;

	if(!$row['active']) 
		$tool_content .= "class=\"invisible\""; 
	$tool_content .= ">".$row['titre']."</a></td>";
	
	$langModify_temp = htmlspecialchars($langModify);
	$langConfirmYourChoice_temp = addslashes(htmlspecialchars($langConfirmYourChoice));
	$langDelete_temp = htmlspecialchars($langDelete);
	$tool_content .= <<<cData

        <td width="10%" align="center"><a href="admin.php?exerciseId=${row['id']}"><img src="../../template/classic/img/edit.gif" border="0" alt="${langModify_temp}"></a></td>
        <td width="10%" align="center"><a href="$_SERVER[PHP_SELF]?choice=delete&exerciseId=${row['id']}" 
        onclick="javascript:if(!confirm('${langConfirmYourChoice_temp}')) return false;"><img src="../../template/classic/img/delete.gif" border="0" alt="${langDelete_temp}"></a></td>
cData;

		// if active
		if($row['active'])
		{

	if (isset($page))	
				$tool_content .= "
        <td width='20%' align='center'>"."<a href=\"$_SERVER[PHP_SELF]?choice=disable&page=${page}&exerciseId=".$row['id']."\">"."<img src=\"../../template/classic/img/invisible.gif\" border=\"0\" alt=\"".htmlspecialchars($langDeactivate)."\"></a></td>";
			else
				$tool_content .= "
        <td width='20%' align='center'><a href='$_SERVER[PHP_SELF]?choice=disable&exerciseId=".$row['id']."'>"."<img src='../../template/classic/img/invisible.gif' border='0' alt='".htmlspecialchars($langDeactivate)."'></a></td>";
}
// else if not active
else
{
	if (isset($page))
		$tool_content .= "
        <td width='20%' align='center'>"."<a href='$_SERVER[PHP_SELF]?choice=enable&page=${page}&exerciseId=".$row['id']."'>"."<img src='../../template/classic/img/visible.gif' border='0' alt='".htmlspecialchars($langActivate)."'></a></td>";
	else
		$tool_content .= "
        <td width='20%' align='center'>"."<a href='$_SERVER[PHP_SELF]?choice=enable&exerciseId=".$row['id']."'>"."<img src='../../template/classic/img/visible.gif' border='0' alt='".htmlspecialchars($langActivate)."'></a></td>";
}


$eid = $row['id'];
$NumOfResults = mysql_fetch_array(db_query("SELECT COUNT(*) FROM exercise_user_record WHERE eid='$eid'", $currentCourseID));

if ($NumOfResults[0]) {
	$tool_content .= "
        <td width=\"20%\" align=\"center\"><nobr><a href=\"results.php?&exerciseId=".$row['id']."\">".
	$langExerciseScores1."</a> | <a href=\"csv.php?&exerciseId=".$row['id']."\">".$langExerciseScores3."</a></nobr></td>
      </tr>";
} else {
	$tool_content .= "
        <td width=\"20%\" align=\"center\">	</td>
      </tr>";
}
	}
	// student only
	else {
	$page_offset_temp = @($i+($page*$limitExPage)).'.';
	$CurrentDate = time();

	$temp_StartDate = mktime(substr($row['StartDate'], 11,2),substr($row['StartDate'], 14,2),substr($row['StartDate'], 17,2),substr($row['StartDate'], 5,2),substr($row['StartDate'], 8,2),substr($row['StartDate'], 0,4));
	$temp_EndDate = mktime(substr($row['EndDate'], 11,2),substr($row['EndDate'], 14,2),substr($row['EndDate'], 17,2),substr($row['EndDate'], 5,2),substr($row['EndDate'], 8,2),substr($row['EndDate'], 0,4));
	if (($CurrentDate >= $temp_StartDate) && ($CurrentDate < $temp_EndDate)) {
		$tool_content .= "
      <tr>
        <td><a href=\"exercice_submit.php?exerciseId=".$row['id']."\">".$row['titre']."</a>";
	} else {
		$tool_content .= $row['titre'];
	}
	  $tool_content .= "</td>
        <td align='center'>$row[StartDate]</td><td align='center'>$row[EndDate]</td>";
	  if ($row['TimeConstrain']>0)
		  	$tool_content .= "
        <td>$row[TimeConstrain] $langExerciseConstrainUnit</td>";
		else 	
				$tool_content .= "
        <td align='center'> - </td>";
	  if ($row['AttemptsAllowed']>0)	
		   $tool_content .= "
        <td align='center'>$row[AttemptsAllowed]</td>"; 
		else
			 $tool_content .= "
        <td align='center'> - </td>";
	  
	  $tool_content .= "
      </tr>";
	}

	// skips the last exercise, that is only used to know if we have or not to create a link "Next page"
	if ($i == $limitExPage) {
		break;
	}

	$i++;
}	// end while()

$tool_content .= "
      </table>";
draw($tool_content, 2);
?>
