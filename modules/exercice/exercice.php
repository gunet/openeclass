<?php 
 
 // $Id$
/*
      +----------------------------------------------------------------------+
      | CLAROLINE version 1.4.0 $Revision$                            |
      +----------------------------------------------------------------------+
      | Copyright (c) 2001, 2003 Universite catholique de Louvain (UCL)      |
      +----------------------------------------------------------------------+
      |   This program is free software; you can redistribute it and/or      |
      |   modify it under the terms of the GNU General Public License        |
      |   as published by the Free Software Foundation; either version 2     |
      |   of the License, or (at your option) any later version.             |
      +----------------------------------------------------------------------+
      | Authors: Olivier Brouckaert <oli.brouckaert@skynet.be>               |
      +----------------------------------------------------------------------+
*/

		/*>>>>>>>>>>>>>>>>>>>> EXERCISE LIST <<<<<<<<<<<<<<<<<<<<*/

/**
 * This script shows the list of exercises for administrators and students.
 */

include('exercise.class.php');
include('question.class.php');
include('answer.class.php');
$require_current_course = TRUE;
$langFiles='exercice';

$require_help = TRUE;
$helpTopic = 'Exercise';

include('../../include/init.php');

$nameTools = $langExercices;
begin_page($nameTools);

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
?>

<table border="0" align="center" cellpadding="2" cellspacing="2" width="100%">
<tr>

<?php
if($is_allowedToEdit)
{
?>

  <td width="80%">
	<a href="admin.php"><?php echo $langNewEx; ?></a> |
	<a href="question_pool.php"><?php echo $langQuestionPool; ?></a>
	<!-- | <a href="results.php?exerciseId=<?php echo $exerciseId; ?>"><?php echo $langResults; ?></a>-->
  </td>
  <td width="50%" align="right">

<?php
}
else
{
?>

	<td align="right">

<?php
}

if(isset($page))
{
?>

<small><a href="<?= $PHP_SELF; ?>?page=<?php echo ($page-1); ?>">&lt;&lt; <?= $langPreviousPage; ?></a></small> |

<?php
}
elseif($nbrExercises > $limitExPage)
{
?>
	<small>&lt;&lt; <?= $langPreviousPage; ?> |</small>
<?php
}

if($nbrExercises > $limitExPage)
{
?>

	<small><a href="<?php echo $PHP_SELF; ?>?page=<?php echo ($page+1); ?>"><?php echo $langNextPage; ?> &gt;&gt;</a></small>

<?php
}
elseif(isset($page))
{
?>

	<small><?php echo $langNextPage; ?> &gt;&gt;</small>

<?php
}
?>

  </td>
</tr>
</table>

<table border="0" align="center" cellpadding="2" cellspacing="2" width="100%">

<?php
// shows the title bar only for the administrator
if($is_allowedToEdit)
{

?>

<tr bgcolor="#E6E6E6">
  <td align="center">
	<?php echo $langExerciseName; ?>
  </td>
  <td align="center">
	<?php echo $langModify; ?>
  </td>
  <td align="center">
	<?php echo $langDelete; ?>
  </td>
  <td align="center">
	<?php echo "$langActivate / $langDeactivate"; ?>
  </td>
  <td align="center">
	<?php echo "$langResults"; ?>
  </td>
</tr>

<?php
}

if(!$nbrExercises)
{
?>

<tr>
  <td <?php if($is_allowedToEdit) echo 'colspan="4"'; ?>><?php echo $langNoEx; ?></td>
</tr>

<?php
}

$i=1;

// while list exercises
while($row=mysql_fetch_array($result))
{
?>

<tr>

<?php
	// prof only
	if($is_allowedToEdit)
	{
?>

  <td width="60%">
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
	<tr>
	  <td width="20" align="right"><?php echo ($i+(@$page*$limitExPage)).'.'; ?></td>
	  <td width="1">&nbsp;</td>
	  <td><a href="exercice_submit.php?exerciseId=<?= $row['id']; ?>" <?php if(!$row['active']) echo 'class="invisible"'; ?>><?php echo $row['titre']; ?></a></td>
	</tr>
	</table>
  </td>
  <td width="10%" align="center"><a href="admin.php?exerciseId=<?= $row['id']; ?>"><img src="../../images/edit.gif" border="0" alt="<?php echo htmlspecialchars($langModify); ?>"></a></td>
  <td width="10%" align="center"><a href="<?php echo $PHP_SELF; ?>?choice=delete&exerciseId=<?= $row['id']; ?>" onclick="javascript:if(!confirm('<?php echo addslashes(htmlspecialchars($langConfirmYourChoice)); ?>')) return false;"><img src="../../images/delete.gif" border="0" alt="<?php echo htmlspecialchars($langDelete); ?>"></a></td>

<?php
		// if active
		if($row['active'])
		{

if (isset($page))	
	echo "<td width='20%' align='center'>
	<a href='$PHP_SELF?choice=disable&page=$page&exerciseId=$row[id]'>
	<img src='../../images/visible.gif' border='0' alt='htmlspecialchars($langDeactivate)'></a></td>";
else
	echo "<td width='20%' align='center'>
	<a href='$PHP_SELF?choice=disable&exerciseId=$row[id]'>
	<img src='../../images/visible.gif' border='0' alt='htmlspecialchars($langDeactivate)'></a></td>";

}
// else if not active
else
{

if (isset($page))
	echo "<td width='20%' align='center'>
	<a href='$PHP_SELF?choice=enable&page=$page&exerciseId=$row[id]'>
	<img src='../../images/invisible.gif' border='0' alt='htmlspecialchars($langActivate)'></a></td>";
else
	echo "<td width='20%' align='center'>
	<a href='$PHP_SELF?choice=enable&exerciseId=$row[id]'>
	<img src='../../images/invisible.gif' border='0' alt='htmlspecialchars($langActivate)'></a></td>";

}
?>


<?php
	echo "<td width='20%' align='center'>
	<a href='results.php?&exerciseId=$row[id]'>
	<img src='../../images/invisible.gif' border='0' alt='htmlspecialchars($langActivate)'></a></td></tr>";
	}
	// student only
	else
	{
?>

  <td width="100%">
	<table border="0" cellpadding="0" cellspacing="0" width="100%">
	<tr>
	  <td width="20" align="right"><?php echo @($i+($page*$limitExPage)).'.'; ?></td>
	  <td width="1">&nbsp;</td>
	  <td>
<?php
	$CurrentDate = time();
//	$CurrentDate = date("Y-m-d H:i:s");
//	echo "CurrentDate = ".$CurrentDate."<br>\n";
//	echo substr($CurrentDate, 11,2)."|".
//		substr($CurrentDate, 14,2)."|".
//		substr($CurrentDate, 17,2)."|".
//		substr($CurrentDate, 5,2)."|".
//		substr($CurrentDate, 8,2)."|".
//		substr($CurrentDate, 0,4)."<br><br>";
//	$CurrentDate = mktime(substr($CurrentDate, 11,2),substr($CurrentDate, 14,2),substr($CurrentDate, 17,2),substr($CurrentDate, 5,2),substr($CurrentDate, 8,2),substr($CurrentDate, 0,4));
	//echo "temp_StartDate = ".$row['StartDate']."<br>\n";
	//echo "temp_EndDate = ".$row['EndDate']."<br>\n";
	$temp_StartDate = mktime(substr($row['StartDate'], 11,2),substr($row['StartDate'], 14,2),substr($row['StartDate'], 17,2),substr($row['StartDate'], 5,2),substr($row['StartDate'], 8,2),substr($row['StartDate'], 0,4));
	$temp_EndDate = mktime(substr($row['EndDate'], 11,2),substr($row['EndDate'], 14,2),substr($row['EndDate'], 17,2),substr($row['EndDate'], 5,2),substr($row['EndDate'], 8,2),substr($row['EndDate'], 0,4));
//	echo "CurrentDate = ".$CurrentDate."<br>\n";
//	echo "temp_StartDate = ".$temp_StartDate."<br>\n";
//	echo "temp_EndDate = ".$temp_EndDate."<br>\n";
	if (($CurrentDate >= $temp_StartDate) && ($CurrentDate < $temp_EndDate)) {
?>
	  <a href="exercice_submit.php?exerciseId=<?= $row['id']; ?>"><?=  $row['titre']; ?></a>
<?php
	} else {
?>
		<?=  $row['titre']; ?>
<?php
	}
?>  
	  <?php echo "<br>".$langExerciseStart; ?>: <?=  $row['StartDate']; ?>
	  <?php echo "<br>".$langExerciseEnd; ?>: <?=  $row['EndDate']; ?>
	  <?php 
	  if ($row['TimeConstrain']>0)
	  	echo "<br>".$langExerciseConstrain.": ".$row['TimeConstrain']." ".$langExerciseConstrainUnit;; 
	  if ($row['AttemptsAllowed']>0)	
	   echo "<br>".$langExerciseAttemptsAllowed.": ".$row['AttemptsAllowed']; 
	  
	  
	  ?>
	  </td>
	</tr>
	</table>
  </td>
</tr>

<?php
	}

	// skips the last exercise, that is only used to know if we have or not to create a link "Next page"
	if($i == $limitExPage)
	{
		break;
	}

	$i++;
}	// end while()
?>

</table>

<?php
/*****************************************/
/* Exercise Results (uses tracking tool) */
/*****************************************/

// if tracking is enabled
if(isset($is_trackingEnabled)):
?>

<br><br>

<table cellpadding="2" cellspacing="2" border="0" width="80%">
<tr bgcolor="#E6E6E6" align="center">
  <td width="50%"><?php echo $langExercice; ?></td>
  <td width="30%"><?php echo $langDate; ?></td>
  <td width="20%"><?php echo $langResult; ?></td>
</tr>

<?php
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

?>
<tr>
  <td class="content"><?php echo $results[$i][0]; ?></td>
  <td class="content" align="center"><small><?php echo strftime($dateTimeFormatLong,$results[$i][3]); ?></small></td>
  <td class="content" align="center"><?php echo $results[$i][1]; ?> / <?php echo $results[$i][2]; ?></td>
</tr>

<?php
	}
}
else
{
?>

<tr>
  <td colspan="3"><?php echo $langNoResult; ?></td>
</tr>

<?php
}
?>

</table>

<?php
endif; // end if tracking is enabled

?>
