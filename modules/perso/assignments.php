<?PHP
/*
*
*	File : assignments.php
*
*	Assignments
*
*	The class responsible for filtering out all deadlined due
*	for each lesson the user is subscribed to.
*
*	@author Evelthon Prodromou <eprodromou@upnet.gr>
*
*	@access public
*
*	@version 1.0.1
*
*	@license http://opensource.org/licenses/gpl-license.php GNU General Public License
*
*/



function getUserAssignments($param, $type) {
	//	global $dbname;
	$uid				= $param['uid'];
	$lesson_code		= $param['lesson_code'];
	$max_repeat_val		= $param['max_repeat_val'];
	$lesson_titles		= $param['lesson_titles'];
	$lesson_code		= $param['lesson_code'];
	$lesson_professor	= $param['lesson_professor'];


	for ($i=0;$i<$max_repeat_val;$i++) {

		$assignments_query[$i] = "SELECT assignments.id, title, assignments.description, assignments.deadline
										FROM  ".$lesson_code[$i].".assignments
										WHERE (TO_DAYS(deadline) - TO_DAYS(NOW())) >= '0'
										AND assignments.active = '1'
										ORDER BY deadline";

	}

	//initialise array to store all assignments from all lessons
	$assignGroup = array();

	for ($i=0;$i<$max_repeat_val;$i++) {//each iteration refers to one lesson

		$mysql_query_result = db_query($assignments_query[$i], $lesson_code[$i]);
		$assignments_repeat_val=0;
		while($myAssignments = mysql_fetch_row($mysql_query_result)){

			if ($myAssignments){
				array_push($myAssignments, $lesson_code[$i]);

				if ($submission = findSubmission($uid, $myAssignments[0], $lesson_code[$i])) {
					$lesson_assign[$i][$assignments_repeat_val]['delivered'] = 1; //delivered
					array_push($myAssignments, 1);
				} else {
					$lesson_assign[$i][$assignments_repeat_val]['delivered'] = 0;//not delivered
					array_push($myAssignments, 0);
				}

				array_push($assignGroup, $myAssignments);
			}

		}

	}
	
//	dumpArray($assignGroup);

//	count the number of assignments
//	$countValidAssigns =  count($assignGroup);
//
//	if more than four dont do anything else
//	if zero, try to get five expired to have something to sisplay
//	else get {5-(not expired)} assignments to have at least five to display
//
//	if ($countValidAssigns < 5) {
//
//		for ($i=0;$i<$max_repeat_val;$i++) {
//			query to get expired assignments
//			$assignments_query[$i] = "SELECT assignments.id, title, assignments.description, assignments.deadline
//										FROM  ".$lesson_code[$i].".assignments
//										WHERE (TO_DAYS(deadline) - TO_DAYS(NOW())) < '0'
//										AND assignments.active = '1'
//										ORDER BY deadline";
//
//		}
//
//		$expiredAssignGroup = array();
//		for ($i=0;$i<$max_repeat_val;$i++) {	//each iteration refers to one lesson
//
//			$mysql_query_result = db_query($assignments_query[$i], $lesson_code[$i]);
//			$assignments_repeat_val=0;
//			while($myAssignments = mysql_fetch_row($mysql_query_result)){
//
//				if ($myAssignments){
//					array_push($myAssignments, $lesson_code[$i]);
//
//					if ($submission = findSubmission($uid, $myAssignments[0], $lesson_code[$i])) {
//												$lesson_assign[$i][$assignments_repeat_val]['delivered'] = 1;
//						array_push($myAssignments, 1);//delivered
//					} else {
//						$lesson_assign[$i][$assignments_repeat_val]['delivered'] = 0;
//						array_push($myAssignments, 0);//not delivered
//					}
//
//					array_push($expiredAssignGroup, $myAssignments);
//				}
//
//			}
//
//		}
//	}


	//Sort all expired assignments by date to get the ones that expired recently
//	$expiredAssignGroup = columnSort($expiredAssignGroup, 3);
	//	get {5-(not expired)} assignments to have at least five to display
//	array_slice($expiredAssignGroup,5-count($assignGroup));

	//	$assigns = $assignGroup + $expiredAssignGroup;
//	array_push($assignGroup, $expiredAssignGroup);
	
	if($type == "html") {
		return assignHtmlInterface($assignGroup);
	} elseif ($type == "data") {
		return $assignGroup;
	}
	//If no assignments exists there will be no data.

}

function assignHtmlInterface($data) {
	$assign_content= <<<aCont
	<table width="100%">
		<thead>
			<tr>
				<th>ma8hma</th>
				<th>ergasia</th>
				<th>Dioria</th>
			<tr>
		</thead>
		<tbody>
aCont;
$iterator =  count($data);
for ($i=0; $i < $iterator; $i++){
	$assign_content .= "
		<tr>
			<td>ma8hma??</td>
			<td>".$data[$i][1]."</td>
			<td>".$data[$i][3]."</td>
		</tr>
	";
}

$assign_content .= "
	</tbody></table>
";
return $assign_content;
//	$assign_content .= 
}

function columnSort($unsorted, $column) {
	//bubbleSort
	$sorted = $unsorted;
	for ($i=0; $i < sizeof($sorted)-1; $i++) {
		for ($j=0; $j<sizeof($sorted)-1-$i; $j++)
		if ($sorted[$j][$column] > $sorted[$j+1][$column]) {
			$tmp = $sorted[$j];
			$sorted[$j] = $sorted[$j+1];
			$sorted[$j+1] = $tmp;
		}
	}
	return $sorted;
}

function findSubmission($uid, $id, $lesson_db)
{
	//	$lv = new DBI();
	if (isGroupAssignment($id, $lesson_db))	{
		$gid = getUserGroup($uid, $lesson_db);
		$res = db_query("SELECT id FROM $lesson_db.assignment_submit
								WHERE assignment_id = '$id'
								AND (uid = '$uid' OR group_id = '$gid')", $lesson_db);
	} else {
		$res =db_query("SELECT id FROM $lesson_db.assignment_submit
				WHERE assignment_id = '$id' AND uid = '$uid'", $lesson_db);
	}

	if ($res) {
		$row = mysql_fetch_row($res);
		return $row[0];
	} else {
		return FALSE;
	}

}

// Is this a group assignment?
function isGroupAssignment($id, $lesson_db)
{
	//	$lv = new DBI();
	$res = db_query("SELECT group_submissions FROM $lesson_db.assignments WHERE id = '$id'", $lesson_db);
	if ($res) {
		$row = mysql_fetch_row($res);
		if ($row[0] == 0) {
			return FALSE;
		} else {
			return TRUE;
		}
	} else {
		die("Error: assignment $id doesn't exist");
	}
}

function getUserGroup($uid, $lesson_db)
{
	//	$lv = new DBI();
	$res =db_query("SELECT team FROM $lesson_db.user_group WHERE user = '$uid'", $lesson_db);
	if ($res) {
		$row = mysql_fetch_row($res);
		if ($row[0] == 0) {
			return $row[0];
		} else {
			return FALSE;
		}
	}
}


?>