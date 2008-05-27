<?PHP
/**===========================================================================
*              GUnet e-Class 2.0
*       E-learning and Course Management Program
* ===========================================================================
*	Copyright(c) 2003-2006  Greek Universities Network - GUnet
*	A full copyright notice can be read in "/info/copyright.txt".
*
*  Authors:	Costas Tsibanis <k.tsibanis@noc.uoa.gr>
*				Yannis Exidaridis <jexi@noc.uoa.gr>
*				Alexandros Diamantidis <adia@noc.uoa.gr>
*
*	For a full list of contributors, see "credits.txt".
*
*	This program is a free software under the terms of the GNU
*	(General Public License) as published by the Free Software
*	Foundation. See the GNU License for more details.
*	The full license can be read in "license.txt".
*
*	Contact address: 	GUnet Asynchronous Teleteaching Group,
*					Network Operations Center, University of Athens,
*					Panepistimiopolis Ilissia, 15784, Athens, Greece
*					eMail: eclassadmin@gunet.gr
============================================================================*/

/**
 * Personalised Assignments Component, e-Class Personalised
 * 
 * @author Evelthon Prodromou <eprodromou@upnet.gr>
 * @version $Id$
 * @package e-Class Personalised
 * 
 * @abstract This component populates the assignments block on the user's personalised 
 * interface. It is based on the diploma thesis of Evelthon Prodromou.
 *
 */

/**
 * Function getUserAssignments
 * 
 * Populates an array with data regarding the user's personalised assignments
 *
 * @param array $param
 * @param string $type (data, html)
 * @return array
 */
function getUserAssignments($param, $type) {
	global $mysqlMainDb;
	$uid	= $param['uid'];
	$lesson_code	= $param['lesson_code'];
	$max_repeat_val	= $param['max_repeat_val'];
	$lesson_titles	= $param['lesson_titles'];
	$lesson_code	= $param['lesson_code'];
	$lesson_professor	= $param['lesson_professor'];

	for ($i=0;$i<$max_repeat_val;$i++) {
		$assignments_query[$i] = "SELECT DISTINCT assignments.id, assignments.title, 
		assignments.description, assignments.deadline, 
		cours.intitule,(TO_DAYS(assignments.deadline) - TO_DAYS(NOW())) AS days_left
		FROM  ".$lesson_code[$i].".assignments, ".$mysqlMainDb.".cours, ".$lesson_code[$i].".accueil
		WHERE (TO_DAYS(deadline) - TO_DAYS(NOW())) >= '0'
		AND assignments.active = 1 AND cours.code = '".$lesson_code[$i]."'
		AND ".$lesson_code[$i].".accueil.visible =1
		AND ".$lesson_code[$i].".accueil.id =5 ORDER BY deadline";
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

	$assignGroup = columnSort($assignGroup, 3);
	if($type == "html") {
		return assignHtmlInterface($assignGroup);
	} elseif ($type == "data") {
		return $assignGroup;
	}

}

/**
 * Function assignHtmlInterface
 * 
 * Generates html content for the assignments block of e-class personalised.
 *
 * @param array $data
 * @return string HTML content for the assignments block
 * @see getUserAssignments()
 */
function assignHtmlInterface($data) {
	global  $langCourse, $langAssignment, $langDeadline, $langNoAssignmentsExist;
	$assign_content = "";
	$iterator =  count($data);

	if ($iterator > 0) {
		$assign_content .= <<<aCont
	
	<table  width="100%" class="assign">
		<thead>
		<tr><th class="assign">$langCourse</th>
		<th class="assign">$langAssignment</th>
		<th class="assign">$langDeadline</th>
		</tr></thead><tbody>
aCont;

		for ($i=0; $i < $iterator; $i++){

			if($data[$i][7] == 1) {
				$class = "class=\"tick\"";
			} elseif ($data[$i][5] < 3) {
				$class = "class=\"exclamation\"";
			} else {
				$class = "";
			}
			$url = $_SERVER['PHP_SELF'] . "?perso=1&c=" .$data[$i][6]."&i=".$data[$i][0];
			$assign_content .= "<tr>
			<td class=\"assign\"><p>".$data[$i][4]."</p></td>			
			<td class=\"assign\">
			<div id=\"assigncontainer\">
			<ul id=\"assignlist\">
			<li><a $class href=\"$url\"><div class=\"assign_pos\">".$data[$i][1]."</div>
			</a></li></ul>
			</div> 
			</td>
			<td class=\"assign\"><p>".$data[$i][3]."</p></td></tr>";
		}
		$assign_content .= "</tbody></table>";
	} else {
		$assign_content .= "<p>$langNoAssignmentsExist</p>";
	}
	return $assign_content;
}

/**
 * Function columnSort
 *
 * Sorts an array by one of it's columns specified by $column
 * 
 * @param array $unsorted
 * @param mixed $column (array dimension to sort)
 * @return array sorted $unsorted
 */
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

/**
 * Function findSubmission
 *
 *  Gets the id of an assignments
 * 
 * @param int $uid
 * @param int $id
 * @param string $lesson_db
 * @return mixed (assignment id if true, else false)
 */
function findSubmission($uid, $id, $lesson_db) {

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

/**
 * Function isGroupAssignment
 *
 * Checks if an assignments is a group assignment
 * Returns true if it is.
 * 
 * @param int $id
 * @param string $lesson_db
 * @return boolean
 */
function isGroupAssignment($id, $lesson_db) {
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

/**
 * Function getUserGroup
 *
 * Returns the user's group he is enrolled at, false otherwise
 * 
 * @param int $uid
 * @param string $lesson_db
 * @return mixed 
 */
function getUserGroup($uid, $lesson_db) {

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
